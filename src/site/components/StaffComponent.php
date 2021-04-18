<?php

namespace Site\Components;
use Engine\Library\ImprovedComponent;
use Engine\Library\ListTemplate;

class StaffComponent extends ImprovedComponent {
  const CODE = 'staff'; 
  const MODEL = 'StaffModel';

  function __construct($db = null) {
    parent::__construct(self::CODE);

    if ($db) $this->setModel(self::MODEL, $db);

    $this->setTemplates([
      'default' => ['template' => 'bl-staff'],
    ]);
  }

  function render($staff, $name = '') {
    $staffTemplate = 
      new ListTemplate('bl-staff__item', 'components/staff');

    $staffTemplate = $staffTemplate->parse($staff);

    return $this->parse([
      'Title' => $name ?: 'Сотрудники',
      'List' => $staffTemplate,
    ]);
  }
}