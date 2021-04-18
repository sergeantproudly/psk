<?php
    
    // create file
    function flCreateFile($filename){
		touch($filename);
	}
    
    // save file
    function flSaveFile($filename,$content,$binary=false){
		$fp=fopen($filename,$binary?'wb':'w');
		fwrite($fp,$content);
		fclose($fp);
	}
	
	// load file
	function flLoadFile($filename,$binary=false){
		if(file_exists($filename)){
			return file_get_contents($filename,$binary?FILE_BINARY:FILE_TEXT);
		}else{
			return '';
		}
	}
	
	// row load file
	function flLoadFileLines($filename,$binary=false){
		if(file_exists($filename)){
			return file($filename,$binary?FILE_BINARY:FILE_TEXT);
		}else{
			return '';
		}
	}
	
	// copy file
	function flCopyFile($sourcepath,$destpath,$force=false){
		if(!$force){
			$info=flGetInfo($destpath);
			$destpath=flUniqueName($info['basename'],$info['directory']);
		}
		copy($sourcepath,$destpath);
	}
	
	// move file
	function flMoveFile($sourcepath,$destpath,$force=false){
		if(!$force){
			$info=flGetInfo($destpath);
			$destpath=flUniqueName($info['basename'],$info['directory']);
		}
		rename($sourcepath,$destpath);
	}
	
	// delete file
	function flDeleteFile($filename){
		@unlink($filename);
	}
	
	// is file uploaded
	function flIsUploaded($tmpname){
		return is_uploaded_file($tmpname);
	}
	
	// move uploaded
	function flMoveUploaded($tmpname,$filename){
		move_uploaded_file($tmpname,$filename);
		@chmod($filename,CONFIG_MASK_FILE^0666);
	}
	
	// file info
	function flGetInfo($filepath){
		$basename=basename($filepath);
		$directory=mb_substr($filepath,0,mb_strpos($filepath,$basename));
		$extension=mb_strpos($basename,'.')?mb_substr($basename,mb_strpos($basename,'.')+1):'';
		$caption=$extension?mb_substr($basename,0,mb_strpos($basename,'.')):$basename;
		$size=@filesize($filepath);
		$abspath=$_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:'http://'.$_SERVER['HTTP_HOST'].'/mycms/';
		$root=mb_substr($abspath,0,mb_strpos($abspath,basename($abspath)));
		$absolute=$root.$directory.$basename;
		return Array(
			'basename'	=> $basename,
			'directory'	=> $directory,
			'absolute'	=> $absolute,
			'caption'	=> $caption,
			'extension'	=> $extension,
			'size'		=> $size
		);
	}
	
	// unique name
	function flUniqueName($filename,$directory){
		$counter=1;
		if(mb_substr($directory,mb_strlen($directory)-1)!='/')$directory.='/';
		$info=flGetInfo($directory.$filename);
		$filepath=$directory.$filename;
		while(file_exists($filepath)){
			$counter++;
			$filepath=$directory.$info['caption'].'('.$counter.').'.$info['extension'];
		}
		return $filepath;
	}
	
	// upload file
	function flUpload($filename,$tmpname,$directory,$keepOrigin=false){
		krnLoadLib('chars');
		$filepath=flUniqueName($keepOrigin?$filename:chrTranslit($filename),$directory);
		flMoveUploaded($tmpname,$filepath);
		return $filepath;
	}
	
	// create dir
	function flCreateDirectory($directory){
		if(!is_dir($parDir=dirname($directory))){
			flCreateDirectory($parDir);
		}
		return @mkdir($directory,CONFIG_MASK_DIR^0777);
	}
	
	// delete dir
	function flDeleteDirectory($directory){
		if($dp=@opendir($directory)){ 
			while(($filename=readdir($dp))!==false){
				if($filename!='.' && $filename!='..'){
					if(is_dir("$dirname/$filename"))
						cmDeleteDir("$dirname/$filename");
					else
						cmDeleteFile("$dirname/$filename");
				}
			}
			closedir($dp);
			rmdir($directory);
		}
	}

?>