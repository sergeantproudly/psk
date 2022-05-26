<?php
namespace Site\Pages;

use Engine\Library\ListTemplate;
use Engine\Library\Page;
use Engine\Library\Template;
use Engine\Library\UserSession;
use Engine\Utility\Morphology;
use Site\Components\BreadcrumbsComponent;
use Site\Components\PaginationComponent;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory; 

class PersonalPage extends Page {
  const CODE = 'personal';
  const DIR = self::CODE;
  public $modelName = 'PersonalModel';

  public function __construct() {
    parent::__construct(self::CODE, self::DIR);
  }

  public function detail($params = []) {
    global $Settings;

    $code = $params['code'];
    $data = $this->model->getDataByCode($code);

    $templateLayout = new Template('layout', 'personal');
    $templateData = new Template('personal', 'personal');

    $templateRendered = $templateData->parse([
      'Name' => $data['Name'],
      'Alt' => htmlspecialchars($data['Name'], ENT_QUOTES),
      'Photo' => $data['Photo105_105'] ?: '/assets/images/_team_person.jpg',
      'Post' => $data['Post'],
      'Tel' => $data['Tel'],
      'TelCode' => preg_replace('/[^\d\+]/', '', $data['Tel']),
      'Email' => $data['Email'],
      'Website' => $data['Website'],
      'Company' => $data['Company'],
      'FileVcf' => $data['FileVcf'],
      'LangCall' => 'Позвонить',
      'LangWrite' => 'Написать на почту',
      'LangTel' => 'Мобильный телефон',
      'LangEmail' => 'Электронная почта',
      'LangWebsite' => 'Сайт',
      'LangButton' => 'Добавить контакт'
    ]);

    $layoutRendered = $templateLayout->parse([
      'Lang' => 'ru',
      'Title' => $data['Name'],
      'Content' => $templateRendered,
      'Version' => $Settings->get('AssetsVersion') ? '?v2.' . $Settings->get('AssetsVersion') : ''
    ]);


    echo $layoutRendered;
    exit;
  } 

  public function detailEn($params = []) {
    global $Settings;

    $code = $params['code'];
    $data = $this->model->getDataByCode($code);

    $templateLayout = new Template('layout', 'personal');
    $templateData = new Template('personal', 'personal');

    $templateRendered = $templateData->parse([
      'Name' => $data['NameEn'],
      'Alt' => htmlspecialchars($data['Name'], ENT_QUOTES),
      'Photo' => $data['Photo105_105'] ?: '/assets/images/_team_person.jpg',
      'Post' => $data['PostEn'],
      'Tel' => $data['TelEn'],
      'TelCode' => preg_replace('/[^\d\+]/', '', $data['Tel']),
      'Email' => $data['EmailEn'],
      'Website' => $data['Website'],
      'Company' => $data['CompanyEn'],
      'FileVcf' => $data['FileVcfEn'],
      'LangCall' => 'Call',
      'LangWrite' => 'Send an e-mail',
      'LangTel' => 'Tel',
      'LangEmail' => 'E-mail',
      'LangWebsite' => 'Website',
      'LangButton' => 'Add contact'
    ]);

    $layoutRendered = $templateLayout->parse([
      'Lang' => 'en',
      'Title' => $data['NameEn'],
      'Content' => $templateRendered,
      'Version' => $Settings->get('AssetsVersion') ? '?v2.' . $Settings->get('AssetsVersion') : ''
    ]);


    echo $layoutRendered;
    exit;
  } 

}
