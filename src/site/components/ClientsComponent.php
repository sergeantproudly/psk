<?php

namespace Site\Components;
use Engine\Library\ImprovedComponent;
use Engine\Library\ListTemplate;

class ClientsComponent extends ImprovedComponent {
  const CODE = 'client'; 
  const MODEL = 'ClientModel';

  function __construct($db = null) {
    parent::__construct(self::CODE);

    if ($db) $this->setModel(self::MODEL, $db);

    $this->setTemplates([
      'default' => ['template' => 'bl-clients'],
    ]);
  }

  function render($clients, $name = '') {
    $clientsTemplate = 
      new ListTemplate('bl-clients__item', 'components/client');

    $clientsTemplate = $clientsTemplate->parse($clients);

    return $this->parse([
      'Title' => $name ?: 'Заказчики',
      'List' => $clientsTemplate,
    ]);
  }
}