<?php

namespace Engine\Library;

abstract class ImprovedComponent {
  protected $data   = [];
  protected $dir    = '';
  protected $code   = '';
  public    $model  = null;

  protected $currentTemplate;
  
  protected $templates = [];  // Templates of the component
  protected $partials  = [];  // Templates to include into component
  
  function __construct($code) {
    $dir = 'components/'. strtolower($code).'/';
    
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
  
  function includePartials($templateNameList, $partialTemplates) {
    foreach ($templateNameList as $templateName) {
      // Get the content of the view template
      $templateContent = $this->templates[$templateName]->getPlain();
      
      // Get names of partials used inside the component view
      $templateIncludes = Parser::getIncludes($templateContent);
      // ['links']

      // Get the array of partials keys found in the template
      $includedContent = [];

      // Check whether partials exists in the view's include list 
      foreach ($partialTemplates as $name => $partial) {
        // var_dump($partial);
        if (\in_array(strtoupper($templateName), $templateIncludes)) {
          $includedContent['{{INCLUDE:'.strtoupper($templateName).'}}'] = $partial->getPlain();
        }
        // var_dump($includes);
      }

      $updatedContent = strtr($templateContent, $includedContent);
      $this->template[$templateName]->setPlain($updatedContent);  

      // var_dump($this->views[$viewName]);
    }
  }
  
  function setTemplates(array $templateData) {
    foreach ($templateData as $name => $template) {
      
      $this->templates[$name] = new Template($template['template'], $this->dir);
      $this->templates[$name]->setName($name);
      // var_dump($this->templates[$name]);
      if ($name == 'default') {
        $this->currentTemplate = $this->templates[$name];
      }
    }
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

  public function getTemplate($name = '') {
    return $this->currentTemplate;
  }

  public function setTemplate($name) {
    $this->currentTemplate = 
      $this->templates[$name] ?? $this->templates['default'];
    
    return $this;
  }

  public function attachTemplate($template, $name = '') {
    // var_dump($this->theme());
    $this->currentTemplate->addInclude($template, $name);
  }

  public function parse($data) {
    return $this->getTemplate()->parse($data);
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
