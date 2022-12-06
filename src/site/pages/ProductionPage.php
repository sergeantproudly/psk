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
      'detail' => ['template' => 'production__detail'],
      'subcategory' => ['template' => 'production__subcategory'],
      'goody' => ['template' => 'production__goody']
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
    $contacts = $this->model->getContent('contacts');

    $products = $this->model->getProducts();
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

    $subcategories = $this->model->getSubCategories($product['Id']);
    $subcategories = Common::setLinks($subcategories, 'production', $code);

    $this->getPage('detail')->addInclude($this->partial('subcategories'));

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
      'subcategories' => $subcategories,
      'promo' => $blockPromoRendered,
      'other' => $blockOtherRendered,
    ]);
  }

  function subcategory($params = []) {
    if (!isset($params['product']) || empty($params['product']) || !isset($params['subcategory']) || empty($params['subcategory']))
      return $this->index();

    global $Database;

    $content = $this->model->getContent($this->code());
    $contacts = $this->model->getContent('contacts');

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

    $this->getPage('subcategory')->addInclude($this->partial('goods'));

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

    return $this->getPage('subcategory')->parse($subcategory + $this->page + [
      'breadcrumbs' => $breadcrumbsRendered,
      'goods' => $goods,
      'promo' => $blockPromoRendered,
      'other' => $blockOtherRendered,
    ]);
  }

  function goody($params = []) {
    if (!isset($params['product']) || empty($params['product']) || !isset($params['subcategory']) || empty($params['subcategory']) || !isset($params['goody']) || empty($params['goody']))
      return $this->index();

    global $Database;

    $content = $this->model->getContent($this->code());
    $contacts = $this->model->getContent('contacts');

    $codeProduct = $params['product'];
    $codeSubcategory = $params['subcategory'];
    $codeGoody = $params['goody'];
    $product = $this->model->getProductByCode($codeProduct);
    $subcategory = $this->model->getSubcategoryByCode($codeSubcategory);
    $goody = $this->model->getGoodyByCode($codeGoody);
    
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

    return $this->getPage('goody')->parse($goody + $this->page + [
      'breadcrumbs' => $breadcrumbsRendered,
      'chars' => $chars,
      'promo' => $blockPromoRendered,
      'other' => $blockOtherRendered,
    ]);
  }
}


?>
