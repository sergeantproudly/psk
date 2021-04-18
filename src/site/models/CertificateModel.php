<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 22-Jun-18
 * Time: 1:57 PM
 */

namespace Site\Models;


use Engine\Library\Model;

class CertificateModel extends Model {

  public function getCertificates($count = 0, $offset = 0, $sortColumn, $sortDirection = 'ASC') {
    $table = $this->tables['certificates'];
    $query = "SELECT * FROM {$table} ORDER BY `{$sortColumn}` {$sortDirection}";

    return $this->getByQuery($query, $count, $offset);
  }

  public function getByManufacturer($id, $count = 0, $offset = 0, $sortColumn, $sortDirection = 'ASC') {
    $table = $this->tables['certificates'];
    $query = $this->db->parse(
      "SELECT * FROM {$table} 
       WHERE `ManufacturerId` = ?i 
       ORDER BY `{$sortColumn}` {$sortDirection}", $id);

    return $this->getByQuery($query, $count, $offset);
  }


  public function getByType($id, $count = 0, $offset = 0, $sortColumn, $sortDirection = 'ASC') {
    $table = $this->tables['certificates'];
    $query = $this->db->parse("SELECT * FROM {$table} WHERE `TypeId` = ?i ORDER BY `{$sortColumn}` $sortDirection", $id);

    return $this->getByQuery($query, $count, $offset);
  }

  public function getByTypeAndManufacturer($typeId, $manId, $count = 0, $offset = 0, $sortColumn, $sortDirection = 'ASC') {

    $table = $this->tables['certificates'];
    $query = $this->db->parse("SELECT * FROM {$table} WHERE `TypeId` = ?i AND `ManufacturerId` = ?i ORDER BY {$sortColumn} {$sortDirection}", $typeId, $manId);

    return $this->getByQuery($query, $count, $offset);

  }


  public function getByQuery($query, $count = 0, $offset = 0) {

    $certificates = $this->_getAllByQuery($query, $count, $offset);
    $types = $this->getTypes();
    $mans = $this->getManufacturers();

    foreach ($certificates as &$cert) {
      $type = $types[$cert['TypeId']]['Title'] ?? '';
      $man = $mans[$cert['ManufacturerId']]['Title'] ?? '';

      $cert['Type'] = $type;
      $cert['Manufacturer'] = $man;
      $cert['Date'] = $this->_formatDate($cert['Date']);
    }

    unset($cert);
    return $certificates;
  }

//  public function getCertificates($count = 0, $offset = 0) {
//    if ($count >= 1) {
//      $limit = $this->db->parse("LIMIT ?i OFFSET ?i", $count, $offset);
//    } else {
//      $limit = '';
//    }
//  }

  public function getManufacturers () {
    $table = $this->tables['cert-manufacturers'];
    $mans = $this->db->getInd('Id', "SELECT * FROM {$table}");
    return $mans;
  }

  public function getTypes() {
    $table = $this->tables['cert-types'];
    $types = $this->db->getInd('Id', "SELECT * FROM {$table}");
    return $types;
  }

  public function getCountCertificates() {
    return $this->getCount($this->tables['certificates']);
  }

  public function getCountByManufacturer($manId) {
    $count = $this->getCountWhere($this->tables['certificates'], '`ManufacturerId` = ?i', $manId);
//    var_dump($count);

    return $count;
  }

  public function getCountByType($typeId) {
    return $this->getCountWhere($this->tables['certificates'], '`TypeId` = ?i', $typeId);
  }

  public function getCountByTypeAndManufacturer($typeId, $manId) {
    $cond = $this->db->parse('`TypeId` = ?i AND `ManufacturerId` = ?i', $typeId, $manId);
    return $this->getCountWhere($this->tables['certificates'], $cond);
  }
}
