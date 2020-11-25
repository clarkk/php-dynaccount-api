<?php

/*
*	Transfer sales to Dynaccount via plain ledger bookkeeping
*	1. Get debtor or create if it doesn't exists
*	2. Creates an enclosure with accountings
*/

header('content-type: text/plain');

try{
	$api_id 	= 0;
	$api_key 	= '';
	$api_secret = '';
	
	require_once 'library/Dynaccount_webshop_API.php';
	$Dyn = new \Dynaccount\Webshop_API($api_id, $api_key, $api_secret);
	
	//	Open connection to Dynaccount API
	$Dyn->connect();
	
	$debtor_number 		= 15466;
	$debtor_group_name 	= 'erhverv';
	
	//	Get bookkeeping draft
	$draft_id = $Dyn->get_draft_id('bilag');
	
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
	
	$voucher = [
		'time'	=> '01-02-14', // This value can also be a INT(10) UNIX timestamp
		'txt'	=> 'Webshop order: 29292'
	];
	
	//	Monetary totals in order
	$accounting = [
		'total_products'	=> 594,
		'total_shipping'	=> 5.75,
		'total_discounts'	=> 0,
		'total_vat'			=> 148.5,
		'total_paid'		=> 748.25
	];
	
	//	Cost accounts in Dynaccount
	$accounts = [
		'sales'		=> 1100,
		'discounts'	=> 1300,
		'vat'		=> 14262,
		'shipping'	=> 1200
	];
	
	//	Book voucher
	$Dyn->put_enclosure($draft_id, $voucher, $accounting, $accounts, $debtor_number);
	
	//	Close connection to Dynaccount API
	$Dyn->disconnect();
}
catch(\Dynaccount\Error $e){
	echo 'Error: '.$e->getMessage();
}