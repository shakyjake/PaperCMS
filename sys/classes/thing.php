<?php
class Thing {
	
	public $id = null;
	public $parent_id = null;
	public $type = null;
	public $name = null;
	public $data = [];
	public $data_loaded = false;

	function get_data(string $key, bool $value_only = true){
		if(empty($this->data[$key])){
			if(!$this->data_loaded){
				$this->load_data();
			}
			if(empty($this->data[$key])){
				return null;
			}
		}
		if($value_only){
			return $this->data[$key]->value;
		}
		return $this->data[$key];
	}

	function set_data(string $key, $value){
		if(empty($this->data[$key])){
			$this->data[$key] = new ThingData($this->id, null, $key, $value);
		} else {
			$this->data[$key]->value = $value;
		}
		$this->data[$key]->save();
	}

	function save(){
		execute_sql('thing_Save', [
			[$this->id, SQLSRV_PARAM_OUT],
			$this->parent_id,
			$this->type,
			$this->name
		]);
	}

	function delete(){
		execute_sql('thing_Delete', [
			$this->id
		]);
	}
	
	function load_data(){
		$this->data_loaded = true;
		$RS = get_records('thingData_List', [
			$this->id
		]);
		if(!$RS->eof){
			while(!$RS->eof){
				$this->data[$RS->row['DataKey']] = new ThingData($this->id, $RS->row['ThingDataId'], $RS->row['DataKey'], $RS->row['DataValue']);
				$RS->move_next();
			}
		}
		$RS = null;
	}
	
	function load(bool $load_data = false){
		$RS = get_records('thing_Detail', [
			$this->id
		]);
		if(!$RS->eof){
			$this->parent_id = $RS->row['ParentId'];
			$this->type = $RS->row['TypeName'];
			$this->name = $RS->row['Name'];
			if($load_data){
				$this->load_data();
			}
		}
		$RS = null;
	}
	
	function __construct(int $id = null, int $parent_id = null, string $type = null, string $name = null){
		$this->id = $id;
		$this->parent_id = $parent_id;
		$this->type = $type;
		$this->name = $name;
		$this->data = [];
		$this->data_loaded = false;
	}
	
	function __destruct(){
		$this->id = null;
		$this->parent_id = null;
		$this->type = null;
		$this->name = null;
		$this->data = null;
		$this->data_loaded = false;
	}
	
}

class ThingData {
	
	public $id = null;
	public $thing_id = null;
	public $name = null;
	public $value = null;
	
	function save(){
		execute_sql('thingData_Save', [
			[$this->id, SQLSRV_PARAM_OUT],
			$this->thing_id,
			$this->name,
			$this->value
		]);
	}
	
	function load(){
		$RS = get_records('thingData_Detail', [
			$this->id,
			$this->thing_id,
			$this->name
		]);
		if(!$RS->eof){
			$this->thing_id = $RS->row['ThingId'];
			$this->name = $RS->row['DataKey'];
			$this->value = $RS->row['DataValue'];
		}
	}

	function delete(){
		execute_sql('thingData_Delete', [
			$this->id
		]);
	}
	
	function __construct(int $thing_id, ?int $thing_data_id = null, ?string $name = null, ?string $value = null){
		if(empty($thing_id)){
			throw new \Exception('Thing Data must have an associated Thing.');
		}
		$this->id = $thing_data_id;
		$this->thing_id = $thing_id;
		$this->name = $name;
		$this->value = $value;
	}
	
	function __destruct(){
		$this->id = null;
		$this->thing_id = null;
		$this->name = null;
		$this->value = null;
	}

}

function template_list(){

	global $current_user;

	$response = [];

	if($current_user->group() < 3){
		return $response;
	}

	$RS = get_records('thing_Search', [
		null,
		null,
		'template',
		1,
		999
	]);

	while(!$RS->eof){
		
		$row = [
			'id' => $RS->row['ThingId'],
			'name' => $RS->row['Name']
		];

		$response[] = $row;

		$RS->move_next();

	}

	$RS = null;

	header('Content-Type: application/json');
	echo json_encode($response);

	die();

}
ajax_add('template', 'list');

?>