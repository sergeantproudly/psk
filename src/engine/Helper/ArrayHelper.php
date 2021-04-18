<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 23-Jun-18
 * Time: 10:48 AM
 */

namespace Engine\Helper;


class ArrayHelper {
  public static function bindKeyValue($valueArray, $keyArray) {
    $pairs = [];
//      var_dump($requestParams, $routeParams);

    for ($i = 0; $i < count($keyArray); $i++) {
      $key = $keyArray[$i];
      $value = $valueArray[$i] ?? null;

      $pairs[$key] = $value;
    }

    return $pairs;
  }
}
