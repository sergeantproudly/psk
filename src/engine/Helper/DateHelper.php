<?php

namespace Engine\Helper;

class DateHelper {
  public static function getCurrentYear($locale = 'ru_RU') {
    setlocale(LC_TIME, $locale);
    return strftime('%Y');
  }
}
