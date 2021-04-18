<?php
namespace Site\Models;
use Engine\Library\Model;

class PageModel extends Model {

  function getContacts() {
    return $this->getContent('contacts');
  }

  function getPageList() {
    return $this->db->getInd('Id', "SELECT * FROM {$this->tables['pages']}");
  }

  function getAuthNavigationList() {
    $table = $this->tables['auth-navigation'];

    $navigationItems = $this->db->getAll("SELECT * FROM {$table} ORDER BY `Order`");
    $pages = $this->getPageList();

    foreach ($navigationItems as &$navItem) {
      $id = $navItem['PageId'];
      $navItem['Title'] = $pages[$id]['Title'];
      $navItem['Code'] = $pages[$id]['Code'];
    }

    unset($navItem);
    return $navigationItems;
  }

  function getAuthPageList() {
    $authNavList = $this->getAuthNavigationList();
    $authPages = [];

    foreach ($authNavList as $navItem) {
      $authPages[] = $navItem['Code'];
    }

    return $authPages;
  }


}




?>
