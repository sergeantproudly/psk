<?php
namespace Site\Models;
use Engine\Library\Model;

class ClientModel extends Model {
  function getClients($count = 0, $offset = 0) {
    return $this->table('clients')->getAllSorted(false, false, $count, $offset);
  }
}

?>