<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 17-Jun-18
 * Time: 9:48 PM
 */

namespace Site\Models;


use Engine\Library\Model;
use Engine\Library\SafeMySQL;

class CatalogModel extends Model {
  protected const TABLE_KEY = 'catalog';

  protected $_statusClassPairs;
  protected $_placeholders;

  public function __construct(SafeMySQL $db) {
    parent::__construct($db);

    $this->_statusClassPairs = [
      'FastShipping' => 'short-delivery',
      'MinimumBalance' => 'minimal-balance'
    ];
    $this->_placeholders['status'] = 'Status_Class';
  }

  public function getAllItems($count = 0, $offset = 0) {
    if ($count >= 1) {
      $limit = $this->db->parse("LIMIT ?i OFFSET ?i", $count, $offset);
    } else {
      $limit = '';
    }

    $itemList = $this->db->getAll("SELECT * FROM {$this->tables['catalog']} {$limit}");
    $itemList = $this->_setStatusFields($itemList);
    $itemList = $this->_loopFormatDate($itemList, 'Date');

    return $itemList;
  }

  public function getItemById ($id) {
    $item = $this->db->getRow("SELECT * FROM {$this->tables['catalog']} WHERE `Id` = ?i LIMIT 1", $id);
//    var_dump($item);
    return $item;
  }

  function getAllLike ($search, $count = 0, $offset = 0) {
    // var_dump($search);
    if (!$search)
      return [];
    $search = "%{$search}%";
//    var_dump($search);
    if ($count >= 1) {
      $limit = $this->db->parse("LIMIT ?i OFFSET ?i", $count, $offset);
    } else {
      $limit = '';
    }

    $itemList = $this->db->getAll(
      "SELECT * FROM {$this->tables['catalog']} 
       WHERE `Mnemocode` LIKE ?s 
       OR `Title` LIKE ?s " . $limit,
      $search, $search);
    $itemList = $this->_setStatusFields($itemList);
    $itemList = $this->_loopFormatDate($itemList, 'Date');
    
    return $itemList;
  }

  function getAllLikeWithFilter ($search, $filter, $count = 0, $offset = 0) {
    $filter = $this->db->getRow(
      "SELECT * FROM {$this->tables['catalog_filters']}
       WHERE `Code` = ?s", $filter);
    $search = "%{$search}%";
//    var_dump($search);
    if ($count >= 1) {
      $limit = $this->db->parse("LIMIT ?i OFFSET ?i", $count, $offset);
    } else {
      $limit = '';
    }

    $items = $this->db->getAll(
      "SELECT * FROM {$this->tables['catalog']} 
       WHERE (`Mnemocode` LIKE ?s 
       OR `Title` LIKE ?s) AND (`{$filter['Code']}` = TRUE) " . $limit,
      $search, $search);
    $items = $this->_setStatusFields($items);
    $items = $this->_loopFormatDate($items, 'Date');

    return $items;
  }

  function getAllByFilter ($filter, $count = 0, $offset = 0) {
//    $search = "%{$search}%";
    $filter = $this->db->getRow(
      "SELECT * FROM {$this->tables['catalog_filters']}
       WHERE `Code` = ?s", $filter);

//    var_dump($filter);

//    var_dump($filter);

    if ($count >= 1) {
      $limit = $this->db->parse("LIMIT ?i OFFSET ?i", $count, $offset);
    } else {
      $limit = '';
    }

    if ($filter) {
      $items = $this->db->getAll(
        "SELECT * FROM {$this->tables['catalog']} 
         WHERE `{$filter['Code']}` = TRUE " . $limit);
      if ($items) {
        $items = $this->_setStatusFields($items);
        $items = $this->_loopFormatDate($items);
      }
      return $items;
    } else
      return [];
  }


  function getCountItems () {
    return parent::getCount($this->tables['catalog']);
  }

  function getCountItemsLike ($search) {
    $search = "%{$search}%";       
    return $this->db->getOne(
      "SELECT COUNT(`Id`) FROM {$this->tables['catalog']} 
       WHERE `Mnemocode` LIKE ?s 
       OR `Title` LIKE ?s",
      $search, $search
    );
  }

  function getCountItemsLikeWithFilter ($search, $filter) {
    $search = "%{$search}%";
    
    $filter = $this->db->getRow(
      "SELECT * FROM {$this->tables['catalog_filters']}
       WHERE `Code` = ?s", $filter);
    
    // var_dump($filter);

    return $this->db->getOne(
      "SELECT COUNT(`Id`) FROM {$this->tables['catalog']} 
       WHERE (`Mnemocode` LIKE ?s 
       OR `Title` LIKE ?s) AND (`{$filter['Code']}` = TRUE) ",
      $search, $search
    );
  }

  function getCountItemsFiltered ($filter) {
    $filter = $this->db->getRow(
      "SELECT * FROM {$this->tables['catalog_filters']}
       WHERE `Code` = ?s", $filter);
    
    $count = $this->db->getOne(
      "SELECT COUNT(`Id`) FROM {$this->tables['catalog']} 
       WHERE `{$filter['Code']}` = TRUE");
    return $count;
  }

  function _setStatusFields($itemList) {
    foreach ($itemList as &$item) {
      $placeholder = $this->_placeholders['status'];
      $item[$placeholder] = '';

      foreach ($this->_statusClassPairs as $status => $class) {
        if ($item[$status]) $item[$placeholder] .= "{$class} ";
      }
    }
    unset($item);
    return $itemList;
  }


//  public function getItemsById($idAmountPairs) {
//    return $this->getItemsBy($idAmountPairs, 'Id');
//  }

  public function getItems($idAmountPairs) {
    $keys = array_keys($idAmountPairs);
    $column = 'Id';

    $items = $this->db->getInd('Id', "SELECT * FROM {$this->tables['catalog']} WHERE `{$column}` IN (?a)", $keys);
    $totalAmount = 0;

    foreach ($items as &$item) {
      $item['Amount'] = $idAmountPairs[$item[$column]];
      $totalAmount += $item['Amount'];
    }
//    var_dump($items);

    return [
      'items' => $items,
      'count' => count($items),
      'totalAmount' => $totalAmount
    ];
  }

  public function saveOrder ($userId, $items) {
    $tableOrder = $this->tables['orders'];
    $tableOrderItems = $this->tables['orders-items'];

    $result = $this->db->query("INSERT INTO {$tableOrder} (`Id`, `UserId`, `OrderDate`, `Confirmed`) VALUES (NULL, $userId, NOW(), 0)");
    $id = $this->db->insertId();

    if (!$result)
      return false;


    foreach ($items as $item) {
      $orderItem = [
        'OrderId' => $id,
        'Title' => $item['Title'],
        'Amount' => $item['Amount'] . ' ' . $item['Measure'].'.',
//        'Measure' => ,
      ];
      $result = $this->db->query("INSERT INTO {$tableOrderItems} SET ?u", $orderItem);
      if (!$result)
        return false;
    }

    return true;
  }

  /** NEW */

  public function truncateAll() {
    return $this->_truncateAll('catalog');
  }

  public function setItem ($data) {
    if ($data['Id']) {
      $query = "UPDATE {$this->tables['catalog']} SET `Id` = {$data['Id']}, `Title` = ?s, `Description` = ?s, `Price` = ?s, `Measure` = ?s, `Mnemocode` = ?s, `Date` = ?s, `Request` = ?s";
    } else {
      $query = "INSERT INTO {$this->tables['catalog']} (`Title`, `Description`, `Price`, `Measure`, `Mnemocode`, `Date`, `Request`) VALUES (?s, ?s, ?s, ?s, ?s, ?s, ?s)";
    }
    $this->db->query($query,
      $data['Title'],
      $data['Description'],
      $data['Price'],
      $data['Measure'],
      $data['Mnemocode'],
      $data['Date'],
      $data['Request']
    );
  }
  /** /NEW */
}
