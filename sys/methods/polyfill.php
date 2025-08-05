<?php

/**
 * str_contains polyfill
 * @param string $haystack [required] The string to be checked
 * @param string $needle [required] The string to find inside $haystack
 * @return bool
 */
if(!function_exists('str_contains')){
	function str_contains(string $haystack, string $needle){
		return (strpos($haystack, $needle) > -1);
	}
}

/**
 * Return a given number of characters from the left side of a string. 
 * @param string $input [required] The URL to be trimmed
 * @param int $length [required] The number of characters to return
 * @return string
*/
function left(string $input, int $length){
	if(strlen($input) <= $length){
		return $input;
	}
	return substr($input, 0, $length);
}

/**
 * Return a given number of characters from the right side of a string. 
 * @param string $input [required] The URL to be trimmed
 * @param int $length [required] The number of characters to return
 * @return string
*/
function right(string $input, int $length){
	if(strlen($input) <= $length){
		return $input;
	}
	return substr($input, strlen($input) - $length, $length);
}

/**
 * Remove a specified string from the LHS of another string (note: ltrim doesn't work how you think it works)
 * @param string $input [required] The string to be trimmed
 * @param string $trim [required] The exact string to remove
 * @return string
*/
function trim_left(string $input, string $trim = ' '){
	if(strlen($trim) > strlen($input)){
		return $input;
	}
	while(left($input, strlen($trim)) === $trim){
		$input = right($input, strlen($input) - strlen($trim));
	}
	return $input;
}

/**
 * Remove a specified string from the RHS of another string (note: rtrim doesn't work how you think it works)
 * @param string $input [required] The string to be trimmed
 * @param string $trim [required] The exact string to remove
 * @return string
*/
function trim_right(string $input, string $trim = ' '){
	if(strlen($trim) > strlen($input)){
		return $input;
	}
	while(right($input, strlen($trim)) === $trim){
		$input = left($input, strlen($input) - strlen($trim));
	}
	return $input;
}

/**
 * Remove a specified string from the LHS & RHS of another string (note: trim doesn't work how you think it works)
 * @param string $input [required] The string to be trimmed
 * @param string $trim [required] The exact string to remove
 * @return string
*/
function trim_both(string $input, string $trim = ' '){
	$input = trim_left($input, $trim);
	$input = trim_right($input, $trim);
	return $input;
}

/**
 * Return an absolute path for a given relative (to the site root) path
 * @param string $sub_path [required] The root-relative path
 * @return string
 */
function map_path(string $sub_path){
	return trim_right(__ROOT__, '/') . '/' . trim_left($sub_path, '/');
}

?>