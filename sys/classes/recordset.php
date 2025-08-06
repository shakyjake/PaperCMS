<?php

class RecordSet {

	public $eof = true;
	public $rows = [];
	public $row = null;
	public $index = 0;
	
	private $sql = '';
	
    function __construct(string $sql, array &$params = [], array $options = [], int $fetch_type = SQLSRV_FETCH_ASSOC){

		global $current_user;
	
		$this->sql = $sql;

		$connection = get_connection();

		$query = sqlsrv_query($connection, $sql, $params, $options);
	
		if($query === false){

			$errors = sqlsrv_errors();

			sqlsrv_close($connection);

			$error_msg = 'Unable to obtain RecordSet for query ';

			if($current_user->group() > 2){
				$sql_parts = explode('?', $sql);
				foreach($params as $key => $value){
					$sql_parts[$key] .= '\'' . (string)$value[0] . '\'';
				}
				$sql = implode('', $sql_parts);
			}

			$error_msg .= $sql;

			throw new \Exception($error_msg . ': ' . print_r($errors, true));
			
		} else {
			
			while($row = sqlsrv_fetch_array($query, $fetch_type)){
				$this->rows[] = $row;
			}

			sqlsrv_close($connection);

			$this->eof = (count($this->rows) === 0);
			if(!$this->eof){
				$this->row = $this->rows[0];
			}
		
		}
	
    }
	
	function collapse(string $column_name, string $glue = ',', bool $ignore_empty = true){
		$out = [];
		foreach($this->rows as $row){
			if($ignore_empty){
				if(!empty($row[$column_name])){
					$out[] = $row[$column_name];
				}
			} else {
				if(empty($row[$column_name])){
					$out[] = '';
				} else {
					$out[] = $row[$column_name];
				}
			}
		}
		return implode($glue, $out);
	}
	
	function move_next(){
	
		if($this->index >= count($this->rows)){
			throw new \Exception('move_next(); Recordset for query ' . $this->sql . ' is already eof');
		}
		
		$this->index += 1;
		$this->eof = ($this->index >= count($this->rows));
		
		if($this->index >= count($this->rows)){
			$this->row = null;
		} else {
			$this->row = $this->rows[$this->index];
		}
	
	}

	function parse_template(string $template, string $tag = 'RS'){
		foreach($this->row as $key => $value){
			$template = tag_parse($template, tag_build(sprintf('%1$s(%2$s)', $tag, $key)), $value);
		}
		return $template;
	}
	
}

?>