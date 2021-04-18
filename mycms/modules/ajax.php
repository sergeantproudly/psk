<?php

class ajax extends krn_abstract{
	
	function __construct($params=array()) {
		parent::__construct();
	}
	
	function GetResult(){
		if($_POST['act']&&method_exists($this,$_POST['act'])){
			echo $this->$_POST['act'];
		}
		exit;
	}
	
	function GetPopup(){
		krnLoadLib('popup');
		$popupCode=$_POST['code'];
		$result=LoadTemplate('popup_base');
		$inner=LoadTemplate('popup_'.$popupCode);
		$func='Popup';
		$r=explode('_',$popupCode);
		foreach($r as $k) {
			$k{0}=strtoupper($k{0});
			$func.=$k;
		}
		if(function_exists($func))$inner=$func($inner);
		$result=strtr($result,array(
			'<%CONTENT%>'	=> $inner
		));
		return $result;
	}
	
	function GetUploader(){
		krnLoadLib('uploader');
		$uploaderCode=$_POST['code'];
		$func='Uploader';
		$r=explode('_',$uploaderCode);
		foreach($r as $k) {
			$k{0}=strtoupper($k{0});
			$func.=$k;
		}
		if(function_exists($func))return $func();
		return false;
	}
	
	function RemoveFile(){
		krnLoadLib('files');
		$filepath=$_POST['filepath'];
		flDeleteFile($filepath);
		return '1';
	}
	
	function SetRowsOnPage(){
		$rowsOnPage=(int)$_POST['rop'];
		if($rowsOnPage>0){
			krnLoadLib('settings');
			global $Settings;
			$Settings->SetCmsSetting(3,$rowsOnPage);
			$_SESSION['Cms']['RowsOnPage']=$rowsOnPage;
		}
		return 'reload(true);';
	}
	
	function UploadImage(){
		krnLoadLib('files');
		$uploadpath=UPLOADS_DIR;
		if(mb_substr($uploadpath,mb_strlen($uploadpath)-1)!='/')$uploadpath.='/';
		$filepath=flUpload($_FILES['upload']['name'],$_FILES['upload']['tmp_name'],UPLOADS_DIR);
		$fileinfo=flGetInfo($filepath);
		$callback=$_REQUEST['CKEditorFuncNum'];
		$result='<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction("'.$callback.'", "'.$fileinfo['absolute'].'","Файл загружен" );</script>';
		return $result;
	}

}
?>