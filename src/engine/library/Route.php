<?php

namespace Engine\Library;

class Route {
  /**
   * Request method
   * @var string
   */
  protected $method;
  /**
   * URI with placeholders (e.g. :placeholder)
   * @var string
   */
  protected $pattern;
  /**
   * Array of required param names if not compiled else param name/ param value pairs
   * @var array
   */
  protected $requiredParams;
  /**
   * Array of optional param names if not compiled else param name/ param value pairs 
   * @var array
   */
  protected $optionalParams;
  /**
   * Default key /value pairs for optinal params if those are not specified
   * @var array
   */
  protected $defaultParams;
  /**
   * Name of the controller class which should respond on route request
   * @var string
   */
  protected $controller;
  /**
   * Callable method name in Controller class
   * @var string 
   */
  protected $action;

  public function __construct($args) {
    $this->method = $args['method'];
    $this->pattern = $args['pattern'];

    $this->requiredParams = $args['params']['required'];
    $this->optionalParams = $args['params']['optional'];
    $this->defaultParams = $args['defaultParams'];
    
    $this->controller = $args['controller'];
    $this->action = $args['action'];
  }

  public function method () {
    return $this->method;
  }
  
  public function pattern () {
    return $this->pattern;
  }

  public function params ($params = []) {
    if (!$params) {
      return 
        $this->requiredParams + $this->optionalParams;
    }
    // var_dump($this->requiredParams);
    // var_dump($this->optionalParams);
    
    $required = [];
    $optional = [];

    foreach ($params as $name => $value) {
      // var_dump(array_search($name, $this->requiredParams));
      if (array_search($name, $this->requiredParams) !== false) {
        $required[$name] = $value;
      } else if (array_search($name, $this->optionalParams) !== false) {
        $optional[$name] = $value;
      } else {
        $optional[$name] = $value;
      }
    }
    $this->requiredParams = $required;
    $this->optionalParams = $optional;
  }

  public function requiredParams ($params = []) {
    // Get method
    if (!$params) {
      return $this->requiredParams;
    }

    // Set method
    $this->requiredParams = $params;
    return $this;
  }

  public function optionalParams ($params = []) {
    // Get method
    if (!$params) {
      return $this->optionalParams;
    }

    // Set method
    $this->optionalParams = $params;
    return $this;
  }

  public function controller () {
    return $this->controller;
  }

  public function action () {
    return $this->action;
  }

  public function defaultParams () {
    return $this->defaultParams;
  }

}