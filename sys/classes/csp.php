<?php
class CSP {
	
	private $policies;
	private $has_been_sent;
	private $in_use;
	
	function add(string $name, string $value){

		$this->in_use = true;

		if(!isset($this->policies[$name])){
			$this->policies[$name] = new CSPItem($name, $value);
		} else {
			$this->policies[$name]->add($value);
		}

	}
	
	function to_header(){
		$output = [];
		foreach($this->policies as $policy){
			$output[] = $policy->output();
		}
		return implode('; ', $output);
	}

	function output(){
		if(!$this->in_use){
			return;
		}
		if($this->has_been_sent){
			return;
		}
		$this->has_been_sent = true;
		header('Content-Security-Policy: ' . $this->to_header());
	}
	
	function __construct(){
		$this->has_been_sent = false;
		$this->in_use = false;
		$this->policies = [];
		$this->policies['default-src'] = new CSPItem('default-src', '\'self\'');
	}
	
	function __destruct(){
		$this->policies = null;
		$this->has_been_sent = null;
		$this->in_use = null;
	}
	
}

class CSPItem {

	public $name;
	public $value;
	
	function add(string $value = ''){
		if(empty($value)){
			return;
		}
		foreach($this->value as $existing_value){
			if($existing_value === $value){
				return;
			}
		}
		$this->value[] = $value;
	}
	
	function remove(string $value = ''){
		if(has_value($value)){
			if($index = array_search($value, $this->value) !== false){
				unset($this->value[$index]);
			}
		} else {
			$this->value = [];
		}
	}
	
	function output(){
		if(empty($this->value)){
			return '';
		}
		return sprintf(
			'%1$s %2$s',
			$this->name,
			implode(' ', $this->value)
		);
	}
	
	function clear(){
		$this->value = [];
	}
	
	function __construct(string $name, string $value = ''){
		$this->name = $name;
		$this->value = [];
		if(has_value($value)){
			$this->value[] = $value;
		}
	}
	
	function __destruct(){
		$this->name = null;
		$this->value = null;
	}

}

?>