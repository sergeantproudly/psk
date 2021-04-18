<?php
namespace Site\Pages;
use Engine\Library\Page;
use Engine\Library\Template;
use Site\Components\BreadcrumbsComponent;
use Site\Models\PageModel;

class ContactsPage extends Page {
  protected $_template;

  public function __construct() {
    parent::__construct('contacts');

    $name      = $this->code();
    $directory = $name;

    $this->_template = new Template($name, $directory);
  }

  function index() {
    global $Database;
    
    $breadcrumbs = new BreadcrumbsComponent($Database);
    $breadcrumbsRendered = $breadcrumbs->render($this->code(), [
      ['Code' => 'home', 'Link' => '/', 'Title' => 'Главная'],
      ['Code' => 'contacts', 'Link' => '/contacts/', 'Title' => 'Контакты']
    ]);

    $pageModel = new PageModel($Database);
    $content = $pageModel->getContent($this->code());
    
    $formTemplate = new Template('bl-feedback', $this->code());
    $formTemplate = $formTemplate->parse([
        'Title' => strip_tags($content['FormHeading']),
        'Noise' => uniqid()
    ]);

    return $this->_template->parse($content + [
      'Form' => $formTemplate,
      'Breadcrumbs' => $breadcrumbsRendered
    ]);
  }
}
