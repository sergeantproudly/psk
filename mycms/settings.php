<?php

	if (getenv('DEV')) {
		$Config['Db']['Host'] 	= 'localhost';
		$Config['Db']['Login'] 	= 'root';
		$Config['Db']['Pswd'] 	= '';
		$Config['Db']['DbName'] = 'psk-si';   	
	} else {
		$Config['Db']['Host'] 	= 'localhost';
		$Config['Db']['Login'] 	= 'codeshow_psk';
		$Config['Db']['Pswd'] 	= 'MayT|-|EfoRce_be_with_u';
		$Config['Db']['DbName'] = 'codeshow_psk';
	}
   	
	$Config['Site']['Title']		= 'ПСК СтройИнвест';
	$Config['Site']['Email']		= 'info@psk-si.ru';
	$Config['Site']['Flush'] 		= 0;
	$Config['Site']['Keywords']		= '';
	$Config['Site']['Description']	= '';
	$Config['Site']['Url']			= 'http://psk-si.ru';
	
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
	define ('ROOT_DIR', $_SERVER['DOCUMENT_ROOT']);   	

?>
