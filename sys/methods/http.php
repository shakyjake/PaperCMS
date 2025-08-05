<?php

	/**
	 * Send an HTTP status code to the browser
	 * @param int $code [required] The HTTP status code to send
	 * @param bool $exit [optional] Should we terminate the request immediately?
	 * @return undefined
	 */
	function do_status(int $code, bool $exit = false){
		http_response_code($code);
		if($exit){
			die();
		}
	}

	/**
	 * Send a 302 (temporary) redirect response
	 * @param string $url [required] The URL to redirect to
	 * @return undefined
	 */
	function temporary_redirect(string $url){
		header('Location: ' . $url);
		die();
	}

	/**
	 * Send a 301 (permanent) redirect response
	 * @param string $url [required] The URL to redirect to
	 * @return undefined
	 */
	function permanent_redirect(string $url){
		header('Location: ' . $url, true, 301);
		die();
	}

	/**
	 * Append a QueryString to a given URL
	 * @param string $base_uri [required] The URL to append the QueryString to
	 * @param string $qs [required] The QueryString to be appended. $base_uri will be returned if $qs is ommitted or empty
	 * @return string
	 */
	function qs_add(string $base_uri = '/', string $qs = ''){
		if(has_value($qs)){
			if(str_contains($base_uri, '?')){
				return sprintf('%1$s&%2$s', $base_uri, $qs);
			} else {
				return sprintf('%1$s?%2$s', $base_uri, $qs);
			}
		}
		return $base_uri;
	}

	/**
	 * Append a QueryString Name/Value pair to a given URL
	 * @param string $base_uri [required] The URL to append the QueryString to
	 * @param string $name [required] The QueryString parameter key. $base_uri will be returned if ommitted or empty
	 * @param string $value [required] The QueryString parameter value. $base_uri will be returned if ommitted or empty
	 * @return string
	 */
	function qs_param_add(string $base_uri = '/', string $name = '', string $value = ''){
		if(has_value($name)){
			if(has_value($value)){
				if(str_contains($base_uri, '?')){
					return sprintf('%1$s&%2$s=%3$s', $base_uri, $name, urlencode($value));
				} else {
					return sprintf('%1$s?%2$s=%3$s', $base_uri, $name, urlencode($value));
				}
			}
		}
		return $base_uri;
	}

?>