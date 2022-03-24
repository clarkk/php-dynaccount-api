<?php

namespace Dynaccount_API;

class Service extends API {
	
	protected $api_id 			= 0;
	protected $api_key			= '';
	protected $api_secret		= '';
	
	protected $host 			= 'service.dynaccount.com';
	protected $api_version 		= 'v1';
	
	public function send_document(string $input){
		$this->check_connection();
		
		return $this->request($this->url_path('send_document'), $input);
	}
}