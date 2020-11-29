<?php

header('content-type: text/plain');


try{
	$api_id 		= 0;
	$api_key 		= '';
	$api_secret 	= '';
	
	require_once 'library/Dynaccount_account_API.php';
	$Dyn = new \Dynaccount\Account_API($api_id, $api_key, $api_secret);
	
	//	Open connection to Dynaccount API
	$Dyn->connect();
	
	//	Get
	/*$table 	= 'account';
	$id 		= 0;
	$select 	= [];
	$where 		= [];
	$order 		= [];
	$limit 		= '';
	$result = $Dyn->get($table, $id, $select, $where, $order, $limit);
	print_r($result);*/
	
	//	Put
	/*$table 	= 'account';
	$id 		= 0;
	$fields = [
		'account_id_'		=> 1,
		'name'				=> 'heheheh',
		'type'				=> 'BALANCE',
		'is_monitored'		=> 'NO',
		'vatcode_name'		=> '',
		'sum_account_id_'	=> '',
		'ref_currency_name'	=> 'dkk',
		'is_dimension'		=> 'NO'
	];
	$result = $Dyn->put($table, $id, $fields);
	print_r($result);*/
	
	//	Insert_bulk
	/*$table = 'account';
	$rows = [
		[
			'account_id_'		=> 1,
			'name'				=> 'heheheh',
			'type'				=> 'BALANCE',
			'is_monitored'		=> 'NO',
			'vatcode_name'		=> '',
			'sum_account_id_'	=> '',
			'ref_currency_name'	=> 'dkk',
			'is_dimension'		=> 'NO',
			'accountoff_id_'	=> ''
		],[
			'account_id_'		=> 2,
			'name'				=> 'heheheh',
			'type'				=> 'BALANCE',
			'is_monitored'		=> 'NO',
			'vatcode_name'		=> '',
			'sum_account_id_'	=> '',
			'ref_currency_name'	=> 'dkk',
			'is_dimension'		=> 'NO',
			'accountoff_id_'	=> ''
		]
	];
	$result = $Dyn->insert_bulk($table, $rows);
	print_r($result);*/
	
	//	Delete
	/*$table 	= 'account';
	$id 		= 0;
	$result = $Dyn->delete($table, $id);
	print_r($result);*/
	
	//	Action
	/*$action = 'send_debtor_invoice';
	$params = [
		'invoice_id'	=> 0,
		'email'			=> 'to@example.com',
		'msg'			=> 'weeeee',
		'is_attached'	=> 'NO'
	];
	$result = $Dyn->action($action, $params);
	print_r($result);*/
	
	//	Upload voucher
	/*$label = 'test bilag';
	$files = [
		'/root/test.pdf'
	];
	$from = 'username or email';
	$result = $Dyn->upload_voucher($label, $files, $from);
	print_r($result);*/
	
	//	Close connection to Dynaccount API
	$Dyn->disconnect();
}
catch(\Dynaccount\Error $e){
	echo 'Error: '.$e->getMessage();
}