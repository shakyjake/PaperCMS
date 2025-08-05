<?php

/**
 * Do nothing
 * @return undefined
 */
function noop(){
	return;
}

/**
 * Does the incoming request want html?
 * @return bool
 */
function accept_html() : bool {
	return strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false;
}

/**
 * Does a variable have a value? 
 * 
 * empty() returns true in some situations where a variable does actually have a value
 * 
 * @param mixed $value [required] The variable to check
 * @return bool
 */
function has_value(mixed $value = null) : bool {

	if(!isset($value)){
		return false;
	}

	if(is_string($value)){
		if($value === ''){
			return false;
		}
		return true;
	}

	if(is_int($value)){
		return true;
	}

	if(is_bool($value)){
		return true;
	}

	if(is_float($value)){
		return true;
	}

	if(is_countable($value)){
		return count($value) > 0;
	}

	if(is_object($value)){
		return true;
	}

	return false;

}

/**
 * Is $value a (relative) file path?
 * @param string $value [required] The variable to check
 * @return bool
 */
function is_path(?string $value = '') : bool {

	if(!isset($value)){
		return false;
	}

	if(is_string($value)){
		if($value === ''){
			return false;
		}
		if(substr($value, 0, 1) === '/'){
			if(substr($value, 0, 1) === '//'){
				return false;
			}
			return true;
		}
	}

	return false;

}

/**
 * Is $value a(n absolute) URL?
 * @param mixed $value [required] The variable to check
 * @return bool
 */
function is_url(string $value = '') : bool {

	if(empty($value)){
		return false;
	}

	if(is_string($value)){
		if($value === ''){
			return false;
		}
		if(left($value, strlen('https://')) === 'https://'){
			return true;
		}
		if(left($value, strlen('http://')) === 'http://'){
			return true;
		}
		if(left($value, strlen('//')) === '//'){
			return true;
		}
	}

	return false;

}

/**
 * Check if a country requires postcodes for delivery
 * @param string $country_code [required] ISO-3166-2 country code to check
 * @return bool
 */
function postcode_required(string $country_code = '') : bool {

	if(empty($country_code)){
		return true;
	}

	return !in_array($country_code, [
		'AO', 'AG', 'AW', 'BS', 'BZ', 'BJ', 'BW', 'BF', 'BI', 'CM', 
		'CF', 'KM', 'CG', 'CD', 'CR', 'CI', 'DJ', 'DM', 'GM', 'GH', 
		'GD', 'GN', 'GY', 'HK', 'JM', 'KE', 'KI', 'KP', 'MO', 'MW', 
		'ML', 'MR', 'MU', 'MS', 'NR', 'NL', 'NU', 'PA', 'QA', 'RW', 
		'KN', 'LC', 'ST', 'SA', 'SC', 'SL', 'SB', 'SO', 'ZA', 'SR', 
		'SY', 'TZ', 'TL', 'TK', 'TO', 'TT', 'TV', 'UG', 'AE', 'VU', 
		'YE', 'ZW'
	]);

}

/**
 * CURL helper function
 * @param string $uri [required] The URL to send the request to
 * @param string $http_method [required] HTTP request method
 * @param string $data [optional] HTTP request data (url-encoded)
 * @return string
 */
function curly(string $uri = null, string $http_method = 'GET', string $data = '') : string {

	$headers = [];

	if(!has_value($uri)){
		return '';
	}

	$curly = curl_init();

	curl_setopt($curly, CURLOPT_URL, $uri);
	curl_setopt($curly, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curly, CURLOPT_USERAGENT, 'PaperCMS');

	$headers[] = 'Content-Length: ' . strlen($data);

	if($http_method === 'POST'){
		$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		curl_setopt($curly, CURLOPT_POSTFIELDS, $data);
	}

	curl_setopt($curly, CURLOPT_HEADER, $headers);

	$response_text = curl_exec($curly);

	curl_close($curly);

	return $response_text;

}

/**
 * Return a default value if the supplied value is empty
 * @param mixed $value [required] The variable to check
 * @param mixed $default_value [required] The default value to return if $value is empty
 * @return mixed
 */
function do_check(mixed $value = null, mixed $default_value = null) : mixed {

	if(has_value($value)){
		return $value;
	}
	
	return $default_value;

}

/**
 * Strip accented letters from a string. 
 * 
 * No reason they shouldn't work but I've had issues with browser support in the past (maybe fixed now, but once-bitten twice-shy and all that)
 * 
 * @param string $value [required] The variable to change
 * @return string
 */
function anglicise(string $value = '') : string {
	
	if(empty($value)){
		return '';
	}

	$accented_chars = [
		'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A',
		'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I',
		'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U',
		'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a',
		'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i',
		'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u',
		'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'Ğ'=>'G', 'İ'=>'I', 'Ş'=>'S', 'ğ'=>'g', 'ı'=>'i', 'ş'=>'s',
		'ü'=>'u', 'ă'=>'a', 'Ă'=>'A', 'ș'=>'s', 'Ș'=>'S', 'ț'=>'t', 'Ț'=>'T'
	];
	$value = strtr($value, $accented_chars);
	
	return $value;

}
?>