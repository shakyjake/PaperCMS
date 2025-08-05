<?php

	$conn_string = null;

	/**
	 * Obtains the connection string to be used in get_connection();
	 * 
	 * "_string" is a misnomer - it's actually an array
	 * 
	 * @return array
	 */
	function get_connection_string() : array {

		global $conn_string;

		if(!has_value($conn_string)){

			$config = simplexml_load_file(map_path('/web.config'));
			$node = $config->xpath("//connectionStrings/add[@name='PaperCMS']")[0];
			$attributes = $node->attributes();
			$raw = '';
			foreach($attributes as $key => $value){
				if($key === 'connectionString'){
					$raw = $value->__toString();
				}
			}
			if(has_value($raw)){

				$conn_arr = explode(';', $raw);

				$conn_string = [
					'db_host' => '',
					'options' => [
						'PWD' => '',
						'UID' => '',
						'Database' => ''
					]
				];

				foreach($conn_arr as $part){

					$nvp = explode('=', $part);
					$key = $nvp[0];
					array_shift($nvp);
					$value = implode('=', $nvp);

					switch($key){
						case 'Data Source':
							$conn_string['db_host'] = $value;
							break;
						case 'Initial Catalog':
							$conn_string['options']['Database'] = $value;
							break;
						case 'User ID':
							$conn_string['options']['UID'] = $value;
							break;
						case 'Password':
							$conn_string['options']['PWD'] = $value;
							break;
						case 'Trusted_Connection':
							$conn_string['options']['TrustServerCertificate'] = (($value === '1') ? true : false);
							break;
						case 'Driver':
							$conn_string['options']['Driver'] = $value;
							break;
						case 'Encrypt':
							$conn_string['options']['Encrypt'] = (($value === '1') ? true : false);
							break;
						case 'Pooling':
							$conn_string['options']['ConnectionPooling'] = (($value === '1') ? true : false);
							break;
					}

				}

			}

		}

		return $conn_string;
	}

	/**
	 * Obtains the connection to be used in subsequent database queries
	 * @return resource
	 */
	function get_connection(){

		$conn_string = get_connection_string();

		$connection = sqlsrv_connect($conn_string['db_host'], $conn_string['options']);

		if($connection === false){
			if($_SERVER['REMOTE_HOST'] === '90.254.143.106'){
				throw new Exception('get_connection(); Unable to connect to database \'' . $conn_string['options']['Database'] . '\' with provided credentials: ' . print_r($conn_string, true));
			}
			throw new Exception('get_connection(); Unable to connect to database \'' . $conn_string['options']['Database'] . '\' with provided credentials.');
		}

		return $connection;

	}

	/**
	 * Obtains a RecordSet from a Stored Procedure
	 * @param string $sql [required] the Stored Procedure name
	 * @param array $params [optional] The parameter(s) to be passed to the Stored Procedure
	 * @param array $options [optional] The option(s) to be passed to the sqlsrv_query
	 * @param int $fetch_type [optional] The desired fetch type
	 * @return RecordSet
	 */
	function get_records(string $sql, mixed $params = null, ?array $options = [], ?int $fetch_type = SQLSRV_FETCH_ASSOC) : RecordSet {

		if(!is_array($options)){
			$options = [$options];
		}

		sql_params_parse($params);

		$proc_params = [];

		if(count($params)){

			foreach($params as $key => $value){
				if($key === 0 && $params[$key][1] === SQLSRV_PARAM_OUT){
					$sql = '? = call ' . $sql;
				} else {
					if($key === 0){
						/* not an ouput param but we still need "call" */
						$sql = 'call ' . $sql;
					}
					$proc_params[] = '?';
				}
			}

		} else {
			$sql = 'call ' . $sql;
		}


		$sql = sprintf(
			'{%1$s(%2$s)}',
			$sql,
			implode(', ', $proc_params)
		);

		return new RecordSet($sql, $params, $options, $fetch_type);

	}

	/**
	 * Returns the first column in the first row of a given Stored Procedure
	 * @param string $sql [required] the Stored Procedure name
	 * @param array $params [optional] The parameter(s) to be passed to the Stored Procedure
	 * @param array $options [optional] The option(s) to be passed to the sqlsrv_query
	 * @return mixed
	 */
	function get_single_value(string $sql, mixed &$params = null, ?array $options = []) : mixed {

		$RS = get_records($sql, $params, $options,  SQLSRV_FETCH_NUMERIC);
		if($RS->eof){
			return null;
		}
		return $RS->row[0];

	}

	/**
	 * Executes a Stored Procedure
	 * @param string $sql [required] the Stored Procedure name
	 * @param mixed $params [optional] The parameter(s) to be passed to the Stored Procedure
	 * @param array $options [optional] The option(s) to be passed to the sqlsrv_query
	 * @return bool
	 */
	function execute_sql(string $sql, mixed &$params = null, ?array $options = []) : bool {

		sql_params_parse($params);

		if(!is_array($options)){
			$options = [$options];
		}

		$proc_params = [];

		if(count($params)){

			foreach($params as $key => $value){
				if($key === 0 && $params[$key][1] === SQLSRV_PARAM_OUT){
					$sql = '? = call ' . $sql;
				} else {
					if($key === 0){
						/* no ouput param but we still need "call" */
						$sql = 'call ' . $sql;
					}
					$proc_params[] = '?';
				}
			}

		} else {
			$sql = 'call ' . $sql;
		}

		$sql = sprintf(
			'{%1$s(%2$s)}',
			$sql,
			implode(', ', $proc_params)
		);

		$connection = get_connection();

		$query = sqlsrv_query($connection, $sql, $params, $options);

		sqlsrv_close($connection);
	
		if($query === false){
			throw new Exception('Unable to execute query ' . $sql . ': ' . print_r(sqlsrv_errors(), true));
		}
		
		return true;

	}

	/**
	 * Parses parameters for an SQL operation
	 * @param array $params [optional] The parameter(s) to be passed to the Stored Procedure
	 * @return undefined
	 */
	function sql_params_parse(mixed &$params = null){

		if(empty($params)){
			$params = [];
			return;
		}

		if(!is_array($params)){
			$params = [$params];
		}

		foreach($params as $key => $value){

			if(is_countable($value)){
				if(count($params[0]) === 1){
					$params[$value][] = SQLSRV_PARAM_IN;
				}
				if($key === 0 && $value[0][1] === SQLSRV_PARAM_OUT && count($value[0]) === 2){
					$params[$value][] = SQLSRV_PHPTYPE_INT;/* SQLSRV only returns integers (unless blah blah blah, never ever use OUTPUT parameters in SQL besides RETURN) */
				}
			} else {
				$params[$key] = [$value, SQLSRV_PARAM_IN];
			}
		}

	}

	/**
	 * Constructs a set of pagination buttons
	 * @param string $base_uri [optional] The URL (sans pagination parameters) to be used for the links
	 * @param string $page_field [optional] The querystring key for the current page number
	 * @param string $per_page_field [optional] The querystring key for the number of records per page
	 * @param int $page_count [optional] The number of pages
	 * @param int $max_buttons [optional] The maximum number of buttons to output (not including next/prev/first/last)
	 * @return string
	 */
	function pagination(?string $base_uri = '/', ?string $page_field = 'page', ?string $per_page_field = 'per_page', ?int $page_count = 1, ?int $max_buttons = 9) : string {

		if($page_count < 2){
			return '';
		}

		$current_page = (has_value($_GET[$page_field]) ? $_GET[$page_field] : 1);
		if(!is_numeric($current_page)){
			$current_page = 1;
		}
		$current_page = (int)$current_page;
		if($current_page < 1){
			$current_page = 1;
		}

		$per_page = (has_value($_GET[$per_page_field]) ? $_GET[$per_page_field] : 24);
		if(!is_numeric($per_page)){
			$per_page = 24;
		}
		$per_page = (int)$per_page;
		if($per_page < 10){
			$per_page = 10;
		}
		
		if($per_page != 24){
			$base_uri = qs_param_add($base_uri, $per_page_field, $per_page);
		}
		
		$i = $current_page - floor($max_buttons / 2);
		if($i < 1){
			$i = 1;
		}
		$j = $i + $max_buttons;
		if($j > $page_count){
			$j = $page_count;
			$i = $j - $max_buttons;
			if($i < 1){
				$i = 1;
			}
		}

		$out = new RapidString();

		$out->add('<div class="pagination">');

			if($current_page > 1){

				$out->add('<a class="pagination__link pagination__link--first" href="');
				$out->add(html($base_uri));
				$out->add('">');
				$out->add('First');
				$out->add('</a>');
				
				/*
					TODO?: add <link rel="prev"> to head
				*/

				$out->add('<a class="pagination__link pagination__link--previous" href="');
				if($current_page > 2){
					$out->add(html(qs_param_add($base_uri, $page_field, $current_page - 1)));
				} else {
					$out->add(html($base_uri));
				}
				$out->add('">');
				$out->add('Previous');
				$out->add('</a>');

			}

			while($i < $j){

				$out->add('<a class="pagination__link pagination__link--numeric');
				if($i === $current_page){
					$out->add(' pagination__link--selected');
				}
				$out->add('" href="');
				if($i > 1){
					$out->add(html(qs_param_add($base_uri, $page_field, $i)));
				} else {
					$out->add(html($base_uri));
				}
				$out->add('">');
				$out->add($i);
				$out->add('</a>');

				$i += 1;
			}

			if($current_page < $page_count){
				
				/*
					TODO?: add <link rel="next"> to head
				*/

				$out->add('<a class="pagination__link pagination__link--next" href="');
				$out->add(html(qs_param_add($base_uri, $page_field, $current_page + 1)));
				$out->add('">');
				$out->add('Next');
				$out->add('</a>');

				$out->add('<a class="pagination__link pagination__link--last" href="');
				$out->add(html(qs_param_add($base_uri, $page_field, $page_count)));
				$out->add('">');
				$out->add('Last');
				$out->add('</a>');

			}

		$out->add('</div>');

		$pagination = $out->dump();

		$out = null;

		return $pagination;

	}

	/**
	 * Returns an HTML select input containing all tables in the database
	 * @param string $name [optional] The HTML name attribute of the select input
	 * @param string $id [optional] The HTML id attribute of the select input
	 * @param array $attributes [optional] An associative array containing any additional HTML attributes
	 * @return string
	 */
	function db_table_select(?string $name = 'table_name', ?string $id = '', ?array $attributes = []){
		
		$id = empty($id) ? $name : $id;

		$params = [
			null,
			'dbo',
			null,
			'\'TABLE\''
		];
		
		$RS = get_records('sp_tables', $params);
		
		$out = new RapidString();
		$out->add('<select name="');
		$out->add(html($name));
		$out->add('" id="');
		$out->add(html($id));
		$out->add('"');
		foreach($attributes as $key => $value){
			if($key === 'name'){
				continue;
			}
			if($key === 'id'){
				continue;
			}
			$out->add(html($key));
			$out->add('="');
			$out->add(html($value));
			$out->add('"');
		}
		$out->add('>');

		while(!$RS->eof){
			$out->add('<option value="');
			$out->add('[');
			$out->add(html($RS->row['TABLE_OWNER']));/* "dbo" */
			$out->add('].[');
			$out->add(html($RS->row['TABLE_NAME']));
			$out->add(']');
			$out->add('">');
			$out->add(html($name));
			$out->add('</option>');
			$RS->move_next();
		}
		
		$out->add('</select>');
		
		return $out->dump();

	}

	/**
	 * TODO
	 * @param string $table_name [required] The name of the table
	 * @return string
	 */
	function db_table_details(string $table_name){
		
		$RS = get_records('sp_MShelpcolumns', $table_name);
		
		$table = new Table();
		
	}


	/**
	 * TODO
	 * @param string $table_name [required] The name of the table
	 * @return string
	 */
	function generate_sp_text(string $table_name){
		
	}

?>