<?php

namespace Site\Components;
use Engine\Library\Component;

class FooterComponent extends Component {
  const CODE = 'footer';
  const DIR = 'layout';
  const MODEL = 'PageModel';

  function __construct($database) {
    parent::__construct(self::CODE, self::DIR, false);
    
    $this->setModel(self::MODEL, $database);
    
    $this->setViews([
      'default' => ['template' => 'footer']
    ]);
  }
}