<?php

header('content-type: text/plain');

try{
	$api_id 		= 0;
	$api_key 		= '';
	$api_secret 	= '';
	
	require_once 'library/Dynaccount_scan_API.php';
	$Dyn = new \Dynaccount\Scan_API($api_id, $api_key, $api_secret);
	
	//	Open connection to Dynaccount API
	$Dyn->connect();
	
	//	Scan
	$country = 'DK';
	$vatno = '34223475';
	$result = $Dyn->scan($country, $vatno, file_get_contents('test.pdf'));
	print_r($result);
	
	//	Close connection to Dynaccount API
	$Dyn->disconnect();
}
catch(\Dynaccount\Error $e){
	echo 'Error: '.$e->getMessage();
}