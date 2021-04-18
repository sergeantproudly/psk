<?php

namespace Site\Components;
use Engine\Library\ImprovedComponent;
use Engine\Library\ListTemplate;


class ArticlesComponent extends ImprovedComponent {
  const CODE = 'articles'; 
  const MODEL = 'ArticleModel';

  function __construct($db = null) {
    parent::__construct(self::CODE);

    if ($db) $this->setModel(self::MODEL, $db);

    $this->setTemplates([
      'default' => ['template' => 'bl-news'],
    ]);
  }

  function render($articles, $name = '', $template = '') {
    if ($template) {
      $this->setTemplates([
        'default' => ['template' => $template ?: 'bl-news'],
      ]);
    }

    $articlesTemplate = 
      new ListTemplate('bl-news__item', 'components/articles');

    $articlesTemplate = $articlesTemplate->parse($articles);

    return $this->parse([
      'Title' => $name ?: 'Новости',
      'List' => $articlesTemplate,
    ]);
  }
}
