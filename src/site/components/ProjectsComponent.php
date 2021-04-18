<?php

namespace Site\Components;
use Engine\Library\ImprovedComponent;
use Engine\Library\ListTemplate;


class ProjectsComponent extends ImprovedComponent {
  const CODE = 'project'; 
  const MODEL = 'ProjectModel';

  function __construct($db = null) {
    parent::__construct(self::CODE);

    if ($db) $this->setModel(self::MODEL, $db);

    $this->setTemplates([
      'default' => ['template' => 'bl-projects'],
    ]);
  }

  function render($projects, $name = '') {
    $projectsTemplate = 
      new ListTemplate('bl-projects__item', 'components/project');

    $projectsTemplate = $projectsTemplate->parse($projects);

    return $this->parse([
      'Title' => $name ?: 'Проекты',
      'List' => $projectsTemplate,
    ]);
  }
}
