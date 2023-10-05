<?php    
namespace Engine\Library;

use Site\Components\HeaderComponent; 	
use Site\Components\FooterComponent;
use Site\Components\ServicesComponent;
use Engine\Library\Youtube;
use Engine\Helper\DateHelper;

class Site {
	private $page = 'home';
	private $action = 'index';
	private $params = [];
	private $db = [];
	private $defaultParams = [];
	
	function __construct($db = []) {
		$this->db = $db;

		$request = $this->parseURL();
		// 	var_dump($this->pageExists($request[0]));
		$this->page = $this->pageExists($request[0]) ? 
									array_shift($request) : 
									$this->page;
		$this->action = $this->actionExists($this->page, $request[0]) ? 
										array_shift($request) :
										$this->action;
		$this->params = $request ?? $defaultParams;
	}

	/**
	 * Возвращает адрес текущей страницы, начиная от корня (/).
	 * 
	 * @return string
	 */
	function getPage() {
		return $this->page;
	}

	function isHome($code) {
		return $code === 'home';
	}

	function isContacts($code) {
		return $code === 'contacts';
	}

	function isCompany($code) {
		$urlData = $this->parseURL();
		return $code === 'company' && !isset($urlData[1]);
		//return $code === 'company' && isset($urlData[1]) && $urlData[1] !== 'media';
	}

	function isCompanyMedia($code) {
		$urlData = $this->parseURL();
		return $code === 'company' && isset($urlData[1]) && $urlData[1] === 'media';
	}

	function isArticle($code) {
		$urlData = $this->parseURL();
		return $code === 'articles' && isset($urlData[1]) && $urlData[1];
	}

	function getAction() {
		return $this->action;
	}

	function getParams() {
		return $this->params;
	}
	
	protected function parseURL() {
		$URL = $_SERVER['REQUEST_URI'];
		$URL = urldecode($URL);
		// var_dump(mb_convert_encoding(, "UTF-8"));
		$URL = preg_split('/(\/|\?|=)/u', trim($URL, '/'));
		// var_dump($URL);
		return $URL ?? [];
	}

	protected function pageExists ($pageName) {
		return file_exists(PAGES.ucfirst($pageName).'Page'.'.php');
	}

	protected function actionExists ($pageName, $actionName) {
		return $actionName;
	}


	// получить страницу с замененными атрибутами
	function buildPage($page, Settings $settings = null, $action = 'index', $params = []) {
		global $Settings;

		$code = $page->code();
		$pageData = $this->db->getRow("SELECT * FROM `pages` WHERE `Code` = ?s", $code);

		if ($pageData) {
			$page->init($pageData);
		}

		// Dependencies
		$header 	= new HeaderComponent($this->db); 
		$services = new ServicesComponent($this->db);
		$footer 	= new FooterComponent($this->db);

		$baseTemplate = new Template('layout', 'layout');
		$baseTemplate->addInclude($header->view('default'), 'header');
		$baseTemplate->addInclude($footer->view('default'), 'footer');
		 
		// Contacts data for header and footer 
		$contacts = $header->model->getContent('contacts');
		$contacts['PhoneLink'] = Common::GetTelLink($contacts['Phone']);
    	$contacts['PhoneCommonLink'] = Common::GetTelLink($contacts['PhoneCommon']);
		
		// Header data
		$headerData = $header->model->getContent('home');

    	$pageModel = new \Site\Models\PageModel($this->db);

//		$userModel = new UserModel($this->db);
		$userSession = new UserSession();
//		$user = [];

	    // $authPageList = $pageModel->getAuthPageList();

	    // if ($userSession->isLoggedIn()) {
	    //   $clientNavTemplate   = new Template('client-navigation', 'components/client-navigation');
	    //   $clientItemTemplate  = new ListTemplate('_item.htm', 'components/client-navigation');
	    //   $clientItemTemplate  = $clientItemTemplate->parse($pageModel->getAuthNavigationList());
	    //   $clientNavTemplate   = $clientNavTemplate->parse([
	    //     'List' => $clientItemTemplate
	    //   ]);
	    //   $login = $clientNavTemplate;
	    // } else {
	    //   $authTemplate = new Template('login', 'layout');
	    //   $login = $authTemplate->parse();
	    // }

	    // contacts block
	    $templateContacts = new Template('bl-contacts', 'components/contacts');
	    $contactsRendered = $templateContacts->parse([
	    	'Heading' => $contacts['Heading'],
	    	'Location' => $contacts['Location'],
	    	'Phone' => $contacts['Phone'],
	    	'Email' => $contacts['Email'],
	    ]);

	    // modals
	    $templateModal = new Template('modal_base', 'modals');
	    if ($this->isHome($code) || $this->isCompany($code)) {
	    	$youtube = new Youtube();
	    	$templateModalVideo = new Template('modal_video', 'modals');
	    	$modalsRendered[] = $templateModal->parse([
		    	'Code' => 'video',
		    	'Id' => 'video',
		    	'Content' => $templateModalVideo->parse([
		    		'CodeRus' => $youtube->GetCodeFromSource($Settings->get('YoutubeCodeRus')),
        			'CodeEng' => $youtube->GetCodeFromSource($Settings->get('YoutubeCodeEng')),
		    	]),
		    ]);

	    } elseif ($this->isCompanyMedia($code) || $this->isArticle($code)) {
	    	$youtube = new Youtube();
	    	$templateModalVideo = new Template('modal_video2', 'modals');
	    	$modalsRendered[] = $templateModal->parse([
		    	'Code' => 'video',
		    	'Id' => 'video',
		    	'Content' => $templateModalVideo->parse(),
		    ]);
	    }

	    // production links block
	    $productionModel = new \Site\Models\ProductionModel();
		$productionModel->setDB($this->db);
		
		$productionList = $productionModel->getProducts();
		$productionList = Common::setLinks($productionList, 'production');
		$templateLinks = new Template('bl-links', 'components/production');
		$templateLinksItem = new ListTemplate('bl-links__item', 'components/production');
		$linksRendered .= $templateLinks->parse([
          'Title' => $headerData['BlockLinksHeading'],
          'List' => $templateLinksItem->parse($productionList)
        ]);

		$companyModel = new \Site\Models\CompanyModel();
		$companyModel->setDB($this->db);
        $companyNavigation = $companyModel->getCompanyChildren();
      	$companyNavigation = $companyNavigation ? Common::setLinks($companyNavigation, 'company') : [];
      	$templateCompanyNavigationItem = new ListTemplate('footer-company-navigation__elem.htm', 'components/navigation');
      // 	$this->page('index')->addInclude($this->partial('nav')->setCallback(function($item) use ($current) {
      //   return $item['Code'] == $current['Code'];
      // }));

      	$productionModel = new \Site\Models\ProductionModel();
		$productionModel->setDB($this->db);
        $directionsNavigation = $productionModel->getProductsDirections();
      	$directionsNavigation = $directionsNavigation ? Common::setLinks($directionsNavigation, 'production', 'direction') : [];
      	array_walk($directionsNavigation, function(&$direction) {
      		$direction['Title'] = strip_tags($direction['Title']);
      	});
      	$templateDirectionsNavigationItem = new ListTemplate('footer-directions-navigation__elem.htm', 'components/navigation');

      	$categoriesNavigation = $productionModel->getAllProducts();
      	$categoriesNavigation = $categoriesNavigation ? Common::setLinks($categoriesNavigation, 'production') : [];
      	array_walk($categoriesNavigation, function(&$category) {
      		$category['Title'] = strip_tags($category['Title']);
      	});
      	$templateCategoriesNavigationItem = new ListTemplate('footer-categories-navigation__elem.htm', 'components/navigation');

		return $baseTemplate->parse([
			'Settings' => [
				'site_title' => $Settings->get('SiteTitle'),
				'seo_keywords' => $Settings->get('SeoKeywords'),
				'seo_description' => $Settings->get('SeoDescription'),
				'site_protocol' => $Settings->get('SiteProtocol'),
				'site_domain' => $Settings->get('SiteDomain'),
				'site_url' => $Settings->get('SiteUrl'),
				//'site_meta_viewport' => in_array($page->code(), $authPageList) ? '' : '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes"/>',
				'ogimage' => '/img/OG.jpg',
				'yandex_metrika' => $Settings->get('YandexMetrika'),
				'meta_verification' => $Settings->get('MetaVerification'),
			],
			'Page' => [
				'title' => $page->getSeoTitle() ?: $page->getTitle(),
				'heading' => $page->getHeading(),
				'seo_description' => $page->getSeoDescription() ?: $Settings->get('SeoDescription'),
				'seo_keywords' => $page->getSeoKeywords() ?: $Settings->get('SeoKeywords'),
			],
			'Header' => $contacts + [
				'Navigation' => Components::getNavigation($this->db, $code, $params['code']),
				//'Login' => $login,
				//'Logo' => '/img/logo-white.svg',
				'Logo' => '/img/logo-scaling.svg',
				'Alt' => htmlspecialchars($Settings->get('SiteTitle'), ENT_QUOTES),
			],
			'Content' => $page->{$action}($params),
			'Contacts' => !$this->isContacts($code) ? $contactsRendered : '',
			'Links' => $linksRendered,
			'Footer' => $contacts + [
				'CompanyNav' => $templateCompanyNavigationItem->parse($companyNavigation),
				'DirectionsNav' => $templateDirectionsNavigationItem->parse($directionsNavigation),
				'CategoriesNav' => $templateCategoriesNavigationItem->parse($categoriesNavigation),
				'ContainerClass' => !$this->isHome($code) && !$this->isContacts($code) ? 'border-top' : '',
				'Year'=> DateHelper::getCurrentYear(),
				'Alt' => htmlspecialchars($Settings->get('SiteTitle'), ENT_QUOTES),
			],
			'Modals' => isset($modalsRendered) ? implode('', $modalsRendered) : '',
			'Version' => $Settings->get('AssetsVersion') ? '?v2.' . $Settings->get('AssetsVersion') : ''
		]);
	}		
}
