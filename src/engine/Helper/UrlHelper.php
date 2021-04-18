<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 22-Jun-18
 * Time: 12:34 PM
 */

namespace Engine\Helper;


class UrlHelper {
  const DM = '/';

  public static function extractUriParams($uri, $uriBase) {
    // Remove static routing part (e.g. /catalog/order/12/20/ --> 12/20/)
    $requestUriParams = StringHelper::replaceFirst($uriBase, '', $uri);
    // Trim starting and ending '/' characters (i.e. 12/20/ --> 12/20)
    $requestUriParams = trim($requestUriParams, self::DM.' ');

    // Ensure that uri = '/' will be handled properly
    $requestParams = !empty($requestUriParams) ? explode(self::DM, $requestUriParams) : [];
    
    return $requestParams;
  }
}
