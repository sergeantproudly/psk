<?php

namespace Engine\Library;

class Settings {
	private $settings = [];

	public function __construct($database) {
		$this->db = $database;
		$this->settings = $this->LoadSettings();
	}	

	public function LoadSettings() {
		$settings = $this->db->getInd('Code', "SELECT * FROM `data_settings`");
		
		array_walk($settings, function (&$item) {
			$item = $item['Value'];
		});

		return $settings;
	}

	public function get($code, $default = '') {
		return $this->settings[$code] ?? $default;
	}

	public function Set($code, $value) {
		$code = trim($code);
		$value = trim($value);
		
		if (!$code || !$value) {
			return false;
		}
		
		dbDoQuery('UPDATE `data_settings` SET `Value`="'.$value.'" WHERE `Code`="'.$code.'"',__FILE__,__LINE__);

		$this->settings[$code] = $value;
		return true;
	}

}



?>