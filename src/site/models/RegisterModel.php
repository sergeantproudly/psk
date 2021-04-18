<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 19-Jun-18
 * Time: 9:45 AM
 */

namespace Site\Models;


use Engine\Library\Common;
use Engine\Library\Model;
use Engine\Library\UserSession;

class RegisterModel extends Model {
  // Ключ к имени таблицы, использующийся в конструкторе родителя
  const TABLE_KEY = 'register';

  public function getAll($sort = '', $direction = '', $count = 0, $offset = 0) {
    $selectQuery = "SELECT register.*, SUBSTR(register.Title, 9) AS Num, contractor.Title AS Contractor, branchOffice.Title AS BranchOffice 
      FROM {$this->table} AS register 
      LEFT JOIN {$this->tables['register-company']} AS contractor 
        ON register.ContractorId = contractor.Id
      LEFT JOIN {$this->tables['register-company']} AS branchOffice 
        ON register.BranchOfficeId = branchOffice.Id";

    return $this->_getForCurrentUser($selectQuery, null, $sort, $direction, $count, $offset);
  }

  function getAllLike ($like, $sort, $direction, $count = 0, $offset = 0) {
    $selectQuery =
      "SELECT register.*, SUBSTR(register.Title, 9) AS Num, contractor.Title AS Contractor, branchOffice.Title AS BranchOffice 
      FROM {$this->table} AS register 
      LEFT JOIN {$this->tables['register-company']} AS contractor 
        ON register.ContractorId = contractor.Id
      LEFT JOIN {$this->tables['register-company']} AS branchOffice 
        ON register.BranchOfficeId = branchOffice.Id";
    
    $likePattern = "%{$like}%";
    $likeCondition = $this->db->parse("AND (register.Title LIKE ?s OR register.Object LIKE ?s)", $likePattern, $likePattern);

    return $this->_getForCurrentUser($selectQuery, $likeCondition, $sort, $direction, $count, $offset);
  }

  public function getRecords($registerNumber, $shouldMatch = null) {
    $registerId = $this->getRegisterIdFromNumber($registerNumber);
    $condition = 'RegisterId = ?i';
    if ($shouldMatch) {
      $shouldMatch = "%{$shouldMatch}%";
      $condition .= $this->db->parse(
        " AND (
          `Mnemocode` LIKE ?s OR 
          `ConsignmentNumber` LIKE ?s OR 
          `ItemName` LIKE ?s
        )",
        $shouldMatch, 
        $shouldMatch, 
        $shouldMatch
      );
    } 
    $records = $this->table('register-record')->getAllWhere($condition, $registerId);
    $record['ConsignmentDate'] = $this->_loopFormatDate($records, 'ConsignmentDate');
    return $records;
  }

  public function getRegisterIdFromCode ($code) {
    $id = $this->db->getOne("SELECT `Id` FROM {$this->tables['register']} WHERE `Code` = ?s", $code);
//    var_dump("Register ID: $id");
    return $id;
  }

  public function getRegisterIdFromNumber ($number) {
    $id = $this->db->getOne("SELECT `Id` FROM {$this->tables['register']} WHERE `Title` = ?s", $number);
//    var_dump("Register ID: $id");
    return $id;
  }

  public function getRegisterFromNumber ($number) {
    $register = $this->db->getRow("SELECT * FROM {$this->tables['register']} WHERE `Title` = ?s", $number);
    return $register;
  }

  public function getRegisterNumberFromCode ($code) {
    /**
     * NOTE: Column with register number was called `Title` 
     * instead of `Number` because of the mycms engine 
     * inner table in admin panel can not be reached 
     * if we do not have `Name` or `Title` column 
     * (link to inner table is not attached to any 
     * other column except for the mentioned two)
     */ 
    $number = $this->db->getOne("SELECT `Title` FROM {$this->tables['register']} WHERE `Code` = ?s", $code);
    return $number;
  }

  public function getCountItems ($likePattern = null) {
    $selectQuery = "SELECT COUNT(register.Id) as Count
      FROM {$this->tables['register']} AS register";

    $likeCondition = '';
    if ($likePattern) {
      $likePattern = "%{$likePattern}%";
      $likeCondition = $this->db->parse("AND (`Title` LIKE ?s OR `Object` LIKE ?s)", $likePattern, $likePattern);
    }

    $count = $this->_getForCurrentUser(
      $selectQuery, $likeCondition, null, null);
    $count = $count[0]['Count'];

    return $count;
  }

  private function _getForCurrentUser($selectQuery, $likeCondition = '', $sort = '', $direction = '', $count = 0, $offset = 0) {
    $userSession = new UserSession;
    $userId = $userSession->id();

    $userModel = new UserModel($this->getDB());
    $user = $userModel->getUserById($userId);

    $condition = "WHERE `Published` = TRUE AND (
      `ContractorId` = {$user['CompanyId']} OR 
      `BranchOfficeId` = {$user['CompanyId']} OR
      `ContractorId` IN (SELECT `ContractorId` FROM {$this->tables['user-to-contractor']} WHERE `UserId` = {$userId}) OR
      'BranchOfficeId' IN (SELECT `ContractorId` FROM {$this->tables['user-to-contractor']} WHERE `UserId` = {$userId})
    )";

    if ($likeCondition) {
      $condition .= " {$likeCondition}";
    }

    if ($sort) {
      $condition .= " ORDER BY `{$sort}` {$direction}";
    } else {
      $condition .= " ORDER BY `Date` DESC";
    }

    $query = $selectQuery . " " . $condition;

    return $this->_getByQuery($query, $count, $offset);
  }

  private function _getByQuery($query, $count = 0, $offset = 0) {
    $register = $this->_getAllByQuery($query, $count, $offset);
    
    if ($register) {
      $register = Common::setLinks($register, 'register', false, 'Title');
      $register = $this->_loopFormatDate($register, 'Date');
    }
    return $register;
  }

  public function add ($registerData) {
    $title = $registerData['Title'];
    $contractorId = $registerData['ContractorId'];
    $object = $registerData['Object'];
    $branchOfficeId = $registerData['BranchOfficeId'];
    $date = $registerData['Date'];

    $published = $registerData['Published'] ?: 0; // По умолчанию, "Неопубликован"

    $insertQuery = "INSERT INTO {$this->tables['register']} (`Id`, `Title`,  `ContractorId`, `Object`, `BranchOfficeId`, `Date`, `Published`) VALUES (NULL, ?s, ?i, ?s, ?i, ?s, ?i)";

    $this->db->query($insertQuery, $title, $contractorId, $object, $branchOfficeId, $date, $published);
  }

  public function addContents ($contentsData) {
    $number = $contentsData['Number'];
    $contractNumber = $contentsData['ContractNumber'];
    $consignmentNumber = $contentsData['ConsignmentNumber'];
    $consignmentDate = $contentsData['ConsignmentDate'];
    $mnemocode = $contentsData['Mnemocode'];
    $itemName = $contentsData['ItemName'];
    $itemAmount = $contentsData['ItemAmount'];
    $itemMesure = $contentsData['ItemMesure'];
    $itemPrice = $contentsData['ItemPrice'];
    $sum = $contentsData['Sum'];
    $VAT = $contentsData['VAT'];
    $sumVAT = $contentsData['SumVAT'];
    $registerId = $contentsData['RegisterId'];

    $insertQuery = "INSERT INTO {$this->tables['register-record']} (`Id`, `Number`,  `ContractNumber`, `ConsignmentNumber`, `ConsignmentDate`, `Mnemocode`, `ItemName`, `ItemAmount`, `ItemMesure`, `ItemPrice`, `Sum`, `VAT`, `SumVAT`, `RegisterId`) VALUES (NULL, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?s, ?i, ?s, ?i)";

    $this->db->query($insertQuery, 
      $number, 
      $contractNumber, 
      $consignmentNumber, 
      $consignmentDate, 
      $mnemocode, 
      $itemName, 
      $itemAmount, 
      $itemMesure, 
      $itemPrice, 
      $sum, 
      $VAT, 
      $sumVAT, 
      $registerId
    );
  }

  public function truncateAll() {
    return $this->_truncateAll('register') && $this->_truncateAll('register-record');
  }  
}
