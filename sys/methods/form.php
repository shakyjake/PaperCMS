<?php

	/**
	 * Return a request parameter value, or a default value if the former doesn't exist
	 * @param string $name [required] The $_REQUEST parameter key
	 * @param mixed $default_value [optional] The default value to return if no request parameter exists for $name
	 * @return string
	 */
	function do_form(string $name, ?string $default_value = '') : string {
		if(isset($_REQUEST[$name])){
			if(is_array($_REQUEST[$name])){
				return implode(',', $_REQUEST[$name]);
			}
			if(is_string($_REQUEST[$name])){
				return $_REQUEST[$name];
			}
			if(!empty($_REQUEST[$name])){// should never reach here but w/e
				return $_REQUEST[$name];
			}
		}
		return $default_value;
	}

	/**
	 * Add a querystring parameter to a URL
	 * @param string $url [required] The $_REQUEST parameter key
	 * @param string $name [required] The parameter key
	 * @param string $value [optional] The parameter value
	 * @return string
	 */
	function query_param_add(string $url, string $name, ?string $value = '') : string {
		if(str_contains($url, '?')){
			return sprintf('%1$s&%2$s=%3$s', $url, $name, urlencode($value));
		}
		return sprintf('%1$s?%2$s=%3$s', $url, $name, urlencode($value));
	}

	/**
	 * Generate an HTML input for a CSRF token
	 * @param string $action [required] The action for which the token is being generated (*should* be unique per-form)
	 * @return string
	 */
	function csrf_input(string $action) : string {

		$out = [];

		$out[] = '<input type="hidden" name="csrf_token" value="';
		$out[] = html(csrf_token_generate($action));
		$out[] = '" />';

		return implode('', $out);

	}

	/**
	 * Generate a CSRF token
	 * @param string $action [required] The action for which the token is being generated (*should* be unique per-form)
	 * @return string
	 */
	function csrf_token_generate(string $action) : string {

		global $current_user;

		if(empty($current_user->csrf_salt)){
			$current_user->csrf_salt = random_string(32);
		}

		return hash('sha256', $current_user->csrf_salt . $action);

	}

	/**
	 * Validate a CSRF token
	 * @param string $action [required] The action for which the token is being generated (*should* be unique per-form)
	 * @param string $token [optional] The token to validate. Will default to $_REQUEST['csrf_token'] if not provided
	 * @return bool
	 */
	function csrf_token_valid(string $action, ?string $token) : bool {

		if(empty($token)){
			if(!empty($_REQUEST['csrf_token'])){
				$token = $_REQUEST['csrf_token'];
			}
		}

		if(empty($token)){/* still empty? No token to validate */
			return false;
		}

		return hash_equals(csrf_token_generate($action), $token);

	}

?>