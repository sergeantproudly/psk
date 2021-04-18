<?php

namespace Site\Components;
use Engine\Library\ImprovedComponent;
use Engine\Library\ListTemplate;

class PartnersComponent extends ImprovedComponent {
  const CODE = 'partner'; 
  const MODEL = 'PartnerModel';

  function __construct($db = null) {
    parent::__construct(self::CODE);

    if ($db) $this->setModel(self::MODEL, $db);

    $this->setTemplates([
      'default' => ['template' => 'bl-partners'],
    ]);
  }

  function render($partners, $name = '') {
    $partnersTemplate = 
      new ListTemplate('bl-partners__item', 'components/partner');

    $partnersTemplate = $partnersTemplate->parse($partners);

    return $this->parse([
      'Title' => $name ?: 'Партнеры',
      'List' => $partnersTemplate,
    ]);
  }
}