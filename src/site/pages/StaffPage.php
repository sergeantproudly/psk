<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 19-Jun-18
 * Time: 7:56 PM
 */

namespace Site\Pages;


use Engine\Library\ListTemplate;
use Engine\Library\Page;
use Engine\Library\Template;
use Site\Components\BreadcrumbsComponent;
use Site\Models\StaffModel;

class StaffPage extends Page {
  protected $_template;

  public function __construct() {
    parent::__construct('staff');
    $name      = $this->code();
    $directory = $name;

    $this->_template = new Template($name, $directory);
  }

  public function index () {
    global $Database;
    $staffModel = new StaffModel($Database);

    $breadcrumbs = new BreadcrumbsComponent();
    $breadcrumbsRendered = $breadcrumbs->render($this->code(), [
      ['Code' => '/', 'Link' => '/' ,'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => '/'.$this->code().'/' ,'Title' => $this->getTitle()],
    ]);

    // $staff = $staffModel->getStaff();
    $staffByDep = $staffModel->getStaffByDepartment();

    $staffTemplate = new ListTemplate('_person', 'staff');
    $departmentTemplate = new Template('_department', 'staff');

    $template = '';
    
    foreach ($staffByDep as $key => $dep) {
      foreach($dep['Staff'] as $i => $staff) {
        $dep['Staff'][$i]['PhoneEx'] = $staff['PhoneEx'] ? ('<a href="tel:' . $staff['PhoneEx'] . '" class="tel">' . $staff['PhoneEx'] . '</a>') : '';
      }

      $template .= $departmentTemplate->parse([
        'Title' => $dep['Title'],
        'Staff' => $staffTemplate->parse($dep['Staff'])
      ]);
    }

    // $staffTemplate = $staffTemplate->parse($staff);
    // $template = $departmentTemplate->parse($staffByDep);

    return $this->_template->parse([
      'Breadcrumbs' => $breadcrumbsRendered,
      'Title' => $this->getTitle(),
      'Staff' => $template
    ]);
  }
}
