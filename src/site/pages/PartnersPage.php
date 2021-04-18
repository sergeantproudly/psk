<?php
namespace Site\Pages;
use Engine\Library\Page;

class PartnersPage extends Page {
  const CODE = 'partners';
  const DIR = self::CODE;
  public $modelName = 'PartnerModel';

  function __construct() {
    parent::__construct(self::CODE, self::DIR);
  
    $this->setPages([
      'index' => ['template' => 'partners']
    ]);

    $this->setPartials([
      'list' => [
        'type' => 'list',
        'template' => 'partners__card'
      ],
    ]);
  }
  
  function index() {
    $partners = $this->model->getPartners();

    $this->page('index')->addInclude($this->partial('list'));
    return $this->page('index')->parse($this->page + [
      'list' => $partners
    ]);
  }

    
}



?>