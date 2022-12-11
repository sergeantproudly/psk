<?php
  require_once $_SERVER['DOCUMENT_ROOT'].'/src/bootstrap.php';
  use Engine\Library\SafeMySQL;
  use Engine\Library\Site;
  use Engine\Library\Settings;
  use Engine\Library\Route;


	$Config   = require ROOT.'/src/config.php';
	$Database = new SafeMySQL($Config['Database']);

	$Settings = new Settings($Database);
	$Site     = new Site($Database);

  $Router = new Engine\Library\Router;

  $Router->add('GET', '/', 'HomePage@index');
  $Router->add('GET', '/contacts/', 'ContactsPage@index');
  $Router->add('GET', '/company/{code?}/', 'CompanyPage@index');

  //$Router->add('GET', '/services/', 'ServicesPage@index');
  //$Router->add('GET', '/services/{service}', 'ServicesPage@detail');

  $Router->add('GET', '/production/', 'ProductionPage@index');
  $Router->add('GET', '/production/direction/{direction}', 'ProductionPage@direction');
  $Router->add('GET', '/production/direction/{direction}/page/{page}', 'ProductionPage@direction');
  $Router->add('GET', '/production/{product}', 'ProductionPage@product');
  $Router->add('GET', '/production/{product}/page/{page}', 'ProductionPage@product');
  $Router->add('GET', '/production/{product}/{subcategory}', 'ProductionPage@subcategory');
  $Router->add('GET', '/production/{product}/{subcategory}/{goody}', 'ProductionPage@goody');

  $Router->add('GET', '/articles/', 'ArticlesPage@index');
  $Router->add('GET', '/articles/{article}', 'ArticlesPage@detail');
  $Router->add('GET', '/articles/page/{page}', 'ArticlesPage@index');

  $Router->add('GET', '/projects/', 'ProjectsPage@index');
  $Router->add('GET', '/projects/{project}', 'ProjectsPage@detail');
  $Router->add('GET', '/projects/page/{page}', 'ProjectsPage@index');

  $Router->add('GET', '/catalog/download/', 'CatalogPage@download');

  $Router->add('GET', '/catalog/{page?}', 'CatalogPage@index', ['page' => 1]);
  $Router->add('GET', '/catalog/filter/{filter}/{page?}', 'CatalogPage@index', ['page' => 1]);

  $Router->add('GET', '/catalog/search/{search}/{page?}', 'CatalogPage@index', ['page' => 1]);

  $Router->add('GET', '/catalog/filter/{filter}/search/{search}/{page?}', 'CatalogPage@index', ['page' => 1]);

  $Router->add('GET', '/certs/download/', 'CertsPage@download');
  $Router->add('GET', '/certs/{page?}', 'CertsPage@index', ['page' => 1]);
  $Router->add('GET', '/certs/search/{search}/{page?}', 'CertsPage@index', ['page' => 1]);

  $Router->add('GET', '/register/download/', 'RegisterPage@download');
  $Router->add('GET', '/register/download/{number}', 'RegisterPage@download');
  
  $Router->add('GET', '/register/', 'RegisterPage@index');
  $Router->add('GET', '/register/{number}', 'RegisterPage@detail');
  $Router->add('GET', '/register/page/{page}', 'RegisterPage@index');

  $Router->add('GET', '/register/sort/{sort}/{direction?}/page/{page?}', 'RegisterPage@index', ['direction' => 'asc', 'page' => 1]);
  
  $Router->add('GET', '/register/sort/{sort}/{direction?}', 'RegisterPage@index', ['direction' => 'asc']);

  $Router->add('POST', '/register/get-data/', 'AjaxHandler@getRegister');

  $Router->add('GET', '/certificates/{page?}/', 'CertificatesPage@index', ['page' => 1]);
  $Router->add('GET', '/certificates/sort/{sort}/{sort-direction?}/{page?}/', 'CertificatesPage@index', ['sort-direction' => 'asc', 'page' => 1]);

  $Router->add('GET', '/certificates/select/{select-1}/{value-1}/{page?}/', 'CertificatesPage@index', ['page' => 1]);

  $Router->add('GET', '/certificates/select/{select-1}/{value-1}/select/{select-2}/{value-2}/{page?}/', 'CertificatesPage@index', ['page' => 1]);

  $Router->add('GET', 
  '/certificates/select/{select-1}/{value-1}/select/{select-2}/{value-2}/sort/{sort}/{sort-direction?}/{page?}/', 'CertificatesPage@index', ['sort-direction' => 'asc', 'page' => 1]);

  $Router->add('GET', 
  '/certificates/select/{select-1}/{value-1}/sort/{sort}/{sort-direction?}/{page?}/', 'CertificatesPage@index', ['sort-direction' => 'asc', 'page' => 1]);

  $Router->add('POST', '/certificates/get-data/', 'AjaxHandler@getCertificates');

  $Router->add('GET', '/stores/', 'StoresPage@index');

  $Router->add('GET', '/personal/{code}', 'PersonalPage@detail');
  $Router->add('GET', '/personal/en/{code}', 'PersonalPage@detailEn');

  $Router->add('GET', '/staff/', 'StaffPage@index');
  $Router->add('GET', '/shipping/', 'ShippingPage@index');

  // Cart API
  $Router->add('POST', '/cart/make-order/', 'AjaxHandler@makeOrder');
  $Router->add('GET', '/cart/get-item/all/', 'AjaxHandler@getCart');
  $Router->add('GET', '/cart/get-item/{id}/', 'AjaxHandler@getItem');
  $Router->add('GET', '/cart/set-item/{id}/{amount}/', 'AjaxHandler@setItem');
  $Router->add('GET', '/cart/remove-item/{id}/', 'AjaxHandler@removeItem');
  $Router->add('POST', '/cart/remove-item/all/', 'AjaxHandler@cleanCart');

  // Question form API
  $Router->add('POST', '/form/add-question/', 'AjaxHandler@addQuestion');

  // Auth API
  $Router->add('POST', '/auth/log-in/', 'AjaxHandler@logIn');
  $Router->add('POST', '/auth/log-out/', 'AjaxHandler@logOut');
  $Router->add('GET', '/auth/log-out/', 'AjaxHandler@logOut');

  $route = $Router->dispatch();
  if (!$route) $route = new Route([
    'method'      => 'GET',
    'controller'  => 'StaticPage',
    'action'      => 'index'
  ]);

	if ($route->controller() === 'AjaxHandler') {
    $ajaxHandler = new \Engine\Library\Ajax\AjaxHandler;

    $response = $ajaxHandler->execute($route->action(), $route->params());

//    ini_set('xdebug.var_display_max_depth', 5);
//    ini_set('xdebug.var_display_max_children', 256);
//    ini_set('xdebug.var_display_max_data', 1024);

    echo $response;
  } else {
    // var_dump($route);
	  $userSession = new \Engine\Library\UserSession();
//	  var_dump($userSession->isLoggedIn());

	  if (!$userSession->isLoggedIn()) {
	    switch ($route->controller()):
        case 'CatalogPage':
        //case 'StaffPage':
        case 'RegisterPage':
        case 'CertificatesPage':
        case 'ShippingPage':
          header("Location: /" );
          exit;
      endswitch;
    }


    $page = "\\Site\\Pages\\{$route->controller()}";
    $page = new $page();

    $action = $route->action();
    $params = $route->params();

    //	$pageClass = . ucfirst($pageName) . 'Page';
    //	$page = new $pageClass();

    if ($Database) {
      $data = $Database->getRow("SELECT * FROM `pages` WHERE `Code` = ?s", $page->getCode);
      $page->init($data);
    }

    if ($page->modelName) {
      $modelClass = '\\Site\\Models\\' . ucfirst($page->modelName);
      $model = new $modelClass();

      $model->setDB($Database);
      $page->setModel($model);
    }

    mb_internal_encoding('utf8');
    header('Content-type: text/html; charset=utf-8');

    echo $Site->buildPage($page, $Settings, $action, $params);
  }

?>