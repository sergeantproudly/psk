<?php 
namespace Site\Pages;
use Engine\Library\Common;
use Engine\Library\Page;
use Engine\Library\Template;
use Engine\Library\ListTemplate;
use Site\Components\PaginationComponent;
use Site\Components\ArticlesComponent;
use Site\Components\BreadcrumbsComponent;
use Engine\Library\Youtube;


class ArticlesPage extends Page {
  const CODE = 'articles';
  const DIR = self::CODE;
  public $modelName = 'ArticleModel';

  function __construct () {
    parent::__construct(self::CODE, self::DIR);

    $this->setPages([
      'index' => ['template' => 'articles'], 
      'detail' => ['template' => 'articles__detail']
    ]);

    $this->setPartials([
      'list' => [
        'template' => 'articles__card',
        'type' => 'list'
      ]
    ]);
  }

  function index($params = []) {    
    global $Settings;

    $pagination = new PaginationComponent($this->model->getDB());

    $articlesPerPage = $Settings->get('ArticlesCount') ?: 10;
    $articlesCountAll = $this->model->getCountArticles();
    
    $currentPage = $params['page'] ?? 1;          

    $paginationData = $pagination->pages(
      $articlesPerPage, 
      $articlesCountAll, 
      $this->code(),
      $currentPage
    );

    $pages = $paginationData['pages'];

    $nextPage = $paginationData['nextPage'];
    $prevPage = $paginationData['previousPage'];

    $pageNumber = $currentPage <= count($pages) ? $currentPage : 1;

    $offset = ($pageNumber - 1) * $articlesPerPage;
    $offset = $offset >= 0 ? $offset : 0;

    if ($currentPage == 1) {
      $articles = $this->model->getArticles($articlesPerPage + 1, $offset);
    } else {
      $articles = $this->model->getArticles($articlesPerPage, $offset + 1);
    }
    $articles = Common::setLinks($articles, 'articles');

    if ($currentPage == 1) {
      $firstArticle = array_shift($articles);
      $firstArticle['TitleClass'] = mb_strlen($firstArticle['Title']) <= 70 ? 'title-lg' : 'title-md';
      $firstArticle['PreviewWebp'] = Common::flGetWebpByImage($firstArticle['Preview']);
      $firstArticle['Alt'] = htmlspecialchars($firstArticle['Title'], ENT_QUOTES);
      $firstArticleTemplate = new Template('partial/articles__first__card.htm', 'articles');
    }

    foreach ($articles as &$article) {
      $article['DateTime'] = Common::excess($article['PublishDate'], ' 00:00:00');
      $article['Date'] = Common::ModifiedDate($article['PublishDate']);
      $article['PreviewWebp'] = Common::flGetWebpByImage($article['Preview']);
      $article['Alt'] = htmlspecialchars($article['Title'], ENT_QUOTES);
    }
    
    $this->getPage('index')->addInclude($this->partial('list'));
    if (count($pages) > 1) {
      $this->getPage('index')->addInclude(
        $pagination->view('default'), 'pagination'
      );
    }    

    $breadcrumbs = new BreadcrumbsComponent;
    $breadcrumbsRendered = $breadcrumbs->render($this->code(), [
      ['Code' => '/', 'Link' => '/' ,'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => '/'.$this->code().'/' ,'Title' => $this->page['Title']],
    ]);


    return $this->getPage('index')->parse($this->page + [
      'Breadcrumbs' => $breadcrumbsRendered,
      'Class' => $currentPage == 1 ? 'news-offset' : '',
      'FirstCard' => $currentPage == 1 ? $firstArticleTemplate->parse($firstArticle) : '',
      'list' => $articles,
      'Pagination' => [
        'Class' => 'news__pagination',
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
      ]
    ]);
  }

  function detail($params = []) {
    global $Settings;

    if (!isset($params['article']) || empty($params['article']))
      return $this->index();

    $code = $params['article'];
    $article = $this->model->getArticleByCode($code);

    if (!$article) {
      Common::Get404Page();
    }

    $article['DateTime'] = Common::excess($article['PublishDate'], ' 00:00:00');
    $article['Date'] = Common::ModifiedDate($article['PublishDate']);
    $article['ImageWebp'] = Common::flGetWebpByImage($article['Image']);
    $article['Alt'] = htmlspecialchars($article['Title'], ENT_QUOTES);
    $article['ShortDescription'] = nl2br($article['ShortDescription']);
    $article['ShareUrl'] = urlencode($Settings->get('SiteUrl') . (substr($Settings->get('SiteUrl'), -1) != '' ? '/' : '') . self::CODE . '/' . $code . '/');
    $article['ShareTitle'] = htmlspecialchars($article['Title'], ENT_QUOTES);
    $article['ShareDescription'] = htmlspecialchars(strip_tags($article['ShortDescription']), ENT_QUOTES);
    $article['ShareImage'] = urlencode($Settings->get('SiteUrl')) . htmlspecialchars($article['Image'], ENT_QUOTES);

    if ($article['CodeVideo']) {
      $youtube = new Youtube();
      $article['CoverVideoWebp'] = Common::flGetWebpByImage($article['CoverVideo']);
      $article['CodeVideo'] = $youtube->GetCodeFromSource($article['CodeVideo']);
      $videoTemplate = new Template('articles__detail__video', 'articles');
      $videoRendered = $videoTemplate->parse($article);
    }

    $photos = $this->model->getArticlePhotos($code);
    if (count($photos)) {
      foreach ($photos as &$photo) {
        $photo['Alt'] = htmlspecialchars($photo['Title'], ENT_QUOTES);
        $photo['ImageWebp'] = Common::flGetWebpByImage($photo['Image']);
      }
      $photosTemplate = new Template('articles__detail__photos', 'articles');
      $photosListTemplate = new ListTemplate('articles__detail__photos__card', 'articles/partial');
      $photosRendered = $photosTemplate->parse([
        'List' => $photosListTemplate->parse($photos),
      ]);
    }
    
    $breadcrumbs = new BreadcrumbsComponent;
    $breadcrumbsRendered = $breadcrumbs->render($code, [
      ['Code' => '/', 'Link' => '/' ,'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => '/'.$this->code().'/' ,'Title' => $this->page['Title']],
      ['Code' => $code, 'Title' => $article['Title']],
    ]);

    $similarArticles = $this->model->getSimilarArticles($article, 3, 0);
    $similarArticles = Common::setLinks($similarArticles, 'articles');
    $articlesComponent = new ArticlesComponent($this->model->getDB());
    $articlesRendered = $articlesComponent->render($similarArticles, 'Другие новости', 'bl-news-similar');
    $article['Similar'] = $articlesRendered;

    return $this->getPage('detail')->parse($article + [
      'Breadcrumbs' => $breadcrumbsRendered,
      'Video' => isset($videoRendered) ? $videoRendered : '',
      'Photos' => isset($photosRendered) ? $photosRendered : '',
    ]);
  }

  function page($number) {
    $number = intval(array_shift($number));
    $number = is_int($number) ? $number : 1;
    $number =  $number >= 0 ?  $number : 1;

    return $this->index(['page' => $number]);
  }
}


?>
