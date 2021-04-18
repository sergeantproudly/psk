<?php
namespace Site\Models;
use Engine\Library\Model;

class CompanyModel extends Model {

  function getCompanyChildren() {
    return $this->table('company')->getAll();
  }

  function getCurrentChild($code) {
    return $this->table('company')->getOneWhere('Code = ?s', $code);
  }

  function getLicenseImages() {
    return $this->table('license')->getAllSorted();
  }

  function getReviewImages() {
    return $this->table('review')->getAllSorted();
  }

  function getAdvantages() {
  	return $this->table('advantages')->getAllSorted();
  }
}



?>