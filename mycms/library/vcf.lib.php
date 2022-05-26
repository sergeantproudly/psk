<?php

	krnLoadLib('files');

	function vcfParseName($namerow) {
		$namedata = explode(' ', $namerow);
		return array(
			'Surname' => $namedata[0],
			'Name' => $namedata[1],
			'Patronymic' => $namedata[2],
		);
	}
    
    function vcfCreateFile($filepath, $data) {
    	flCreateFile($filepath);

    	$name = vcfParseName($data['Name']);

    	$content = 'BEGIN:VCARD' . PHP_EOL;
    	$content .= 'VERSION:3.0' . PHP_EOL;
    	$content .= 'N:' . $name['Surname'] . ';' . $name['Name'] . PHP_EOL;
    	$content .= 'FN:' . $name['Name'] . ($name['Patronymic'] ? (' ' . $name['Patronymic']) : '') . ' ' . $name['Surname'] . PHP_EOL;
    	$content .= 'ORG:' . $data['Company'] . PHP_EOL;
    	//$content .= 'ADR:;; ;╨Ü╨░╨╖╨░╨╜╤î;;;╨á╨╛╤ü╤ü╨╕╤Å' . PHP_EOL;
    	$content .= 'TEL;CELL:' . $data['Tel'] . PHP_EOL;
    	$content .= 'EMAIL;WORK;INTERNET:' . $data['Email'] . PHP_EOL;
    	$content .= 'URL:' . $data['Website'] . PHP_EOL;
    	$content .= 'END:VCARD' . PHP_EOL;

    	flSaveFile($filepath, $content);
    	return true;
    }

    function vcfCreateFileEn($filepath, $data) {
    	flCreateFile($filepath);

    	$name = vcfParseName($data['NameEn']);

    	$content = 'BEGIN:VCARD' . PHP_EOL;
    	$content .= 'VERSION:3.0' . PHP_EOL;
    	$content .= 'N:' . $name['Surname'] . ';' . $name['Name'] . PHP_EOL;
    	$content .= 'FN:' . $name['Name'] . ($name['Patronymic'] ? (' ' . $name['Patronymic']) : '') . ' ' . $name['Surname'] . PHP_EOL;
    	$content .= 'ORG:' . $data['CompanyEn'] . PHP_EOL;
    	//$content .= 'ADR:;; ;╨Ü╨░╨╖╨░╨╜╤î;;;╨á╨╛╤ü╤ü╨╕╤Å' . PHP_EOL;
    	$content .= 'TEL;CELL:' . $data['TelEn'] . PHP_EOL;
    	$content .= 'EMAIL;WORK;INTERNET:' . $data['EmailEn'] . PHP_EOL;
    	$content .= 'URL:' . $data['Website'] . PHP_EOL;
    	$content .= 'END:VCARD' . PHP_EOL;

    	flSaveFile($filepath, $content);
    	return true;
    }

?>