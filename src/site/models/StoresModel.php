<?php

namespace Site\Models;

use Engine\Library\Model;
use Engine\Library\SafeMySQL;

class StoresModel extends Model {

  protected $_attribs;
  protected $_vehicles;
  protected $_photos;

  public function __construct(SafeMySQL $db) {
    parent::__construct($db);

    $this->getAllAttributes();
    $this->getAllVehicles();
    $this->getAllPhotos();
  }

  public function getAllItems() {
    $itemList = $this->db->getAll("SELECT * FROM {$this->tables['stores']} ORDER BY IF(`Order`,-1000/`Order`,0), Title");
    foreach ($itemList as $k => $item) {
      $itemList[$k]['Attributes'] = $this->getAttributesByItem($item);
      $itemList[$k]['Vehicles'] = $this->getVehiclesByItem($item);
      $itemList[$k]['Photos'] = $this->getPhotosByItem($item);
    }

    return $itemList;
  }

  protected function getAllAttributes() {
    $items = $this->db->getAll("SELECT * FROM {$this->tables['stores_attributes']} ORDER BY IF(`Order`,-1000/`Order`,0)");
    foreach ($items as $item) {
      $this->_attribs[$item['StoreId']][] = $item;
    }
  }

  public function getAttributesByItem($item) {
    $list = $this->_attribs[$item['Id']];
    return $list;
  }

  protected function getAllVehicles() {
    $items = $this->db->getAll("SELECT * FROM {$this->tables['stores_vehicles']} ORDER BY IF(`Order`,-1000/`Order`,0)");
    foreach ($items as $item) {
      $this->_vehicles[$item['StoreId']][] = $item;
    }
  }

  public function getVehiclesByItem($item) {
    $list = $this->_vehicles[$item['Id']];
    return $list;
  }

  protected function getAllPhotos() {
    $items = $this->db->getAll("SELECT Id, Title, StoreId, Image3840 AS ImageFull, Image626_506 AS Image FROM {$this->tables['stores_photos']} WHERE Image <> '' ORDER BY IF(`Order`,-1000/`Order`,0)");
    foreach ($items as &$item) {
      $item['Alt'] = htmlspecialchars($item['Title'], ENT_QUOTES);
      $this->_photos[$item['StoreId']][] = $item;
    }
  }

  public function getPhotosByItem($item) {
    $list = $this->_photos[$item['Id']];
    return $list;
  }
}
