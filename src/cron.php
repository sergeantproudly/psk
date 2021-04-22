<?php

// запущено через консоль
if (isset($argv) && isset($argv[0])) {
	$_SERVER['DOCUMENT_ROOT'] = dirname(dirname(__FILE__));

	if (isset($argv[1])) 
		$task = $argv[1];
}

require_once 'bootstrap.php';
use Engine\Library\SafeMySQL;

$Config   = require 'config.php';
$Database = new SafeMySQL($Config['Database']);


function importCatalog($rows) {
	global $catalogModel;

	$counter = 0;
	foreach ($rows as $row) {
		$csv = str_getcsv($row, '|');
		$item = array(
			'Title' => $csv[1],
			'Description' => $csv[2],
			'Price' => str_replace(',', '.', $csv[3]),
			'Measure' => $csv[4],
			'Mnemocode' => $csv[5],
			'Date' => date('Y-m-d', strtotime($csv[6])),
			'Request' => ''
		);
		$catalogModel->setItem($item);
		$counter++;
	}
	echo 'command import/catalog - '.$counter.' records managed'.PHP_EOL;
	return true;
}

function importRegister($rows) {
	global $registerModel;
	global $companyModel;

	$counter = 0;
	foreach ($rows as $row) {
		$csv = str_getcsv($row, '|');

		$csv[0] = str_replace(array(chr(239),chr(187),chr(191)), '', $csv[0]);

		$date = mb_substr($csv[0], 0, 4) . '-' . mb_substr($csv[0], 4, 2) . '-' . mb_substr($csv[0], 6, 2) . ' 00:00:00';

		$contractorName = $csv[1];
		if ($contractorName) $companyModel->add($contractorName);
		$contractorId = $companyModel->getIdByName($contractorName);

		$branchOfficeName = $csv[3];
		if ($branchOfficeName) $companyModel->add($branchOfficeName);
		$branchOfficeId = $companyModel->getIdByName($branchOfficeName);

		if ($contractorId && $branchOfficeId) {
			$item = array(
				'Title' => $csv[0],
				'ContractorId' => $contractorId,
				'Object' => $csv[2],
				'BranchOfficeId' => $branchOfficeId,
				'Date' => $date,
				'Published' => 1
			);
			$registerModel->add($item);
			$counter++;
		}
	}
	echo 'command import/register - '.$counter.' records managed'.PHP_EOL;
	return true;
}

function importRegisterContents($rows) {
	global $registerModel;

	$counter = 0;
	foreach ($rows as $row) {
		$csv = str_getcsv($row, '|');

		$registerNumber = str_replace(array(chr(239),chr(187),chr(191)), '', $csv[0]);
		$registerId = $registerModel->getRegisterIdFromNumber($registerNumber);

		$vat = $csv[11] ? preg_replace("/[^0-9]/", '', $csv[11]) : 0;

		if ($registerId) {
			$item = array(
				'Number' => $csv[1],
				'ContractNumber' => $csv[2],
				'ConsignmentNumber' => $csv[3],
				'ConsignmentDate' => date('Y-m-d', strtotime($csv[4])),
				'Mnemocode' => $csv[5],
				'ItemName' => $csv[6],
				'ItemAmount' => $csv[7],
				'ItemMesure' => $csv[8],
				'ItemPrice' => str_replace(',', '.', $csv[9]),
				'Sum' => str_replace(',', '.', $csv[10]),
				'VAT' => $vat ? $vat : 0,
				'SumVAT' => str_replace(',', '.', $csv[12]),
				'RegisterId' => $registerId
			);
			$registerModel->addContents($item);
			$counter++;
		}
	}
	echo 'command import/register - '.$counter.' contents records managed'.PHP_EOL;
	return true;
}


/*
// Импорт номенклатора (task = import/catalog)
if (!isset($task) || $task == 'import/catalog') {
	$catalogModel = new \Site\Models\CatalogModel($Database);

	$catalogModel->truncateAll();

	$importFile = ROOT.'/import1c/import_catalogue.csv';
	importCatalog(file($importFile));	
}

// Импорт реестров накладных (task = import/register)
if (!isset($task) || $task == 'import/register') {
	$registerModel = new \Site\Models\RegisterModel($Database);
	$companyModel = new \Site\Models\RegisterCompanyModel($Database);

	$registerModel->truncateAll();

	$importFile = ROOT.'/import1c/import_register%.csv';
	for ($counter = 0; $counter < 50; $counter++) {
		$file = str_replace('%', $counter?$counter:'', $importFile);
		if (file_exists($file)) importRegister(file($file));
	}

	$importFile = ROOT.'/import1c/import_register_contents%.csv';
	for ($counter = 0; $counter < 50; $counter++) {
		$file = str_replace('%', $counter?$counter:'', $importFile);
		if (file_exists($file)) importRegisterContents(file($file));
	}
}
*/

function importCerts($rows) {
	global $certModel;

	$counter = 0;
	foreach ($rows as $row) {
		$csv = str_getcsv($row, ',');

		$mnemocode = trim($csv[0], '"');
		$title = trim($csv[1], '"');
		$file = '/import1c/certs/' . trim($csv[2], '"');
		$csv[3] = str_replace(array(chr(239),chr(187),chr(191)), '', trim($csv[3], '"'));
		$termDate = mb_substr($csv[3], 6, 4) . '-' . mb_substr($csv[3], 3, 2) . '-' . mb_substr($csv[3], 0, 2) . ' 00:00:00';

		if (!strtotime($termDate)) continue;

		$item = array(
			'Mnemocode' => $mnemocode,
			'Title' => $title,
			'File' => $file,
			'TermDate' => $termDate
		);
		$certModel->manageItem($item);

		$counter++;
	}
	echo 'command import/certs - '.$counter.' records managed'.PHP_EOL;
	return true;
}

if (!isset($task) || $task == 'import/certs') {
	$certModel = new \Site\Models\CertModel($Database);
	die($certModel);

	$certModel->truncateAll();

	$importFile = ROOT.'/import1c/certs/_certs.csv';
	if (file_exists($importFile)) importCerts(file($importFile));
}