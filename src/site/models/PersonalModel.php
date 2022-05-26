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

  public function incVisitCountByCode($code) {
    $this->db->query('UPDATE ?n SET `VisitCount` = `VisitCount` + 1 WHERE `Code` = ?s', $this->tables['personal'], $code);
  }

  public function incVisitCountByCodeEn($code) {
    $this->db->query('UPDATE ?n SET `VisitCount` = `VisitCount` + 1 WHERE `CodeEn` = ?s', $this->tables['personal'], $code);
  }
}
