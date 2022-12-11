<?php
namespace Site\Pages;
use Engine\Library\ListTemplate;
use Engine\Library\Common;
use Engine\Library\Page;
use Engine\Library\Template;
use Engine\Library\Youtube;
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
      'stores' => ['template' => 'company__stores'],
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
    global $Settings;
    
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

      // VIDEO
      $youtube = new Youtube();
      $templateVideoBlock = new Template('bl-video', 'video');
      $videoBlockRendered = $templateVideoBlock->parse([
        'CodeRus' => $youtube->GetCodeFromSource($Settings->get('YoutubeCodeRus')),
        'CodeEng' => $youtube->GetCodeFromSource($Settings->get('YoutubeCodeEng')),
      ]);

      $advantages = $this->model->getAdvantages();
      //$advantages = Common::setLinks($advantages, 'production');
      $advantagesTemplate = new Template('bl-advantages', 'company');
      $advantagesItemTemplate = new Template('bl-advantages__item', 'company/partial');
      $advantagesItemLinkedTemplate = new Template('bl-advantages-linked__item', 'company/partial');
      //$advantagesItemTemplate  = $advantagesItemTemplate->parse($advantages);
      $attrHtml = '';
      foreach ($advantages as $advantage) {
        $advantage['Value'] = preg_replace('/(\d+)/', '<span class="count-number' . ($advantage['NoSpaces'] ? ' no-spacing' : '') . '">$1</span>', $advantage['Value']);
        if ($advantage['Link']) {
          $attrHtml .= $advantagesItemLinkedTemplate->parse($advantage);
        } else {
          $attrHtml .= $advantagesItemTemplate->parse($advantage);
        }
      }

      $advantagesRendered = $advantagesTemplate->parse([
            'List' => $attrHtml
      ]);

      $content['BlockProductionHeading'] = strip_tags($content['BlockProductionHeading']);
      $rendered = $this->page('index')->parse($content + [
        'Nav' => $navigation,
        'BlockMission' => trim(strip_tags($content['BlockMissionText'])) ? '<div class="about__text article__content" data-animation><div><h3>Миссия</h3>' . $content['BlockMissionText'] . '</div></div>' : '',
        'BlockStandarts' => trim(strip_tags($content['BlockStandartsText'])) ? '<div class="about__text article__content" data-animation><div><h3>Стандарты</h3>' . $content['BlockStandartsText'] . '</div></div>' : '',
        'BlockGuarantees' => trim(strip_tags($content['BlockGuaranteesText'])) ? '<div class="about__text article__content" data-animation><div><h3>Гарантии</h3>' . $content['BlockGuaranteesText'] . '</div></div>' : '',
        'Video' => $videoBlockRendered,
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
          $license['PreviewWebp'] = Common::flGetWebpByImage($license['Preview']);
          if ($license['File']) $license['Upload'] = '<a href="' . $license['File'] . '" download class="sert__download icon-share">Скачать</a>';
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
          $review['PreviewWebp'] = Common::flGetWebpByImage($review['Preview']);
          if ($review['File']) $review['Upload'] = '<a href="' . $review['File'] . '" download class="sert__download icon-share">Скачать</a>';
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
          $partner['ImageWebp'] = Common::flGetWebpByImage($partner['Image']);
          $partner['Alt'] = htmlspecialchars($partner['Title'], ENT_QUOTES);
          if ($partner['Link']) $partner['Link'] = '<a href="' . $partner['Link'] .'" target="_blank" rel="nofollow" class="company__link icon-link">' . $partner['Link'] .'</a>';
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
          $client['ImageWebp'] = Common::flGetWebpByImage($client['Image']);
          $client['Alt'] = htmlspecialchars($client['Title'], ENT_QUOTES);
          if ($client['Link']) $client['Link'] = '<a href="' . $client['Link'] .'" target="_blank" rel="nofollow" class="company__link icon-link">' . $client['Link'] .'</a>';
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
          $staff['PhotoPreviewWebp'] = Common::flGetWebpByImage($staff['PhotoPreview']);
        }
        $staffsItemTemplate = new ListTemplate('company__staff__item', 'company/partial');
        $staffsRendered  = $staffsItemTemplate->parse($staffs);

        $rendered = $this->page($current['Code'])->parse([
          'Breadcrumbs' => $breadcrumbsRendered,
          'Title' => strip_tags($content['PageStaffHeading']),
          'List' => $staffsRendered,
        ] + $content);

      // STORES PAGE
      } elseif ($current['Code'] == 'stores') {
        $storesModel = new \Site\Models\StoresModel($this->model->getDB());
        $stores = $storesModel->getAllItems();

        $attrTemplate = new Template('_stores-attributes-row.htm', 'stores');
        $vehicleTemplate = new Template('_stores-vehicles-row.htm', 'stores');
        $photoTemplate = new Template('_stores-photos-row.htm', 'stores');
        $first = true;
        foreach ($stores as $i => &$store) {
          $store['AttrsHtml'] = '';
          $store['VehiclesHtml'] = '';
          $store['PhotosHtml'] = '';

          if ($first) {
            $store['NavClass'] = ' active';
            $first = false;
          } else {
            $store['NavClass'] = '';
          }

          // if ($store['Attributes'] && count($store['Attributes']) > 4) {
          //   $store['AttrsClass'] = ' many';
          // }

          if ($store['Attributes']) {
            foreach ($store['Attributes'] as $attr) {
              $attr['Value'] = preg_replace('/(\d+)/', '<span class="count-number">$1</span>', $attr['Value']);
              $attr['Title'] = 
              $store['AttrsHtml'] .= $attrTemplate->parse($attr);
            }
          }
          if ($store['Vehicles']) {
            foreach ($store['Vehicles'] as $vehicle) {
              $store['VehiclesHtml'] .= $vehicleTemplate->parse($vehicle);
            }
          }
          if ($store['Photos']) {
            $counter = 0;
            foreach ($store['Photos'] as $photo) {
              $counter++;

              $photo['ImageWebp'] = Common::flGetWebpByImage($photo['Image']);
              $photo['Class'] = ($counter == 3 && count($store['Photos']) > 3) ? ' more-photos' : '';
              $photo['MoreText'] = ($counter == 3 && count($store['Photos']) > 3) ? ('<span>Смотреть еще ' . (count($store['Photos']) - 3) . '</span>') : '';
              $store['PhotosHtml'] .= $photoTemplate->parse($photo);
            }
          }
        }

        $storesItemTemplate = new ListTemplate('_stores-row', 'stores');
        $storesRendered  = $storesItemTemplate->parse($stores);

        $storesNavigationTemplate = new ListTemplate('_stores-navigation-row', 'stores');
        $navigationRendered  = $storesNavigationTemplate->parse($stores);

        $rendered = $this->page($current['Code'])->parse([
          'Breadcrumbs' => $breadcrumbsRendered,
          'Title' => strip_tags($content['PageStoresHeading']),
          'Navigation' => $navigationRendered,
          'List' => $storesRendered,
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
