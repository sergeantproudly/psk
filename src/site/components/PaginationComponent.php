<?php

namespace Site\Components;
use Engine\Library\Component;

class PaginationComponent extends Component {
  const CODE = 'pagination';
  const DIR = 'components';
  const SUB_DIR = self::CODE;
  const MODEL = 'ProjectModel'; 

  function __construct($database = null) {
    parent::__construct(self::CODE, self::DIR, self::SUB_DIR);
    
    if ($database) $this->setModel(self::MODEL, $db);

    $this->setViews([
      'default' => ['template' => 'c-pagination']
    ]);

    $this->setPartials([
      'pages' => [
        'type' => 'navigation',
        'template' => [
          'default' => 'c-pagination__item', 
          'active' => 'c-pagination__item--active', 
          'empty' => 'c-pagination__item--empty', 
        ]
      ]
    ]);
  }

  function pages($entriesPerPage, $entriesCount, $parentCode,
                 $current = 1, $addPageCode = true, $getParams = true) {
    $pages = [];
    $viewableRange = 5;
    $halfRange = $viewableRange / 2;

    $pagesCount = ceil($entriesCount / $entriesPerPage);
    $currentPage = $current;

    if($current < 1 || $current > $pagesCount) {
      $current = 1;
    }

    for ($i = 0; $i < $pagesCount; $i++) {
      $counter = $i + 1;
      $link = "/{$parentCode}";
      $link .= $addPageCode ? "/page" : '';
      $link .= "/{$counter}/";
      if ($getParams && count($_GET)) $link .= '?' . $_SERVER['QUERY_STRING'];
      $pages[$i] = [
        'class' => '',
        'html' => '<a href="' . $link . '" class="pagination__link">' . ($counter < 10 ? '0' . $counter : $counter) . '</a>',
        'number' => $counter < 10 ? '0' . $counter : $counter,
        'link' => $link,
      ];
      if ($current == $counter) {
        $pages[$i]['active'] = true;
        $current = $i;  // Reset current number to match array number agreement
      }
    }

    $nextPage = $pages[$current + 1] ?? false;
    $prevPage = $pages[$current - 1] ?? false;

    if ($pagesCount > $viewableRange) {
      $leftLimit = $currentPage - $halfRange - 1; // 1 will make left and right page ranges to be equal
      $rightLimit = $currentPage + $halfRange;

      $leftLimit = $leftLimit < 1 ? 1 : $leftLimit;
      $rightLimit = $rightLimit > $pagesCount ? $pagesCount : $rightLimit;

      $length = $rightLimit - $leftLimit;

      $viewablePages = array_slice($pages, $leftLimit, $length);


      if ($leftLimit >= $halfRange) {
        array_unshift($viewablePages, [
          'html' => '...'
        ]);
      }
      if ($leftLimit > 0)
        array_unshift($viewablePages, $pages[0]);
      if ($rightLimit < $pagesCount - 1) {
        array_push($viewablePages, [
          'html' => '...'
        ]);
      }
      if ($rightLimit < $pagesCount)
        array_push($viewablePages, $pages[$pagesCount - 1]);


      if ($leftLimit > 1) {
        //$viewablePages[1]['class'] = 'dotts';
      }
    }

    if (!$viewablePages)
      $viewablePages = $pages;

    return [
      'pages' => $viewablePages,
      'nextPage' => $nextPage,
      'previousPage' => $prevPage
    ];
  }
}
