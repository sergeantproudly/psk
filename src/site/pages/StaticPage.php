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

class StaticPage extends Page {
  protected $_template;

  public function __construct() {
    parent::__construct('static');

    $name      = $this->code();
    $directory = $name;

    $this->_template = new Template($name, $directory);
  }

  public function index ($params) {
    global $Database;
    global $Settings;

    $request = new Request();
    $code = trim($request->uri(), '/');

    $staticModel = new StaticModel($Database);
    $page = $staticModel->getPage($code);
    
    if (!$page) {
      Common::Get404Page();
    }

    $breadcrumbs = new BreadcrumbsComponent();
    $breadcrumbsRendered = $breadcrumbs->render($code, [
      ['Code' => '/', 'Link' => '/' ,'Title' => 'Главная'],
      ['Code' => $code, 'Link' => '/'.$code.'/' ,'Title' => $page['Title']],
    ]);

    $content = $staticModel->getContent($code);

    return $this->_template->parse($content + [
      'Breadcrumbs' => $breadcrumbsRendered,
      'Title' => $page['Heading'],
    ]);
  }
}
