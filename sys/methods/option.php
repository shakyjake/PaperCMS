<?php

$options = [];

/**
 * Returns the value of a given option
 * @param string $name [required] The name of the option to return
 * @return string
 */
function option(string $name = ''){

	global $options;

	if(!has_value($name)){
		return '';
	}

	if(!isset($options[$name])){
		$options[$name] = get_single_value('option_Detail', $name);
	}

	return $options[$name];

}

/**
 * Sets the value of a given option
 * @param string $name [required] The name of the option to set
 * @param string $value [required] The value of the option to set
 * @param bool $persistent [optional] Whether to save this new option value in the database
 * @return undefined
 */
function option_set(string $name = '', string $value = '', bool $persistent = true){

	global $options;

	if(!has_value($name)){
		return;
	}

	$options[$name] = $value;
	if($persistent){
		execute_sql('option_Save', [$name, $value]);
	}

}

/**
 * Loads all options marked as "preload"
 * @return undefined
 */
function options_preload(){
	$RS = get_records('option_List', true);
	while(!$RS->eof){
		option_set($RS->row['name'], $RS->row['value'], false);
		$RS->move_next();
	}
}

/**
 * Delete an option from the database
 * @param string $name [required] The name of the option to return
 * @return undefined
 */
function option_remove(string $name = ''){

	global $options;

	if(!has_value($name)){
		return;
	}

	unset($options[$name]);
	execute_sql('option_Remove', $name);
	
}

?>