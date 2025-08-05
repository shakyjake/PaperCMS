<?php

class CacheFile {

	public $file_name;
	public $file_path;
	public $file_contents;
	public $subdirectory;

	function save(){
		if(empty($this->file_contents)){
			$this->file_contents = '';
		}
		file_put_contents($this->file_path, $this->file_contents);
	}

	function output(){
		if(empty($this->file_contents)){
			$this->file_contents = '';
		}
		return $this->file_contents;
	}
		
	function __construct(string $file_name, string $file_contents = '', string $subdirectory = null){
		$this->file_name = $file_name;
		if(empty($subdirectory)){
			$this->file_path = map_path(config_path('Cache') . '/' . $file_name);
		} else {
			$this->subdirectory = $subdirectory;
			$this->file_path = map_path(config_path('Cache') . '/' . $subdirectory . '/' . $file_name);
		}
		$this->file_contents = $file_contents;
	}
	
	function __destruct(){
		$this->file_name = null;
		$this->file_path = null;
		$this->file_contents = null;
		$this->subdirectory = null;
	}

}

?>