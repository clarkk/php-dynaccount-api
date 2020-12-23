<?php

namespace Dynaccount_API;

abstract class API {
	
	protected $api_id;
	protected $api_key;
	protected $api_secret;
	
	protected $host;
	protected $api_version;
	
	protected $url;
	protected $socket;
	protected $out;
	
	protected $boundary;
	
	protected $headers = [
		'Accept-Encoding: gzip'
	];
	
	const CRLF = "\r\n";
	
	const CONTENT_JSON 			= 'application/json';
	const CONTENT_PDF 			= 'application/pdf';
	const CONTENT_GZIP 			= 'application/gzip';
	const CONTENT_FORMDATA 		= 'application/x-www-form-urlencoded';
	const CONTENT_MULTIPART 	= 'multipart/form-data';
	
	protected $print_response = false;
	
	public function __construct(int $api_id=0, string $api_key='', string $api_secret='', string $api_version='', string $host=''){
		if($api_id){
			$this->api_id 		= $api_id;
		}
		if($api_key){
			$this->api_key 		= $api_key;
		}
		if($api_secret){
			$this->api_secret 	= $api_secret;
		}
		if($api_version){
			$this->api_version 	= $api_version;
		}
		if($host){
			$this->host 		= $host;
		}
		
		$this->boundary = md5($api_key);
	}
	
	public function connect(bool $ssl=true, bool $verbose=false, bool $print_response=false, bool $ssl_verify=true){
		$this->url = ($ssl ? 'https://' : 'http://').$this->host;
		
		$this->print_response = $print_response;
		
		$this->socket = curl_init();
		curl_setopt_array($this->socket, [
			CURLOPT_RETURNTRANSFER 	=> true,
			CURLOPT_VERBOSE 		=> $verbose,
			CURLOPT_ENCODING 		=> '',
			CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_2_0,
			CURLOPT_SSL_VERIFYPEER 	=> $ssl_verify
		]);
		
		if($verbose){
			$this->out = fopen('php://output', 'w');
			curl_setopt($this->socket, CURLOPT_STDERR, $this->out);
		}
	}
	
	public function disconnect(){
		curl_close($this->socket);
	}
	
	public function request(string $url, $post_data=[], string $hash_base='', bool $return_headers=false){
		$headers = $this->headers;
		
		if(is_array($post_data)){
			$headers[]	= $this->header_type(self::CONTENT_FORMDATA);
			$body 		= $this->build_query($post_data);
			$hash 		= $this->generate_hash($url, $body);
		}
		else{
			$headers[] 	= $this->header_type(self::CONTENT_MULTIPART, $this->boundary);
			$body 		= $post_data.'--'.$this->boundary.'--';
			$hash 		= $this->generate_hash($url, $hash_base);
		}
		
		$headers[] = 'X-Hash: '.$hash;
		
		$this->check_post_size($body);
		
		curl_setopt($this->socket, CURLOPT_URL, $url);
		curl_setopt($this->socket, CURLOPT_HTTPHEADER, $headers);
		
		if($body){
			curl_setopt($this->socket, CURLOPT_POST, true);
			curl_setopt($this->socket, CURLOPT_POSTFIELDS, $body);
		}
		else{
			curl_setopt($this->socket, CURLOPT_POST, false);
		}
		
		$response_headers = [];
		
		if($return_headers){
			curl_setopt($this->socket, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$response_headers){
				$len = strlen($header);
				$header = explode(':', $header, 2);
				
				if(count($header) < 2){
					return $len;
				}
				
				$response_headers[strtolower(trim($header[0]))] = trim($header[1]);
				
				return $len;
			});
		}
		
		if(!$response = curl_exec($this->socket)){
			throw new Error('HTTP request failed: '.curl_error($this->socket));
		}
		
		if($this->out){
			fclose($this->out);
		}
		
		if($this->print_response){
			fwrite(STDERR, "$response\n");
		}
		
		if(curl_getinfo($this->socket, CURLINFO_CONTENT_TYPE) == self::CONTENT_JSON){
			$response = json_decode($response, true);
			
			$response = array_merge([
				'http_code' => curl_getinfo($this->socket, CURLINFO_HTTP_CODE)
			], $response);
		}
		
		if($return_headers){
			return [
				'headers'	=> $response_headers,
				'response'	=> $response
			];
		}
		else{
			return $response;
		}
	}
	
	public function url_path(string $method='', string $table='', string $id=''){
		return $this->url.'/'.implode('/', array_filter([
			$this->api_version,
			$this->api_id,
			$this->api_key,
			$method,
			$table,
			$id
		])).'/';
	}
	
	protected function generate_hash(string $url, string $body): string{
		$url = explode('://', $url);
		
		return sha1($url[1].$body.$this->api_secret);
	}
	
	protected function form_json(string $key, Array $values, string &$hash_base): string{
		$json = json_encode($values);
		
		$hash_base .= $json;
		
		return '--'.$this->boundary.self::CRLF
			.$this->header_disposition($key).self::CRLF
			.$this->header_type(self::CONTENT_JSON).self::CRLF
			.$this->header_length(strlen($json)).self::CRLF.self::CRLF
			.$json.self::CRLF;
	}
	
	protected function form_data(string $key, string $value, string &$hash_base=''): string{
		$hash_base .= $value;
		
		return '--'.$this->boundary.self::CRLF
			.$this->header_disposition($key).self::CRLF
			.$this->header_length(strlen($value)).self::CRLF.self::CRLF
			.$value.self::CRLF;
	}
	
	protected function form_file(string $key, string $file, string $file_name, string &$hash_base): string{
		$file = gzencode($file, 6);
		
		$hash_base .= $file;
		
		return '--'.$this->boundary.self::CRLF
			.$this->header_disposition($key, $file_name).self::CRLF
			.$this->header_type(self::CONTENT_GZIP).self::CRLF
			.$this->header_length(strlen($file)).self::CRLF.self::CRLF
			.$file.self::CRLF;
	}
	
	protected function check_connection(){
		if(!$this->socket){
			throw new Error('No connection is established');
		}
	}
	
	private function header_disposition(string $name, string $file_name=''): string{
		return 'Content-Disposition: form-data; name="'.$name.'"'.($file_name ? '; filename="'.$file_name.'"' : '');
	}
	
	private function header_type(string $type, string $boundary=''): string{
		$header = 'Content-Type: '.$type;
		switch($type){
			case self::CONTENT_MULTIPART:
				$header .= '; boundary='.$boundary;
		}
		
		return $header;
	}
	
	private function header_length(int $length): string{
		return 'Content-Length: '.$length;
	}
	
	private function build_query(Array $arr): string{
		$str = '';
		$this->build_query_recursive('', $arr, $str);
		
		return substr($str, 0, -1);
	}
	
	private function build_query_recursive(string $key, Array $arr, string &$str){
		foreach($arr as $k => $v){
			if(is_array($v)){
				if(empty($v)){
					$str .= $k.'[]=&';
				}
				else{
					$this->build_query_recursive($k, $v, $str);
				}
			}
			elseif($key){
				$str .= $key.'['.$k.']='.urlencode($v).'&';
			}
			else{
				$str .= $k.'='.urlencode($v).'&';
			}
		}
	}
	
	private function check_post_size(string $body){
		$max_post = 1024 * 1024 * 20;
		if(strlen($body) > $max_post){
			throw new Error('Max post size exceeded');
		}
	}
}

class Error extends \Error {}