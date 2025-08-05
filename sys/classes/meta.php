<?php

class MetaTag {
	
	public $id = null;
	public $label = null;
	public $name = null;
	public $default_value = null;
	public $fixed = null;
	public $attributes = null;
	
	function name_valid(){
		if(regex_test('/^[a-z][\da-z\:\-]+[a-z][\da-z\-]*$/', $this->name)){
			return true;
		}
		return false;
	}

	function attribute_add(int $id = 0, string $attribute_name = '', string $default_value = '', bool $fixed = false){
		$this->attributes[$attribute_name] = new MetaAttribute($id, $attribute_name, $default_value, $fixed);
	}

	function attribute_remove(string $attribute_name = ''){
		unset($this->attributes[$attribute_name]);
	}
	
	function __construct(int $id = 0, string $tag_name = 'meta', string $label = '', string $default_value = '', bool $fixed = false){
		$this->id = $id;
		$this->name = $tag_name;
		$this->label = $label;
		$this->default_value = $default_value;
		$this->fixed = $fixed;
		$this->attributes = [];
	}
	
	function __destruct(){
		$this->id = null;
		$this->name = null;
		$this->label = null;
		$this->default_value = null;
		$this->fixed = null;
		$this->attributes = null;
	}
	
}

class MetaAttribute {

	public $id = null;
	public $name = null;
	public $default_value = null;
	public $fixed = null;
	
	function name_valid(){
		if(regex_test('/^[a-z][\da-z\:\-]+[a-z][\da-z\-]*$/', $this->name)){
			return true;
		}
		return false;
	}
	
	function __construct(int $id = 0, string $attribute_name = 'meta', string $default_value = '', bool $fixed = false){
		$this->id = $id;
		$this->name = $attribute_name;
		$this->default_value = $default_value;
		$this->fixed = $fixed;
	}
	
	function __destruct(){
		$this->id = null;
		$this->name = null;
		$this->default_value = null;
		$this->fixed = null;
	}

}

?>