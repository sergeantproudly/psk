<?php

	function GetElementCodes($code=false){
		$elementsCodes=Array(
			1 => Array('Title'=>'String','Input'=>'inp_text'),
			2 => Array('Title'=>'Text','Input'=>'inp_textarea'),
			3 => Array('Title'=>'Date','Input'=>'inp_date'),
			4 => Array('Title'=>'Image','Input'=>'inp_file'),
			5 => Array('Title'=>'File','Input'=>'inp_file'),
			6 => Array('Title'=>'Checkbox','Input'=>'inp_checkbox'),
			7 => Array('Title'=>'Radiogroup','Input'=>'inp_radio'),
			8 => Array('Title'=>'Select','Input'=>'inp_select'),
			9 => Array('Title'=>'Multiselect','Input'=>'inp_multiselect')
		);
		return $code?$elementsCodes[$code]:$elementsCodes;
	}
	
	function GetElementProperties($elementType){
		$elementProperties=Array(
			1 => Array('Title'=>'Required','Input'=>'inp_checkbox'),
			2 => Array('Title'=>'Unique','Input'=>'inp_checkbox'),
			3 => Array('Title'=>'Default','Input'=>'inp_text'),
			4 => Array('Title'=>'Comment','Input'=>'inp_textarea'),
			5 => Array('Title'=>'Syntax pattern','Input'=>'inp_text'),
			10 => Array('Title'=>'Max symbols','Input'=>'inp_text'),
			20 => Array('Title'=>'Editor type','Input'=>'inp_text'),
			21 => Array('Title'=>'Max symbols','Input'=>'inp_text'),
			22 => Array('Title'=>'Width','Input'=>'inp_text'),
			23 => Array('Title'=>'Height','Input'=>'inp_text'),
			30 => Array('Title'=>'Date type','Input'=>'inp_text'),
			40 => Array('Title'=>'Save path','Input'=>'inp_text'),
			41 => Array('Title'=>'No translit','Input'=>'inp_checkbox'),
			42 => Array('Title'=>'Image resize','Input'=>'inp_checkbox'),
			43 => Array('Title'=>'Resize source','Input'=>'inp_text'),
			44 => Array('Title'=>'Resize type','Input'=>'inp_text'),
			45 => Array('Title'=>'Resize width','Input'=>'inp_text'),
			46 => Array('Title'=>'Resize height','Input'=>'inp_text'),
			50 => Array('Title'=>'Save path','Input'=>'inp_text'),
			51 => Array('Title'=>'No translit','Input'=>'inp_checkbox'),
			70 => Array('Title'=>'Options','Input'=>'inp_textarea'),
			80 => Array('Title'=>'Source table','Input'=>'inp_text'),
			81 => Array('Title'=>'Source field','Input'=>'inp_text'),
			82 => Array('Title'=>'Link field','Input'=>'inp_text'),
			83 => Array('Title'=>'No linkself','Input'=>'inp_checkbox'),
			84 => Array('Title'=>'Order field','Input'=>'inp_text'),
			85 => Array('Title'=>'Order direction','Input'=>'inp_text'),
			90 => Array('Title'=>'Source table','Input'=>'inp_text'),
			91 => Array('Title'=>'Source field','Input'=>'inp_text'),
			92 => Array('Title'=>'Link field','Input'=>'inp_text'),
			94 => Array('Title'=>'Order field','Input'=>'inp_text'),
			95 => Array('Title'=>'Order direction','Input'=>'inp_text'),
			96 => Array('Title'=>'Storage table','Input'=>'inp_text'),
			97 => Array('Title'=>'Storage selffield','Input'=>'inp_text'),
			98 => Array('Title'=>'Storage field','Input'=>'inp_text')
		);
		if(mb_substr($elementType,0,1)=='p'){
			$propertyCode=mb_substr($elementType,1);
			return $elementProperties[$propertyCode];
			
		}elseif($elementType){
			$slice=Array();
			foreach($elementProperties as $code=>$info){
				if($code<10 || ($code>=$elementType*10 && $code<($elementType+1)*10)){
					$slice[$code]=$info;
				}			
			}
			return $slice;
		}
		return $elementProperties;
	}
	
	function GetUserStatuses(){
		$userStatuses=Array(
			PERMISSION_MASK_MODERATOR		=> 'Доступ модератора',
			PERMISSION_MASK_ADMINISTRATOR	=> 'Доступ администратора',
			PERMISSION_MASK_DEVELOPER		=> 'Доступ разработчика'
		);
		return $userStatuses;
	}
	
	define('PERMISSION_MASK_MODERATOR',1<<0); // 001 (1)
	define('PERMISSION_MASK_ADMINISTRATOR',1<<1); // 010 (2)
	define('PERMISSION_MASK_DEVELOPER',1<<2); // 100 (4)
	
	function GetFolders(){
		$folders=Array(
			Array('Title'=>'Документы','Icon'=>'','Module'=>'documents','Link'=>'index.php?module=documents','PermissionMask'=>PERMISSION_MASK_DEVELOPER),
			Array('Title'=>'Элементы','Module'=>'elements','Link'=>'','PermissionMask'=>PERMISSION_MASK_DEVELOPER),
			Array('Title'=>'Таблицы','Icon'=>'','Module'=>'tables','Link'=>'index.php?module=tables','PermissionMask'=>PERMISSION_MASK_DEVELOPER),
			Array('Title'=>'Поля','Module'=>'fields','Link'=>'','PermissionMask'=>PERMISSION_MASK_DEVELOPER),
			Array('Title'=>'Управление сайтом','Icon'=>'','Module'=>'records','Link'=>'index.php?module=records','PermissionMask'=>PERMISSION_MASK_DEVELOPER|PERMISSION_MASK_ADMINISTRATOR),
			Array('Title'=>'Пользователи','Icon'=>'','Module'=>'users','Link'=>'index.php?module=users','PermissionMask'=>PERMISSION_MASK_DEVELOPER|PERMISSION_MASK_ADMINISTRATOR|PERMISSION_MASK_MODERATOR),
			Array('Title'=>'Ajax','Module'=>'ajax','Link'=>'','PermissionMask'=>PERMISSION_MASK_DEVELOPER|PERMISSION_MASK_ADMINISTRATOR|PERMISSION_MASK_MODERATOR),
			Array('Title'=>'Enter','Module'=>'enter','Link'=>'','PermissionMask'=>PERMISSION_MASK_DEVELOPER|PERMISSION_MASK_ADMINISTRATOR|PERMISSION_MASK_MODERATOR)
		);
		return $folders;
	}
	
	define('PAMASK_READ',1<<0);
	define('PAMASK_CREATE',1<<1);
	define('PAMASK_EDIT',1<<2);
	define('PAMASK_DELETE',1<<3);
	define('PAMASK_DEVELOPE',1<<4);
	define('PAMASK_ALL',PAMASK_READ|PAMASK_CREATE|PAMASK_EDIT|PAMASK_DELETE);
	define('PAMASK_ALLDEV',PAMASK_ALL|PAMASK_DEVELOPE);

?>