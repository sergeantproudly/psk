<?php
namespace Site\Pages;
use Engine\Library\Page;
use Engine\Library\Template;
use Engine\Library\ListTemplate;
use Engine\Library\Common;
//use Engine\Library\Youtube;
use Engine\Library\VkVideo;

use Site\Components\SliderComponent;
use Site\Components\ProductionDirectionsComponent;
use Site\Components\ProjectsComponent;
use Site\Components\ClientsComponent;
use Site\Components\ArticlesComponent;
use Site\Components\ProductionComponent;
use Site\Components\StaffComponent;
use Site\Components\PartnersComponent;

class HomePage extends Page {
	const CODE = 'home';
	const DIR = '';
	public $modelName = 'PageModel'; 
	
	function __construct() {
		parent::__construct(self::CODE, self::DIR);

		$this->setPages([
			'index' => ['template' => 'home']
		]);
	}

	function index($params = []) {
		global $Settings;

		$database = $this->model->getDB();
		$content = $this->model->getContent($this->code());

		$contacts = $this->model->getContent('contacts');

		// SLIDER
		$sliderModel = new \Site\Models\SliderModel();
		$sliderModel->setDB($this->model->getDB());

		$slides = $sliderModel->getSlides();
		array_walk($slides, function(&$slide, $key) {
		  $slide['Num'] = $key + 1;
	      $slide['ImageWebp'] = Common::flGetWebpByImage($slide['Image']);
	      $slide['HTag'] = ($key === 0) ? 'h1' : 'h2';
	      $slide['Alt'] = htmlspecialchars($slide['Title'], ENT_QUOTES);
	      if (!$slide['Button']) $slide['Button'] = 'Подробнее';
	      $slide['Text'] = nl2br($slide['Text']);
	    });
		$sliderComponent = new SliderComponent;
		$sliderRendered = $sliderComponent->render($slides);

		// PRODUCTION DIRECTIONS
		// $productionModel = new \Site\Models\ProductionModel();
		// $productionModel->setDB($this->model->getDB());
		
		// $directionsList = $productionModel->getProductsDirections();
		// $directionsList = $directionsList ? Common::setLinks($directionsList, 'production', 'direction') : [];
		// $directionsComponent = new ProductionDirectionsComponent;
		// $directionsRendered = $directionsComponent->render($directionsList);
		
		// PROJECTS
		$projectModel = new \Site\Models\ProjectModel();
		$projectModel->setDB($this->model->getDB());

		$projectList = $projectModel->getProjects(3);
		$projectList = Common::setLinks($projectList, 'projects');
		foreach ($projectList as $i => &$project) {
			$project['Image'] = $project['Image1164_508'];
			$project['ImageWebp'] = Common::flGetWebpByImage($project['Image']);
			$project['Alt'] = htmlspecialchars($project['Title'], ENT_QUOTES);
		}
		$projectsComponent = new ProjectsComponent;
		$projectsRendered = $projectsComponent->render($projectList, $content['ProjectsHeading']);

		// CLIENTS
		$clientModel = new \Site\Models\ClientModel();
		$clientModel->setDB($this->model->getDB());

		$clientList = $clientModel->getClientsForHomePage(3);
		foreach ($clientList as $i => &$client) {
			//$client['Style'] = $client['Width'] ? 'style="width: ' . $client['Width'] . 'px"' : '';
			$client['ImageWebp'] = Common::flGetWebpByImage($client['Image']);
			$client['Alt'] = htmlspecialchars($client['Title'], ENT_QUOTES);
		}
		$clientsComponent = new ClientsComponent;
		$clientsRendered = $clientsComponent->render($clientList);

		// PARTNERS
		$partnerModel = new \Site\Models\PartnerModel();
		$partnerModel->setDB($this->model->getDB());

		$partnerList = $partnerModel->getPartnersForHomePage(3);
		foreach ($partnerList as $i => &$partner) {
			//$partner['Style'] = $partner['Width'] ? 'style="width: ' . $partner['Width'] . 'px"' : '';
			$partner['ImageWebp'] = Common::flGetWebpByImage($partner['Image']);
			$partner['Alt'] = htmlspecialchars($partner['Title'], ENT_QUOTES);
		}
		$partnersComponent = new PartnersComponent;
		$partnersRendered = $partnersComponent->render($partnerList);

		// VIDEO
		//$youtube = new Youtube();
		$vkvideo = new VkVideo();
		$templateVideoBlock = new Template('bl-video-main', 'video');
		$videoBlockRendered = $templateVideoBlock->parse([
			// 'CodeRus' => $youtube->GetCodeFromSource($Settings->get('YoutubeCodeRus')),
        	// 'CodeEng' => $youtube->GetCodeFromSource($Settings->get('YoutubeCodeEng')),
        	'CodeRus' => $vkvideo->GetCodeFromSource($Settings->get('VkVideoCodeRus')),
        	'CodeEng' => $vkvideo->GetCodeFromSource($Settings->get('VkVideoCodeEng')),
        	'Owner' => $vkvideo->GetOwnerFromSource($Settings->get('VkVideoCodeRus')),
		]);

		// NEWS/ARTICLES
		$articleModel = new \Site\Models\ArticleModel();
		$articleModel->setDB($this->model->getDB());
		
		$articleList = $articleModel->getArticles(4);
		$articleList = Common::setLinks($articleList, 'articles');
		foreach ($articleList as $i => &$article) {
			$article['Date'] = Common::ModifiedDate($article['PublishDate']);
			$article['PreviewImage'] = $article['Preview2'] ?: $article['Preview'];
			$article['PreviewWebp'] = Common::flGetWebpByImage($article['PreviewImage']);
			$article['Alt'] = htmlspecialchars($article['Title'], ENT_QUOTES);
		}
		$articlesComponent = new ArticlesComponent;
		$articlesRendered = $articlesComponent->render($articleList);

		// PRODUCTION
		// $productionModel = new \Site\Models\ProductionModel();
		// $productionModel->setDB($this->model->getDB());
		
		// $productionList = $productionModel->getProducts();
		// $productionList = Common::setLinks($productionList, 'production');
		// $productionComponent = new ProductionComponent;
		// $productionRendered = $productionComponent->render($productionList);

		// COMPANY
		// $blockTextTemplate = new Template('bl-company', 'company');
		// $blockTextRendered = $blockTextTemplate->parse([
		// 	'Heading' => strip_tags($content['BlockTextHeading']),
		// 	'Text' => $content['BlockTextText'],
		// 	'More' => strip_tags($content['BlockTextMore']),
		// ]);

		// ABOUT
		// $company = $this->model->getContent('company');
		// $blockAboutTemplate = new Template('bl-about', 'company');
		// $blockAboutRendered = $blockAboutTemplate->parse([
		// 	'MissionText' => strip_tags($company['BlockMissionText']),
		// 	'StandartsText' => strip_tags($company['BlockStandartsText']),
		// 	'GuaranteesText' => strip_tags($company['BlockGuaranteesText']),
		// ]);

		// STAFF
		$staffModel = new \Site\Models\StaffModel();
		$staffModel->setDB($this->model->getDB());
		
		$staffList = $staffModel->getStaffDirection(true);
		$staffComponent = new StaffComponent;
		$staffRendered = $staffComponent->render($staffList, 'Руководство');

		return $this->page('index')->parse($content + $contacts + [
			'Slider' => $sliderRendered,
			//'Directions' => $directionsRendered,
			'Projects' => $projectsRendered,
			'Clients' => $clientsRendered,
			'Video' => $videoBlockRendered,
			'Articles' => $articlesRendered,
			//'Production' => $productionRendered,
			//'Company' => $blockTextRendered,
			//'About' => $blockAboutRendered,
			'Staff' => $staffRendered,
			'Partners' => $partnersRendered,
		]);
	}
}

?>
