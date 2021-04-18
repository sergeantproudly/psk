<?php
	header ('Content-type: text/html; charset=utf-8');	
	mb_internal_encoding('utf8');
	
	require_once 'settings.php';
	$Params['Db']['Link'] = new mysqli(
		$Config['Db']['Host'], 
		$Config['Db']['Login'], 
		$Config['Db']['Pswd'], 
		$Config['Db']['DbName']
	);

	mysqli_query($Params['Db']['Link'], 'SET NAMES utf8');
	
	require_once LIBRARY_DIR.'dbmysql.lib.php';
	require_once LIBRARY_DIR.'common.lib.php';
	require_once LIBRARY_DIR.'kernel.lib.php';
	require_once LIBRARY_DIR.'settings.lib.php';
	
	if(function_exists('date_default_timezone_set')) 
		date_default_timezone_set('Europe/Moscow');
	
	session_start();

	if($Config['Site']['Flush']) {
		ob_start();
	}
	
	$authed = $_SESSION['User']['Id'] && !$_SESSION['User']['Logout'];
	if($authed) {
		krnLoadLib('users');
		if($moduleName = $_GET['module']) {
			if(!CheckPermission($moduleName)) {
				unset($moduleName);
			}
		}
		if(!$moduleName) {	
			$folder = GetStartFolder();
			$moduleName = $folder['Module'];
		}	
	} else {
		$moduleName = 'enter';
	}
		
	$krnModule = krnLoadModuleByName($moduleName);
	$actionName = $_GET['act'] ? $_GET['act'] : $_POST['act'];
	if($actionName) {
		if(method_exists($krnModule, $actionName)) {
			echo $krnModule->$actionName();
		} else {
			echo 'Method '.$actionName.' not found';
		}
	} else {
		if($authed) {
			$menu = krnLoadModuleByName('menu');	
			$notifications = krnLoadModuleByName('notifications');

			$result = LoadTemplate('base');
			$result = strtr($result,array(
				'<%SITE_TITLE%>'	=> mb_strtoupper($Config['Site']['Title']),
				'<%USER_ID%>'		=> $_SESSION['User']['Id'],
				'<%USER_NAME%>'		=> $_SESSION['User']['Name'],
				'<%CONTENT%>'		=> $krnModule->GetResult(),
				'<%MENU%>'			=> $menu->GetMenu(),
				'<%NOTIFICATIONS%>'	=> $notifications->GetNotificationsHtml()
			));
		} else {
			$result=$krnModule->GetResult();
		}
	}

	$result = strtr($result,array(
		'<%PAGE_TITLE%>'	=> $Config['Site']['Title'],		
		'<%YEAR%>'			=> date('Y')
	));
	echo $result;	

	if($Config['Site']['Flush']) {
		ob_end_flush();
	}

?>