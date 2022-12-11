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

class CertsPage extends Page {
  protected $template;

  public function __construct() {
    parent::__construct('certs');

    $name      = $this->code();
    $directory = $name;

    $this->template = new Template($name, $directory);
  }

  public function index ($params) {
    $userSession = new UserSession();

    global $Database;
    global $Settings;

    $certsModel = new \Site\Models\CertModel($Database);
    $pageModel = new \Site\Models\PageModel($Database);

    $itemsPerPage = $Settings->get('CertsItemsPerPage') ?: 15;
    $currentPage = intval($params['page']);

    $search = $params['search'] ?? false;

    if ($search) {
    
      $itemsCountAll = $certsModel->getCountItemsLike($search);
    
    } else {
    
      $itemsCountAll = $certsModel->getCountItems();
    
    }

    $offset = ($currentPage - 1) * $itemsPerPage;
    $offset = $offset >= 0 ? $offset : 0;

    $content = $pageModel->getContent($this->code());
    if ($search)
      $certs = $certsModel->getAllLike($search, $itemsPerPage, $offset);
    else
      $certs = $certsModel->getAllItems($itemsPerPage, $offset );

    $fileTemplate = new Template('_certs-file-row.htm', 'certs');
    $termTemplate = new Template('_certs-term-row.htm', 'certs');
    foreach ($certs as $i => $cert) {
      $certs[$i]['FilesHtml'] = '';
      $certs[$i]['TermsHtml'] = '';
      $hasExpired = false;

      if ($cert['Files']) {
        $counter = 0;
        foreach ($cert['Files'] as $file) {
          $counter++;
          if (strtotime($file['TermDate']) + 86400 < time()) {
            $hasExpired = true;
            $certs[$i]['FilesHtml'] .= '<div class="js-toggler toggler">показать старые</div><div class="hidden">';
            $certs[$i]['TermsHtml'] .= '<div class="hidden">';
          }

          if (!$file['Title']) $file['Title'] = 'Сертификат ' . ($counter > 1 ? $counter : '');
          $certs[$i]['FilesHtml'] .= $fileTemplate->parse($file);
          $certs[$i]['TermsHtml'] .= $termTemplate->parse($file);
        }

        if ($hasExpired) {
          $certs[$i]['FilesHtml'] .= '</div>';
          $certs[$i]['TermsHtml'] .= '</div>';
        }
      }
    }

    $pagination = new PaginationComponent($Database);

    $code = $this->code();
    if ($search)
      $code = "{$code}/search/{$search}";

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

    $table = new ListTemplate('_certs-row.htm', 'certs');
    $table = $table->parse($certs);

    $breadcrumbs = new BreadcrumbsComponent();

    $route = [
      ['Code' => '/', 'Link' => '/' ,'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => '/'.$this->code().'/' ,'Title' => $this->getTitle()],
    ];

    $breadcrumbsRendered = $breadcrumbs->render($this->code(), $route);

    return $this->template->parse([
      'Title' => "<a href='/{$this->code}/'>{$content['Heading']}</a>",
      'User' => [
        'Id' => $userSession->id(),
      ],
      'Breadcrumbs' => $breadcrumbsRendered,
      'Search' => $search,
      'Table' => $table,
      'SearchAction' => "/{$this->code()}/search/",
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

}
