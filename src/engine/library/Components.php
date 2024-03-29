<?php

namespace Engine\Library;

class Components {
  const COMPONENTS_DIR = 'components';
  protected $model = '';

  function __construct() {
  }

  static function getBreadcrumbs(array $route, string $currentPage, bool $useSeparator = false, string $separator = '&raquo;') {
    $templateName = 'breadcrumbs';
    $template = new Template($templateName, self::COMPONENTS_DIR);

    $route = array_map(function($link, $title) {
      return "<a href='{$link}'>{$title}</a>" . ($useSeparator ? ' '.$separator.' ' : '');
    }, array_keys($route), $route);

    $route  = implode($route); 
    $route .= "<span href='#' class='motion'>{$currentPage}</span>";

    return $template->Parse([
      "{{CONTENT}}" => $route,
    ]);
  }

  	/**
	 * Возвращает шаблон навигации, после замены плейсхолдеров на данные.
	 * 
	 * @return string
	 */
	static function getNavigation($db, $currentPageCode, $currentSubPageCode) {
		define('PRODUCTION_ITEM_ID', 4);

		$urlData = parse_url($_SERVER['REQUEST_URI']);
		$lastPathNode = array_pop(array_filter(explode('/', $urlData['path'])));

		$navigationPath = 'components/navigation';
	
		$navigation = new Template('navigation.htm', $navigationPath);
		$element =	new Template('navigation__elem.htm', $navigationPath);

		$subNavigation = new Template('sub-navigation.htm', $navigationPath);
		$subElement =	new Template('sub-navigation__elem.htm', $navigationPath);
		
		$content = '';		
		
	    // Запрос страниц сайта из базы
	    $pages = 'pages';
	    $nav = 'component_navigation';
	    $subNav = 'component_sub-navigation';
		$navigationItems = $db->getAll("SELECT nav.Id, nav.Title, pages.Code, nav.Link FROM `{$nav}` as nav LEFT JOIN {$pages} AS pages ON nav.PageId = pages.Id ORDER BY IF(`Order`,-1000/`Order`,0) ASC");

		$res = $db->getAll("SELECT Title, Code, Link, ItemId FROM `{$subNav}` ORDER BY IF(`Order`,-1000/`Order`,0) ASC");
		foreach ($res as $subNavigationItem) {
			$subNavigationItems[$subNavigationItem['ItemId']][] = $subNavigationItem;
		}

		foreach($navigationItems as $item) {
			$currentLink = '/'.$currentPage.'/';
			$isCurrent = $currentPageCode == $item['Code'];	// Активная страница

			$item['List'] = '';
			$class = [];
			$classtwo = [];
			if ($isCurrent) $classtwo[] = 'active';
			if (isset($subNavigationItems[$item['Id']])) {
				$class[] = 'menu-parent';
				//$classtwo[] = 'icon-arrow';
				$subItems = '';

				$item['Arrow'] = '<button class="menu__arrow icon-arrow"></button>';

				foreach ($subNavigationItems[$item['Id']] as $subItem) {
					$isCurrentSub = $currentSubPageCode == $subItem['Code'];	// Активная страница

					$subItem['Class'] =  $isCurrentSub ? ' active' : '';
					$subItems .= $subElement->Parse($subItem);
				}

				$item['List'] = $subNavigation->Parse([
					'CLASS' => $item['Id'] == PRODUCTION_ITEM_ID ? 'submenu-lg' : '',
					'CONTENT'	=> $subItems
				]);
			}
			if (count($class)) $item['Class'] = ' ' . implode(' ', $class);
			if (count($classtwo)) $item['ClassTwo'] = ' ' . implode(' ', $classtwo);
			if ($lastPathNode != $item['Code']) $item['Style'] = ' style="pointer-events:auto;"';
			
			$content .= $element->Parse($item);
		}
    
		$result = $navigation->Parse([
			'CONTENT'	=> $content
		]);

		return $result;
	}
}
