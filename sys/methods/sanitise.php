<?php

/**
 * Strip invalid slug characters from a full (relative) url
 * @param string $value [required] The variable to check
 * @return string
*/
function sanitise_url(?string $value = '') : string {
	
	if(!isset($value)){
		return '';
	}

	if($value !== '0'){
		if(empty($value)){
			return '';
		}
	}

	$value = strtolower($value);
	$value = trim($value);
	$value = trim($value, '/');

	$parts = explode('/', $value);
	foreach($parts as $key => $part){
		$parts[$key] = sanitise_slug($part);
	}
	$value = implode('/', $parts);

	return sprintf('/%1$s', $value);

}

/**
 * Strip invalid slug characters from a string
 * @param string $value [required] The variable to check
 * @return string
*/
function sanitise_slug(string $value) : string {
	
	if(empty($value)){
		return '';
	}

	$value = strtolower($value);
	$value = anglicise($value);
	$value = regex_replace('[^\da-z\-]', '-', $value);
	$value = regex_replace('\-{2,}', '-', $value);
	$value = trim($value, '-');
	
	return $value;

}

/**
 * Strip invalid slug characters from a filename
 * @param string $value [required] The variable to check
 * @return string
*/
function sanitise_filename(string $value) : string {
	
	if(!isset($value)){
		return '';
	}

	$value = strtolower($value);/* no, Linus, paths _shouldn't_ be case sensitive */
	$pathinfo = pathinfo($value);

	$value = sanitise_slug($pathinfo['filename']);
	
	return sprintf(
		'%1$s.%2$s',
		$value,
		$pathinfo['extension']
	);

}

/**
 * Return $value if $value is a valid float
 * @param string $value [required] The variable to check
 * @return string
*/
function sanitise_float(string $value) : string {

	if(!is_string($value)){
		$value = (string)$value;
	}

	if(regex_test('/^(?:\-)?\d+(?:\.\d+)?$/', $value)){
		return $value;
	}
	
	return null;

}

/**
 * Return $value if $value is a valid integer
 * @param string $value [required] The variable to check
 * @return string
*/
function sanitise_int(string $value) : string {

	if(!is_string($value)){
		$value = (string)$value;
	}

	if(regex_test('/^(?:\-)?\d+$/', $value)){
		return $value;
	}
	
	return null;

}

/**
 * Format $value as a valid ajax object/action, return nothing if invalid
 * @param string $value [required] The variable to check
 * @return string
*/
function sanitise_ajax_path(string $value) : string {

	if(empty($value)){
		return null;
	}

	$value = strtolower($value);
	$value = str_replace('-', '_', $value);

	if(regex_test('/[^\da-z\_]/', $value)){
		return null;
	}
	
	return $value;

}

/**
 * Return $value if $value is a valid guid (curly braces optional)
 * @param string $value [required] The variable to check
 * @return string
*/
function sanitise_guid(string $value) : string {

	if(!is_string($value)){
		$value = (string)$value;
	}

	if(regex_test('/^\{?[\da-f]{8}\-[\da-f]{4}\-[\da-f]{4}\-[\da-f]{4}\-[\da-f]{12}\}?$/i', $value)){
		return $value;
	}
	
	return null;

}

?>