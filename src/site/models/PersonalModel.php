<?php

namespace Site\Models;

use Engine\Library\Model;
use Engine\Library\SafeMySQL;

class PersonalModel extends Model {

  public function getDataByCode($code) {
    return $this->table('personal')->getOneWhere('Code = ?s', $code);
  }

  public function getDataByCodeEn($code) {
    return $this->table('personal')->getOneWhere('CodeEn = ?s', $code);
  }
}
