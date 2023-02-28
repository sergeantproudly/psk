<?php
namespace Site\Models;
use Engine\Library\Model;

class PartnerModel extends Model {
  function getPartners($count = 0, $offset = 0) {
    return $this->table('partners')->getAllSorted(false, false, $count, $offset);
  }

  function getPartnersForHomePage($count = 0) {
    $data = $this->db->getAll("SELECT Title, Preview2 AS Image FROM ?n WHERE IsShowOnMain = 1 ORDER BY IF(`Order`, -1000/`Order`, 0) LIMIT ?i", $this->tables['partners'], $count);
    
    return $data;
  }
}

?>