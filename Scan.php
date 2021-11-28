<?php

namespace Dynaccount_API;

class Scan extends API {
	
	protected $api_id 			= 0;
	protected $api_key			= '';
	protected $api_secret		= '';
	
	protected $host 			= 'api-scan.dynaccount.com';
	protected $api_version 		= 'v1';
	
	private $multiparts 		= [];
	
	public function scan_document(string $file_contents, bool $process_skip=false, bool $return_processed_file=false){
		$this->check_connection();
		
		$hash_base = '';
		
		$data = [
			'document'	=> [
				'type'		=> 'DOCUMENT'
			]
		];
		
		if($process_skip){
			$data['process']	= 'SKIP';
		}
		elseif($return_processed_file){
			$data['process']	= 'RETURN';
		}
		
		$body = $this->form_json('json', $data, $hash_base)
				.$this->form_file('file', $file_contents, 'file.pdf', $hash_base);
		
		$result = $this->request($this->url_path('scan'), $body, $hash_base, true);
		
		return $this->scan_response($result);
	}
	
	public function scan_invoice(string $country, string $vatno, string $file_contents, string $callback='', bool $process_skip=false, int $ocr_delay=0){
		$this->check_connection();
		
		$hash_base = '';
		
		$data = [
			'document'	=> [
				'type'		=> 'INVOICE',
				'country'	=> $country,
				'vatno'		=> $vatno
			]
		];
		
		if($callback){
			$data['callback']	= $callback;
		}
		
		if($process_skip){
			$data['process']	= 'SKIP';
			$data['ocr_delay']	= $ocr_delay;
		}
		
		$body = $this->form_json('json', $data, $hash_base)
				.$this->form_file('file', $file_contents, 'file.pdf', $hash_base);
		
		$result = $this->request($this->url_path('scan'), $body, $hash_base, true);
		
		return $this->scan_response($result);
	}
	
	public function abort_invoice(int $api_scan_id){
		$this->check_connection();
		
		return $this->request($this->url_path('scan_abort'), [
			'id' => $api_scan_id
		]);
	}
	
	protected function scan_response(Array $result){
		if(empty($result['headers']['content-type'])){
			throw new Error('Parsing headers: Content-type not found');
		}
		
		if(strpos($result['headers']['content-type'], self::CONTENT_MULTIPART.';') === 0){
			return $this->parse_multipart($result['headers']['content-type'], $result['response']);
		}
		else{
			return $result['response'];
		}
	}
	
	private function parse_multipart(string $header, string $body): Array{
		if(!$boundary = explode('boundary=', $header)[1] ?? ''){
			throw new Error('Parsing multipart response: Boundary not found');
		}
		
		$this->multiparts = [];
		
		foreach(array_filter(explode('--'.$boundary.self::CRLF, $body)) as $part){
			$pos = strpos($part, self::CRLF.self::CRLF);
			$this->parse_multipart_part(substr($part, 0, $pos), substr($part, $pos));
		}
		
		return $this->multiparts;
	}
	
	private function parse_multipart_part(string $headers, string $body){
		$name 		= '';
		$file_name 	= '';
		$type 		= '';
		$length 	= 0;
		
		foreach(explode(self::CRLF, strtolower($headers)) as $header){
			if(preg_match('/content-disposition: form-data; name="([^"]+)"(?:; filename="([^"]+)")?/', $header, $matches)){
				$name = $matches[1];
				if(!empty($matches[2])){
					$file_name = $matches[2];
				}
			}
			elseif(preg_match('/content-type: (.+)/', $header, $matches)){
				$type = $matches[1];
			}
			elseif(preg_match('/content-length: (\d+)/', $header, $matches)){
				$length = $matches[1];
			}
		}
		
		if($name == 'file' && $file_name){
			$this->multiparts[$name] = [
				'content'	=> $type,
				'file_name'	=> $file_name,
				'length'	=> $length,
				'data'		=> substr($body, 4, $length)
			];
		}
		elseif($name == 'json'){
			$this->multiparts[$name] = json_decode(substr($body, 4, $length), true);
		}
	}
}