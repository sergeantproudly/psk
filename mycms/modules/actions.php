<?php

define('COMPANY_PAGE_ID', 3);

class actions extends krn_abstract{
	
	function __construct(){
		parent::__construct();
	}
	
	function GetResult(){
	}
	
	/** System */
	function SystemMultiSelect($params){
		$storageTable=$params['storageTable'];
		$storageSelfField=$params['storageSelfField'];
		$storageField=$params['storageField'];
		$selfValue=$params['selfValue'];
		dbDoQuery('DELETE FROM `'.$storageTable.'` WHERE `'.$storageSelfField.'`="'.$selfValue.'"',__FILE__,__LINE__);
		if(isset($params['values'])){
			foreach($params['values'] as $value){
				dbDoQuery('INSERT INTO `'.$storageTable.'` SET `'.$storageSelfField.'`="'.$selfValue.'", `'.$storageField.'`="'.$value.'"',__FILE__,__LINE__);
			}
		}
	}
	
	/** Static page */
	function OnAddStaticPage($newRecord){
		if(!$newRecord['Code']){
			krnLoadLib('chars');
			$code=mb_strtolower(chrTranslit($newRecord['Title']));
			$code=strtr($code,array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE static_pages SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		}else{
			dbDoQuery('UPDATE static_pages SET LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		}		
	}
	
	function OnEditStaticPage($newRecord,$oldRecord){
		if(!$newRecord['Code']){
			krnLoadLib('chars');
			$code=mb_strtolower(chrTranslit($newRecord['Title']));
			$code=strtr($code,array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE static_pages SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		}else{
			dbDoQuery('UPDATE static_pages SET LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		}	
	}

	function BeforeAddPasswordHash(&$newRecord) {
		$newRecord['Password'] = password_hash($newRecord['Password'], PASSWORD_DEFAULT);
		return true;
	}
	
	function BeforeEditPasswordHash(&$newRecord, &$oldRecord) {
	    $newRecord['Password'] = password_hash($newRecord['Password'], PASSWORD_DEFAULT);
	    return true;
	}

	function OnAddPageCompany($newRecord) {
		if ($newRecord['Code'] && $newRecord['Title']) {
			$headerLabel = $newRecord['Title'] . ' - Заголовок';
			$headerValue = $newRecord['Title'];
			$headerCode = 'Page' . ucfirst($newRecord['Code']) . 'Heading';
			dbDoQuery('INSERT INTO `data_content` SET `Field` = "'.$headerLabel.'", `Value` = "'.$headerValue.'", `Code` ="'.$headerCode.'", PageId = ' . COMPANY_PAGE_ID, __FILE__, __LINE__);

			$textLabel = $newRecord['Title'] . ' - Текст';
			$textValue = '';
			$textCode = 'Page' . ucfirst($newRecord['Code']) . 'Text';
			dbDoQuery('INSERT INTO `data_content` SET `Field` = "'.$textLabel.'", `Value` = "'.$textValue.'", `Code` ="'.$textCode.'", PageId = ' . COMPANY_PAGE_ID, __FILE__, __LINE__);
		}
	}

	function OnEditPageCompany($newRecord, $oldRecord) {
		if ($newRecord['Code'] && $newRecord['Title'] && $oldRecord['Code'] && $oldRecord['Title']) {
			$headerLabelNew = $newRecord['Title'] . ' - Заголовок';
			$headerValueNew = $newRecord['Title'];
			$headerCodeNew = 'Page' . ucfirst($newRecord['Code']) . 'Heading';
			$headerCodeOld = 'Page' . ucfirst($oldRecord['Code']) . 'Heading';
			dbDoQuery('UPDATE `data_content` SET `Field` = "'.$headerLabelNew.'", `Value` = "'.$headerValueNew.'", `Code` ="'.$headerCodeNew.'" WHERE `Code` = "'.$headerCodeOld.'" AND PageId = ' . COMPANY_PAGE_ID, __FILE__, __LINE__);

			$textLabelNew = $newRecord['Title'] . ' - Текст';
			$textCodeNew = 'Page' . ucfirst($newRecord['Code']) . 'Text';
			$textCodeOld = 'Page' . ucfirst($oldRecord['Code']) . 'Text';
			$textValueNew = str_replace($oldRecord['Title'], $newRecord['Title'], dbGetValueFromDb('SELECT `Value` FROM `data_content` WHERE `Code` = "'.$textCodeOld.'" AND PageId = ' . COMPANY_PAGE_ID));
			
			dbDoQuery('UPDATE `data_content` SET `Field` = "'.$textLabelNew.'", `Value` = "'.$textValueNew.'", `Code` ="'.$textCodeNew.'" WHERE `Code` = "'.$textCodeOld.'" AND PageId = ' . COMPANY_PAGE_ID, __FILE__, __LINE__);
		}
	}

	function OnDeletePageCompany($oldRecord) {
		if ($oldRecord['Code'] && $oldRecord['Title']) {
			$headerLabel = $oldRecord['Title'] . ' - Заголовок';
			$headerValue = $oldRecord['Title'];
			$headerCode = 'Page' . ucfirst($oldRecord['Code']) . 'Heading';
			dbDoQuery('DELETE FROM `data_content` WHERE `Field` = "'.$headerLabel.'" AND `Value` = "'.$headerValue.'" AND `Code` ="'.$headerCode.'" AND PageId = ' . COMPANY_PAGE_ID, __FILE__, __LINE__);

			$textLabel = $oldRecord['Title'] . ' - Текст';
			$textValue = '';
			$textCode = 'Page' . ucfirst($oldRecord['Code']) . 'Text';
			dbDoQuery('DELETE FROM `data_content` WHERE `Field` = "'.$textLabel.'" AND `Value` = "'.$textValue.'" AND `Code` ="'.$textCode.'" AND PageId = ' . COMPANY_PAGE_ID, __FILE__, __LINE__);
		}
	}
}

?>
