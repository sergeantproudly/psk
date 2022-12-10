<?php

krnLoadLib('define');

class main extends krn_abstract{
	
	function __construct(){
		parent::__construct();
	}
	
	function GetResult(){
		$result=$this->GetContent();
		return $result;
	}
	
	function GetContent(){
		$records=krnLoadModuleByName('records2');
		$documents='<div class="inner-wrapper">'.$records->BrowseDocuments().'</div>';
		
		if($_SESSION['User']['Status']!=PERMISSION_MASK_MODERATOR){
			$records=krnLoadModuleByName('records');
			$documents.='<div class="inner-wrapper">'.$records->BrowseDocuments().'</div>';
		}
		
		//return $this->GetStatistics();		
		return '';
	}
	
	function GetStatistics(){
		//$stats['CompaniesTotal']=dbGetValueFromDb('SELECT COUNT(Id) FROM companies',__FILE__,__LINE__);
		//$stats['UsersTotal']=dbGetValueFromDb('SELECT COUNT(Id) FROM users WHERE Confirmed=1',__FILE__,__LINE__);
		//$stats['SumToday']=$stats['InvoicesCountToday']*20000;
		//$stats['SumLastMonth']=$stats['InvoicesCountLastMonth']*20000;

		$stats['Orders']=dbGetValueFromDb('SELECT COUNT(Id) FROM `data_users-order` WHERE OrderDate >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)');
		$stats['Catalog']=dbGetValueFromDb('SELECT COUNT(Id) FROM `data_items`');
		$stats['Register']=dbGetValueFromDb('SELECT COUNT(Id) FROM `page_register`');
		$stats['Users']=dbGetValueFromDb('SELECT COUNT(Id) FROM `data_users` WHERE Confirmed = 1');

		
		$result=LoadTemplate('main');
		$result=strtr($result,array(
			'<%DOCUMENTS%>'	=> $documents,
			'<%LINK1%>'		=> 'http://psk-si.ru/mycms/index.php?module=records&document_id=48',
			'<%VALUE1%>'	=> $stats['Orders'],
			'<%UNIT1%>'		=> '',
			'<%TITLE1%>'	=> 'Заказов за месяц',
			'<%LINK2%>'		=> 'http://psk-si.ru/mycms/index.php?module=records&document_id=40',
			'<%VALUE2%>'	=> $stats['Catalog'],
			'<%UNIT2%>'		=> '',
			'<%TITLE2%>'	=> 'Товаров в каталоге',
			'<%LINK3%>'		=> 'http://psk-si.ru/mycms/index.php?module=records&document_id=43',
			'<%VALUE3%>'	=> $stats['Register'],
			'<%UNIT3%>'		=> '',
			'<%TITLE3%>'	=> 'Накладных в реестре',
			'<%LINK4%>'		=> 'http://psk-si.ru/mycms/index.php?module=records&document_id=47',
			'<%VALUE4%>'	=> $stats['Users'],
			'<%UNIT4%>'		=> '',
			'<%TITLE4%>'	=> 'Пользователей',
		));
		return $result;
	}
	
}

?>