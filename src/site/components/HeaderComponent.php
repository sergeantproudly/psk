<?php

namespace Site\Components;
use Engine\Library\Component;

class HeaderComponent extends Component {
  const CODE = 'header';
  const DIR = 'layout';
  const MODEL = 'PageModel';

  function __construct($database) {
    parent::__construct(self::CODE, self::DIR, false);
    
    $this->setModel(self::MODEL, $database);
    
    $this->setViews([
      'default' => ['template' => 'header']
    ]);
  }
}
