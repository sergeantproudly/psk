<?php

namespace Site\Models;
use Engine\Library\Model;

class ContactModel extends Model {

  function getContactsContent($code) {
    $data = $this->table('content')->getAllWhere('PageCode = ?s', $code);
    foreach ($data as $key => $content) {
      $data[$content['Code']] = $content['Value'];
      unset($data[$key]);
    }
    return $data;
  }

}

?>