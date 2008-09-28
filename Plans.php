<?php
	// Boilerplate code for _all_ Plans scripts
	require_once('Configuration.php');
	putenv('TZ=' . TZ);
		
	// Set some testings only for when we're called through /beta/.
	if (strstr($_SERVER['REQUEST_URI'], '/dev/') != FALSE) {
		ini_set('error_reporting', E_ALL);
		ini_set('register_globals',	FALSE);
		ini_set('html_errors',	TRUE);
	}
	
	// ini_set('include_path', '/inc');
	
	function __autoload($classname) {
		require_once("inc/$classname.php");
	}

	require_once("inc/SessionBroker.php");
	header('Content-Type: text/html; charset=UTF-8');
?>