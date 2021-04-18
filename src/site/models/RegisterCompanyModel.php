<?php

namespace Site\Models;
use Engine\Library\Model;

class RegisterCompanyModel extends Model {
  /**
   * Добавляет нового Контрагента в таблицу контрагентов
   *
   * @param string $contractor Имя создаваемого контрагента
   */
  public function add($company) {
    if (!$this->db->getOne("SELECT COUNT(`Id`) FROM {$this->tables['register-company']} WHERE `Title` = ?s", $company))
      $this->db->query("INSERT INTO {$this->tables['register-company']} (`Id`, `Title`) VALUES (NULL, ?s)", $company);
  }

  /**
   * Возвращает Id контрагента по его имени, если такой контрагент существует.
   * Иначе flase
   *
   * @param string $contractor Имя контрагента, Id которого нужно получить
   * @return int|boolean
   */
  public function getIdByName($company) {
    $id = $this->db->getOne("SELECT `Id` FROM {$this->tables['register-company']} WHERE `Title` = ?s", $company);
    if ($id)
      return (int) $id;
    return false;
  }

  public function truncateAll() {
    return $this->_truncateAll('register-company');
  }
}