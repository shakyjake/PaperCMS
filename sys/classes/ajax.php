<?php

class AJAXResponse implements \JsonSerializable {

	public $status_code = null;
	public $data = null;
	public $messages = null;
	public $redirect = null;
	public $pagination = null;
	public $debug = null;

	function message_add(string $message, ?string $key = null, ?string $type = null){
		if(empty($key)){
			$this->messages[] = new AJAXResponse_Message($message, $type);
		} else {
			if(isset($this->messages[$key])){
				$this->messages[$key]->messages[] = $message;
			} else {
				$this->messages[$key] = new AJAXResponse_Message($message, $type);
			}
		}
	}

	function rs_add(\RecordSet $rs, ?int $eof_http_status_code = 404){
		
		if($rs->eof){
			
			$this->status_code = $eof_http_status_code;

		} else {
		
			foreach($rs->rows as $row){

				$response_row = [];

				foreach($row as $key => $value){
					$response_row[$key] = $value;
				}

				$this->data[] = $response_row;

			}

		}
	
	}

	function output(){
		header('Content-Type: application/json');
		http_response_code($this->status_code);
		echo json_encode($this);
	}

	function __construct(){
		$this->status_code = 200;
		$this->data = [];
		$this->messages = [];
		$this->redirect = [];
	}

	function __destruct(){
		$this->status_code = null;
		$this->data = null;
		$this->messages = null;
		$this->redirect = null;
		$this->pagination = null;
	}
		
	function jsonSerialize() : mixed {
		global $current_user;
		$json = [
			'status_code' => $this->status_code,
			'data' => $this->data,
			'messages' => $this->messages,
			'redirect' => $this->redirect,
			'pagination' => $this->pagination
		];
		if($current_user->group() >= 6){
			$json['debug'] = $this->debug;
		}
		return $json;
	}
		
	function __toString(){
		$json = [
			'status_code' => $this->status_code,
			'data' => $this->data,
			'messages' => $this->messages,
			'redirect' => $this->redirect,
			'pagination' => $this->pagination
		];
		return json_encode($json);
	}

}
class AJAXResponse_Message implements \JsonSerializable {

	public $type = null;
	public $messages = null;

	function add(?string $message = ''){
		if(strlen($message)){
			$this->messages[] = $message;
		}
	}

	function __construct(?string $message = '', ?string $type = 'info'){
		$this->type = $type;
		$this->messages = [];
		if(strlen($message)){
			$this->messages[] = $message;
		}
	}

	function __destruct(){
		$this->type = null;
		$this->messages = null;
	}
		
	function jsonSerialize() : mixed {
		$json = [
			'type' => $this->type,
			'messages' => $this->messages
		];
		return $json;
	}

}

?>