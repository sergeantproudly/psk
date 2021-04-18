<?php
    
    class Settings{
	
		private $cms_settings=array();
		private $settings=array();
		
		public function __construct(){
			$this->LoadCmsSettings();
		}
		
		public function LoadCmsSettings(){
			$this->cms_settings=array();
			$res=dbDoQuery('SELECT `Code`, `Value` FROM `mycms_properties` WHERE `ElementId`=0',__FILE__,__LINE__);
			while($rec=dbGetRecord($res)){
				$this->cms_settings[$rec['Code']]=$rec['Value'];
			}
		}
		
		public function GetCmsSetting($code,$default=''){
			if($this->cms_settings[$code]){
				return $this->cms_settings[$code];
			}
			return $default;
		}
		
		public function SetCmsSetting($code,$value){
			if(dbGetValueFromDb('SELECT COUNT(Id) FROM `mycms_properties` WHERE `ElementId`=0 AND `Code`='.$code,__FILE__,__LINE__)){
				$res=dbDoQuery('UPDATE `mycms_properties` SET `Value`="'.$value.'" WHERE `ElementId`=0 AND `Code`='.$code,__FILE__,__LINE__);
			}else{
				$res=dbDoQuery('INSERT `mycms_properties` SET `ElementId`=0, `Code`='.$code.', `Value`="'.$value.'"',__FILE__,__LINE__);
			}
			$this->cms_settings[$code]=$value;
		}
		
		public function GetElementSettings($element,$codes=Array()){
			$result=Array();
			if(!is_array($codes))$codes=Array($codes);
			foreach($codes as $k=>$code){
				if($value=$this->settings[$element][$code]){
					$result[$code]=$value;
					unset($codes[$k]);
				}
			}
			if(count($codes)){
				$res=dbDoQuery('SELECT `Code`, `Value` FROM `mycms_properties` WHERE `ElementId`='.$element.' AND `Code`='.implode(' OR `Code`=',$codes).')',__FILE__,__LINE__);			
			}else{
				$res=dbDoQuery('SELECT `Code`, `Value` FROM `mycms_properties` WHERE `ElementId`='.$element,__FILE__,__LINE__);
			}
			while($rec=dbGetRecord($res)){
				$this->settings[$element][$rec['Code']]=$result[$rec['Code']]=$rec['Value'];
			}
			return $result;
		}
		
		public function GetElementsSettings($elements){
			$result=Array();
			if(!is_array($elements))$elements=Array($elements);
			foreach($elements as $k=>$element){
				if($this->settings[$element]&&count($this->settings[$element])){
					foreach($this->settings[$element] as $code=>$value){
						$result[$code]=$value;
					}
					unset($elements[$k]);
				}			
			}
			if(count($elements)){
				$res=dbDoQuery('SELECT `ElementId`, `Code`, `Value` FROM `mycms_properties` WHERE `ElementId`='.implode(' OR `ElementId`=',$elements),__FILE__,__LINE__);
				while($rec=dbGetRecord($res)){
					$this->settings[$rec['ElementId']][$rec['Code']]=$result[$rec['ElementId']][$rec['Code']]=$rec['Value'];
				}
			}
			return $result;
		}	
		
		public function SetElementSetting($element,$code,$value){
			if(dbGetValueFromDb('SELECT COUNT(Id) FROM `mycms_properties` WHERE `ElementId`='.$element.' AND Code='.$code,__FILE__,__LINE__)){
				dbDoQuery('UPDATE `mycms_properties` SET `Value`="'.$value.'" WHERE `ElementId`='.$element.' AND `Code`='.$code,__FILE__,__LINE__);
			}else{
				dbDoQuery('INSERT INTO `mycms_properties` SET `ElementId`='.$element.', `Code`='.$code.', `Value`="'.$value.'"',__FILE__,__LINE__);
			}
			$this->settings[$element][$code]=$value;
		}
	}
	
	$GLOBALS['Settings']=new Settings();
	
	
	
	class SiteSettings{
	
	private $all_settings_arr=array();
	
	public function __construct(){
		$this->LoadAllSettings();
	}
	
	public function LoadAllSettings(){
		$this->all_settings_arr=array();
		$res=dbDoQuery('SELECT `Code`, `Value` AS Value FROM `data_settings`',__FILE__,__LINE__);
		while($rec=dbGetRecord($res)){
			$this->all_settings_arr[$rec['Code']]=$rec['Value'];
		}
	}
	
	public function GetSetting($code,$default=''){
		if(!$this->all_settings_arr)$this->LoadAllSettings();
		foreach($this->all_settings_arr as $c=>$v){
			if($c==$code)return $v;
		}
		return $default;
	}
	
	public function SetSetting($code,$value){
		if(!$this->all_settings_arr)$this->LoadAllSettings();
		dbDoQuery('UPDATE `setting` SET `Value`="'.$value.'" WHERE `Code`="'.$code.'"',__FILE__,__LINE__);
		foreach($this->all_settings_arr as $c=>$v){
			if($c==$code){
				$this->all_settings_arr[$c]=$value;
				return true;
			}
		}
		return false;
	}
}

$GLOBALS['SiteSettings']=new SiteSettings();

function stGetSetting($code,$default=''){
	global $SiteSettings;
	return $SiteSettings->GetSetting($code,$default);
}
function stSetSetting($code,$value){
	global $SiteSettings;
	return $SiteSettings->SetSetting($code,$value);
}

?>