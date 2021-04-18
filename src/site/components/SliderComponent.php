<?php

namespace Site\Components;
use Engine\Library\ImprovedComponent;
use Engine\Library\ListTemplate;


class SliderComponent extends ImprovedComponent {
  const CODE = 'slider'; 
  const MODEL = 'SliderModel';

  function __construct($db = null) {
    parent::__construct(self::CODE);

    if ($db) $this->setModel(self::MODEL, $db);

    $this->setTemplates([
      'default' => ['template' => 'slider'],
    ]);
  }

  function render($slides, $speedAutoChange = 5) {
    $slidesTemplate = 
      new ListTemplate('slider-item', 'components/slider');

    $slidesTemplate = $slidesTemplate->parse($slides);

    return $this->parse([
      'List' => $slidesTemplate,
      'Speed' => $speedAutoChange,
    ]);
  }
}
