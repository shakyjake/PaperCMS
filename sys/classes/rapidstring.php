<?php

class RapidString {
	
	public $string = null;

	function add(string $value){
		if(has_value($value)){
			$this->string[] = $value;
		}
	}

	function secure_reuse(){
		foreach($this->string as $index => $value){/* Concerned about UAF? I gotchu */
			$this->string[$index] = str_repeat('a', strlen($value));
		}
		$this->string = [];
	}

	function reuse(){
		$this->string = [];
	}

	function dump(){
		return implode('', $this->string);
	}

	function __construct(){
		$this->string = [];
	}

	function __destruct(){
		$this->string = null;
	}

	function __toString(){
		return $this->dump();
	}

}

?>