<?php
namespace Site\Pages;
use Engine\Library\Page;
use Engine\Library\Template;
use Engine\Library\ListTemplate;
use Engine\Library\Common;
use Site\Components\BreadcrumbsComponent;
use Site\Models\PageModel;

class ContactsPage extends Page {
  protected $_template;

  const CODE = 'contacts';
  const DIR = 'contacts';

  public function __construct() {

    parent::__construct(self::CODE, self::DIR);

    // $name      = $this->code();
    // $directory = $name;

    //$this->_template = new Template($name, $directory);

    $this->setPages([
      'index' => ['template' => 'contacts'],
    ]);

    $this->setPartials([
      'list' => [
        'type' => 'list',
        'template' => 'contacts__card'
      ]
    ]);
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
    $content['PhoneLink'] = Common::GetTelLink($content['Phone']);
    $content['PhoneCommonLink'] = Common::GetTelLink($content['PhoneCommon']);

    $storesModel = new \Site\Models\StoresModel($Database);
    $stores = $storesModel->getAllItems();
    foreach ($stores as $i => $store) {
      $stores[$i]['TelLink'] = Common::GetTelLink($store['Tel']);
      $stores[$i]['ImageWebp'] = Common::flGetWebpByImage($store['Image']);
    }
    $storesListTemplate = new ListTemplate('contacts__card', 'contacts/partial');
    $storesListRendered = $storesListTemplate->parse($stores);
    
    $formTemplate = new Template('bl-feedback', $this->code());
    $formTemplate = $formTemplate->parse([
        'Title' => strip_tags($content['FormHeading']),
        'Noise' => uniqid()
    ]);

    return $this->getPage('index')->parse($content + [
      'List' => $storesListRendered,
      'Form' => $formTemplate,
      'Breadcrumbs' => $breadcrumbsRendered
    ]);
  }
}
