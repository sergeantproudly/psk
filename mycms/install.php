<?php

	// файл: install.php
   	// назначение: инсталляция системы
   	
	header ('Content-type: text/html; charset=utf-8');
	mb_internal_encoding('utf8');
	
	require_once 'settings.php';
	require_once LIBRARY_DIR.'common.lib.php';
	// [TODO]: Заменить вызовы функций dbmysql.lib.php на вызовы dbmysqli.lib.php
	require_once LIBRARY_DIR.'dbmysql.lib.php';
	require_once LIBRARY_DIR.'kernel.lib.php';
	require_once LIBRARY_DIR.'files.lib.php';
	
	$sql = flLoadFileLines('install.sql');
	foreach($sql as $query){
		$query = strtr($query,array("\r\n"=>'',"\r"=>'',"\n"=>''));
		if($query) 
			dbDoQuery($query,__FILE__,__LINE__);
	}
	
	global $Config;
	dbDoQuery('INSERT INTO `settings` (`Id`, `Title`, `Code`, `Type`, `Value`) VALUES
		(1, "Глобальные - Название сайта", "SiteTitle", "string", "'.$Config['Site']['Title'].'"),
		(2, "Глобальные - E-mail сайта", "SiteEmail", "string", "'.$Config['Site']['Email'].'"),
		(3, "Глобальные - E-mail администратора", "AdminEmail", "string", "'.$Config['Site']['Email'].'"),
		(4, "Глобальные - Адрес сайта", "SiteUrl", "string", "'.$Config['Site']['Url'].'"),
		(5, "Глобальные - Копирайт", "TextCopyright", "string", "© 2011 «Workgroup site title»"),
		(6, "Глобальные - Направление деятельности", "SiteDirection", "string", "Workgroup Site Direction")');
	
	$result=LoadTemplate('install_success');
	$result=strtr($result,array(
		'<%PAGE_TITLE%>'	=> 'Инсталляция завершена'
	));
	echo $result;

?>