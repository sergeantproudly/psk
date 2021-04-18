<?php

function SendMail($From,$FromName,$To,$ToName,$Subject,$Html,$Text,$AttmFiles=array()){
	$From=iconv('utf-8','windows-1251',$From);
	$FromName=iconv('utf-8','windows-1251',$FromName);
	$To=iconv('utf-8','windows-1251',$To);
	$ToName=iconv('utf-8','windows-1251',$ToName);
	$Subject=iconv('utf-8','windows-1251',$Subject);
	$Html=iconv('utf-8','windows-1251',$Html);
	$Text=iconv('utf-8','windows-1251',$Text);
	
	$Subject="=?koi8-r?B?".base64_encode(convert_cyr_string($Subject,"w","k"))."?=";
	
	$OB="----=_OuterBoundary_000";
	$IB="----=_InnerBoundery_001";
	$Html=$Html?$Html:preg_replace("/\n/","<br>",$Text);
	$Text=$Text?$Text:"Sorry, but you need an html mailer to read this mail.";
	
	$headers ="MIME-Version: 1.0\r\n";
	$headers.="From: ".($FromName?"=?koi8-r?B?".base64_encode(convert_cyr_string($FromName,"w","k"))."?= <".$From.">":$From)."\n";
	$headers.="Reply-To: ".($FromName?"=?koi8-r?B?".base64_encode(convert_cyr_string($FromName,"w","k"))."?= <".$From.">":$From)."\n";
	$headers.="X-Priority: 3\n";
	$headers.="X-MSMail-Priority: High\n";
	$headers.="X-Mailer: Site Mailer\n";
	$headers.="Content-Type: multipart/mixed;\n\tboundary=\"".$OB."\"\n";
	
	//Messages start with text/html alternatives in OB
	$Msg ="This is a multi-part message in MIME format.\n";
	$Msg.="\n--".$OB."\n";
	$Msg.="Content-Type: multipart/alternative;\n\tboundary=\"".$IB."\"\n\n";
	
	//plaintext section
	$Msg.="\n--".$IB."\n";
	$Msg.="Content-Type: text/plain;\n\tcharset=\"windows-1251\"\n";
	$Msg.="Content-Transfer-Encoding: base64\n\n";
	
	// plaintext goes here 
	$Msg.=chunk_split(base64_encode($Text))."\n\n";
	
	// html section
	$Msg.="\n--".$IB."\n";
	$Msg.="Content-Type: text/html;\n\tcharset=\"windows-1251\"\n";
	$Msg.="Content-Transfer-Encoding: base64\n\n";
	
	// html goes here
	$Msg.=chunk_split(base64_encode($Html))."\n\n";
	
	// end of IB
	$Msg.="\n--".$IB."--\n";
	
	// attachments
	if(count($AttmFiles)){
		foreach($AttmFiles as $FileName=>$AttmFile){
			if(file_exists($AttmFile)){
				$Msg.= "\n--".$OB."\n"; 
				$Msg.="Content-Type: application/octetstream;\n\tname=\"".$FileName."\"\n";
				$Msg.="Content-Transfer-Encoding: base64\n";
				$Msg.="Content-Disposition: attachment;\n\tfilename=\"".$FileName."\"\n\n";
				
				//file goes here
				$FileContent=cmLoadFile($AttmFile);
				$FileContent=chunk_split(base64_encode($FileContent));
				$Msg.=$FileContent;
				$Msg.="\n\n";
			} 
		}
	}
	
	//message ends
	$Msg.="\n--".$OB."--\n";
	
	return mail($To,$Subject,$Msg,$headers);
}
?>