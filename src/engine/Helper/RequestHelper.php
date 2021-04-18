<?php

namespace Engine\Helper;

class RequestHelper {
  public static function paramDecode ($param) {
    if (is_array($param)) {
      return array_map(__METHOD__, $param);
    } else if (is_string($param)) {
      return urldecode($param);
    }

    throw new Exception("Request param {$param} has unknown type", 1);
  }
}

?>
