<?php

	$ajax_endpoints = new ActionList();

	/**
	 * Handle a request made to a registered AJAX endpoint
	 * @param string $object [required] The object being acted upon
	 * @param string $action [required] The action being performed
	 * @param mixed $id [optional] The id of the object being acted upon
	 * @return undefined
	 */
	function ajax_do(string $object, string $action, mixed $id){
		global $ajax_endpoints;
		return $ajax_endpoints->process('ajax__' . $object . '_' . $action, $id);
	}

	/**
	 * Add an AJAX endpoint
	 * @param string $object [required] The object being acted upon
	 * @param string $action [required] The action being performed
	 * @return undefined
	 */
	function ajax_add(string $object, string $action){
		global $ajax_endpoints;
		$ajax_endpoints->add('ajax__' . $object . '_' . $action, $object . '_' . $action);
	}

?>