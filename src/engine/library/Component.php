<?php

namespace Engine\Library;

abstract class Component {
  const COMPONENT = 'component';

  // protected $data = [];
  protected $dir = '';
  protected $code = '';
  public $model = null;

  protected $views = [];
  protected $partials = [];
  
  function __construct($code, $dir, $sub = self::COMPONENT) {
    if ($sub) {
      $dir = $dir.'/'.$sub;
    }

    $this->code = $code;
    $this->dir = $dir;
  }

  function setPartials($templates) {
    foreach ($templates as $name => $partial) {
      $this->setPartial($name, $partial);
    }
  }

  function setPartial($name, $partial) {
    $template = $this->initTemplate($partial);
    $template->setName($name);
    $this->partials[$name] = $template;
  }

  function getPartial($name) {
    return $this->partials[$name];
  }

  function partial($name) {
    // $this->current['template'] = $this->getPartial($name);
    // var_dump('TYPE: '.self::TYPE_PARTIAL);
    // $this->type['type'] = self::TYPE_PARTIAL;
    return $this->getPartial($name);
  }

  // function addData($data) {
  //   $this->template->setData($data + $initial);
  // }

  function getView($name) {
    // var_dump($this->views[$name]);
    return $this->views[$name] ?? null;
  }

  function setViews($views) {
    foreach ($views as $name => $view) {
      $this->views[$name] = new Template($view['template'], $this->dir);
    }
  }

  function view($name) {
    return $this->getView($name);
  }

  function setModel($model, $db) {
    $class = '\\Site\\Models\\'.$model;
    $model = new $class();
    $model->setDB($db);
    $this->model = $model;
  }

  public function setLinks($array, $action) {
    // var_dump(array_keys($array));
    // var_dump(range(0, count($array) - 1));
  
    foreach ($array as $key => $item) {
      $array[$key]['Link'] = "/{$this->code}/{$action}/{$item['Code']}";
    }
    return $array;
  }

  function initTemplate($partial) {
    $type = $partial['type'];
    // var_dump($type);
    switch ($type) {
      // case 'component':
      //   $dir = $this->dir.'/partial';
      //   $template = new Template($partial['template'], $dir);
      // break;
      case 'list':
        $template = new ListTemplate($partial['template'], 
          $this->dir);
      break;
      case 'navigation':
        // var_dump($partial['template']);
        $template = new NavigationTemplate($partial['template'], $this->dir);
      break;
      default:
        var_dump('Unknown type of partial: '.$type);
      break;
    }
    return $template;
  }

}






?>