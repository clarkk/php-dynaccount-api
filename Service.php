<?php

namespace Dynaccount_API;

class Service extends API {
	
	protected $api_id 			= 0;
	protected $api_key			= '';
	protected $api_secret		= '';
	
	protected $host 			= 'service.dynaccount.com';
	protected $api_version 		= 'v1';
	
	public function send_document(string $file_contents){
		$this->check_connection();
		
		$hash_base = '';
		$body = $this->form_file('file', $file_contents, 'file.xml', $hash_base);
		
		return $this->request($this->url_path('send_document'), $body, $hash_base);
	}
}