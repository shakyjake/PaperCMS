<?php 

	/**
	 * Get the user's IP address
	 * @return string
	 */
	function ip_get(){

		/* Check a couple of headers */
		$keys = ['HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR', 'REMOTE_HOST'];

		foreach($keys as $key){
			if(isset($_SERVER[$key])){
				if(!empty(ip_sanitise($_SERVER[$key]))){
					return ip_sanitise($_SERVER[$key]);
				}
			}
		}

		return '';

	}

	/**
	 * Makes sure an IP address is valid
	 * @param string $ip [required] The IP address to check
	 * @return string
	 */
	function ip_sanitise(string $ip){

		if(!has_value($ip)){
			return '';
		}

		if(filter_var($ip, FILTER_VALIDATE_IP) === false){
			return '';
		}

		return $ip;

	}

?>