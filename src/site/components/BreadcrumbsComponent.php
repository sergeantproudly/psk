<?php

namespace Site\Components;
use Engine\Library\ImprovedComponent;
use Engine\Library\NavigationTemplate;

class BreadcrumbsComponent extends ImprovedComponent {
  const CODE      = 'breadcrumbs';
  const DIR       = 'components';
  const SUB_DIR   = self::CODE;
  const MODEL     = 'PageModel'; 


  function __construct($database = null) {
    parent::__construct(self::CODE, self::DIR, self::SUB_DIR);
    
    if ($database !== null) {
      $this->setModel(self::MODEL, $db);
    }

    $this->setTemplates([
      'default' => ['template' => 'breadcrumbs']
    ]);
  }

  public function getRoute(string $pageCurrent, array $routes)  {

    // $templateName = 'breadcrumbs';
    // $template = new Template($templateName, $this->dir);

    // $route = array_map(function($link, $title) {
    //   return "<a href='{$link}'>{$title}</a>" . ($useSeparator ? ' '.$separator.' ' : '');
    // }, array_keys($route), $route);

    // $route  = implode($route); 
    // $route .= "<span class='motion'>{$currentPageTitle}</span>";

    // return $template->Parse([
    //   "{{CONTENT}}" => $route,
    // ]);
  }

  public function render($currentCode, $routes) {
    $templateRoute = new NavigationTemplate([
      'default' => 'breadcrumbs__link',
      'active' => 'breadcrumbs__link--active',
      'dir' => 'components/breadcrumbs'
    ]);

    $templateRoute->setCallback(function ($item) use ($currentCode) {
      return $item['Code'] == $currentCode;
    });

    $this->attachTemplate($templateRoute, 'Content');

    return $this->parse([
      'Content' => $routes
    ]);
  }

  
}

