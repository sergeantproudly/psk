<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 20-Jun-18
 * Time: 9:49 PM
 */

namespace Engine\Helper;


class StringHelper {

  public static function replaceFirst($needle, $replace, $haystack) {
    $pos = strpos($haystack, $needle);
    $string = $haystack;

    if ($pos !== false) {
      $string = substr_replace($string, $replace, $pos, strlen($needle));
    }

    return $string;
  }

}
