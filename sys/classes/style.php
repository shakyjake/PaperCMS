<?php

class Style {
	
	public $src = null;
	public $body = null;
	public $media = null;
	public $attributes = null;
	
	function output(bool $inline = false){
		
		if($inline){
			$this->body = file_get_contents($this->src);
		}
		
		if(!has_value($this->src) && !has_value($this->body)){
			return '';
		}
		
		if($inline){
			csp_add('style-src', 'sha512-' . base64_encode(hash('sha512', $this->body || '', true)));
		}
		
		$output = new RapidString();
		
		if($inline){
			$output->add('<style type="text/css">');
				$output->add($this->body);
			$output->add('<style>');
		} else {
			$output->add('<link rel="stylesheet" media="print"');
			if($this->media !== 'print'){
				$output->add(' onload="this.media=\'');
				$output->add(html($this->media));
				$output->add('\'"');
			}
			$output->add(' href="');
			$output->add(html($this->src));
			$output->add('" />');
		}
		
		$return = $output->dump();
		
		$output = null;
		
		return $return;
		
	}
	
	function __construct(string $src = null, string $media = 'all'){
		$this->src = $src;
		$this->media = $media;
		$this->body = null;
		$this->attributes = null;
	}
	
	function __destruct(){
		$this->src = null;
		$this->body = null;
		$this->media = null;
		$this->attributes = null;
	}

}

?>