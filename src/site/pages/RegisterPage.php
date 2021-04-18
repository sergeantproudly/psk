<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 19-Jun-18
 * Time: 9:42 AM
 */

namespace Site\Pages;


use Engine\Library\Ajax\Event;
use Engine\Library\ListTemplate;
use Engine\Library\Page;
use Engine\Library\Template;
use Site\Components\BreadcrumbsComponent;
use Site\Components\PaginationComponent;
use Site\Models\RegisterModel;
use TCPDF;

class RegisterPage extends Page {
  protected $template;

  public function __construct() {
    parent::__construct('register');

    $name      = $this->code();
    $directory = $name;

    $this->template = new Template($name, $directory);
  }

  public function index ($params = []) {
    global $Database;
    global $Settings;

    $currentPage = $params['page'] ?? 1;
    $search = $params['search'];

    $registerModel = new RegisterModel($Database);

    $breadcrumbs = new BreadcrumbsComponent;
    $breadcrumbsRendered = $breadcrumbs->render($this->code(), [
      ['Code' => '/', 'Link' => '/' ,'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => '/'.$this->code().'/' ,'Title' => $this->getTitle()],
    ]);

    $downloadLink = '/'.$this->code().'/download';

    $itemsPerPage = $Settings->get('RegisterItemsPerPage') ?: 10;
    $itemsCountAll = 0;

    // var_dump($search);

    if ($search) {
      $itemsCountAll = $registerModel->getCountItems($search);
    } else {
      $itemsCountAll = $registerModel->getCountItems();
    }
    // var_dump($itemCountAll);
    
    $pagination = new PaginationComponent;

    $sortOptions = [
      'branch' => 'BranchOffice',
      'object' => 'Object',
      'date' => 'Date',
    ];

    $sortDirections = [
      'asc' => 'ASC',
      'desc' => 'DESC',
    ];

    $sortColumn = $sortOptions[$params['sort']] ?? null;
    $sortDirection = $sortDirections[$params['direction']] ?? $sortDirections['asc'];

    $sortColumnKey = array_search($sortColumn, $sortOptions) ?? null;
    $sortDirectionKey = array_search($sortDirection, $sortDirections) ?? $sortDirections['asc'];
    
    $code = $this->code();
    if ($search) {
      $code .= "/search/{$search}"; 
    }
    if ($sortColumn) {
      $code .= "/sort/{$sortColumnKey}/{$sortDirectionKey}";
    }
    $paginationData = $pagination->pages(
      $itemsPerPage,
      $itemsCountAll,
      $code,
      $currentPage,
      true
    );

    $pages = $paginationData['pages'];
    // var_dump($pages);
    $offset = ($currentPage - 1) * $itemsPerPage;
    $offset = $offset >= 0 ? $offset : 0;

    $register = [];

    if ($search) {
      $register = $registerModel->getAllLike($search, $sortColumn, $sortDirection, $itemsPerPage, $offset);
    } else {
      $register = $registerModel->getAll($sortColumn, $sortDirection, $itemsPerPage, $offset);
    }

    $table = new ListTemplate('_row.htm', 'register');
    $table = $table->parse($register);


    $nextPage = $paginationData['nextPage'];
    $prevPage = $paginationData['previousPage'];

    $sortClassList = [
      'asc' => '',
      'desc' => 'rev',
    ];

    $sortClass = $sortClassList[$sortDirectionKey];

    return $this->template->parse([
      'Title' => $this->getTitle(),
      'Breadcrumbs' => $breadcrumbsRendered,
      'DownloadLink' => $downloadLink,
      'Search' => [
        'Value' => $params['search'] ?? '',
        'Action' => '/register/search/',      
      ],
      'Table' => $table,
      $sortColumn => "act $sortClass",
      'Pagination' => count($pages) > 1 ? $pagination->view('default')->parse([
        'Previous' => [
          'Status' => $prevPage ? '' : 'disabled',
          'Link' => $prevPage['link'] ?: '#'
        ],
        'Next' => [
          'Status' => $nextPage ? '' : 'disabled',
          'Link' => $nextPage['link'] ?: '#'
        ],
        'List' => count($pages) > 1
          ? $pagination->partial('pages')->setCallback(function ($item) {
            return $item['active'] == true;
          })->parse($pages) : '',
      ]) : '',
    ]);
  }

  public function detail ($params = []) {
    global $Database;

    if(!isset($params['number']) || empty($params['number']))
      return $this->index();

    $search = $params['search'];
    $number = $params['number'];
    $registerModel = new RegisterModel($Database);
    
    // TODO: Оптимизировать проверку доступности пользователю заправшиваемых записей 
    $register = $registerModel->getAll($sortColumn, $sortDirection);

    $userAllowedNumbers = array_map(function($item) {
      return $item['Title'];
    }, $register);
  
    if(array_search($number, $userAllowedNumbers) === false)
      return $this->index();

    $register = $registerModel->getRegisterFromNumber($number);
    $records = $registerModel->getRecords($number, $search);

    $numberTitle = '№'.mb_substr($number, 8);

    $recordTemplate = new Template('record', 'register/record');
    $tableTemplate = new ListTemplate('_row', 'register/record');
    $tableTemplate = $tableTemplate->parse($records);

    // var_dump($recordTemplate);

    $breadcrumbs = new BreadcrumbsComponent;
    $breadcrumbsRendered = $breadcrumbs->render($number, [
      ['Code' => '/', 'Link' => '/' ,'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => '/'.$this->code().'/' ,'Title' => $this->page['Title']],
      ['Code' => $number, 'Title' => $numberTitle],
    ]);

    $downloadLink = '/'.$this->code().'/download/'.$number;

    return $recordTemplate->parse([
      'Breadcrumbs' => $breadcrumbsRendered,
      'DownloadLink' => $downloadLink,
      'Title' => '№' . mb_substr($number, 8),
      'Object' => $register['Object'],
      'Table' => $tableTemplate,
      'Search' => [
        'Value' => $search ?? '',
        'Action' => "/register/{$number}/search/",
      ],
    ]);
  }

  public function getContentSorted($params) {
    global $Database;
    global $Settings;

    $registerModel = new RegisterModel($Database);

    $currentPage = $params['page'] ?? 1;
    $search = $params['search'];

    $itemsPerPage = $Settings->get('RegisterItemsPerPage') ?: 10;
    $itemsCountAll = 0;
    
    if ($search) {
      $itemsCountAll = $registerModel->getCountItems($search);
    } else {
      $itemsCountAll = $registerModel->getCountItems();
    }
    

    $pagination = new PaginationComponent;

    $sortOptions = [
      'branch' => 'BranchOffice',
      'object' => 'Object',
      'date' => 'Date',
    ];

    $sortDirections = [
      'asc' => 'ASC',
      'desc' => 'DESC',
    ];

    $sortColumn     = $sortOptions[$params['sort']['by']] ?? null;
    $sortDirection  = $sortDirections[$params['sort']['direction']] ?? $sortDirections['asc'];

    $sortColumnKey    = array_search($sortColumn, $sortOptions) ?? null;
    $sortDirectionKey = array_search($sortDirection, $sortDirections) ?? $sortDirections['asc'];

    $code = $this->code();

    if ($search) {
      $code .= "/search/{$search}"; 
    }
    if ($sortColumn) {
      $code .= "/sort/{$sortColumnKey}/{$sortDirectionKey}";
    }

    $paginationData = $pagination->pages(
      $itemsPerPage,
      $itemsCountAll,
      $code,
      $currentPage,
      true
    );

    $pages = $paginationData['pages'];

    $offset = ($currentPage - 1) * $itemsPerPage;
    $offset = $offset >= 0 ? $offset : 0;

    $register = [];

    // var_dump($sortColumn);
    // var_dump($sortDirection);

    if ($search) {
      $register = 
        $registerModel->getAllLike($search, $sortColumn, $sortDirection, $itemsPerPage, $offset);
    } else {
      $register = 
        $registerModel->getAll($sortColumn, $sortDirection, $itemsPerPage, $offset);
    }

    $table = new ListTemplate('_row.htm', 'register');
    $table = $table->parse($register);


    $nextPage   = $paginationData['nextPage'];
    $prevPage   = $paginationData['previousPage'];

    return [
      'Table' => $table,
      'Pagination' => count($pages) > 1 ? $pagination->view('default')->parse([
        'Previous' => [
          'Status' => $prevPage ? '' : 'disabled',
          'Link' => $prevPage['link'] ?: '#'
        ],
        'Next' => [
          'Status' => $nextPage ? '' : 'disabled',
          'Link' => $nextPage['link'] ?: '#'
        ],
        'List' => count($pages) > 1
          ? $pagination->partial( 'pages')->setCallback(function ($item) {
            return $item['active'] == true;
          })->parse($pages) : '',
      ]) : '',
    ];
  }

  public function download($params = []) {
    global $Database;

    if(isset($params['number']) ?? !empty($params['number'])) {
      $number = $params['number'];
      $fileName = 'register_' . $number . '_' . date('d_m_Y') . '.pdf';
      $registerModel = new RegisterModel($Database);
      $register = $registerModel->getAll();

      $userAllowedNumbers = array_map(function($item) {
        return $item['Title'];
      }, $register);
    
      if(array_search($number, $userAllowedNumbers) === false)
        return false;

      $records = $registerModel->getRecords($number);

      $register = $registerModel->getRegisterFromNumber($number);

      $title = 'Реестр товарных накладных №'.mb_substr($number, 8) . ' от ' . date('d.m.Y', strtotime($register['Date'])) . ', подтверждающих факт приобретения ТМЦ, используемых на объекте '. $register['Object'];
      $titleSize = 10;
      $recordTemplate = new Template('record', 'register/record');
      $tableTemplate = new ListTemplate('_row_pdf', 'register/record');
      $tableTemplate = $tableTemplate->parse($records);
      $html = new Template('record_pdf', 'register/record');
      $html = $html->parse([
        'table' => $tableTemplate,
        'today' => date('d.m.Y'),
      ]);

    } else {
      $fileName = 'register_' . date('d_m_Y') . '.pdf';
      $registerModel = new RegisterModel($Database);
      $register = $registerModel->getAll();

      $title = 'Реестр накладных';
      $table = new ListTemplate('_row.htm', 'register');
      $table = $table->parse($register);
      $html = new Template('register_pdf', 'register');
      $html = $html->parse([
        'table' => $table
      ]);
    }    

    // configuration
    $fontName='dejavusanscondensed';
    $headerText = $title;
    $headerSize = $titleSize ?? 17;
    $textColor = array( 80, 80, 80 );
    $headerColor = array( 60, 60, 60 );
    $subHeaderColor = array( 132, 132, 132 );
    $tableHeaderTextColor = array( 255, 255, 255 );
    $tableHeaderFillColor = array( 255, 80, 77 );
    $tableSubHeaderFillColor = array( 222, 222, 222 );
    $tableBorderColor = array( 200, 200, 200 );
    $tableRowFillColor = array( 242, 242, 242 );
    $logoFile = ROOT.'/public/images/logo.jpg';
    $cellHeight = 12;
    $cellPadding = 3;

    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(20, 20, 20);
    $pdf->SetAuthor('ПСК СтройИнвест');
    $pdf->SetCreator('ПСК СтройИнвест');
    $pdf->SetDisplayMode('real', 'default');
    $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);    
    
    $pdf->AddPage();
    $pdf->Image($logoFile, '', '', 40, 11.94, '', '', '', false, 300, 'C');
    $pdf->Ln(25);
    
    $pdf->SetTextColor($headerColor[0], $headerColor[1], $headerColor[2]);
    $pdf->SetFont($fontName, 'B', $headerSize);
    $pdf->Multicell(0, 7, $headerText, 0, 'C');
    $pdf->Ln(5);

    $pdf->SetLeftMargin(20);
    $pdf->writeHTML($html);

    $pdf->Output($fileName, 'I');
    
    return $filename;
  }
}
