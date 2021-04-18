<?php

namespace Site\Models;
use Engine\Library\Model;


class BranchOfficeModel extends Model {
  /**
   * Добавить новый Филиал в таблицу филиалов
   *
   * @param string $branchOffice Имя нового филиала
   */
  public function add($branchOffice) {
    $this->db->query("INSERT INTO {$this->tables['branch-office']} (`Id`, `Title`) VALUES (NULL, ?s)", $branchOffice);
  }
  
  /**
   * Возвращает Id, соответвующий указанному названию филиала, если такой
   * филиал существует в базе. Иначе false.
   *
   * @param string $branchOffice Имя филиала
   * @return int|boolean
   */
  public function getIdByName($branchOffice) {
    $id = $this->db->getOne("SELECT `Id` FROM {$this->tables['branch-office']} WHERE `Title` = ?s", $branchOffice);
    if ($id)
      return (int) $id;
    return false;
  }

  public function truncateAll() {
    return $this->_truncateAll('branch-office');
  }

}