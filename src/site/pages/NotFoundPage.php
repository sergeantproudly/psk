<?php

namespace Site\Pages;


use Engine\Library\ListTemplate;
use Engine\Library\Page;
use Engine\Library\Template;
use Engine\Library\Request;
use Engine\Library\Common;
use Site\Components\BreadcrumbsComponent;
use Site\Components\PaginationComponent;
use Site\Models\PageModel;
use Site\Models\StaticModel;

class NotFoundPage extends Page {
  protected $_template;

  public function __construct() {
    parent::__construct('404');

    $name = $this->code();

    $this->_template = new Template($name);
  }

  public function index ($params) {
    global $Database;
    
    $staticModel = new StaticModel($Database);
    $page = $staticModel->getPage($this->code());

    return $this->_template->parse([
      'Title' => $page['Heading'],
    ]);
  }
}
