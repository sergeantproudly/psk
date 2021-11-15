<?php

namespace Engine\Library;

class Youtube {	
	protected $sourceCode;
	protected $code;
	protected $params = [];

	protected $regExp1 = '/^.+youtube\.com\/watch\?v=([a-zA-Z0-9_\-]+)&?.*$/';
	protected $regExp2 = '/^.+youtu\.be\/([a-zA-Z0-9_\-]+)\??.*$/';
	protected $regExp3 = '/^.+youtube\.com\/embed\/([a-zA-Z0-9_\-]+)\??.*$/';
	
	public function __construct($sourceCode = false, $params = []) {
		if ($sourceCode) $this->ParseCodeFromSource($sourceCode);
	}

	public function ParseCodeFromSource($sourceCode) {
		if (preg_match($this->regExp1, $sourceCode, $m)) {
			$code = $m[1];
		} elseif (preg_match($this->regExp2, $sourceCode, $m)) {
			$code = $m[1];
		} elseif (preg_match($this->regExp3, $sourceCode, $m)) {
			$code = $m[1];
		}
		if ($code) $this->code = $code;
	}

	public function GetCode() {
		return $this->code ? $this->code : false;
	}

	public function GetCodeFromSource($sourceCode) {
		$this->ParseCodeFromSource($sourceCode);
		return $this->GetCode();
	}
	
}

?>