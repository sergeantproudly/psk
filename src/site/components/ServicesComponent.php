<?php

namespace Site\Components;
use Engine\Library\ImprovedComponent;
use Engine\Library\ListTemplate;


class ServicesComponent extends ImprovedComponent {
  const CODE = 'services'; 
  const MODEL = 'ServiceModel';

  function __construct($db = null) {
    parent::__construct(self::CODE);
    
    if ($db) $this->setModel(self::MODEL, $db);

    $this->setTemplates([
      'default' => ['template' => 'c-services-similar'],
      'footer' => ['template' => 'c-footer-services']
    ]);
  }

  function render($services) {
    $servicesTemplate = 
      new ListTemplate('c-services-similar__item', 'components/services');
    $servicesTemplate = $servicesTemplate->parse($services);
    
    return $this->parse([
      'Title' => 'Похожие услуги',
      'List' => $servicesTemplate
    ]);
  }
}