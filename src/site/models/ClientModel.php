<?php
namespace Site\Models;
use Engine\Library\Model;

class ClientModel extends Model {
  function getClients($count = 0, $offset = 0) {
    return $this->table('clients')->getAllSorted(false, false, $count, $offset);
  }

  function getClientsForHomePage($count = 0) {
    $data = $this->db->getAll("SELECT Title, Preview2 AS Image FROM ?n WHERE IsShowOnMain = 1 ORDER BY IF(`Order`, -1000/`Order`, 0) LIMIT ?i", $this->tables['clients'], $count);
    
    return $data;
  }
}

?>