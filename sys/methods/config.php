<?php 

	$config_cache = [];

	/**
	 * Returns the value of a given xpath query on config.xml
	 * @param string $xpath_query [required] The xpath query to be evaluated
	 * @return mixed
	 */
	function config(string $xpath_query) : mixed {

		global $config;
		global $config_cache;

		/*
			Check the config cache so we're not having to traverse the config doc for repeated queries
			Use md5 to hash the query cos not sure if PHP likes slashes in array keys
		*/
		$key_hash = md5($xpath_query);
		if(array_key_exists($key_hash, $config_cache)){
			return $config_cache[$key_hash];
		}

		if(!has_value($config)){
			config_load();
		}
		if(!has_value($config)){
			throw new Exception('config.xml not loaded');
			return '';
		}
		if($config === false){
			throw new Exception('config.xml false');
			return '';
		}
		
		$value = $config->xpath($xpath_query);

		if(empty($value)){

			$config_cache[$key_hash] = '';

		} else if(is_countable($value)){
			if(count($value) === 1){
				$config_cache[$key_hash] = config_value($value[0]);
			} else {
				$config_cache[$key_hash] = [];
				foreach($value as $node){
					$config_cache[$key_hash][] = config_value($node);
				}
			}
		} else {
			$config_cache[$key_hash] = config_value($value);
		}

		return $config_cache[$key_hash];

	}

	/**
	 * Returns a best-guess typecasted value for an xml node
	 * @param mixed $value [required] The value to be typecasted
	 * @return mixed
	 */
	function config_value(mixed $value = null) : mixed {

		if(!isset($value)){
			return null;
		}

		$value = (string)$value;
		$value = trim($value);

		if(strlen($value) === 0){
			return null;
		}
		
		if(strtolower($value) === 'true'){
			return true;
		}
		
		if(strtolower($value) === 'false'){
			return false;
		}
		
		if(is_numeric($value)){
			$value = (float)$value;
			if($value == (int)$value){
				$value = (int)$value;
			}
		}

		return $value;

	}

	/**
	 * Return a value from the Paths portion of config.xml
	 * @param string $path_name [required] The path to retrieve
	 * @return string
	 */
	function config_path(string $path_name) : string {
		return config('Paths/' . $path_name);
	}

	/**
	 * Return a value from the PageIds portion of config.xml
	 * @param string $page_name [required] The page ID to retrieve
	 * @return string
	 */
	function config_page_id(string $page_name) : string {
		return config('PageIds/' . $page_name);
	}

	/**
	 * Load the config.xml file
	 * @return undefined
	 */
	function config_load(){
		global $config;
		if(!has_value($config)){
			$config = simplexml_load_file(map_path('/config.xml'));
			if($config === false){
				foreach(libxml_get_errors() as $error){
					error_handle($error->code, $error->message, $error->file, $error->line);
				}
			}
		}
	}

	/**
	 * Set email parameters from config.xml.
	 * @param string $id [required] The Id attribute of the Email node
	 * @param string $recipient [required] The recipient variable to be assigned
	 * @param string $sender [required] The sender variable to be assigned
	 * @param string $subject [required] The subject variable to be assigned
	 * @return undefined
	 */
	function email_config(string $id, string &$recipient, string &$sender, string &$subject){

		$recipient = config('Email[@Id=\'' . $id . '\']/Recipient');
		$sender = config('Email[@Id=\'' . $id . '\']/Sender');
		$subject = config('Email[@Id=\'' . $id . '\']/Subject');

	}

	if(!defined('TAGSTART')){
		define('TAGSTART', config('SnippetOpen'));
	}

	if(!defined('TAGEND')){
		define('TAGEND', config('SnippetClose'));
	}

?>