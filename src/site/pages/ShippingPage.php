<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 22-Jun-18
 * Time: 2:06 PM
 */

namespace Site\Pages;


use Engine\Library\ListTemplate;
use Engine\Library\Page;
use Engine\Library\Template;
use Site\Components\BreadcrumbsComponent;
use Site\Components\PaginationComponent;
use Site\Models\CertificateModel;
use Site\Models\PageModel;

class ShippingPage extends Page {
  protected $_template;

  public function __construct() {
    parent::__construct('shipping');

    $name      = $this->code();
    $directory = $name;

    $this->_template = new Template($name, $directory);
  }

  public function index ($params) {
    global $Database;
    global $Settings;

//    $shippingModel = new \Site\Models\ShippingModel($Database);
//    $shipping = $shippingModel->getShippingSchedule();
//
//    $tableTemplate = new ListTemplate('_row', $this->code());
//    $tableTemplate = $tableTemplate->parse($shipping);

    $breadcrumbs = new BreadcrumbsComponent();
    $breadcrumbsRendered = $breadcrumbs->render($this->code(), [
      ['Code' => '/', 'Link' => '/' ,'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => '/'.$this->code().'/' ,'Title' => $this->getTitle()],
    ]);
    $pageModel = new PageModel($Database);
    $content = $pageModel->getContent($this->code());

    return $this->_template->parse([
      'Breadcrumbs' => $breadcrumbsRendered,
      'Title' => $this->getTitle(),
      'Content' => $content['ShippingTable'],
    ]);
  }
}
