<?php

class IFTT {
	
	public $url;
	public $data;
	
	function send(){
		
		$headers = [];

		if(!has_value($this->url)){
			return '';
		}

		$curly = curl_init();
		
		$data_string = json_encode($this->data);
		
		$headers[] = 'Content-Length: ' . strlen($data_string);
		$headers[] = 'Content-Type: application/json';

		curl_setopt($curly, CURLOPT_URL, $this->url);
		curl_setopt($curly, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curly, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($curly, CURLOPT_HEADER, $headers);

		$response_text = curl_exec($curly);

		curl_close($curly);

		return $response_text;
		
	}
	
	function __construct(string $event_name, string $key, ?array $data  = [], ?bool $send_immediately = true){
		
		$this->url = sprintf('https://maker.ifttt.com/trigger/%1$s/with/key/%2$s',
			$event_name,
			$key
		);
		
		$this->data = $data;
		
		if($send_immediately){
			return $this->send();
		}
		
	}
	
}

?>