<?php
namespace Site\Models;
use Engine\Library\Model;

class ClientModel extends Model {
  function getClients() {
    return $this->table('clients')->getAllSorted();
  }
}

?>