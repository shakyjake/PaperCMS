<?php

class ActionList {

	private $actions = null;

	function process(string $name, ...$parameters){
		return $this->actions[$name]->process(...$parameters);
	}

	function add(string $name, string $function, ...$parameters){
		if(isset($this->actions[$name]) && is_callable($function)){
			$this->actions[$name]->add($function, ...$parameters);
		}
		$this->actions[$name] = new Action($name, $function, ...$parameters);
	}

	function remove(string $name, string $callable){
		return $this->actions[$name]->remove($callable);
	}

	function get(string $name){
		return $this->actions[$name];
	}

	function __construct(){
		$this->actions = [];
	}

	function __destruct(){
		$this->actions = null;
	}

}

class Action {
	
	public $name = null;
	private $todo = [];

	function process(...$parameters){
		foreach($this->todo as $todo){
			$todo->process(...$parameters);
		}
	}

	function add($callable, ...$parameters){
		$this->todo[] = new ActionCallback($callable, ...$parameters);
	}

	function remove($callable){
		$callable_name = callable_name($callable);
		$remove = [];
		foreach($this->todo as $key => $callable){
			if(callable_name($callable) === $callable_name){
				$remove[] = $key;
			}
		}
		foreach($remove as $key){
			array_splice($this->todo, $key, 1);
		}
	}

	function __construct(string $name, $callable, ...$parameters){
		$this->name = $name;
		$this->todo[] = new ActionCallback($callable, $parameters);
	}

	function __destruct(){
		$this->name = null;
		$this->todo = null;
	}

}

class ActionCallback {

	public $action = null;
	public $params = null;

	function process(...$parameters){
		if(!is_callable($this->action)){
			throw new \Exception(sprintf('ActionCallback: action %1$s is not callable', $this->action));
		}
		call_user_func($this->action, ...$this->params, ...$parameters);
	}

	function __construct($callable, ...$parameters){
		$this->action = $callable;
		$this->params = $parameters;
	}

	function __destruct(){
		$this->action = null;
		$this->params = null;
	}

}

?>