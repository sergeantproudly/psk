<?php
    
    abstract class krn_abstract{		
		public function __construct(){
		}
		
		abstract function GetResult();
	}  

    function krnGetPageCode(){
		return $_GET['p_code'] ? $_GET['p_code'] : 'index';
	}	

	function krnGetPageModule(){
		global $Params;
		return $Params['Site']['Page']['Code']?$Params['Site']['Page']['Code']:false;
	}	

	function krnLoadModule(){
		global $Params;
		if(!$Params['Site']['Page']['Module'])return false;
		if(!file_exists(MODULES_DIR.$Params['Site']['Page']['Module'].'.php')){
			echo 'Module not found';
		}
		require_once(MODULES_DIR.$Params['Site']['Page']['Module'].'.php');
		$module=new $Params['Site']['Page']['Module'];
		return $module;
	}

	function krnLoadModuleByName($module_name){
		if(!$module_name || !file_exists(MODULES_DIR.$module_name.'.php'))return false;
		require_once(MODULES_DIR.$module_name.'.php');
		$module=new $module_name;
		return $module;
	}

	function krnLoadPage($templateName=''){
		return LoadTemplate($templateName?$templateName:'base');
	}

	function krnLoadLib($libname){
		require_once(file_exists(LIBRARY_DIR.$libname.'.php')?LIBRARY_DIR.$libname.'.php':LIBRARY_DIR.$libname.'.lib.php');
	}

	abstract class krn_class_base{

		function __construct($params=array()){
			$this->Set($params);
		}		

		function Set($params=array()){
			if($params){
				foreach($params as $k=>$v){
					$k{0}=strtolower($k{0});
					if(isset($this->$k)){
						$this->$k=$v;
					}
				}
			}
		}		

		function Reset(){
			$vars=get_class_vars(get_class($this));
			if($vars){
				foreach($vars as $k=>$v){
					if(is_string($this->$k))$this->$k='';
					elseif(is_array($this->$k))$this->$k=array();
					elseif(is_bool($this->$k))$this->$k=false;
					else $this->$k=0;
				}
			}
		}	

		function __call($name,$arguments){
			echo 'Call to undefined method '.$name.' in (file: '.__FILE__.', row: '.__LINE__.')';

		}

		

		function __get($name){

			echo 'Call to undefined var '.$name.' in (file: '.__FILE__.', row: '.__LINE__.')';

		}

		

		function __set($name,$value){

			echo 'Set value '.$value.' to undefined var '.$name.' in (file: '.__FILE__.', row: '.__LINE__.')';

		}

		

	}



?>