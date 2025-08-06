<?php 

	error_reporting(E_ALL);
	set_exception_handler('exception_handle');
	set_error_handler('error_handle', E_ALL);

	const ERROR_MODE__PRODUCTION = 1;
	const ERROR_MODE__DEVELOPMENT = 2;

	if(!defined('ERROR_MODE')){
		define('ERROR_MODE', ERROR_MODE__DEVELOPMENT);
	}

	/**
	 * Custom Exception handler
	 * @param Exception $exception [required] The Exception to... handle, i guess
	 * @return undefined
	 */
	function exception_handle($exception){
		$error_number = (string)$exception->getCode();
		$error_msg = $exception->getMessage();
		$error_file = $exception->getFile();
		$error_line = $exception->getLine();
		$stack_trace = $exception->getTrace();
		error_handle($error_number, $error_msg, $error_file, $error_line, $stack_trace, true);
	}

	/**
	 * Custom Error handler
	 * @param string $error_number [optional] The error number/code
	 * @param string $error_msg [optional] A (hopefully) friendly text description of what the error was
	 * @param string $error_file [optional] The file that the error occurred in (deepest point in stack)
	 * @param string $error_line [optional] The line that the error occurred at (deepest point in stack)
	 * @param array $stack_trace [optional] An exhaustive diagram of exactly where we were when the error occurred
	 * @param bool $error_is_fatal [optional] Are we not able to continue? 
	 * @return undefined
	 */
	function error_handle(?string $error_number = null, ?string $error_msg = '', ?string $error_file = null, ?int $error_line = null, ?array $stack_trace = array(), ?bool $error_is_fatal = false){

		/* 
			prioritise availability for production environments
			prioritise writing correct code in development environments (to lessen the chance of errors after deployment)
		*/
		if(ERROR_MODE === ERROR_MODE__DEVELOPMENT){
			$error_is_fatal = true;
		}

		try {

			if(do_check(config('Errors/Display'), false)){ 
				error_write($error_number, $error_msg, $error_file, $error_line, $stack_trace, $error_is_fatal);
			}

			if(do_check(config('Errors/LogDB'), false)){ 
				error_log_db($error_number, $error_msg, $error_file, $error_line, $stack_trace, $error_is_fatal);
			}

			if(do_check(config('Errors/Notify'), false)){ 
				error_notify($error_number, $error_msg, $error_file, $error_line, $stack_trace, $error_is_fatal);
			}

		} catch(Exception $e){

			error_log_local($error_number, $error_msg, $error_file, $error_line, $stack_trace, true);
			error_log_local($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(), true);


			/* dgaf */
			//do_status(500);
			/* Output less friendlyish error page */
		}

		if($error_is_fatal){
			die();
		}

	}


	/**
	 * Write the error to a log file
	 * @param mixed $error_number [optional] The error number/code
	 * @param string $error_msg [optional] A (hopefully) friendly text description of what the error was
	 * @param string $error_file [optional] The file that the error occurred in (deepest point in stack)
	 * @param string $error_line [optional] The line that the error occurred at (deepest point in stack)
	 * @param array $stack_trace [optional] An exhaustive diagram of exactly where we were when the error occurred
	 * @param bool $error_is_fatal [optional] Are we not able to continue? 
	 * @return undefined
	 */
	function error_log_local($error_number = null, $error_msg = '', $error_file = null, $error_line = null, $stack_trace = array(), $error_is_fatal = false){

		$now = new \DateTime();

		$log_file = map_path(config_path('Log')) . '/' . date_format($now, 'Y-m-d') . '.txt';

		$out = new RapidString();

		if(!file_exists($log_file)){
			$out->add("TIME\tURL REQUESTED\tDESCRIPTION\tSOURCE\tREMOTE ADDRESS\tUSER AGENT");
		}

		$out->add("\r\n");

		$out->add(date_format($now, 'H:i:s'));

		$out->add("\t");

		$out->add($_SERVER['URL']);

		$out->add("\t");

		$out->add($error_msg);

		$out->add("\t");

		$out->add($error_file);

		$out->add("\t");

		$out->add(ip_get());

		$out->add("\t");

		$out->add($_SERVER['HTTP_USER_AGENT']);

		file_put_contents($log_file, $out->dump(), FILE_APPEND);

		$out = null;

	}


	/**
	 * Write the error to the log database
	 * @param mixed $error_number [optional] The error number/code
	 * @param string $error_msg [optional] A (hopefully) friendly text description of what the error was
	 * @param string $error_file [optional] The file that the error occurred in (deepest point in stack)
	 * @param string $error_line [optional] The line that the error occurred at (deepest point in stack)
	 * @param array $stack_trace [optional] An exhaustive diagram of exactly where we were when the error occurred
	 * @param bool $error_is_fatal [optional] Are we not able to continue? 
	 * @return undefined
	 */
	function error_log_db($error_number = null, $error_msg = '', $error_file = null, $error_line = null, $stack_trace = array(), $error_is_fatal = false){

		try {

			$params = [
				null,
				$error_file,
				$error_line,
				$error_msg,
				print_r($stack_trace, true)
			];
			
			execute_sql('errorLog_Save', $params);

		} catch(Exception $exception){

			/* fall back to a file-based log */
			error_log_local($error_number, $error_msg, $error_file, $error_line, $stack_trace, $error_is_fatal);

			/* then also log the reason that the DB query failed */
			$error_number = $exception->getCode();
			$error_msg = $exception->getMessage();
			$error_file = $exception->getFile();
			$error_line = $exception->getLine();
			$stack_trace = $exception->getTrace();
			error_log_local($error_number, $error_msg, $error_file, $error_line, $stack_trace, true);

		}

	}

	/**
	 * Send an error email notification
	 * @param mixed $error_number [optional] The error number/code
	 * @param string $error_msg [optional] A (hopefully) friendly text description of what the error was
	 * @param string $error_file [optional] The file that the error occurred in (deepest point in stack)
	 * @param string $error_line [optional] The line that the error occurred at (deepest point in stack)
	 * @param array $stack_trace [optional] An exhaustive diagram of exactly where we were when the error occurred
	 * @param bool $error_is_fatal [optional] Are we not able to continue? 
	 * @return undefined
	 */
	function error_notify($error_number = null, $error_msg = '', $error_file = null, $error_line = null, $stack_trace = array(), $error_is_fatal = false){

		$recipient = '';
		$sender = '';
		$subject = '';

		email_config('error', $recipient, $sender, $subject);

		$recipient = do_check($recipient, 'jake@eskdale.net');
		$sender = do_check($sender, 'errors@eskdale.net');
		$subject = do_check($subject, 'Error Notification [CONFIGERR]');

		$error_template = email_template_get('error');

		$post_params = [];
		foreach($_POST as $post_key => $post_value){
			$post_params[] = '<strong>' . html($post_key) . ':</strong> ' . html(do_check($post_value, ''));
		}

		$get_params = [];
		foreach($_GET as $get_key => $get_value){
			$get_params[] = '<strong>' . html($get_key) . ':</strong> ' . html(do_check($get_value, ''));
		}

		$cookies = [];
		foreach($_COOKIE as $cookie_key => $cookie_value){
			$cookies[] = '<strong>' . html($cookie_key) . ':</strong> ' . html(do_check($cookie_value, ''));
		}

		/* easier to say which ones we want rather than which we don't want */
		$server_keys = ['ORIG_PATH_INFO', 'URL', 'SERVER_PROTOCOL', 'SERVER_PORT', 'SERVER_NAME', 'SCRIPT_NAME', 'SCRIPT_FILENAME', 'REQUEST_URI', 'REQUEST_METHOD', 'REMOTE_HOST', 'REMOTE_ADDR', 'PATH_TRANSLATED', 'HTTP_CACHE_CONTROL', 'CONTENT_TYPE', 'CONTENT_LENGTH', 'HTTP_DNT', 'HTTP_HOST', 'HTTP_CONTENT_LENGTH', 'HTTP_PRAGMA', 'REQUEST_TIME'];

		$server_vars = [];
		foreach($server_keys as $server_key){
			if(array_key_exists($server_key, $_SERVER)){
				$server_vars[] = '<strong>' . html($server_key) . ':</strong> ' . html(do_check($_SERVER[$server_key], ''));
			}
		}

		$error_template = tag_parse($error_template, 'DESCRIPTION', html($error_msg) . "\r\n");
		$error_template = tag_parse($error_template, 'FILE', html($error_file) . "\r\n");
		$error_template = tag_parse($error_template, 'LINE', html($error_line) . "\r\n");
		$error_template = tag_parse($error_template, 'NUMBER', html($error_number) . "\r\n");
		$error_template = tag_parse($error_template, 'STACKTRACE', html(print_r($stack_trace, true)) . "\r\n");
		$error_template = tag_parse($error_template, 'IPADDRESS', html(do_check(ip_get(), 'Unavailable')) . "\r\n");
		$error_template = tag_parse($error_template, 'FORM_DATA', implode("<br />\r\n", $post_params) . "\r\n");
		$error_template = tag_parse($error_template, 'QUERY_DATA', implode("<br />\r\n", $get_params) . "\r\n");
		$error_template = tag_parse($error_template, 'COOKIES', implode("<br />\r\n", $cookies) . "\r\n");
		$error_template = tag_parse($error_template, 'USER_AGENT', html($_SERVER['HTTP_USER_AGENT']) . "\r\n");
		$error_template = tag_parse($error_template, 'SERVER_VARS', implode("<br />\r\n", $server_vars) . "\r\n");

		$error_template = tag_parse($error_template, 'PAGEID', "Unavailable\r\n");

		if($error_is_fatal){
			$error_template = tag_parse($error_template, 'FATAL', 'Yes' . "\r\n");
		} else {
			$error_template = tag_parse($error_template, 'FATAL', 'No' . "\r\n");
		}

		$error_template = tag_parse($error_template, 'EMAIL.SUBJECT', $subject);
		$error_template = tag_parse($error_template, 'EMAIL.SUMMARY', 'An error has occurred on ' . config('SiteUrl'));
		$error_template = template_parse_misc($error_template);
		$error_template = template_parse_datetime($error_template);
		$error_template = template_parse_config($error_template);

		$email = new Email($subject, $error_template, $recipient, $sender, true, null, null, null);
		if(!$email->send()){
			$email->queue();
		}

	}

	/**
	 * Write the error to the browser (if user has sufficient privileges)
	 * @param mixed $error_number [optional] The error number/code
	 * @param string $error_msg [optional] A (hopefully) friendly text description of what the error was
	 * @param string $error_file [optional] The file that the error occurred in (deepest point in stack)
	 * @param string $error_line [optional] The line that the error occurred at (deepest point in stack)
	 * @param array $stack_trace [optional] An exhaustive diagram of exactly where we were when the error occurred
	 * @param bool $error_is_fatal [optional] Are we not able to continue? 
	 * @return undefined
	 */
	function error_write($error_number, $error_msg, $error_file, $error_line, $stack_trace, $error_is_fatal){

		global $current_user;

		$user_group = 1;

		if(!is_null($current_user)){
			if(is_object($current_user)){
				$user_group = $current_user->group();
			}
		}

		$error_template = '';

		if($user_group >= 501){

			$error_template = email_template_get('error');

			$post_params = [];
			foreach($_POST as $post_key => $post_value){
				$post_params[] = '<strong>' . html($post_key) . ':</strong> ' . html(do_check($post_value, ''));
			}

			$get_params = [];
			foreach($_GET as $get_key => $get_value){
				$get_params[] = '<strong>' . html($get_key) . ':</strong> ' . html(do_check($get_value, ''));
			}

			$cookies = [];
			foreach($_COOKIE as $cookie_key => $cookie_value){
				$cookies[] = '<strong>' . html($cookie_key) . ':</strong> ' . html(do_check($cookie_value, ''));
			}

			/* easier to say which ones we want rather than which we don't want */
			$server_keys = ['ORIG_PATH_INFO', 'URL', 'SERVER_PROTOCOL', 'SERVER_PORT', 'SERVER_NAME', 'SCRIPT_NAME', 'SCRIPT_FILENAME', 'REQUEST_URI', 'REQUEST_METHOD', 'REMOTE_HOST', 'REMOTE_ADDR', 'PATH_TRANSLATED', 'HTTP_CACHE_CONTROL', 'CONTENT_TYPE', 'CONTENT_LENGTH', 'HTTP_DNT', 'HTTP_HOST', 'HTTP_CONTENT_LENGTH', 'HTTP_PRAGMA', 'REQUEST_TIME'];

			$server_vars = [];
			foreach($server_keys as $server_key){
				if(array_key_exists($server_key, $_SERVER)){
					$server_vars[] = '<strong>' . html($server_key) . ':</strong> ' . html(do_check($_SERVER[$server_key], ''));
				}
			}

			$error_template = tag_parse($error_template, 'DESCRIPTION', html($error_msg) . "\r\n");
			$error_template = tag_parse($error_template, 'FILE', html($error_file) . "\r\n");
			$error_template = tag_parse($error_template, 'LINE', html($error_line) . "\r\n");
			$error_template = tag_parse($error_template, 'NUMBER', html($error_number) . "\r\n");
			$error_template = tag_parse($error_template, 'STACKTRACE', html(print_r($stack_trace, true)) . "\r\n");
			$error_template = tag_parse($error_template, 'IPADDRESS', html(do_check(ip_get(), 'Unavailable')) . "\r\n");
			$error_template = tag_parse($error_template, 'FORM_DATA', implode("<br />\r\n", $post_params) . "\r\n");
			$error_template = tag_parse($error_template, 'QUERY_DATA', implode("<br />\r\n", $get_params) . "\r\n");
			$error_template = tag_parse($error_template, 'COOKIES', implode("<br />\r\n", $cookies) . "\r\n");
			$error_template = tag_parse($error_template, 'USER_AGENT', html($_SERVER['HTTP_USER_AGENT']) . "\r\n");
			$error_template = tag_parse($error_template, 'SERVER_VARS', implode("<br />\r\n", $server_vars) . "\r\n");

			//if(has_value($Page->id)){
				//$error_template = tag_parse($error_template, 'PAGEID', $Page->id . "\r\n");
			//} else {
				$error_template = tag_parse($error_template, 'PAGEID', 'Unavailable' . "\r\n");
			//}

			if($error_is_fatal){
				$error_template = tag_parse($error_template, 'FATAL', 'Yes' . "\r\n");
			} else {
				$error_template = tag_parse($error_template, 'FATAL', 'No' . "\r\n");
			}

			$error_template = tag_parse($error_template, 'EMAIL.SUBJECT', 'Error');
			$error_template = tag_parse($error_template, 'EMAIL.SUMMARY', 'An error has occurred on ' . config('SiteUrl'));
			$error_template = template_parse_misc($error_template);
			$error_template = template_parse_datetime($error_template);
			$error_template = template_parse_config($error_template);
		
		}
		
		if($error_is_fatal){
			do_status(500, true);
		}

		die($error_template);

	}

?>