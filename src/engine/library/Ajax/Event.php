<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 19-Jun-18
 * Time: 5:19 PM
 */

namespace Engine\Library\Ajax;

class Event {
  const USE_JSON = true;

  static function success($code = null, $data = [], $msg = '') {
    $state = new Response(false, $code, $data, $msg);
    return self::respond($state);
  }

  static function validationFail($data = [], $msg = '') {
    $state = new Response(true, Errors::VALIDATION, $data, $msg);
    return self::respond($state);
  }

  static function sendingFail($data = [], $msg = '') {
    $state = new Response(true, Errors::SENDING, $data, $msg);
    return self::respond($state);
  }

  static function fail($code = '', $data = [], $msg = '') {
    $state = new Response(true, $code ?: Status::FAIL, $data,  $msg);

    return self::respond($state);
  }

  static public function respond($state) {

//    var_dump( self::USE_JSON ? json_encode($state) : $state );
    header("Content-type:application/json");
    return self::USE_JSON ? json_encode($state, JSON_PRETTY_PRINT) : $state;
  }
}
