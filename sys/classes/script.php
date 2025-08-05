<?php

class Script {
	
	public $src = null;
	public $body = null;
	public $attributes = null;
	
	function output(){
		
		if(!has_value($this->src) && !has_value($this->body)){
			return '';
		}
		
		if(has_value($this->body)){
			csp_add('script-src', 'sha512-' . base64_encode(hash('sha512', $this->body, true)));
		}
		
		$output = new RapidString();
		
		$output->add('<script');
		
		if(has_value($this->src)){
			$output->add(' src="');
			$output->add(html($this->src));
			$output->add('"');
		}
		
		foreach($this->attributes as $name => $value){
			$output->add(' '); 
			$output->add(html($name));
			if($name !== $value){/* handle boolean attributes (inelegantly, I admit, but you can thank the HTML spec for that) */
				$output->add('="');
				$output->add(html($value));
				$output->add('"');
			}
		}
		
		$output->add('>');
		if(!has_value($this->src) && has_value($this->body)){
			$output->add($this->body);
		}
		$output->add('</script>');
		
		if(has_value($this->src) && has_value($this->body)){
			$output->add('<script>');
			$output->add($this->body);
			$output->add('</script>');
		}
		
		$return = $output->dump();
		
		$output = null;
		
		return $return;
		
	}
	
	function set_attribute(string $name, string $value){
		$this->attributes[$name] = $value;
	}
	
	function add_attribute(string $name, string $value){
		$this->set_attribute($name, $value);
	}
	
	function remove_attribute(string $name){
		unset($this->attributes[$name]);
	}
	
	function __construct(string $src = null, array $attributes = []){
		$this->src = $src;
		$this->attributes = $attributes;
	}
	
	function __destruct(){
		$this->src = null;
		$this->body = null;
		$this->attributes = null;
	}

}

?>