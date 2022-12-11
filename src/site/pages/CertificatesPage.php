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

class CertificatesPage extends Page {
  protected $_template;

  public function __construct() {
    parent::__construct('certificates');

    $name      = $this->code();
    $directory = $name;

    $this->_template = new Template($name, $directory);
  }

  public function index ($params) {
    global $Database;
    global $Settings;

    $certModel = new CertificateModel($Database);
    
    $mans = $certModel->getManufacturers();
    $types = $certModel->getTypes();

    $currentPage = $params['page'] ?? 1;
    $itemsPerPage = $Settings->get('CatalogItemsPerPage') ?: 15;
    $offset = ($currentPage - 1) * $itemsPerPage;
    $offset = $offset >= 0 ? $offset : 0;

    $optionList = [
      'man' => 'Manufacturer',
      'type' => 'Type'
    ];

    $sortList = [
      'type' => 'TypeId',
      'man' => 'ManufacturerId',
      'name' => 'Standard',
      'link' => 'Link',
      'date' => 'Date'
    ];

    $sortColCode = array_key_exists($params['sort'], $sortList) ? $params['sort'] : 'type';
    $sortColumn = $sortList[$sortColCode];
//    var_dump($sortColCode);

    $sortDirCode = ($params['sort-direction'] ?? null) == 'desc' ? 'desc' : 'asc';
    $sortDirection = $sortDirCode  == 'desc' ? 'DESC' : 'ASC';

    $typeId = null;
    $manId = null;

    $code = '';
    for ($i = 1; $i < 3; $i++) { 
      $keyParamName = 'select-' . $i;
      $valueParamName = 'value-' . $i;

      $selected = $params[$keyParamName] ?? null;
      
      if (array_key_exists($selected, $optionList)) {  
        if ($optionList[$selected] == $optionList['type']) {
        
          $typeId = $params[$valueParamName] ?: null;          
          $code .= "/select/type/{$typeId}";

        } else if ($optionList[$selected] == $optionList['man']) {
        
          $manId = $params[$valueParamName] ?: null;
          $code .= "/select/man/{$manId}";
        
        }
      }
    }
    
    if ($code) {
      $code = trim($code, '/');
      $code = "{$this->code()}/{$code}/sort/{$sortColCode}/{$sortDirCode}";
    } else {
      $code = "{$this->code()}/sort/{$sortColCode}/{$sortDirCode}";
    }

    if ($typeId && $manId) {
      
      $types[$typeId]['Selected'] = 'selected';
      $mans[$manId]['Selected'] = 'selected';

      $itemsCountAll = $certModel->getCountByTypeAndManufacturer($typeId, $manId);
    } else if ($manId) {

      $mans[$manId]['Selected'] = 'selected';
      $itemsCountAll = $certModel->getCountByManufacturer($manId);
    
    } else if ($typeId) {
    
      $types[$typeId]['Selected'] = 'selected';
      $itemsCountAll = $certModel->getCountByType($typeId);
    
    } else
      $itemsCountAll = $certModel->getCountCertificates();

    $pagination = new PaginationComponent();

    $paginationData = $pagination->pages(
      $itemsPerPage,
      $itemsCountAll,
      $code,
      $currentPage,
      false
    );

    $pages = $paginationData['pages'];

    $nextPage = $paginationData['nextPage'];
    $prevPage = $paginationData['previousPage'];

//    var_dump($itemsPerPage, $offset);


    if ($typeId && $manId) {

      $certificates =
        $certModel->getByTypeAndManufacturer($typeId, $manId, $itemsPerPage, $offset, $sortColumn, $sortDirection);

    } else if ($manId) {
    
      $certificates = $certModel->getByManufacturer($manId, $itemsPerPage, $offset, $sortColumn, $sortDirection);
    
    } else if ($typeId) {
    
      $certificates = $certModel->getByType($typeId, $itemsPerPage, $offset, $sortColumn, $sortDirection);
    
    } else {
    
      $certificates = $certModel->getCertificates($itemsPerPage, $offset, $sortColumn, $sortDirection);
    
    }
    $tableTemplate = new ListTemplate('_cert-row', $this->code());
    $tableTemplate = $tableTemplate->parse($certificates);

    $optionsTemplate = new ListTemplate('_option', $this->code());

//    var_dump($types);

    $manOptionsTemplate = $optionsTemplate->parse($mans);
    $typeOptionsTemplate = $optionsTemplate->parse($types);

    $breadcrumbs = new BreadcrumbsComponent();
    $breadcrumbsRendered = $breadcrumbs->render($this->code(), [
      ['Code' => '/', 'Link' => '/' ,'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => '/'.$this->code().'/' ,'Title' => $this->getTitle()],
    ]);

    return $this->_template->parse([
      'Breadcrumbs' => $breadcrumbsRendered,
      'Title' => $this->getTitle(),
      'Table' => $tableTemplate,
      'Class' => [
        $sortColCode => ($sortDirCode == 'asc' ? 'act' : 'act rev'),
      ],
      'Type' => [
        'Default' => $typeId ? '<option value="0">Выбрать тип товара</option>' : '<option selected value="0">Выбрать тип товара</option>',
        'Options' => $typeOptionsTemplate
      ],
      'Manufacturer' => [
        'Default' => $manId ? '<option value="0">Выбрать производителя</option>' :'<option selected value="0">Выбрать производителя</option>',
        'Options' => $manOptionsTemplate
      ],
      'Pagination' => count($pages) > 1 ? $pagination->view('default')->parse([
        'Previous' => [
          'Status' => $prevPage ? '' : 'aria-disabled="true"',
          'Link' => $prevPage['link'] ?: '#'
        ],
        'Next' => [
          'Status' => $nextPage ? '' : 'aria-disabled="true"',
          'Link' => $nextPage['link'] ?: '#'
        ],
        'List' => count($pages) > 1
          ? $pagination->partial('pages')->setCallback(function ($item) {
            return $item['active'] == true;
          })->parse($pages) : '',
      ]) : '',
    ]);
  }

  public function getContentSorted ($params) {
    global $Database;
    global $Settings;
    
    $optionList = [
      'man' => 'Manufacturer',
      'type' => 'Type'
    ];

    $typeId = null; 
    $manId = null;
    
    $code = '';

    foreach ($params['select'] as $key => $data) {
      $selectBy = $data['by'];

      if ($selectBy == 'type') {
        $typeId = $data['id'];
        $code .= "/select/type/{$typeId}";

      } else if ($selectBy == 'man') {
        $manId = $data['id'];
        $code .= "/select/man/{$manId}";
      }
    }

    $certModel = new CertificateModel($Database);
    
    $currentPage = $params['page'] ?? 1;
    $itemsPerPage = $Settings->get('CatalogItemsPerPage') ?: 15;
    $offset = ($currentPage - 1) * $itemsPerPage;
    $offset = $offset >= 0 ? $offset : 0;

    $sortList = [
      'type' => 'TypeId',
      'man' => 'ManufacturerId',
      'name' => 'Standard',
      'link' => 'Link',
      'date' => 'Date'
    ];

    $sortColCode = array_key_exists($params['sort']['by'], $sortList) ? $params['sort']['by'] : 'type';
    $sortColumn = $sortList[$sortColCode];

    $sortDirCode = ($params['sort']['direction'] ?? null) == 'desc' ? 'desc' : 'asc';
    $sortDirection = $sortDirCode  == 'desc' ? 'DESC' : 'ASC';

    if ($code) {
      $code = trim($code, '/');
      $code = "{$this->code()}/{$code}/sort/{$sortColCode}/{$sortDirCode}";
    } else {
      $code = "{$this->code()}/sort/{$sortColCode}/{$sortDirCode}";
    }

    if ($typeId && $manId) {
      
      $types[$typeId]['Selected'] = 'selected';
      $mans[$manId]['Selected'] = 'selected';

      $itemsCountAll = $certModel->getCountByTypeAndManufacturer($typeId, $manId);
    } else if ($manId) {

      $mans[$manId]['Selected'] = 'selected';
      $itemsCountAll = $certModel->getCountByManufacturer($manId);
    
    } else if ($typeId) {
    
      $types[$typeId]['Selected'] = 'selected';
      $itemsCountAll = $certModel->getCountByType($typeId);
    
    } else
      $itemsCountAll = $certModel->getCountCertificates();

    $pagination = new PaginationComponent();

    $paginationData = $pagination->pages(
      $itemsPerPage,
      $itemsCountAll,
      $code,
      $currentPage,
      false
    );

    $pages = $paginationData['pages'];

    $nextPage = $paginationData['nextPage'];
    $prevPage = $paginationData['previousPage'];
    
    if ($typeId && $manId) {
      $both = true;
      $certificates =
        $certModel->getByTypeAndManufacturer($typeId, $manId, $itemsPerPage, $offset, $sortColumn, $sortDirection);
      
    } else if ($manId) {
    
      $certificates = $certModel->getByManufacturer($manId, $itemsPerPage, $offset, $sortColumn, $sortDirection);

    } else if ($typeId) {
    
      $certificates = $certModel->getByType($typeId, $itemsPerPage, $offset, $sortColumn, $sortDirection);
    
    } else {
    
      $certificates = $certModel->getCertificates($itemsPerPage, $offset, $sortColumn, $sortDirection);
    
    }

    $tableTemplate = new ListTemplate('_cert-row', $this->code());
    $tableTemplate = $tableTemplate->parse($certificates);

    return [
      'TypeId' => $typeId,
      'ManId' => $manId,
      'Table' => $tableTemplate,
      'Pagination' => count($pages) > 1 ? $pagination->view('default')->parse([
        'Previous' => [
          'Status' => $prevPage ? '' : 'aria-disabled="true"',
          'Link' => $prevPage['link'] ?: '#'
        ],
        'Next' => [
          'Status' => $nextPage ? '' : 'aria-disabled="true"',
          'Link' => $nextPage['link'] ?: '#'
        ],
        'List' => count($pages) > 1
          ? $pagination->partial('pages')->setCallback(function ($item) {
            return $item['active'] == true;
          })->parse($pages) : '',
      ]) : '',
    ];
  }
}
