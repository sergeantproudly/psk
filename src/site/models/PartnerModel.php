<?php
namespace Site\Models;
use Engine\Library\Model;

class PartnerModel extends Model {
  function getPartners() {
    return $this->table('partners')->getAllSorted();
  }
}

?>