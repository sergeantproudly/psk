<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 18-Jun-18
 * Time: 12:51 PM
 */


namespace Engine\Library;

class UserSession {
  const STORAGE_NAME = 'user';

  protected $_userCookie;
  protected $_userId;
  
  public function __construct() {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    if (!isset($_SESSION[self::STORAGE_NAME])) {
      $_SESSION[self::STORAGE_NAME] = [
        'userCookie' => null,
        'userId' => null,
      ];
    }

    $this->_userCookie =& $_SESSION[self::STORAGE_NAME]['userCookie'];
    $this->_userId =& $_SESSION[self::STORAGE_NAME]['userId'];
  }

  public function id () {
    return $this->_userId;
  }

  public function cookie () {
    return $this->_userCookie;
  }

  public function logIn($userId) {
    session_start();

    if (!$this->isLoggedIn()) {
      $this->_userId = $userId;
      $this->_userCookie = session_id();
    } else {
      $this->logOut();
      $this->logIn($userId);
    }
  }

  public function logOut() {
    $this->_userCookie = null;
    $this->_userId = null;

    session_destroy();
  }

  public function isLoggedIn() {
    if (empty($this->_userCookie) && empty($this->_userId))
      return false;

    return true;
  }
}
