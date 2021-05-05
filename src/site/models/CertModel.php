<?php

namespace Site\Models;


use Engine\Library\Model;
use Engine\Library\SafeMySQL;

class CertModel extends Model {
  protected const TABLE_KEY = 'catalog_cert';

  protected $_statusClassPairs;
  protected $_placeholders;

  protected $_files;

  public function __construct(SafeMySQL $db) {
    parent::__construct($db);

    $this->getAllFiles();
  }

  public function getAllItems($count = 0, $offset = 0) {
    if ($count >= 1) {
      $limit = $this->db->parse("LIMIT ?i OFFSET ?i", $count, $offset);
    } else {
      $limit = '';
    }

    $itemList = $this->db->getAll("SELECT * FROM {$this->tables['catalog_cert']} ORDER BY Date DESC {$limit}");
    $itemList = $this->_loopFormatDate($itemList, 'Date');
    foreach ($itemList as $k => $item) {
      $itemList[$k]['Files'] = $this->getFilesByItem($item);
    }

    return $itemList;
  }

  public function getItemByMnemocode ($mnemocode) {
    return $this->db->getRow("SELECT * FROM {$this->tables['catalog_cert']} WHERE `Mnemocode` = ?s LIMIT 1", $mnemocode);
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
      "SELECT * FROM {$this->tables['catalog_cert']} 
       WHERE `Mnemocode` LIKE ?s 
       OR `Title` LIKE ?s 
       ORDER BY Title " . $limit,
      $search, $search);
    $itemList = $this->_loopFormatDate($itemList, 'Date');
    foreach ($itemList as $k => $item) {
      $itemList[$k]['Files'] = $this->getFilesByItem($item);
    }
    
    return $itemList;
  }


  function getCountItems () {
    return parent::getCount($this->tables['catalog_cert']);
  }

  function getCountItemsLike ($search) {
    $search = "%{$search}%";       
    return $this->db->getOne(
      "SELECT COUNT(`Id`) FROM {$this->tables['catalog_cert']} 
       WHERE `Mnemocode` LIKE ?s 
       OR `Title` LIKE ?s 
       ORDER BY Title",
      $search, $search
    );
  }

  protected function getAllFiles() {
    $items = $this->db->getAll("SELECT * FROM {$this->tables['catalog_cert_file']} ORDER BY TermDate DESC");
    foreach ($items as $item) {
      $this->_files[$item['ItemId']][] = $item;
    }
  }

  public function getFilesByItem($item) {
    $filesList = $this->_files[$item['Id']];
    if ($filesList)
      $filesList = $this->_loopFormatDate($filesList, 'TermDate');
    return $filesList;
  }

  public function truncateAll() {
    return $this->_truncateAll('catalog_cert') && $this->_truncateAll('catalog_cert_file');
  }

  public function manageItem ($itemData) {
    $mnemocode = $itemData['Mnemocode'];
    $title = $itemData['Title'];
    $file = $itemData['File'];
    $termDate = $itemData['TermDate'];

    $itemId = $this->getItemByMnemocode($mnemocode);
    if (!$itemId) {
      $insertQuery = "INSERT INTO {$this->tables['catalog_cert']} (`Title`, `Mnemocode`, `Date`) VALUES (?s, ?s, NOW())";
      $this->db->query($insertQuery, 
        $title, 
        $mnemocode
      );

      $itemId = $this->db->insertId();
    }

    $insertQuery = "INSERT INTO {$this->tables['catalog_cert_file']} (`ItemId`, `File`, `TermDate`) VALUES (?i, ?s, ?s)";
    $this->db->query($insertQuery, 
      $itemId,
      $file,
      $termDate
    );
  }
}
