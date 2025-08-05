<?php 
	
	/**
	 * Sets the value of a cookie, basically an interface for setcookie() but it forces some best-practice options
	 * @param string $name [required] The key for the cookie (e.g. $_COOKIE[$name]) 
	 * @param string $value [optional] The value for the cookie (e.g. $_COOKIE[$name] = $value) 
	 * @param array $options [optional] Options for the cookie
	 * @return undefined
	 */
	function cookie_set(string $name, ?string $value = '', ?array $user_options = []){

		$options = cookie_options_normalise($user_options);
		
		setcookie($name, $value, $options);

   	}
	
	/**
	 * Returns the value for a cookie. Returns an empty string if the cookie doesn't exist
	 * @param string $name [required] The key for the cookie (e.g. $_COOKIE[$name]) 
	 * @return string
	 */
	function cookie_get(string $name) : string {
		if(empty($_COOKIE[$name])){
			return '';
		}
		return $_COOKIE[$name];
	}

	/**
	 * Deletes a cookie
	 * @param string $name [required] The key for the cookie (e.g. $_COOKIE[$name]) 
	 * @param array $options [optional] Options for the cookie, must match any user-supplied options from cookie_set()
	 * @return undefined
	 */
	function cookie_remove(string $name, ?array $user_options = []){

		$options = cookie_options_normalise($user_options);
		
		if(empty($_COOKIE[$name])){
			return;
		}
		
		$options['expires'] = (time() - 86400);
		
		cookie_set($name, '', $options);
		
	}

	/**
	 * Set default values for cookie options
	 * @param array $options [optional] Options for the cookie
	 * @return array
	 */
	function cookie_options_normalise(?array $user_options = []) : array {

		$options = [
			'expires' => time() + 604800,
			'path' => '/',
			'domain' => $_SERVER['HTTP_HOST'],
			'secure' => true,
			'httponly' => true,
			'samesite' => 'lax'
		];
		
		if(is_iterable($user_options)){
			foreach($user_options as $key => $val){/* loop arg keys so users can set options not defined here (future-proofing in case I'm lazy, ignorant or dead) */
				$options[$key] = $val;
			}
		}

		return $options;

	}

?>