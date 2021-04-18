<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 20-Jun-18
 * Time: 11:19 PM
 */

namespace Site\Models;


use Engine\Library\Model;

class UserModel extends Model {
  public function getUser($email, $password) {
    $passwordHashed = $this->getPassword($email);

    if (password_verify($password, $passwordHashed))
      return $this->db->getRow("SELECT * FROM {$this->tables['users']} WHERE `Email` = ?s", $email);
    else
      return false;
  }

  public function getUserById($id) {
    return $this->db->getRow("SELECT * FROM {$this->tables['users']} WHERE `Id` = ?i", $id);
  }

  public function getPassword($email) {
    return $this->db->getOne("SELECT `Password` FROM {$this->tables['users']} WHERE `Email` = ?s", $email);
  }


}
