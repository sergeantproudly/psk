<?php

	if (getenv('DEV')) {
		$Config['Db']['Host'] 	= 'localhost';
		$Config['Db']['Login'] 	= 'root';
		$Config['Db']['Pswd'] 	= '';
		$Config['Db']['DbName'] = 'psk-si';   	
	} else {
		$Config['Db']['Host'] 	= 'localhost';
		$Config['Db']['Login'] 	= 'codeshow_psk_new';
		$Config['Db']['Pswd'] 	= 'Sergeantr1';
		$Config['Db']['DbName'] = 'codeshow_psk_new';
	}
   	
	$Config['Site']['Title']		= 'ПСК СтройИнвест';
	$Config['Site']['Email']		= 'info@psk-si.ru';
	$Config['Site']['Flush'] 		= 0;
	$Config['Site']['Keywords']		= '';
	$Config['Site']['Description']	= '';
	$Config['Site']['Url']			= 'https://psk-si.ru';
	
	error_reporting (E_ALL & ~E_NOTICE);

// constants
	define ('TEMPLATES_DIR', 'templates/');
	define ('TOOLS_DIR', 'tools/');
	define ('IMAGES_DIR', 'images/');
	define ('MODULES_DIR', 'modules/');
	define ('LIBRARY_DIR', 'library/');
	define ('UPLOADS_DIR', 'uploads/');
	define ('TEMP_DIR', 'uploads/temp/');
	
	define ('ABS_PATH', $_SERVER['DOCUMENT_ROOT'].'/mycms/');
	define ('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'].'/');
	define ('ROOT_DIR', '../');  	

?>
