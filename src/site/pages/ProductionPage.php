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

  //const PERSON_ID = 21;

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

    /*
    $content = $this->model->getContent($this->code());
    //$contacts = $this->model->getContent('contacts');

    $directions = $this->model->getProductsDirections();
    $directions = Common::setLinks($directions, 'production', 'direction');

    $this->getPage('index')->addInclude($this->partial('directions'));

    $breadcrumbs = new BreadcrumbsComponent;
    $breadcrumbsRendered = $breadcrumbs->render($this->code(), [
      ['Code' => '/', 'Link' => '/' ,'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => '/'.$this->code().'/' ,'Title' => $this->page['Title']],
    ]);

    return $this->getPage('index')->parse($this->page + [
      'directions' => $directions,
      'breadcrumbs' => $breadcrumbsRendered,
    ]);
    */
    return $this->direction(['direction' => 'sip-armatura']);
  }

  function direction($params = []) {
    if (!isset($params['direction']) || empty($params['direction']))
      return $this->index();

    global $Database;
    global $Settings;

    $this->model->init();

    $searchKeyword = (string) $_GET['search'];

    $pagination = new PaginationComponent($this->model->getDB());
    $perPage = $Settings->get('GoodsCount') ?: 12;
    $goodsCount = !empty($searchKeyword) ? $this->model->getDirectionGoodsCountSearched($params['direction'], $searchKeyword) : $this->model->getDirectionGoodsCount($params['direction']);
    $currentPage = $params['page'] ?? 1;
    $paginationData = $pagination->pages(
      $perPage, 
      $goodsCount, 
      $this->code() . '/direction/' . $params['direction'],
      $currentPage,
      true,
      $searchKeyword ? '?search=' . $searchKeyword : ''
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
    if (!$direction) {
      Common::Get404Page();
    }

    if ($direction['Text']) $direction['Text'] = '<div class="main__subtitle">' . $direction['Text'] . '</div>';

    $breadcrumbs = new BreadcrumbsComponent;
    $breadcrumbsRendered = $breadcrumbs->render($params['direction'], [
      ['Code' => '/', 'Link' => '/' ,'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => '/'.$this->code().'/' ,'Title' => $this->page['Title']],
      ['Code' => $params['direction'], 'Title' => strip_tags($direction['Title'])],
    ]);

    $categories = $this->model->getProducts($params['direction']);
    $categories = Common::setLinks($categories, 'production');
    if (count($categories) > 1) {
      $this->getPage('direction')->addInclude($this->partial('categories'));
      $mobileSelectedOption = strip_tags($categories[0]['Title']);

      // если единственная категория, выводим сразу ее подкатегории
    } elseif (count($categories) === 1) {    
      $subcategories = $this->model->getProductSubcategories($categories[0]['Code']);
      $subcategories = Common::setLinks($subcategories, 'production', $categories[0]['Code']);
      $this->getPage('direction')->addInclude($this->partial('subcategories'));
      $mobileSelectedOption = strip_tags($subcategories[0]['Title']);
    }

    $this->getPage('direction')->addInclude($this->partial('goods'));
    if (count($pages) > 1) {
      $this->getPage('direction')->addInclude(
        $pagination->view('default'), 'pagination'
      );
    } 
    $this->getPage('direction')->addInclude($this->partial('direction_others'));
    
    $goods = !empty($searchKeyword) ? $this->model->getDirectionGoodsSearched($params['direction'], $searchKeyword, $perPage, $offset) : $this->model->getDirectionGoods($params['direction'], $perPage, $offset);
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

    $action = '/' . $this->code() . '/direction/' . $params['direction'] . '/#catalogue';

    return $this->getPage('direction')->parse($direction + [
      'Categories' => $categories,
      'Subcategories' => $subcategories,
      'breadcrumbs' => $breadcrumbsRendered,
      'Action' => $action,
      'Keyword' => $searchKeyword ?: '',
      'MobileSelectedOption' => $mobileSelectedOption,
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

    $searchKeyword = (string) $_GET['search'];

    $pagination = new PaginationComponent($this->model->getDB());
    $perPage = $Settings->get('GoodsCount') ?: 12;
    $goodsCount = !empty($searchKeyword) ? $this->model->getCategoryGoodsCountSearched($params['product'], $searchKeyword) : $this->model->getCategoryGoodsCount($params['product']);
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
    if (!$product) {
      Common::Get404Page();
    }

    if ($product['ShortDescription']) $product['ShortDescription'] = '<div class="main__subtitle">' . $product['ShortDescription'] . '</div>';

    $breadcrumbs = new BreadcrumbsComponent;
    $breadcrumbsRendered = $breadcrumbs->render($codeProduct, [
      ['Code' => '/', 'Link' => '/' ,'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => '/'.$this->code().'/' ,'Title' => $this->page['Title']],
      ['Code' => $codeProduct, 'Title' => strip_tags($product['Title'])],
    ]);

    $subcategories = $this->model->getProductSubcategories($params['product']);
    $subcategories = Common::setLinks($subcategories, 'production', $product['Code']);

    $this->getPage('product')->addInclude($this->partial('subcategories'));
    $this->getPage('product')->addInclude($this->partial('goods'));
    if (count($pages) > 1) {
      $this->getPage('product')->addInclude(
        $pagination->view('default'), 'pagination'
      );
    }
    
    $goods = !empty($searchKeyword) ? $this->model->getCategoryGoodsSearched($params['product'], $searchKeyword, $perPage, $offset) : $this->model->getCategoryGoods($params['product'], $perPage, $offset);
    $goods = Common::setLinksByFields($goods, 'production', 'ProductCode', 'SubcategoryCode', 'Code');
    array_walk($goods, function(&$goody) {
      $goody['ImageWebp'] = Common::flGetWebpByImage($goody['Image']);
    });
    $count = Common::Word125($goodsCount, 'Найден ', 'Найдено ', 'Найдено ') . $goodsCount . ' ' . Common::Word125($goodsCount, ' товар', ' товара', ' товаров');

    $action = '/' . $this->code() . '/' . $params['product'] . '/#catalogue';

    return $this->getPage('product')->parse($product + [
      'Subcategories' => $subcategories,
      'breadcrumbs' => $breadcrumbsRendered,
      'Action' => $action,
      'Keyword' => $searchKeyword ?: '',
      'MobileSelectedOption' => strip_tags($subcategories[0]['Title']),
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
    if (!$subcategory) {
      Common::Get404Page();
    }
    if ($subcategory['ShortDescription']) $subcategory['ShortDescription'] = '<div class="main__subtitle">' . $subcategory['ShortDescription'] . '</div>';
    $subcategory['TitleCleared'] = strip_tags($subcategory['Title']);
    
    $breadcrumbs = new BreadcrumbsComponent;
    $breadcrumbsRendered = $breadcrumbs->render($codeSubcategory, [
      ['Code' => '/', 'Link' => '/' ,'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => '/'.$this->code().'/' ,'Title' => $this->page['Title']],
      //['Code' => $codeProduct, 'Link' => '/'.$this->code().'/'.$codeProduct.'/' ,'Title' => strip_tags($product['Title'])],
      ['Code' => $codeSubcategory, 'Title' => strip_tags($subcategory['Title'])],
    ]);

    $searchKeyword = (string) $_GET['search'];

    $pagination = new PaginationComponent($this->model->getDB());
    $perPage = $Settings->get('GoodsCount') ?: 12;
    $goodsCount = !empty($searchKeyword) ? $this->model->getSubCategoryGoodsCountSearched($subcategory['Id'], $searchKeyword) : $this->model->getSubCategoryGoodsCount($subcategory['Id']);
    $currentPage = $params['page'] ?? 1;
    $paginationData = $pagination->pages(
      $perPage, 
      $goodsCount, 
      $this->code() . '/' . $params['product'] . '/' . $codeSubcategory,
      $currentPage
    );
    $pages = $paginationData['pages'];
    $nextPage = $paginationData['nextPage'];
    $prevPage = $paginationData['previousPage'];
    $pageNumber = $currentPage <= count($pages) ? $currentPage : 1;
    $offset = ($pageNumber - 1) * $perPage;
    $offset = $offset >= 0 ? $offset : 0;

    $goods = !empty($searchKeyword) ? $this->model->getSubCategoryGoodsSearched($subcategory['Id'], $searchKeyword, $perPage, $offset) : $this->model->getSubCategoryGoods($subcategory['Id'], $perPage, $offset);
    $goods = Common::setLinks($goods, 'production/'.$codeProduct, $codeSubcategory);
    $count = Common::Word125($goodsCount, 'Найден ', 'Найдено ', 'Найдено ') . $goodsCount . ' ' . Common::Word125($goodsCount, ' товар', ' товара', ' товаров');

    $subcategories = $this->model->getProductSubcategories($params['product']);
    $subcategories = Common::setLinks($subcategories, 'production', $product['Code']);
    array_walk($subcategories, function(&$subcategory, $key, $codeSubcategory) {
      if ($subcategory['Code'] === $codeSubcategory) $subcategory['Class'] = ' active';
    }, $codeSubcategory);

    $this->getPage('subcategory')->addInclude($this->partial('goods'));
    $this->getPage('subcategory')->addInclude($this->partial('subcategories'));
    if (count($pages) > 1) {
      $this->getPage('subcategory')->addInclude(
        $pagination->view('default'), 'pagination'
      );
    }

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

    $action = '/' . $this->code() . '/' . $codeProduct . '/' . $codeSubcategory . '/#catalogue';

    return $this->getPage('subcategory')->parse($subcategory + $this->page + [
      'Subcategories' => $subcategories,
      'breadcrumbs' => $breadcrumbsRendered,
      'goods' => $goods,
      'Action' => $action,
      'Keyword' => $searchKeyword ?: '',
      'Count' => $count,
      //'promo' => $blockPromoRendered,
      //'other' => $blockOtherRendered,
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
    if (!$goody) {
      Common::Get404Page();
    }

    $goody['PreviewFullWebp'] = Common::flGetWebpByImage($goody['PreviewFull']);
    if ($goody['TextGost']) $goody['TextGost'] = '<strong class="current-product__gost">' . $goody['TextGost'] . '</strong>';
    if ($goody['TextBottom']) $goody['TextBottom'] = strtr($goody['TextBottom'], array(
      'мм2' => 'мм²',
    ));
    
    $breadcrumbs = new BreadcrumbsComponent;
    $breadcrumbsRendered = $breadcrumbs->render($codeGoody, [
      ['Code' => '/', 'Link' => '/' ,'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => '/'.$this->code().'/' ,'Title' => $this->page['Title']],
      //['Code' => $codeProduct, 'Link' => '/'.$this->code().'/'.$codeProduct.'/' ,'Title' => strip_tags($product['Title'])],
      ['Code' => $codeSubcategory, 'Link' => '/'.$this->code().'/'.$codeProduct.'/'.$codeSubcategory.'/' ,'Title' => strip_tags($subcategory['Title'])],
      ['Code' => $codeGoody, 'Title' => strip_tags($goody['Title'])],
    ]);

    $chars = $this->model->getGoodyChars($goody['Id']);
    $chars = Common::setNl2Br($chars, 'Value');
    $chars = Common::setDimCorrections($chars, array(
      'мм2' => 'мм²',
    ));
    $charsTemplate = new ListTemplate('goodychar__card', 'production/partial');
    $charsTemplate  = $charsTemplate->parse($chars);

    $photos = $this->model->getGoodyPhotos($goody['Id']);
    foreach ($photos as &$photo) {
      $photo['PreviewWebp'] = Common::flGetWebpByImage($firstArticle['Preview']);
      $photo['Alt'] = htmlspecialchars($photo['Title'], ENT_QUOTES);
    }
    $photosTemplate = new Template('production__goody_extphotos', 'production');
    $photosItemTemplate = new ListTemplate('goodyextphoto__card', 'production/partial');
    $photosItemTemplate  = $photosItemTemplate->parse($photos);
    $photosTemplateRendered = $photosTemplate->parse([
          'List' => $photosItemTemplate,
          'Arrows' => count($photos) > 3 ? '<div class="main__product-next"></div><div class="main__product-prev"></div>' : '',
    ]);

    //$this->getPage('goody')->addInclude($this->partial('chars'));

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

    $relatedGoods = $this->model->getRelatedGoods($goody['Id'], 4);
    $relatedGoods = Common::setLinksByFields($relatedGoods, 'production', 'ProductCode', 'SubcategoryCode', 'Code');
    $blockRelatedTemplate = new Template('bl-related', 'production');
    $blockRelatedItemTemplate = new ListTemplate('bl-related__item', 'production/partial');
    $blockRelatedItemTemplate  = $blockRelatedItemTemplate->parse($relatedGoods);
    $blockRelatedRendered = $blockRelatedTemplate->parse([
          'Title' => $content['BlockRelatedTitle'],
          'List' => $blockRelatedItemTemplate
    ]);

    $otherGoods = $this->model->getOtherGoods($params['goody'], 4);
    $otherGoods = Common::setLinksByFields($otherGoods, 'production', 'ProductCode', 'SubcategoryCode', 'Code');
    $blockOtherTemplate = new Template('bl-other', 'production');
    $blockOtherItemTemplate = new ListTemplate('bl-other__item', 'production/partial');
    $blockOtherItemTemplate  = $blockOtherItemTemplate->parse($otherGoods);
    $blockOtherRendered = $blockOtherTemplate->parse([
          'Title' => $content['BlockOtherTitle'],
          'List' => $blockOtherItemTemplate
    ]);

    return $this->getPage('goody')->parse($goody + $contacts + $this->page + [
      'breadcrumbs' => $breadcrumbsRendered,
      //'chars' => $chars,
      'chars' => !empty($chars) ? '<div><h3>Характеристики</h3><table>' . $charsTemplate . '</table></div>' : '',
      'extphotos' => !empty($photos) ? $photosTemplateRendered : '',
      // 'promo' => $blockPromoRendered,
      'related' => isset($relatedGoods) && count($relatedGoods) ? $blockRelatedRendered : '',
      'other' => $blockOtherRendered,
    ]);
  }
}


?>
