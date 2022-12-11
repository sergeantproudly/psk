<?php

namespace Site\Components;
use Engine\Library\ImprovedComponent;
use Engine\Library\ListTemplate;
use Engine\Library\Common;

class ProductionDirectionsComponent extends ImprovedComponent {
  const CODE = 'production'; 
  const MODEL = 'ProductionModel';

  function __construct($db = null) {
    parent::__construct(self::CODE);

    if ($db) $this->setModel(self::MODEL, $db);

    $this->setTemplates([
      'default' => ['template' => 'bl-production-directions'],
    ]);
  }

  function render($production, $name = '') {
    array_walk($production, function(&$direction, $counter) {
      $direction['Num'] = $counter + 1;
      $direction['ImageWebp'] = Common::flGetWebpByImage($direction['Image']);
    });

    $productionTemplate = 
      new ListTemplate('bl-production-directions__item', 'components/production');

    $productionTemplate = $productionTemplate->parse($production);

    return $this->parse([
      'List' => $productionTemplate,
    ]);
  }
}