<?php
namespace Site\Pages;

use Engine\Library\CatalogCart;
use Engine\Library\ListTemplate;
use Engine\Library\Page;
use Engine\Library\Template;
use Engine\Library\UserSession;
use Engine\Utility\Morphology;
use Site\Components\BreadcrumbsComponent;
use Site\Components\PaginationComponent;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory; 

class CatalogPage extends Page {
  protected $template;

  public function __construct() {
    parent::__construct('catalog');

    $name      = $this->code();
    $directory = $name;

    $this->template = new Template($name, $directory);
  }

  public function index ($params) {
    $userSession = new UserSession();

    global $Database;
    global $Settings;

    $catalogModel = new \Site\Models\CatalogModel($Database);
    $pageModel = new \Site\Models\PageModel($Database);

    $itemsPerPage = $Settings->get('CatalogItemsPerPage') ?: 15;
    $currentPage = intval($params['page']);

    $search = $params['search'] ?? false;
    $filter = $params['filter'] ?? false;

    if ($search && $filter) {
      
      $itemsCountAll = $catalogModel->getCountItemsLikeWithFilter($search, $filter);

    } else if ($search) {
    
      $itemsCountAll = $catalogModel->getCountItemsLike($search);
    
    } else if ($filter) {
    
      $itemsCountAll = $catalogModel->getCountItemsFiltered($filter);
    
    } else {
    
      $itemsCountAll = $catalogModel->getCountItems();
    
    }

    $offset = ($currentPage - 1) * $itemsPerPage;
    $offset = $offset >= 0 ? $offset : 0;

    $content = $pageModel->getContent($this->code());
    if ($search && $filter)
      $catalog = $catalogModel->getAllLikeWithFilter($search, $filter, $itemsPerPage, $offset);
    else if ($search)
      $catalog = $catalogModel->getAllLike($search, $itemsPerPage, $offset);
    else if ($filter)
      $catalog = $catalogModel->getAllByFilter($filter, $itemsPerPage, $offset);
    else
      $catalog = $catalogModel->getAllItems($itemsPerPage, $offset );

    $pagination = new PaginationComponent($Database);

    $code = $this->code();
    if ($search && $filter)
      $code = "{$code}/filter/{$filter}/search/{$search}";
    else if ($search)
      $code = "{$code}/search/{$search}";
    else if ($filter)
      $code = "{$code}/filter/{$filter}";

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

    $table = new ListTemplate('_catalog-row.htm', 'catalog');
    $table = $table->parse($catalog);

    $breadcrumbs = new BreadcrumbsComponent();

    $route = [
      ['Code' => '/', 'Link' => '/' ,'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => '/'.$this->code().'/' ,'Title' => $this->getTitle()],
    ];

    $filters = ['fastshipping' => 'Краткий срок поставки', 'minimumbalance' => 'Неснижаемый остаток'];

    if ($filter) {
      $route[] = ['Code' => $filter, 'Title' => $filters[$filter]];
    }

    $breadcrumbsRendered = $breadcrumbs->render($filter ?: $this->code(), $route);

    $downloadLink = '/'.$this->code().'/download';

    $cart = new CatalogCart();
    $itemCount = 0;
    $totalAmount = 0;

    if (!$cart->isEmpty()) {
      $idAmountPairs = $cart->getAll();
      $data = $catalogModel->getItems($idAmountPairs);
      $items = $data['items'];
      $itemCount = $data['count'];
      $itemCountMsg  = $itemCount . ' ' . Morphology::numeral($itemCount ,
          ['наименование', 'наименования', 'наименований']);

      $totalAmount = $data['totalAmount'];
      $totalAmountMsg = $totalAmount . ' ' . Morphology::numeral($totalAmount,
          ['позиция', 'позиции', 'позиций']);

      $cartTemplate = new ListTemplate('_cart-row', 'catalog');
      $cartTemplate = $cartTemplate->parse($items);
    }
//    var_dump("COUNT $itemCount TOTAL $totalAmount");
    if (!$itemCount || !$totalAmount) {
      $cartMsg = "<span class='js-cart-count'>нет товаров</span> <span class='js-cart-amount'></span>";
    } else {
      $cartMsg = "<span class='js-cart-count'>{$itemCountMsg}</span> <span class='js-cart-amount'>{$totalAmountMsg}</span>";
    }

    $cartMsg = "<span class='js-cart-message'>{$cartMsg}</span>";

    return $this->template->parse([
      'Title' => "<a href='/{$this->code}/'>{$content['Heading']}</a>",
      'User' => [
        'Id' => $userSession->id(),
      ],
      'Breadcrumbs' => $breadcrumbsRendered,
      'DownloadLink' => $downloadLink,
      'Search' => $search,
      'Table' => $table,
      'CartMessage' => $cartMsg,
      'FastShipping' => [
        'Class' => $filter == 'fastshipping' ? 'js-filter-active' : '',
        'Link' => "/{$this->code()}/filter/fastshipping/",
      ],
      'MinimumBalance' => [
        'Class' => $filter == 'minimumbalance' ? 'js-filter-active' : '',
        'Link' => "/{$this->code()}/filter/minimumbalance/",
      ],
      'SearchAction' => $filter ? "/{$this->code()}/filter/{$filter}/search/" :
      "/{$this->code()}/search/",
      'Cart' => $cartTemplate ?? '',
      'CartEmpty' => $cart->isEmpty() ? 'data-cart-empty' : '',
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

//  public function page ($params) {
//    $number = intval(array_shift($params));
//    $number = is_int($number) ? $number : 1;
//
//    return $this->index(['page' => $number]);
//  }

//  public function search ($params) {
//    $searchValue = count ($params) == 1 ? array_shift($params) : array_pop($params);
//    var_dump($searchValue);
//    return $this->index(['search' => $searchValue]);
//  }

  public function download($params = []) {
    global $Database;

    $catalogModel = new \Site\Models\CatalogModel($Database);
    $catalog = 
      $catalogModel->getAllItems();

    $fileName = 'catalogue_' . date('d_m_Y') . '.xls';

    $spreadsheet = new Spreadsheet();
    $spreadsheet->getProperties()
      ->setCreator('ПСК СтройИнвест')
      ->setLastModifiedBy('ПСК СтройИнвест')
      ->setTitle('Номенклатор')
      ->setSubject('Номенклатор');

    $actSheet = $spreadsheet->setActiveSheetIndex(0);
    $actSheet->setTitle('Номенклатор');
    $actSheet->getColumnDimension('C')->setWidth(80);
    $actSheet->getColumnDimension('D')->setWidth(35);
    $actSheet->getColumnDimension('B')->setAutoSize(true);   
    $actSheet->getColumnDimension('F')->setAutoSize(true);
    for ($z = 1; $z < 7; $z++){
      $actSheet->getStyleByColumnAndRow($z, 1)->getFont()->setBold(true);
    }
    $actSheet->setCellValueByColumnAndRow(1, 1, '№');
    $actSheet->setCellValueByColumnAndRow(2, 1, 'Мнемокод'); 
    $actSheet->setCellValueByColumnAndRow(3, 1, 'Наименование');
    $actSheet->setCellValueByColumnAndRow(4, 1, 'Описание');
    $actSheet->setCellValueByColumnAndRow(5, 1, 'Ед.изм.');
    $actSheet->setCellValueByColumnAndRow(6, 1, 'Цена, Р');

    $i=2;
    foreach ($catalog as $rec) {
      $actSheet->setCellValueByColumnAndRow(1, $i, $i-1);
      $actSheet->setCellValueByColumnAndRow(2, $i, $rec['Mnemocode'], 'String');
      $actSheet->setCellValueByColumnAndRow(3, $i, $rec['Title']);
      $actSheet->setCellValueByColumnAndRow(4, $i, $rec['Description']);
      $actSheet->setCellValueByColumnAndRow(5, $i, $rec['Measure']);
      $actSheet->setCellValueByColumnAndRow(6, $i, $rec['Price']);     
      $i++;
    }

    // Redirect output to a clients web browser (Xls)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

    // If you're serving to IE over SSL, then the following may be needed
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0 
     
    header ( "Content-Disposition: attachment; filename=catalogue_" . date('d_m_Y') . ".xls" );
    
    $writer = IOFactory::createWriter($spreadsheet, 'Xls');
    $writer->save('php://output');
  }

}
