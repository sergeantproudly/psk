<?php

namespace Engine\Library;

class VkVideo {	
	protected $sourceCode;
	protected $code;
	protected $owner;
	protected $params = [];

	protected $regExp1 = '/^.+vk\.com\/video([a-zA-Z0-9\-]+)_?([a-zA-Z0-9\-]*)&?.*$/';
	protected $regExp2 = '/^.+vk\.com\/video_ext\.php\?oid=([a-zA-Z0-9\-]+)&id=([a-zA-Z0-9_]+).*$/';
	
	public function __construct($sourceCode = false, $params = []) {
		if ($sourceCode) $this->ParseDataFromSource($sourceCode);
	}

	public function ParseDataFromSource($sourceCode) {
		if (preg_match($this->regExp1, $sourceCode, $m)) {
			$code = $m[2];
			$owner = $m[1];
		} elseif (preg_match($this->regExp2, $sourceCode, $m)) {
			$code = $m[2];
			$owner = $m[1];
		}
		if ($code) $this->code = $code;
		if ($owner) $this->owner = $owner;
		$this->inited = true;
	}

	public function GetCode() {
		return $this->code ? $this->code : false;
	}

	public function GetOwner() {
		return $this->owner ? $this->owner : false;
	}

	public function GetCodeFromSource($sourceCode) {
		$this->ParseDataFromSource($sourceCode);
		return $this->GetCode();
	}

	public function GetOwnerFromSource($sourceCode) {
		$this->ParseDataFromSource($sourceCode);
		return $this->GetOwner();
	}
	
}

?>