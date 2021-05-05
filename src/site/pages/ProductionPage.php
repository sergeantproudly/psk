<?php 
namespace Site\Pages;
use Engine\Library\Common;
use Engine\Library\Page;
use Engine\Library\Template;
use Engine\Library\ListTemplate;
use Site\Components\PaginationComponent;
use Site\Components\BreadcrumbsComponent;
use Site\Models\StaffModel;


class ProductionPage extends Page {
  const CODE = 'production';
  const DIR = self::CODE;
  public $modelName = 'ProductionModel';

  const PERSON_ID = 21;

  function __construct () {
    parent::__construct(self::CODE, self::DIR);

    $this->setPages([
      'index' => ['template' => 'production'], 
      'detail' => ['template' => 'production__detail']
    ]);

    $this->setPartials([
      'list' => [
        'template' => 'production__card',
        'type' => 'list'
      ],
      'photos' => [
        'template' => 'photo__card',
        'type' => 'list'
      ],
    ]);
  }

  function index($params = []) {
    global $Database; 

    $content = $this->model->getContent($this->code());
    $contacts = $this->model->getContent('contacts');

    $products = $this->model->getProducts($articlesPerPage, $offset);
    $products = Common::setLinks($products, 'production');

    $this->getPage('index')->addInclude($this->partial('list'));

    $blockCertsTemplate = new Template('bl-catalog-certs', 'production');
    $blockCertsRendered = $blockCertsTemplate->parse([
      'Heading' => strip_tags($content['CertBlockHeading']),
      'Text' => $content['CertBlockText'],
    ]);

    $staffModel = new \Site\Models\StaffModel($Database); 
    $person = $staffModel->getPersonById(self::PERSON_ID);

    $blockPromoTemplate = new Template('bl-promo', 'production');
    $blockPromoRendered = $blockPromoTemplate->parse([
      'PhotoPreview' => $content['PromoAuthorPhoto'] ?: $person['PhotoPreview'],
      'Name' => $person['Name'],
      'Rank' => $person['Rank'],
      'Heading' => strip_tags($content['PromoHeading']),
      'Text' => $content['PromoText'],
      'Phone' => $contacts['Phone'],
      'PhoneCommon' => $contacts['PhoneCommon'],
      'Email' => $contacts['Email'],
      'EmailCommon' => $contacts['EmailCommon'],
    ]);

    return $this->getPage('index')->parse($this->page + [
      'list' => $products,
      'certs' => $blockCertsRendered,
      'promo' => $blockPromoRendered,
    ]);
  }

  function detail($params = []) {
    if (!isset($params['product']) || empty($params['product']))
      return $this->index();

    global $Database;

    $content = $this->model->getContent($this->code());
    $contacts = $this->model->getContent('contacts');

    $code = $params['product'];
    $product = $this->model->getProductByCode($code);
    
    $breadcrumbs = new BreadcrumbsComponent;
    $breadcrumbsRendered = $breadcrumbs->render($code, [
      ['Code' => '/', 'Link' => '/' ,'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => '/'.$this->code().'/' ,'Title' => $this->page['Title']],
      ['Code' => $code, 'Title' => $product['Title']],
    ]);

    $photos = $this->model->getProductImages($product['Id']);

    $this->getPage('detail')->addInclude($this->partial('photos'));

    /*
    $similarArticles = $this->model->getSimilarArticles($article, 3, 0);
    $similarArticles = Common::setLinks($similarArticles, 'articles');
    $articlesComponent = new ArticlesComponent($this->model->getDB());
    $articlesRendered = $articlesComponent->render($similarArticles, 'Другие новости', 'bl-news-similar');
    $article['Similar'] = $articlesRendered;
    */

    $staffModel = new \Site\Models\StaffModel($Database);
    $person = $staffModel->getPersonById(self::PERSON_ID);

    $blockPromoTemplate = new Template('bl-promo', 'production');
    $blockPromoRendered = $blockPromoTemplate->parse([
      'PhotoPreview' => $content['PromoAuthorPhoto'] ?: $person['PhotoPreview'],
      'Name' => $person['Name'],
      'Rank' => $person['Rank'],
      'Heading' => strip_tags($content['PromoHeading']),
      'Text' => $content['PromoText'],
      'Phone' => $contacts['Phone'],
      'PhoneCommon' => $contacts['PhoneCommon'],
      'Email' => $contacts['Email'],
      'EmailCommon' => $contacts['EmailCommon'],
    ]);

    $otherProducts = $this->model->getSimilarProduction($product);
    $otherProducts = Common::setLinks($otherProducts, 'production');
    $blockOtherTemplate = new Template('bl-other', 'production');
    $blockOtherItemTemplate = new ListTemplate('bl-other__item', 'production/partial');
    $blockOtherItemTemplate  = $blockOtherItemTemplate->parse($otherProducts);
    $blockOtherRendered = $blockOtherTemplate->parse([
          'Title' => $content['BlockOtherTitle'],
          'List' => $blockOtherItemTemplate
    ]);

    return $this->getPage('detail')->parse($product + $this->page + [
      'breadcrumbs' => $breadcrumbsRendered,
      'photos' => $photos,
      'promo' => $blockPromoRendered,
      'other' => $blockOtherRendered,
    ]);
  }
}


?>
