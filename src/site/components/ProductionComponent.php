<?php

namespace Site\Components;
use Engine\Library\ImprovedComponent;
use Engine\Library\ListTemplate;

class ProductionComponent extends ImprovedComponent {
  const CODE = 'production'; 
  const MODEL = 'ProductionModel';

  function __construct($db = null) {
    parent::__construct(self::CODE);

    if ($db) $this->setModel(self::MODEL, $db);

    $this->setTemplates([
      'default' => ['template' => 'bl-production'],
    ]);
  }

  function render($production, $name = '') {
    $productionTemplate = 
      new ListTemplate('bl-production__item', 'components/production');

    $productionTemplate = $productionTemplate->parse($production);

    return $this->parse([
      'Title' => $name ?: 'Продукция',
      'List' => $productionTemplate,
    ]);
  }
}