<?php
namespace Site\Pages;
use Engine\Library\Page;

class ClientsPage extends Page {
  const CODE = 'clients';
  const DIR = self::CODE;
  public $modelName = 'ClientModel';

  function __construct() {
    parent::__construct(self::CODE, self::DIR);
  
    $this->setPages([
      'index' => ['template' => 'clients']
    ]);

    $this->setPartials([
      'list' => [
        'type' => 'list',
        'template' => 'clients__card'
      ],
    ]);
  }
  
  function index() {
    $clients = $this->model->getClients();

    $this->page('index')->addInclude($this->partial('list'));
    return $this->page('index')->parse($this->page + [
      'list' => $clients
    ]);
  }

    
}



?>