<?php
    
    // image resize
    function imgResize($filepath,$w,$h,$copy=false){
		list($rw,$rh,$type)=getimagesize($filepath);
		if(!$w&&!$h){
			$w=$rw;
			$h=$rh;
		}elseif(!$w){
			$w=round($h*$rw/$rh);
		}elseif(!$h){
			$h=round($w*$rh/$rw);
		}
		if($copy){
			$info=flGetInfo($filepath);
			if(gettype($copy)=='string'){
				$destpath=$copy;
				if(mb_substr($copy,mb_strlen($copy)-1)!='/')$destpath.='/';
				$destpath.=$info['caption'].($w?$w:'').($h?'_'.$h:'').'.'.$info['extension'];		
			}else{
				$destpath=$info['directory'].$info['caption'].($w?$w:'').($h?'_'.$h:'').'.'.$info['extension'];
			}		
		}else{
			$destpath=$filepath;
		}
		if($w!=$rw||$h!=$rh||$copy){
			if(class_exists('Imagick')){
				$img=new Imagick();
				$img->readImage($filepath);
				$img->thumbnailImage($w,$h);
				$img->setImagePage($w,$h,0,0);
				$img->writeImage($destpath);
				$img->destroy();
			}else{
				switch($type){
					case IMAGETYPE_GIF: $extension='gif'; break;
					case IMAGETYPE_JPEG: $extension='jpeg'; break;
					case IMAGETYPE_PNG: $extension='png'; break;
					case IMAGETYPE_WBMP: $extension='wbmp'; break;
					case IMAGETYPE_XBM: $extension='xbm'; break;
				}
				$funcCreate='imagecreatefrom'.$extension;
				$funcSave='image'.$extension;
				if($extension&&function_exists($funcCreate)&&function_exists($funcSave)){
					$img=$funcCreate($filepath);
					$img2=imagecreatetruecolor($w,$h);
					imagecopyresampled($img2,$img,0,0,0,0,$w,$h,$rw,$rh);
					imagealphablending($img2,false);
					imagesavealpha($img2,true);
					$funcSave($img2,$destpath);
				}else{
					$w=$rw;
					$h=$rh;
				}
			}
		}
		return $destpath;
	}
	
	// image fit
	function imgFit($filepath,$w,$h,$copy=false){
		list($rw,$rh)=getimagesize($filepath);
		if($rw<=$w&&$rh<=$h){
			$w=$rw;
			$h=$rh;
		}else{
			if($h/$w<$rh/$rw)
				$w=0;
			else
				$h=0;		
		}
		return imgResize($filepath,$w,$h,$copy);
	}
	
	// iamge thumbnail
	function imgThumbnail($filepath,$w,$h,$copy=false){
		list($rw,$rh,$type)=getimagesize($filepath);
		if($copy){
			$info=flGetInfo($filepath);
			if(gettype($copy)=='string'){
				$destpath=$copy;
				if(mb_substr($copy,mb_strlen($copy)-1)!='/')$destpath.='/';
				$destpath.=$info['caption'].($w?$w:'').($h?'_'.$h:'').'.'.$info['extension'];		
			}else{			
				$destpath=$info['directory'].$info['caption'].($w?$w:'').($h?'_'.$h:'').'.'.$info['extension'];
			}		
		}else{
			$destpath=$filepath;
		}
		if($w!=$rw||$h!=$rh||$copy){
			if(class_exists('Imagick')){
				$k=$rh/$rw;
				$img=new Imagick();
				$img->readImage($filepath);
				if($h/$w<$k){
					$img->thumbnailImage($w,null);
					$img->cropImage($w,$h,0,(int)(($w*$k-$h)/2));
					while($img->hasNextImage()){
						$img->nextImage();
						$img->cropImage($w,$h,0,(int)(($w*$k-$h)/2));
					}
				}else{
					$img->thumbnailImage(null,$h);
					$img->cropImage($w,$h,(int)(($h/$k-$w)/2),0);
					while($img->hasNextImage()){
						$img->nextImage();
						$img->cropImage($w,$h,(int)(($h/$k-$w)/2),0);
					}
				}
				$img->setImagePage($w,$h,0,0);
				$w=$img->getImageWidth();
				$h=$img->getImageHeight();
				$img->writeImage($destpath);
				$img->destroy();
			}else{
				$k=$h/$w;
				switch($type){
					case IMAGETYPE_GIF: $extension='gif'; break;
					case IMAGETYPE_JPEG: $extension='jpeg'; break;
					case IMAGETYPE_PNG: $extension='png'; break;
					case IMAGETYPE_WBMP: $extension='wbmp'; break;
					case IMAGETYPE_XBM: $extension='xbm'; break;
				}
				$funcCreate='imagecreatefrom'.$extension;
				$funcSave='image'.$extension;
				if($extension&&function_exists($funcCreate)&&function_exists($funcSave)){
	
					if($rh/$rw>=$k){
						$x=0;
						$y=(int)(($rh-$rw*$k)/2);
						$w1=$rw;
						$h1=(int)($rw*$k);
					}else{
						$x=(int)(($rw-$rh/$k)/2);
						$y=0;
						$w1=(int)($rh/$k);
						$h1=$rh;
					}
	
					$img=$funcCreate($filepath);
					$img2=imagecreatetruecolor($w,$h);
					imagecopyresampled($img2,$img,0,0,$x,$y,$w,$h,$w1,$h1);
					$funcSave($img2,$destpath);
				}else{
					$w=$rw;
					$h=$rh;
				}
			}
		}
		return $destpath;
	}
	
	function Desaturate($imagepath){
		$imageInfo=flGetInfo($imagepath);
		switch($imageInfo['extension']){
			case 'jpg':
				$img=@imagecreatefromjpeg($imagepath);
			break;
			case 'png':
				$img=@imagecreatefrompng($imagepath);
				
				imagecolortransparent($img,imagecolorallocate($img, 0, 0, 0));
				imagealphablending($img,false);
				imagesavealpha($img, true);
			break;
			case 'gif':
				$img=@imagecreatefromgif($imagepath);
			break;
		}
		imagefilter($img,IMG_FILTER_GRAYSCALE);
		switch($imageInfo['extension']){
				case 'jpg':
					@imagejpeg($img,$imagepath);
				break;
				case 'png':
					@imagepng($img,$imagepath);
				break;
				case 'gif':
					@imagegif($img,$imagepath);
				break;
			}
		@imagedestroy($img);
		return true;
	}

?>