<?php
namespace Site\Pages;
use Engine\Library\Common;
use Engine\Library\Page;
use Engine\Library\Template;
use Engine\Library\ListTemplate;
use Site\Components\PaginationComponent;
use Site\Components\BreadcrumbsComponent;
use Site\Components\ProjectsComponent;


class ProjectsPage extends Page {
  const CODE = 'projects';
  const DIR = 'projects';
  public $modelName = 'ProjectModel';
  
  function __construct () {
    parent::__construct(self::CODE, self::DIR);
    $this->setPages([
      'index' => ['template' => 'projects'],
      'detail' => ['template' => 'projects__detail'],
    ]);
    $this->setPartials([
      'list' => [
        'type' => 'list',
        'template' => 'projects__card'
      ]
    ]);
  }

  function index ($params = []):string {
    /*
    $breadcrumbs = new BreadcrumbsComponent;
    $breadcrumbs->setTemplate('inversed');
    $breadcrumbsRendered = $breadcrumbs->render($this->code(), [
      ['Code' => 'main', 'Link' => '/', 'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => '/'.$this->code().'/',  'Title' => $this->page['Title']],
    ]);
    */

    global $Settings;    
    $count = $Settings->get('ProjectsCount') ?: 3;

    $all = $this->model->getProjects();
    $pagination = new PaginationComponent;
    $pageNumber = $params['page'] ?? 1;

    $paginationData = $pagination->pages(
      $count, 
      count($all), 
      $this->code(),
      $pageNumber
    );

    $pages = $paginationData['pages'];
    $nextPage = $paginationData['nextPage'];
    $prevPage = $paginationData['previousPage'];

    $pageNumber = $pageNumber <= count($pages) ? $pageNumber : 1;
    

    $offset = ($pageNumber - 1) * $count;
    $offset = $offset >= 0 ? $offset : 0;

    $projects = $this->model->getProjects($count, $offset);
    $projects = $projects ? Common::setLinks($projects, $this->code()) : [];

//    var_dump($projects);
//    $this->getPage('index')->addInclude($this->partial('list'));

    $projectListTemplate = new ListTemplate('projects__card', 'projects/partial');
    /*
    $equipmentTemplate   = new ListTemplate('equipment__list', 'projects/partial');

    $projectListRendered = $projectListTemplate
      ->addInclude($equipmentTemplate, 'Equipment')
      ->parse($projects);
    */
    $projectListRendered = $projectListTemplate->parse($projects);

    if (count($pages) > 1) {
      $this->getPage('index')->addInclude(
        $pagination->view('default'), 'pagination'
      );
    }

    return $this->getPage('index')->parse($this->page + [
      //'Breadcrumbs' => $breadcrumbsRendered,
      'List' => $projectListRendered,
      'Pagination' => [
        'Class' => 'projects__pagination',
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

  function detail($params) {
    if (!isset($params['project']) || empty($params['project']))
      return $this->index();

    $code = $params['project'];
    
    if (!$code) {
      return $this->index();
    }

    $content = $this->model->getContent($this->code());

    $project = 
      $this->model->getProjectByCode($code);

    $project['Date'] = strtotime($project['Date']) ? ('<time datetime="' . Common::excess($project['Date'], ' 00:00:00') . '">Дата реализации проекта: ' . Common::ModifiedDate($project['Date']) . '</time>') : '';
    $project['ImageWebp'] = Common::flGetWebpByImage($project['Image']);
    $project['Alt'] = htmlspecialchars($project['Title'], ENT_QUOTES);

      /*
    $gallery = 
      $this->model->getProjectImages($project['Id']);
    */

      

    $equipmentList = $this->model->getProjectEquipment($project['Id']);
    if (count($equipmentList)) {
      $equipmentTemplate   = new Template('project_equipment', 'projects');
      $equipmentItemTemplate = new ListTemplate('equipment__list', 'projects/partial');
      $equipmentItemTemplate = $equipmentItemTemplate->parse($equipmentList);
      $equipmentTemplate   = $equipmentTemplate->parse([
        'Title' => strip_tags($content['EquipmentHeading']),
        'List' => $equipmentItemTemplate
      ]);
    } else {
      $equipmentTemplate = '';
    }

    /*
    $galleryTemplate = new ListTemplate('gallery__image', 'components/gallery');
    $galleryTemplate = $galleryTemplate->parse($gallery); 
    */
    
    $breadcrumbs = new BreadcrumbsComponent();
    $breadcrumbsRendered = $breadcrumbs->render($code, [
      ['Code' => 'main', 'Link' => '/', 'Title' => 'Главная'],
      ['Code' => $this->code(), 'Link' => "/{$this->code()}/", 'Title' => $this->page['Title']],
      ['Code' => $project['Code'], 'Title' => $project['Title']],
    ]);

    /*
    $projects = $this->model->getProjects(3);

    $projectsComponent = new ProjectsComponent;
    $projectsRendered = $projectsComponent->render($projects );
    */

    return $this->getPage('detail')->parse($project + [
      'Breadcrumbs' => $breadcrumbsRendered,
      'TopHeading' => $content['TopHeading'] ? '<h2>'.strip_tags($content['TopHeading']).'</h2>' : '',
      'DescriptionHeading' => $content['DescriptionHeading'] && $project['Description'] ? '<h2>'.strip_tags($content['DescriptionHeading']).'</h2>' : '',
      'EquipmentList' => $equipmentTemplate,
      //'Gallery' => $galleryTemplate,
      //'Others' => $projectsRendered
    ]);
  }

  function page($number) {
    $number = intval(array_shift($number));
    $number = is_int($number) ? $number : 1;
//    var_dump($number);
    return $this->index(['page' => $number]);
  }
}


?>
