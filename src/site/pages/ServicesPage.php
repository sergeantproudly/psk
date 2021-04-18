<?php
namespace Site\Pages;
use Engine\Library\Page;
use Engine\Library\Template;
use Engine\Library\ListTemplate;
use Engine\Library\Common;
use Site\Components\BreadcrumbsComponent;
use Site\Components\ServicesComponent;

class ServicesPage extends Page {
  const CODE = 'services';
  const DIR = 'services';
  public $modelName = 'ServiceModel';

  function __construct () {
    parent::__construct(self::CODE, self::DIR);
    
    $this->setPages([
      'index' => ['template' => 'services'],
      'detail' => ['template' => 'services__detail']
    ]);
  }

  function index ($params = []):string {
    $breadcrumbs = new BreadcrumbsComponent();
    $breadcrumbs->setTemplate('inversed');

    $breadcrumbsRendered = $breadcrumbs->render(self::CODE, [
      ['Code' => 'main', 'Link' => '/', 'Title' => 'Главная'],
      ['Code' => 'services', 'Link' => '/services/', 'Title' => 'Услуги']
    ]);

    // var_dump($breadcrumbsRendered);

    $categoryList = $this->model->getCategoriesWithServices();

    $categoryTemplate = new Template('services__category', 'services/partial');
    $serviceTemplate = new \Engine\Library\ListTemplate('services__service', 'services/partial');

    $rendered = '';
    foreach ($categoryList as $key => $category) {
      $rendered .= $categoryTemplate->parse([
        'Title' => $category['Title'],
        'ServiceList' => $serviceTemplate->parse($category['ServiceList'])
      ]);
    }

    return $this->page('index')->parse($this->page + [
      'CategoryList' => $rendered,
      'Breadcrumbs' => $breadcrumbsRendered
    ]);
  }

  function detail ($params = []) {
    // [TODO]: Добавить вывод страницы 404
    if (!isset($params['service']) || !$params['service'])
      return $this->index();

    $code = $params['service'];

    $service = $this->model->getServiceByCode($code);
    $content = $this->model->getContent($this->code());

    $breadcrumbs = new BreadcrumbsComponent();
    $breadcrumbs->setTemplate('inversed');
    $breadcrumbsRendered = $breadcrumbs->render($code, [
      ['Code' => 'main', 'Link' => '/', 'Title' => 'Главная'],
      ['Code' => 'services', 'Link' => '/services/', 'Title' => 'Услуги'],
      ['Code' => $service['Code'], 'Title' => $service['Title']],
    ]);

    $serviceList = $this->model->getServicesByCategory($service['CategoryId']);
    $serviceList = Common::setLinks($serviceList, 'services');
    $serviceList = array_filter($serviceList, function($item) use ($service) {
      return $item['Code'] != $service['Code'];
    }); 

    // TODO: Добавить проверку наличия комментария
    // TODO: Запросить у верстальщика блок "Нет комментария"
    $review = $this->model->getReview($service['ReviewId'] ?: 1);
    $reviewTemplate = new Template('review-services', 'components/review');
    $reviewTemplate = $reviewTemplate->parse($review);

    // var_dump($service['Id']);
    $gallery = $this->model->getGallery($service['Id']);
    // var_dump($gallery);
    $galleryTemplate = new ListTemplate('gallery__image', 'components/gallery');
    $galleryTemplate = $galleryTemplate->parse($gallery); 

    // var_dump($galleryTemplate);
    $serviceComponent = new ServicesComponent;
    $similarRendered = $serviceComponent->render($serviceList);
    
    return $this->page('detail')->parse($service + [
      'Title' => $parent['Title'] ?: $service['Title'],
      'Breadcrumbs' => $breadcrumbsRendered,
      'Similar' => $similarRendered,
      'Comment' => $reviewTemplate,
      'Gallery' => $galleryTemplate
      // 'others' => [
      //   'heading' => $content['OtherServices'],
      //   'list' => $others->partial('list')->parse($serviceList)
      // ],
      // 'nav' => $navigation
    ]);
  }
}




?>
