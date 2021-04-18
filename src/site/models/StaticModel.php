<?php
namespace Site\Models;
use Engine\Library\Model;

class StaticModel extends Model {

  function getPage($code) {
    return $this->table('pages')->getOneWhere('Code = ?s', $code);
  }
}

?>
