<?php

class Theme {
	
	public $slug = null;
	private $path = null;
	private $uri = null;
	
	function activate(string $slug, bool $permanent = false){
		$this->slug = $slug;
		session_set('theme', $this->slug);
		if($permanent){
			option_add('theme', $this->slug);
		}
	}
	
	function uri(){
		return config('paths/themes') . '/' . $this->slug;
	}
	
	function path(){
		return map_path($this->uri());
	}

	function __construct(){
		$this->slug = session_get('theme');
		if(!has_value($this->slug)){
			$this->slug = option('theme');
			session_set('theme', $this->slug);
		}
	}

	function __destruct(){
		$this->slug = null;
		$this->path = null;
		$this->uri = null;
	}
	
}

function theme_get_current(){
	$theme = new Theme();
}

?>