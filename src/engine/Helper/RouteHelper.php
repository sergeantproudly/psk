<?php

namespace Engine\Helper;

class RouteHelper {
  public static function isParam ($testString) {
    if (mb_substr($testString, 0, 1) == '{' 
    && mb_substr($testString, -1, 1) == '}') {
      return true;
    } else {
      return false;
    }
  }

  public static function isOptionalParam ($testString) {
    if (mb_substr($testString, -2, 1) == '?') {
      return true;
    } else {
      return false;
    }
  }
}
