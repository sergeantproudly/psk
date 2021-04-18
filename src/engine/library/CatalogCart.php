<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 19-Jun-18
 * Time: 4:50 PM
 */

namespace Engine\Library;


class CatalogCart {
  protected $_store;

  public function __construct() {
    session_start();

    if (!isset($_SESSION['items'])) $_SESSION['items'] = [];

    $this->_store =& $_SESSION['items'];
  }

  public function getAll() {
    return $this->_store;
  }

  public function cleanAll() {
    $this->_store = [];
  }

  public function isEmpty() {
    return count($this->_store) === 0 ? true : false;
  }

  public function set($itemId, $amount) {
    if (intval($itemId) <= 0)
      return false;

    $this->_store[$itemId] = $amount;
    return true;
  }

  public function increase ($itemId, $amount) {
    if (!isset($this->_store[$itemId]))
      return false;

    $this->_store[$itemId] += $amount;
    return true;
  }

  public function reduce ($itemId, $amount) {
    if (!isset($this->_store[$itemId])) {
      return false;
    }

    $this->_store[$itemId] -= $amount;

    if ($this->_store[$itemId] < 0)
      $this->remove($itemId);

    return true;
  }

  public  function remove ($itemId) {
    if (!isset($this->_store[$itemId]))
      return false;

    unset($this->_store[$itemId]);
    return true;
  }
}
