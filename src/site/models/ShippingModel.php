<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 22-Jun-18
 * Time: 3:55 PM
 */

namespace Site\Models;


use Engine\Library\Model;

class ShippingModel extends Model {
  protected function _weekdayIdToCode ($weekdayRu) {
    $days = [
      1 => 'Monday',
      2 => 'Tuesday',
      3 => 'Wednesday',
      4 => 'Thursday',
      5 => 'Friday',
      6 => 'Saturday',
      7 => 'Sunday',
    ];
    return $days[$weekdayRu];
  }

  public function getShippingSchedule () {
    $shipping = $this->db->getAll("SELECT * FROM {$this->tables['shipping']}");
    $sortShipping = [];
    $cityNames = $this->getCities();

    foreach ($shipping as $record) {
      $cityId = $record['CityId'];
      $city = $cityNames[$cityId]['Title'];
      $weekdayId = $record['WeekdayId'];

      if (!isset($sortShipping[$weekdayId]))
        $sortShipping[$weekdayId] = [];

      $record['City'] = $city;
      $sortShipping[$weekdayId][] = $record;
    }

//    var_dump($sortShipping);
    $rowList = [];
    $rowCounter = 0;
    $maxRowCounter = 0;

    foreach ($sortShipping as $weekdayId => $cityList) {
      $dayCode = $this->_weekdayIdToCode($weekdayId);

      for ($i = 0; $i < max(count($cityList), $maxRowCounter); $i++) {
        $rowList[$rowCounter][$dayCode] = $cityList[$i]['City'] ?? '';
        $rowCounter++;
      }

      if ($maxRowCounter < $rowCounter)
        $maxRowCounter = $rowCounter;

      $rowCounter = 0;
    }

    return $rowList;
  }

  public function getCities () {
    $table = $this->tables['city'];
    return $this->db->getInd('Id', "SELECT * FROM {$table}");
  }

  public function getWeekdays () {
    $table = $this->tables['weekday'];
    return $this->db->getInd('Id', "SELECT * FROM {$table}");
  }

  public function getLastWeekdayId() {
    $table = $this->tables['weekday'];
    $lastWeekDay = 'Воскресенье';
    return $this->db->getOne("SELECT `Id` FROM {$table} WHERE `Title` = '{$lastWeekDay}'");
  }
}
