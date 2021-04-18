<?php

/**
 * Конфигурационный массив, содержащий настройки актуальные для продакшен-сервера
 * Загрузка полного массива конфигураций происходит через файл config.php
 */

$Config = [];

$Config['Database']['host'] 	  = 'localhost';
$Config['Database']['user'] 	  = 'codeshow_psk';
$Config['Database']['pass'] 	  = 'MayT|-|EfoRce_be_with_u';
$Config['Database']['db']      = 'codeshow_psk_new';

return $Config;
