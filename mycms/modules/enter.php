<?php

class enter extends krn_abstract{
	
	function __construct(){
		parent::__construct();
	}
	
	function GetResult(){
		$result=LoadTemplate('enter');
		return $result;
	}
	
	function Authorise(){
		krnLoadLib('users');
		$login=trim($_POST['login']);
		$password=trim($_POST['password']);
		
		if($user=dbGetRecordFromDb('SELECT Id, Name, Status, GroupId FROM mycms_users WHERE Login="'.dbEscape($login).'" AND Password="'.md5($password).'"')){
			$_SESSION['User']=$user;
			$_SESSION['User']['ActionStatus']=GetActionsPermission();
			return '1';
		}else{
			return '0:Неправильные логин/пароль';
		}
	}
	
	function Logout(){
		unset($_SESSION['User']);
		unset($_SESSION['Cms']);
		$_SESSION['User']['Logout']=true;
		return '1';
	}
}

?>