<?php
	
/**
 * Encode a string for HTML output
 * @param string $input [required] The string to be encoded
 * @return string
 */
function html(?string $input = '') : string {
	if(empty($input)){
		return '';
	}
	return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', false);
}

/**
 * Generate a random string
 * @param int $length [required] The length of the generated string
 * @param array $exclude [optional] An array of characters you wish to exclude from the resulting string
 * @return string
 */
function random_string(int $length, ?array $exclude = []) : string {
	$str = '';
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"$%^*()_+-={}[]:@~;\'#<>?,./|\\';
	while(strlen($str) < $length){
		$char = $characters[random_int(0, strlen($characters) - 1)];
		if(count($exclude)){
			if(in_array($char, $exclude)){
				continue;
			}
		}
		$str .= $char;
	}
	return $str;
}

/**
 * Make sure a URL is technically valid (the best kind of valid)
 * @param string $input [required] The URL to be checked
 * @return string
 */
function url_fixup(?string $input) : string {

	if(empty($input)){
		return '';
	}
	
	if(!str_contains($input, '.')){/* eat shit, localhost */
		return '';
	}

	if(strlen($input) < strlen('http://')){/* WRONG */
		return 'https://' . $input;
	}

	if(left($input, strlen('https://')) === 'https://'){/* noice */
		return $input;
	}

	if(left($input, strlen('http://')) === 'http://'){/* sigh, fine */
		return 'https://' . trim_left($input, 'http://');/* on second thoughts, nah, protect your users */
	}

	if(left($input, strlen('//')) === '//'){/* uh... ok? */
		return 'https:' . $input;/* Nah, not ok actually. Give it an explicit protocol. */
	}

	return 'https://' . $input;

}

/**
 * Return the name of a callable
 * @param callable $callable [required] The callable
 * @return string
 */
function callable_name(callable $callable) : string {
	switch(true){
		case is_string($callable) && strpos($callable, '::'):
		case is_string($callable):
			return (string)$callable;
		case is_array($callable) && is_object($callable[0]):
			return get_class($callable[0])  . '->' . (string)$callable[1];
		case is_array($callable):
			return (string)$callable[0]  . '::' . (string)$callable[1];
		case is_object($callable):
			return get_class($callable);
		default:
			return '';
	}
}

/**
 * Remove superfluous information from a URL (in case horizontal space is at a premium)
 * @param string $uri [required] The URL to be trimmed
 * @return string
 */
function url_output(?string $uri = '') : string {

	if(empty($uri)){
		return '';
	}

	$uri = trim_left($uri, 'https://');
	if(left($uri, strlen('http://')) === 'http://'){/* on second thoughts, insecure protocols are not superfluous information */
		return '<span style="color: red">http://</span>' . trim_left($uri, 'http://');/* lol make it red too, to annoy incompetent devs */
	}
	$uri = trim_left($uri, '//');
	$uri = trim_left($uri, 'www.');
	$uri = trim_right($uri, '/');

	return $uri;

}

/**
 * Strip unnecessary whitespace and HTML comments
 * @param string $input [required] The string to be minified
 * @return string
 */
function html_minify(?string $input = '') : string {

	if(empty($input)){
		return '';
	}

	$input = regex_replace('/[\r\n\t]+/', ' ', $input);
	$input = str_replace('&#160;', ' ', $input);
	$input = str_replace('&nbsp;', ' ', $input);

	if(str_contains($input, '<!--')){
		$input = regex_replace('/<!--(.|\s)*?-->/', ' ', $input);
	}

	while(str_contains($input, '  ')){
		$input = str_replace('  ', ' ', $input);
	}

	$input = trim_both($input, ' ');

	return $input;

}

/**
 * Check if a string is a valid email address. Pass byref to so we can fix the user's mistakes.
 * @param string $email [required] The string to be validated
 * @return bool
 */
function email_valid(?string &$email = '') : bool {

	if(empty($email)){
		return true;
	}

	$email = trim($email);
	$email = str_replace(' ', '', $email);/* it ok we all make mistakes */
	$email = str_replace(',', '.', $email);
	$email = strtolower($email);

	if(filter_var($email, FILTER_VALIDATE_EMAIL) === false){
		return false;
	}

	return true;

}

/**
 * Return a human-readable value for a given (strongly-typed) boolean
 * @param bool $b [required] Yes? No?
 * @return string
 */
function human_bool(?bool $b = false) : string {
	if($b === true){
		return 'Yes';
	}
	return 'No';
}

/**
 * Left-pad a string with the desired character until it's the [required] length. No relying on 3rd-party dependencies for core functionality here, no siree
 * @param string $input [required] The string to be padded
 * @param int $length [required] The minumum string length required
 * @param string $char [required] The character(s) to be prepended
 * @return string
 */
function lpad(?string $input = '', ?int $length = 0, ?string $char = '0') : string {

	if(empty($char)){
		return $input;
	}

	if(strlen($input) >= $length){
		return $input;
	}
	
	return str_repeat($char, $length - strlen($input)) . $input;

}

/**
 * Right-pad a string with the desired character until it's the [required] length
 * @param string $input [required] The string to be padded
 * @param int $length [required] The minumum string length required
 * @param string $char [required] The character(s) to be appended
 * @return string
 */
function rpad(?string $input = '', ?int $length = 0, ?string $char = '0') : string {

	if(empty($char)){
		return $input;
	}

	if(strlen($input) >= $length){
		return $input;
	}
	
	return $input . str_repeat($char, $length - strlen($input));

}

/**
 * Strip non-alphanumeric characters from a string and uppercase the first letter of each word
 * @param string $input [required] The string to be modified
 * @return string
 */
function namify(?string $input = '') : string {

	$input = regex_replace('(\d+)', ' $1 ', $input);
	$input = regex_replace('[^\da-zA-Z]', ' ', $input);
	$input = regex_replace('\s{2,}', ' ', $input);
	$input = trim($input);
	$input = ucwords($input);
	
	return $input;

}

?>