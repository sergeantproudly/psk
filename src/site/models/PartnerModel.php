<?php
namespace Site\Models;
use Engine\Library\Model;

class PartnerModel extends Model {
  function getPartners($count = 0, $offset = 0) {
    return $this->table('partners')->getAllSorted(false, false, $count, $offset);
  }
}

?>