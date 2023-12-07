<?php

namespace Site\Components;
use Engine\Library\ImprovedComponent;
use Engine\Library\ListTemplate;
use Engine\Library\Common;


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
    foreach ($articles as &$article) {
      $article['DateTime'] = Common::excess($article['PublishDate'], ' 00:00:00');
      $article['Date'] = Common::ModifiedDate($article['PublishDate']);
      $article['PreviewImage'] = $article['Preview2'] ?: $article['Preview'];
      $article['PreviewWebp'] = Common::flGetWebpByImage($article['PreviewImage']);
      $article['Alt'] = htmlspecialchars($article['Title'], ENT_QUOTES);
    }

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
