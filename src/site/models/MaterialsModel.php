<?php

namespace Site\Models;


use Engine\Library\Model;
use Engine\Library\SafeMySQL;

class MaterialsModel extends Model {
  public function getMaterials() {
    return $this->table('materials')->getAllSorted();
  }
}
