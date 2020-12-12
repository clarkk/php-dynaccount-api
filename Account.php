<?php

namespace Dynaccount_API;

class Account extends API {
	
	protected $api_id 			= 0;
	protected $api_key			= '';
	protected $api_secret		= '';
	
	protected $host 			= 'api.dynaccount.com';
	protected $api_version 		= 'v7';
	
	public function get(string $table, int $id=0, Array $select=[], Array $where=[], Array $order=[], string $limit=''){
		$this->check_connection();
		
		$query = [];
		
		if($select){
			foreach($select as $key => $value){
				$select[$key] = urlencode($value);
			}
			$query[] = 'select='.implode(',', $select);
		}
		
		if($where){
			$query[] = http_build_query($where);
		}
		
		if($order){
			foreach($order as $key => $value){
				$order[$key] = urlencode($value);
			}
			$query[] = 'order='.implode(',', $order);
		}
		
		if($limit){
			$query[] = 'limit='.urlencode($limit);
		}
		
		$url = $this->url_path('get', $table, $id);
		if($query){
			$url .= '?'.implode('&', $query);
		}
		
		return $this->request($url);
	}
	
	public function put(string $table, int $id=0, Array $fields=[]){
		$this->check_connection();
		
		return $this->request($this->url_path('put', $table, $id), $fields);
	}
	
	public function insert_bulk(string $table, Array $rows=[]){
		$this->check_connection();
		
		return $this->request($this->url_path('insert_bulk', $table), $this->sequential_array($rows));
	}
	
	public function delete(string $table, int $id){
		$this->check_connection();
		
		return $this->request($this->url_path('delete', $table, $id));
	}
	
	public function action(string $action, Array $params){
		$this->check_connection();
		
		return $this->request($this->url_path('action', $action), $params);
	}
	
	public function upload_voucher(string $label, Array $files, string $from=''){
		$this->check_connection();
		
		$hash_base = '';
		
		$body = $this->form_data('label', $label, $hash_base)
			.$this->form_data('from_name', $from, $hash_base);
		
		if($files){
			foreach($files as $file){
				if(!is_file($file)){
					throw new Error("Voucher file $file not found");
				}
				
				$body .= $this->form_file('files[]', file_get_contents($file), basename($file), $hash_base);
			}
		}
		else{
			throw new Error('No files specified');
		}
		
		return $this->request($this->url_path('action', 'upload_voucher'), $body, $hash_base);
	}
	
	public function report(string $report, string $output, string $lang, Array $params){
		$this->check_connection();
		
		return $this->request($this->url_path('report', $report, $output).'?lang='.$lang, $params);
	}
	
	public function document(string $document, string $document_id_){
		$this->check_connection();
		
		return $this->request($this->url_path('document', $document, $document_id_));
	}
	
	private function sequential_array(Array $arr): Array{
		$return = [];
		foreach($arr as $seq => $rows){
			foreach($rows as $key => $value){
				$return[$key][$seq] = $value;
			}
		}
		
		return $return;
	}
}