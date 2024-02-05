<?php

namespace Dynaccount_API;

class Service extends API {
	
	protected $api_id 			= 0;
	protected $api_key			= '';
	protected $api_secret		= '';
	
	protected $host 			= 'service.dynaccount.com';
	protected $api_version 		= 'v1';
	
	public function send_ubl_document(int $block_id, string $document){
		$this->check_connection();
		
		$hash_base = '';
		
		$body = $this->form_data('block_id', $block_id, $hash_base)
			.$this->form_data('document', $document, $hash_base);
		
		return $this->request($this->url_path('send_ubl_document'), $body, $hash_base);
	}
	
	public function edelivery_account(string $action, string $user, int $vatno, string $country){
		$this->check_connection();
		
		$fields = [
			'action'			=> $action,
			'user'				=> $user,
			'vatno'				=> $vatno,
			'country'			=> $country
		];
		return $this->request($this->url_path('edelivery_account'), $fields);
	}
}