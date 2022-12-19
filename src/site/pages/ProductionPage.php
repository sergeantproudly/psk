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
      'index' => ['template' => 'production_directions'], 
      'direction' => ['template' => 'production_direction'], 
      //'direction' => ['template' => 'production'], 
      'product' => ['template' => 'production'],
      'subcategory' => ['template' => 'production__subcategory'],
      'goody' => ['template' => 'production__goody']
    ]);

    $this->setPartials([
      'directions' => [
        'template' => 'production-direction__card',
        'type' => 'list'
      ],
      'direction_others' => [
        'template' => 'production-direction-others__card',
        'type' => 'list'
      ],
      'list' => [
        'template' => 'production__card',
        'type' => 'list'
      ],
      'categories' => [
        'template' => 'production-category__card',
        'type' => 'list'
      ],
      'photos' => [
        'template' => 'photo__card',
        'type' => 'list'
      ],
      'subcategories' => [
        'template' => 'subcategory__card',
        'type' => 'list'
      ],
      'goods' => [
        'template' => 'goody__card',
        'type' => 'list'
      ],
      'chars' => [
        'template' => 'goodychar__card',
        'type' => 'list'
      ],
    ]);
  }

  function index($params = []) {
    global $Database; 

    $content = $this->model->getContent($this->code());
    //$contacts = $this->model->getContent('contacts');

    $directions = $this->model->getProductsDirections();
    $directions = Common::setLinks($directions, 'production', 'direction');

    $this->getPage('index')->addInclude($this->partial('directions'));

    return $this->getPage('index')->parse($this->page + [
      'directions' => $directions,
    ]);
  }

  function direction($params = []) {
    if (!isset($params['direction']) || empty($params['direction']))
      return $this->index();

    global $Database;
    global $Settings;

    $this->model->init();

    $pagination = new PaginationComponent($this->model->getDB());
    $perPage = $Settings->get('GoodsCount') ?: 12;
    $goodsCount = $this->model->getDirectionGoodsCount($params['direction']);
    $currentPage = $params['page'] ?? 1;
    $paginationData = $pagination->pages(
      $perPage, 
      $goodsCount, 
      $this->code() . '/direction/' . $params['direction'],
      $currentPage
    );
    $pages = $paginationData['pages'];
    $nextPage = $paginationData['nextPage'];
    $prevPage = $paginationData['previousPage'];
    $pageNumber = $currentPage <= count($pages) ? $currentPage : 1;
    $offset = ($pageNumber - 1) * $perPage;
    $offset = $offset >= 0 ? $offset : 0;

    $content = $this->model->getContent($this->code());
    //$contacts = $this->model->getContent('contacts');

    $direction = $this->model->getDirectionByCode($params['direction']);

    $categories = $this->model->getProducts($params['direction']);
    $categories = Common::setLinks($categories, 'production');
    if (count($categories) > 1) {
      $this->getPage('direction')->addInclude($this->partial('categories'));

      // если единственная категория, выводим сразу ее подкатегории
    } elseif (count($categories) === 1) {    
      $subcategories = $this->model->getProductSubcategories($categories[0]['Code']);
      $subcategories = Common::setLinks($subcategories, 'production', $categories[0]['Code']);
      $this->getPage('direction')->addInclude($this->partial('subcategories'));
    }

    $this->getPage('direction')->addInclude($this->partial('goods'));
    if (count($pages) > 1) {
      $this->getPage('direction')->addInclude(
        $pagination->view('default'), 'pagination'
      );
    } 
    $this->getPage('direction')->addInclude($this->partial('direction_others'));
    
    $goods = $this->model->getDirectionGoods($params['direction'], $perPage, $offset);
    $goods = Common::setLinksByFields($goods, 'production', 'ProductCode', 'SubcategoryCode', 'Code');
    array_walk($goods, function(&$goody) {
      $goody['ImageWebp'] = Common::flGetWebpByImage($goody['Image']);
    });
    $count = Common::Word125($goodsCount, 'Найден ', 'Найдено ', 'Найдено ') . $goodsCount . ' ' . Common::Word125($goodsCount, ' товар', ' товара', ' товаров');

    $directionsOthers = $this->model->getProductsDirectionsOthers($params['direction']);
    $directionsOthers = Common::setLinks($directionsOthers, 'production', 'direction');

    // $blockCertsTemplate = new Template('bl-catalog-certs', 'production');
    // $blockCertsRendered = $blockCertsTemplate->parse([
    //   'Heading' => strip_tags($content['CertBlockHeading']),
    //   'Text' => $content['CertBlockText'],
    // ]);

    //$staffModel = new \Site\Models\StaffModel($Database); 
    //$person = $staffModel->getPersonById(self::PERSON_ID);

    // $blockPromoTemplate = new Template('bl-promo', 'production');
    // $blockPromoRendered = $blockPromoTemplate->parse([
    //   'PhotoPreview' => $content['PromoAuthorPhoto'] ?: $person['PhotoPreview'],
    //   'Name' => $person['Name'],
    //   'Rank' => $person['Rank'],
    //   'Heading' => strip_tags($content['PromoHeading']),
    //   'Text' => $content['PromoText'],
    //   'Phone' => $contacts['Phone'],
    //   'PhoneCommon' => $contacts['PhoneCommon'],
    //   'Email' => $contacts['Email'],
    //   'EmailCommon' => $contacts['EmailCommon'],
    // ]);

    return $this->getPage('direction')->parse($direction + [
      'Categories' => $categories,
      'Subcategories' => $subcategories,
      'Goods' => $goods,
      'Count' => $count,
      //'certs' => $blockCertsRendered,
      //'promo' => $blockPromoRendered,
      'Pagination' => [
        'Class' => 'products__pagination',
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
      ],
      'direction_others' => $directionsOthers,
    ]);
  }

  function product($params = []) {
    if (!isset($params['product']) || empty($params['product']))
      return $this->index();

    global $Database;
    global $Settings;

    $this->model->init();

    $pagination = new PaginationComponent($this->model->getDB());
    $perPage = $Settings->get('GoodsCount') ?: 12;
    $goodsCount = $this->model->getCategoryGoodsCount($params['product']);
    $currentPage = $params['page'] ?? 1;
    $paginationData = $pagination->pages(
      $perPage, 
      $goodsCount, 
      $this->code() . '/' . $params['product'],
      $currentPage
    );
    $pages = $paginationData['pages'];
    $nextPage = $paginationData['nextPage'];
    $prevPage = $paginationData['previousPage'];
    $pageNumber = $currentPage <= count($pages) ? $currentPage : 1;
    $offset = ($pageNumber - 1) * $perPage;
    $offset = $offset >= 0 ? $offset : 0;

    $content = $this->model->getContent($this->code());

    $product = $this->model->getProductByCode($params['product']);

    $subcategories = $this->model->getProductSubcategories($params['product']);
    $subcategories = Common::setLinks($subcategories, 'production', $product['Code']);

    $this->getPage('product')->addInclude($this->partial('subcategories'));
    $this->getPage('product')->addInclude($this->partial('goods'));
    if (count($pages) > 1) {
      $this->getPage('product')->addInclude(
        $pagination->view('default'), 'pagination'
      );
    } 
    
    $goods = $this->model->getCategoryGoods($params['product'], $perPage, $offset);
    $goods = Common::setLinksByFields($goods, 'production', 'ProductCode', 'SubcategoryCode', 'Code');
    array_walk($goods, function(&$goody) {
      $goody['ImageWebp'] = Common::flGetWebpByImage($goody['Image']);
    });
    $count = Common::Word125($goodsCount, 'Найден ', 'Найдено ', 'Найдено ') . $goodsCount . ' ' . Common::Word125($goodsCount, ' товар', ' товара', ' товаров');

    return $this->getPage('product')->parse($product + [
      'Subcategories' => $subcategories,
      'Goods' => $goods,
      'Count' => $count,
      'Pagination' => [
        'Class' => 'products__pagination',
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
      ],
    ]);
  }

  function subcategory($params = []) {
    if (!isset($params['product']) || empty($params['product']) || !isset($params['subcategory']) || empty($params['subcategory']))
      return $this->index();

    global $Database;
    global $Settings;

    $this->model->init();

    $content = $this->model->getContent($this->code());
    // $contacts = $this->model->getContent('contacts');

    $codeProduct = $params['product'];
    $codeSubcategory = $params['subcategory'];
    $product = $this->model->getProductByCode($codeProduct);
    $subcategory = $this->model->getSubcategoryByCode($codeSubcategory);
    
    $breadcrumbs = new BreadcrumbsComponent;
    $breadcrumbsRendered = $breadcrumbs->render($codeSubcategory, [
      ['Code' => '/', 'Link' => '/' ,'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => '/'.$this->code().'/' ,'Title' => $this->page['Title']],
      ['Code' => $codeProduct, 'Link' => '/'.$this->code().'/'.$codeProduct.'/' ,'Title' => $product['Title']],
      ['Code' => $codeSubcategory, 'Title' => $subcategory['Title']],
    ]);

    $goods = $this->model->getSubCategoryGoods($subcategory['Id']);
    $goods = Common::setLinks($goods, 'production/'.$codeProduct, $codeSubcategory);

    $subcategories = $this->model->getProductSubcategories($params['product']);
    $subcategories = Common::setLinks($subcategories, 'production', $product['Code']);

    $this->getPage('subcategory')->addInclude($this->partial('goods'));
    $this->getPage('subcategory')->addInclude($this->partial('subcategories'));

    // $staffModel = new \Site\Models\StaffModel($Database);
    // $person = $staffModel->getPersonById(self::PERSON_ID);

    // $blockPromoTemplate = new Template('bl-promo', 'production');
    // $blockPromoRendered = $blockPromoTemplate->parse([
    //   'PhotoPreview' => $content['PromoAuthorPhoto'] ?: $person['PhotoPreview'],
    //   'Name' => $person['Name'],
    //   'Rank' => $person['Rank'],
    //   'Heading' => strip_tags($content['PromoHeading']),
    //   'Text' => $content['PromoText'],
    //   'Phone' => $contacts['Phone'],
    //   'PhoneCommon' => $contacts['PhoneCommon'],
    //   'Email' => $contacts['Email'],
    //   'EmailCommon' => $contacts['EmailCommon'],
    // ]);

    // $otherProducts = $this->model->getSimilarProduction($product);
    // $otherProducts = Common::setLinks($otherProducts, 'production');
    // $blockOtherTemplate = new Template('bl-other', 'production');
    // $blockOtherItemTemplate = new ListTemplate('bl-other__item', 'production/partial');
    // $blockOtherItemTemplate  = $blockOtherItemTemplate->parse($otherProducts);
    // $blockOtherRendered = $blockOtherTemplate->parse([
    //       'Title' => $content['BlockOtherTitle'],
    //       'List' => $blockOtherItemTemplate
    // ]);

    return $this->getPage('subcategory')->parse($subcategory + $this->page + [
      'Subcategories' => $subcategories,
      'breadcrumbs' => $breadcrumbsRendered,
      'goods' => $goods,
      //'promo' => $blockPromoRendered,
      //'other' => $blockOtherRendered,
    ]);
  }

  function goody($params = []) {
    if (!isset($params['product']) || empty($params['product']) || !isset($params['subcategory']) || empty($params['subcategory']) || !isset($params['goody']) || empty($params['goody']))
      return $this->index();

    global $Database;

    $this->model->init();

    $content = $this->model->getContent($this->code());
    $contacts = $this->model->getContent('contacts');
    $contacts['PhoneLink'] = Common::GetTelLink($contacts['Phone']);
    $contacts['PhoneCommonLink'] = Common::GetTelLink($contacts['PhoneCommon']);

    $codeProduct = $params['product'];
    $codeSubcategory = $params['subcategory'];
    $codeGoody = $params['goody'];
    $product = $this->model->getProductByCode($codeProduct);
    $subcategory = $this->model->getSubcategoryByCode($codeSubcategory);
    $goody = $this->model->getGoodyByCode($codeGoody);
    $goody['PreviewFullWebp'] = Common::flGetWebpByImage($goody['PreviewFull']);
    
    $breadcrumbs = new BreadcrumbsComponent;
    $breadcrumbsRendered = $breadcrumbs->render($codeGoody, [
      ['Code' => '/', 'Link' => '/' ,'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => '/'.$this->code().'/' ,'Title' => $this->page['Title']],
      ['Code' => $codeProduct, 'Link' => '/'.$this->code().'/'.$codeProduct.'/' ,'Title' => $product['Title']],
      ['Code' => $codeSubcategory, 'Link' => '/'.$this->code().'/'.$codeProduct.'/'.$codeSubcategory.'/' ,'Title' => $subcategory['Title']],
      ['Code' => $codeGoody, 'Title' => $goody['Title']],
    ]);

    $chars = $this->model->getGoodyChars($goody['Id']);
    $chars = Common::setNl2Br($chars, 'Value');
    $this->getPage('goody')->addInclude($this->partial('chars'));

    // $staffModel = new \Site\Models\StaffModel($Database);
    // $person = $staffModel->getPersonById(self::PERSON_ID);

    // $blockPromoTemplate = new Template('bl-promo', 'production');
    // $blockPromoRendered = $blockPromoTemplate->parse([
    //   'PhotoPreview' => $content['PromoAuthorPhoto'] ?: $person['PhotoPreview'],
    //   'Name' => $person['Name'],
    //   'Rank' => $person['Rank'],
    //   'Heading' => strip_tags($content['PromoHeading']),
    //   'Text' => $content['PromoText'],
    //   'Phone' => $contacts['Phone'],
    //   'PhoneCommon' => $contacts['PhoneCommon'],
    //   'Email' => $contacts['Email'],
    //   'EmailCommon' => $contacts['EmailCommon'],
    // ]);

    $otherGoods = $this->model->getOtherGoods($params['goody'], 4);
    $otherGoods = Common::setLinks($otherGoods, 'production/'.$codeProduct, $codeSubcategory);
    $blockOtherTemplate = new Template('bl-other', 'production');
    $blockOtherItemTemplate = new ListTemplate('bl-other__item', 'production/partial');
    $blockOtherItemTemplate  = $blockOtherItemTemplate->parse($otherGoods);
    $blockOtherRendered = $blockOtherTemplate->parse([
          'Title' => $content['BlockOtherTitle'],
          'List' => $blockOtherItemTemplate
    ]);

    return $this->getPage('goody')->parse($goody + $contacts + $this->page + [
      'breadcrumbs' => $breadcrumbsRendered,
      'chars' => $chars,
      // 'promo' => $blockPromoRendered,
      'other' => $blockOtherRendered,
    ]);
  }
}


?>
