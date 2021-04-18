<?php
namespace Engine\Library;

use Engine\Helper\ArrayHelper;
use Engine\Helper\UrlHelper;
use Engine\Helper\RouteHelper;

class Router {
    const GET = 'GET';
    const POST = 'POST';

    private $_routes = [];
//    private $_types = [
//      'i' => '/[0-9]*/',
//      's' => '/[a-zA-Zа-яА-Я\-+_]*/',
//      'm' => '/[a-z]*/',
//      'a' => '/*/'
//    ];

    protected function _parsePattern($pattern) {
      // $delimiter = '/';
      // $pattern = explode($delimiter, trim($pattern, $delimiter));
      
      $matchRequired = [];
      $matchOptional = [];
      
      preg_match_all("/{([^?}]*)\?}/", $pattern, $matchOptional);
      $optionalParams = $matchOptional[1];

      // var_dump($pattern);
      
      $pattern = preg_replace("/{[^?}]*\?}\//", '', $pattern);

      preg_match_all("/{([^}]*)}/", $pattern, $matchRequired);
      $requiredParams = $matchRequired[1];
      
      return [
        'required' => $requiredParams,
        'optional' => $optionalParams,
      ];
    }

    public function add($method, $pattern, $handler, $defaultParams = []) {
      $handler = explode('@', $handler);

      $page = $handler[0];
      $action = $handler[1] ?? 'index';

      $params = $this->_parsePattern($pattern);

      $this->_routes[] = new Route([
        'method'        => $method,
        'pattern'       => $pattern,
        'params'        => $params,
        'defaultParams' => $defaultParams,
        'controller'    => $page,
        'action'        => $action,
      ]);
  
      return $this;
    }

    public function dispatch() {
      $request = new Request();

      foreach ($this->_routes as $key => $route) {
        $route = $this->_match($request, $route);
      //  var_dump($route);
        if ($route) {
          return $route;
        }
      }

      return null;
    }

    protected function _match(Request $request, Route $route) {
      if ($request->method() !== $route->method())
        return false;

      if ($request->method() === self::GET) {

        // Check GET method cases: Uri string contains request params
        return $this->_matchGET($request, $route);

      } else if ($request->method() === self::POST) {

        // Check POST method cases: Property $_params contains request params
        return $this->_matchPOST($request, $route);

      }

      return false;
    }

    protected function _matchGET (Request $request, Route $route) {
      $requestUri   = explode('/', trim($request->uri(), '/'));
      $routePattern = explode('/', trim($route->pattern(), '/'));
      // var_dump($requestUri);

      // 1. If exploded request uri consists of more data than needed
      if (count($requestUri) > count($routePattern))
        return false;

      // 2. If exploded request uri do not store enough data and there are no optional params for route that might be missed 
      if (count($requestUri) !== count($routePattern) 
      && !count($route->optionalParams())) {
        return false;
      }

      $requestParams = [];
      $count = count($routePattern);
      for ($i = 0; $i < $count; $i++) { 
        $routePiece = array_shift($routePattern);
        $requestPiece = array_shift($requestUri);
        
        $isParam = RouteHelper::isParam($routePiece);
        // var_dump($routePiece, $isParam);
        
        // routePiece is like {paramName} or {optionalParamName?}
        if ($isParam) {
          $isOptional = RouteHelper::isOptionalParam($routePiece);
          $paramName = trim($routePiece, '{?}'); // Remove param marks
          $paramValue = trim($requestPiece);

          // If not an optional param and data wasn't sended in request URI
          if (!$isOptional && !$requestPiece) {
            return false;
          }
          
          if ($isOptional) {
            $defaultParams = $route->defaultParams();
            // var_dump($defaultParams);
            if (!$paramValue && array_key_exists($paramName, $defaultParams)) {
              // Param value was not passed in the optional case but there's a default value
              $requestParams[$paramName] = $defaultParams[$paramName];
            } else {
              // Param value was passed in the optional case
              $requestParams[$paramName] = $paramValue ?: null;
            }
          } else {
            // Param value was passed in the required case
            $requestParams[$paramName] = $paramValue ?: null;
            // var_dump($requestParams);
          }

        } else if ($routePiece === $requestPiece) {
          // Two static parts do match (e.g. catalog == catalog)
          continue;
        } else {
          // Two static parts do not match and it's not the param case (e.g. catalog !== company)
          return false;
        }
        
      }
      // var_dump($requestParams);
      $route->params($requestParams);

      return $route;
    }

    protected function _matchPOST (Request $request, Route $route) {

      if ($request->uri() !== $route->pattern())
        return false;

      $route->params($request->params());

      return $route;
    }
}
