<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 19-Jun-18
 * Time: 5:20 PM
 */

namespace Engine\Library\Ajax;


/**
 * Класс, описывающий ответ сервера на AJAX запрос
 *
 * @var bool $failed - true, если не удалось выполнить AJAX запрос, иначе false
 * @var int $code - код, содержащий причину неудачи запроса. Смотреть класс Errors
 * @var string $msg - сообщение с ответом сервера, которое при необходимости можно отобразить на клиенте
 * @var array $data - массив, содержащий данные необходимые клиенту для отображения состояния выполнения действия пользователя
 */
class Response implements \JsonSerializable {
  protected $failed;
  protected $code;
  protected $msg;
  protected $data;


  function __construct($failed, $code, $data = [], $msg = '') {
    $this->failed = $failed;
    $this->code = $code;
    $this->msg = $msg;
    $this->data = $data;

    if (!$msg) {
      $this->msg = $failed ? 'Не удалось выполнить операцию' : 'Операция выполнена успешно';
    }
  }

  function jsonSerialize()	{
    return [
      'failed' => $this->failed,
      'code' => $this->code,
      'msg' => $this->msg,
      'data' => $this->data,
    ];
  }
}
