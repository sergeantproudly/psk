<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 19-Jun-18
 * Time: 8:23 PM
 */

namespace Site\Models;


use Engine\Library\Model;
use Engine\Library\SafeMySQL;

class StaffModel extends Model {
  const DIRECTION_ID = 4;

  public function getStaff() {
    return $this->table('staff')->getAllSorted();
  }

  public function getStaffByDepartment($departmentId = false) {
    if ($departmentId) $departmentDict = $this->db->getInd('Id', "SELECT * FROM {$this->tables['department']} WHERE Id = ?i", $departmentId);
    else $departmentDict = $this->db->getInd('Id', "SELECT * FROM {$this->tables['department']}");
    
    $staff = $this->getStaff();

    $staffByDep = [];
    foreach ($departmentDict as $depId => $dep) {
      $staffByDep[$depId]['Title'] = $dep['Title'];
      $staffByDep[$depId]['Staff'] = array_filter($staff, 
        function ($person) use ($depId) {
          return $person['DepartmentId'] == $depId;
        });
      
      $staffByDep[$depId]['Staff'] = array_values($staffByDep[$depId]['Staff']);
    }

    return $staffByDep;
  }

  public function getStaffDirection($onMain = false) {
    if ($onMain) {
      return $this->table('staff')->getAllWhereSorted('DepartmentId = ?i AND OnMain = 1', self::DIRECTION_ID);
    } else {
      return $this->table('staff')->getAllWhereSorted('DepartmentId = ?i', self::DIRECTION_ID);
    }
  }

  public function getPersonById($personId) {
    return $this->table('staff')->getOneById($personId);
  }
}
