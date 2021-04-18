<?php

class menu extends krn_abstract{
	
	function __construct(){
		parent::__construct();
		$groupId=$_SESSION['User']['GroupId'];
	}
	
	function GetResult(){}
	
	function GetList(){
		krnLoadLib('users');
		$userMask=$_SESSION['User']['Status'];
		$folders=GetFolders();
		if(!$userMask){
			$availableFolders=$folders;
		}else{
			$availableFolders=Array();
			foreach($folders as $item){
				if(CheckPermission($item,$userMask))$availableFolders[]=$item;
			}
		}
		return $availableFolders;	
	}
	
	function GetMenu(){
		$list=$this->GetList();
		$element=LoadTemplate('menu_list_el');
		$content='';
		
		$moduleName=$_GET['module']?$_GET['module']:GetStartFolderModule();
		foreach($list as $listElement){
			if(!$listElement['Link'])continue;
			$content.=strtr($element,array(
				'<%CURR%>'		=> $listElement['Module']==$moduleName?' class="curr"':'',
				'<%ICO%>'		=> '<span class="ico">'.$listElement['Icon'].'</span>',
				'<%TITLE%>'		=> $listElement['Title'],
				'<%LINK%>'		=> $listElement['Link'],
				'<%SUBMENU%>'	=> $listElement['Module']==$moduleName?'':''
			));
		}
		
		$result=LoadTemplate('menu_list');
		return SetContent($result,$content);
	}
	
}

?>