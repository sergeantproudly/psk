<?php
namespace Engine\Library;

use Engine\Helper\RequestHelper;

class Request implements \JsonSerializable {
  const DM = '/';
  const GET = 'GET';
  const POST = 'POST';

  protected $_uri;
  protected $_params;
  protected $_method;

  public function __construct() {
    $this->_method = $_SERVER['REQUEST_METHOD'];
    $this->_uri    = urldecode(strtok($_SERVER['REQUEST_URI'], '?'));

    // TODO: Add compatibility with ?params1=value1&... query component of URI

    if ($this->_method == self::GET)
      $this->_params = $_GET;
    else if ($this->_method == self::POST)
      $this->_params = $_POST;

    $this->_params = RequestHelper::paramDecode($this->_params);
  }

  public function method () {
    return $this->_method;
  }

  public function params () {
    return $this->_params;
  }

  public function uri () {
    return $this->_uri;
  }

  public function jsonSerialize() {
    return [
      'method' => $this->_method,
      'uri' => $this->_uri,
      'params' => $this->_params,
    ];
  }
}
