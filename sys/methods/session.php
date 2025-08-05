<?php

/**
 * Has a session been instantiated?
 * @return bool
 */
function session_exists() : bool {
	if(session_status() === PHP_SESSION_ACTIVE){
		return true;
	}
	$cookie_format = ini_get('session.name');
	foreach($_COOKIE as $key => $value){
		if(left($key, strlen($cookie_format)) === $cookie_format){
			if(has_value($value)){
				session_start();
				return true;
			}
		}
	}
	return false;
}

/**
 * Return the value of a session variable
 * @param string $key [required] The session variable name
 * @return string
 */
function session_get(string $key) : string {
	if(!session_exists()){
		return '';
	}
	if(isset($_SESSION[$key])){
		return $_SESSION[$key];
	}
	return '';
}

/**
 * Add or update a session variable
 * @param string $key [required] The session variable name to set
 * @param string $value [required] The session variable value to set
 * @return string
 */
function session_set(string $key, string $value) : string {

	if(empty($key)){
		throw new Exception('Session variable key is required');
	}

	if(!(session_status() === PHP_SESSION_ACTIVE)){
		session_start();
	}

	$_SESSION[$key] = do_check($value, '');

	return do_check($value, '');

}

/**
 * Remove a session variable
 * @param string $key [required] The session variable name to remove
 * @return undefined
 */
function session_remove(string $key){
	if(!(session_status() === PHP_SESSION_ACTIVE)){
		session_start();
	}
	$_SESSION[$key] = '';
}

/**
 * Destroy a session
 * @return undefined
 */
function session_abandon(){
	session_destroy();
}

?>