<?php

	require_once('sys/global.php');

	$current_user = new User();
	$current_user->imitate_guest(false);
	$current_user->log_in();

	$object = '';
	$action = 'list';
	$id = '';

	if(!empty($_REQUEST['object'])){
		$object = sanitise_ajax_path($_REQUEST['object']);
	}

	if(!empty($_REQUEST['action'])){
		$action = sanitise_ajax_path($_REQUEST['action']);
	}

	if(!empty($_REQUEST['id'])){
		$id = strtolower($_REQUEST['id']);
	}

	if(empty($object)){
		http_response_code(400);
		die();
	}

	if(empty($action)){
		http_response_code(400);
		die();
	}

	if($action === 'detail' && empty($id)){
		http_response_code(400);
		die();
	}

	if($action === 'delete' && empty($id)){
		http_response_code(400);
		die();
	}

	ajax_do($object, $action, $id);

?>