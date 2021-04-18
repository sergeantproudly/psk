<?php
	/**
	 * Конфигурационный массив, содержащий настройки актуальные для сервера, на котором ведется разработка (обычно, локальный сервер разработчика)
	 * Загрузка полного массива конфигураций происходит через файл config.php
	 * Для загрузки данного массива в системе должна быть установлена константа DEV. 
	 * 
	 * Наиболее простой способ добавить константу: использовать дерективу 
	 * SetEnv DEV true в httpd-vhosts.conf (Apache сервер) в теле объявления виртуального хоста
	 */

	$Config = [];
	
	$Config['Database']['host'] 	= 'localhost';
	$Config['Database']['user'] 	= 'root';
	$Config['Database']['pass'] 	= '';
	$Config['Database']['db'] = 'psk-si';

	return $Config;
