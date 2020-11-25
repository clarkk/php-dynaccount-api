<?php

/*
*	Transfer sales to Dynaccount via debtor order
*	1. Get debtor or create if it doesn't exists
*	2. Creates debtor order
*	3. Add products to debtor order
*/

header('content-type: text/plain');

try{
	$api_id 	= 0;
	$api_key 	= '';
	$api_secret = '';
	
	require_once 'library/Dynaccount_webshop_API.php';
	$Dyn = new Dynaccount\Webshop_API($api_id, $api_key, $api_secret);
	
	//	Open connection to Dynaccount API
	$Dyn->connect();
	
	$debtor_number 		= 15466;
	$debtor_group_name 	= 'erhverv';
	
	//	Get debtor group
	$debtor_group = $Dyn->get_debtor_group($debtor_group_name);
	
	//	Get debtor or create if it doesn't exists
	$Dyn->get_debtor([
		'module_id_'		=> $debtor_number,
		'module_group_name'	=> $debtor_group_name,
		'payment_name'		=> $debtor_group['payment_name'],
		'name'				=> 'first/last name',
		'address'			=> '',
		'zip'				=> '',
		'city'				=> '',
		'ref_country_name'	=> 'DK',
		'email'				=> 'alias@domain.com',
		'ref_currency_name'	=> 'DKK'
	]);
	
	//	Create debtor order
	$debtor_order = $Dyn->put_debtor_order([
		'module_id_'		=> $debtor_number,
		'time'				=> date('m-d-Y')
	]);
	
	//	Add products to debtor order
	$Dyn->put_debtor_order_product([
		'order_id'	=> $debtor_order['id']
	]);
	
	//	Close connection to Dynaccount API
	$Dyn->disconnect();
}
catch(Dynaccount\Error $e){
	echo 'Error: '.$e->getMessage();
}