<?php
namespace Site\Pages;
use Engine\Library\ListTemplate;
use Engine\Library\Common;
use Engine\Library\Page;
use Engine\Library\Template;
use Site\Components\BreadcrumbsComponent;
use Site\Models\PageModel;

class CompanyPage extends Page {
  const CODE = 'company';
  const DIR = self::CODE;
  public $modelName = 'CompanyModel';

  function __construct() {
    parent::__construct(self::CODE, self::DIR);
    $this->setPages([
      'index' => ['template' => 'company'],
      'certs' => ['template' => 'company__licenses'],
      'reviews' => ['template' => 'company__reviews'],
      'partners' => ['template' => 'company__partners'],
      'direction' => ['template' => 'company__direction'],
      'clients' => ['template' => 'company__clients'],
      'default' => ['template' => 'company__default']
    ]);

    $this->setPartials([
      'nav' => [
        'type' => 'navigation',
        'template' => [
          'default' => 'company__child',
          'active' => 'company__child-active',
        ]
      ],
    ]);
  }

  function index($params) {
    $page = $params['code'];

    $content = $this->model->getContent($this->code());

    $current = $this->model->getCurrentChild($page); 

    // COMPANY PAGE
    if (!$current['Code']) {
      $navigation = $this->model->getCompanyChildren();
      $navigation = $navigation ? Common::setLinks($navigation, 'company') : [];

      $this->page('index')->addInclude($this->partial('nav')->setCallback(function($item) use ($current) {
        return $item['Code'] == $current['Code'];
      })); 

      $advantages = $this->model->getAdvantages();
      $advantages = Common::setLinks($advantages, 'production');
      $advantagesTemplate = new Template('bl-advantages', 'company');
      $advantagesItemTemplate = new ListTemplate('bl-advantages__item', 'company/partial');
      $advantagesItemTemplate  = $advantagesItemTemplate->parse($advantages);
      $advantagesRendered = $advantagesTemplate->parse([
            'List' => $advantagesItemTemplate
      ]);

      $content['BlockProductionHeading'] = strip_tags($content['BlockProductionHeading']);
      $rendered = $this->page('index')->parse($content + [
        'Nav' => $navigation,
        'BlockMission' => trim(strip_tags($content['BlockMissionText'])) ? '<div class="block" id="bl-mission"><h2>Миссия</h2>' . $content['BlockMissionText'] . '</div>' : '',
        'BlockStandarts' => trim(strip_tags($content['BlockStandartsText'])) ? '<div class="block" id="bl-standarts"><h2>Стандарты</h2>' . $content['BlockStandartsText'] . '</div>' : '',
        'BlockGuarantees' => trim(strip_tags($content['BlockGuaranteesText'])) ? '<div class="block" id="bl-guarantees"><h2>Гарантии</h2>' . $content['BlockGuaranteesText'] . '</div>' : '',
        'Advantages' => $advantagesRendered,
      ]);

    // INNER PAGES
    } else {
      $parent = $this->model->getContent(SELF::CODE);

      $breadcrumbs = new BreadcrumbsComponent;
      $breadcrumbsRendered = $breadcrumbs->render($current['Code'], [
        ['Code' => 'main', 'Link' => '/', 'Title' => 'Главная'],
        ['Code' => SELF::CODE, 'Link' => "/" . SELF::CODE . "/", 'Title' => $parent['Title']],
        ['Code' => $current['Code'], 'Link' => "/" . SELF::CODE . "/{$current['Code']}/", 'Title' => $current['Title']]
      ]);

      // CERTS PAGE
      if ($current['Code'] == 'certs') {
        $licenses = $this->model->getLicenseImages();
        foreach ($licenses as &$license) {
          $license['Alt'] = htmlspecialchars($license['Title'], ENT_QUOTES);
          if ($license['File']) $license['Upload'] = '<a href="' . $license['File'] . '" class="btn">Скачать</a>';
        }
        $licensesItemTemplate = new ListTemplate('company__licenses__item', 'company/partial');
        $licensesRendered  = $licensesItemTemplate->parse($licenses);

        $rendered = $this->page($current['Code'])->parse([
          'Breadcrumbs' => $breadcrumbsRendered,
          'Title' => strip_tags($content['PageLicensesHeading']),
          'Text' => trim(strip_tags($content['PageLicensesText'])) ? '<div class="text">' . $content['PageLicensesText'] . '</div>' : '',
          'List' => $licensesRendered,
        ] + $content);

      // REVIEWS PAGE
      } elseif ($current['Code'] == 'reviews') {
        $reviews = $this->model->getReviewImages();
        foreach ($reviews as &$review) {
          $review['Alt'] = htmlspecialchars($review['Title'], ENT_QUOTES);
          if ($review['File']) $review['Upload'] = '<a href="' . $review['File'] . '" class="btn">Скачать</a>';
        }
        $reviewsItemTemplate = new ListTemplate('company__reviews__item', 'company/partial');
        $reviewsRendered  = $reviewsItemTemplate->parse($reviews);

        $rendered = $this->page($current['Code'])->parse([
          'Breadcrumbs' => $breadcrumbsRendered,
          'Title' => strip_tags($content['PageReviewsHeading']),
          'Text' => trim(strip_tags($content['PageReviewsText'])) ? '<div class="text">' . $content['PageReviewsText'] . '</div>' : '',
          'List' => $reviewsRendered,
        ] + $content);

      // PARTNERS PAGE
      } elseif ($current['Code'] == 'partners') {
        $partnerModel = new \Site\Models\PartnerModel($this->model->getDB());
        $partners = $partnerModel->getPartners();
        foreach ($partners as &$partner) {
          $partner['Style'] = $partner['WidthBig'] ? 'style="width: ' . $partner['WidthBig'] . 'px"' : '';
          $partner['Alt'] = htmlspecialchars($partner['Title'], ENT_QUOTES);
          if ($partner['Link']) $partner['Link'] = '<a href="' . $partner['Link'] .' " class="external" target="_blank" rel="nofollow">' . $partner['Link'] . '</a>';
        }
        $partnersItemTemplate = new ListTemplate('company__partners__item', 'company/partial');
        $partnersRendered  = $partnersItemTemplate->parse($partners);

        $rendered = $this->page($current['Code'])->parse([
          'Breadcrumbs' => $breadcrumbsRendered,
          'Title' => strip_tags($content['PagePartnersHeading']),
          'List' => $partnersRendered,
        ] + $content);

      // CLIENTS PAGE
      } elseif ($current['Code'] == 'clients') {
        $clientModel = new \Site\Models\ClientModel($this->model->getDB());
        $clients = $clientModel->getClients();
        foreach ($clients as &$client) {
          $client['Style'] = $client['WidthBig'] ? 'style="width: ' . $client['WidthBig'] . 'px"' : '';
          $client['Alt'] = htmlspecialchars($client['Title'], ENT_QUOTES);
          if ($client['Link']) $client['Link'] = '<a href="' . $client['Link'] .' " class="external" target="_blank" rel="nofollow">' . $client['Link'] . '</a>';
        }
        $clientsItemTemplate = new ListTemplate('company__client__item', 'company/partial');
        $clientsRendered  = $clientsItemTemplate->parse($clients);

        $rendered = $this->page($current['Code'])->parse([
          'Breadcrumbs' => $breadcrumbsRendered,
          'Title' => strip_tags($content['PageClientsHeading']),
          'List' => $clientsRendered,
        ] + $content);

      // STAFF PAGE
      } elseif ($current['Code'] == 'direction') {
        $staffModel = new \Site\Models\StaffModel($this->model->getDB());
        $staffs = $staffModel->getStaffDirection();
        foreach ($staffs as &$staff) {
          $staff['Alt'] = htmlspecialchars($staff['Name'], ENT_QUOTES);
        }
        $staffsItemTemplate = new ListTemplate('company__staff__item', 'company/partial');
        $staffsRendered  = $staffsItemTemplate->parse($staffs);

        $rendered = $this->page($current['Code'])->parse([
          'Breadcrumbs' => $breadcrumbsRendered,
          'Title' => strip_tags($content['PageStaffHeading']),
          'List' => $staffsRendered,
        ] + $content);

      // DEFAULT PAGE
      } else {
        $codeHeading = 'Page' . ucfirst($current['Code']) . 'Heading';
        $codeText = 'Page' . ucfirst($current['Code']) . 'Text';
        
        $rendered = $this->page('default')->parse([
          'Breadcrumbs' => $breadcrumbsRendered,
          'Title' => strip_tags($content[$codeHeading]),
          'Text' => trim(strip_tags($content[$codeText])) ? '<div class="text">' . $content[$codeText] . '</div>' : '',
        ] + $content);
      }
    }

    return $rendered;
  }


}


?>
