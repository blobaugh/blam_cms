<?php
/*
 * This file contains functions used throughout the site
 *
 * Author: Ben Lobaugh (ben@lobaugh.net)
 */

function get_extension_info($ModuleId) {
	global $Db;
	$return = false;
	
	$ret = $Db->query("SELECT * FROM `".DB_PREFIX."Extensions` WHERE ModuleId='{$ModuleId}'");
	
	if($ret->num_rows > 0) {
		$return = $ret->fetch_assoc();
	}
	return $return;
}