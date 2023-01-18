<?php

/**
 * Конфигурационный массив, содержащий настройки актуальные для продакшен-сервера
 * Загрузка полного массива конфигураций происходит через файл config.php
 */

$Config = [];

$Config['Database']['host'] 	  = 'localhost';
$Config['Database']['user'] 	  = 'codeshow_psk';
$Config['Database']['pass'] 	  = 'Ws*VB6WU';
$Config['Database']['db']      = 'codeshow_psk';

return $Config;
