<?php

    function urlRedirect($url){
        header('Location: '.$url);
        exit;
    }
    
    function urlReload(){
		header('Location: '.$_SERVER['HTTP_REFERER']);
	}

    function urlGetPageQuery(){
        return getenv ('QUERY_STRING');
    }

    function urlGetPageLocation(){
        return getenv ('SCRIPT_NAME');
    }
    
    function urlAddVars($url,$vars){
		$exists=!(strpos($url,'?')===false);
		foreach($vars as $k=>$v){
			if($exists){
				$url=$url."&$k=$v";
			}else{
				$url=$url."?$k=$v";
				$exists=true;
			}
		}
		return $url;
	}
    
    function urlDelVars($url,$vars){
		foreach($vars as $k=>$v){
			$url=preg_replace("/\?$k=[^&]*(&|$)/i","?",$url);
			$url=preg_replace("/&$k=[^&]*(&|$)/i","\\1",$url);
		}
		return $url;
	}
	
	function urlSetVars($url,$vars){
		$url=urlDelVars($url,$vars);
		$url=urlAddVars($url,$vars);
	}

?>