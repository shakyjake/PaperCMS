<?php

	require_once('sys/global.php');

	$current_user = new User();
	$current_user->log_in();
	$current_page = new Page();

	$current_page->load_from_url(true);
	$current_page->output();

?>