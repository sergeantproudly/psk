<?php 
namespace Site\Models;
use Engine\Library\Model;
use Engine\Library\Common;

class SliderModel extends Model {
  /**
   * Возвращает все слайды
   *
   * @return array
   */
  function getSlides() {
    return $this->table('slider')->getAll();
  }

}


?>
