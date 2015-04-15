<?php
/*
 * This file is used to get elements needed for the cms by
 * the templates and whatever else needs it.
 *
 * This api returns JSON objects
 *
 * Please note that many of the functions in here require a user to 
 * be authenticated, and that can be found in $_SESSION['User']
 *
 * Author: Ben Lobaugh (ben@lobaugh.net)
 */

require_once('Bootstrap.php');

if(isset($_GET['q'])) {
	switch($_GET['q']) {
		case 'login':
			echo json_encode(cms_validate_user($_GET['user'], $_GET['pass']));
			break;
		case 'moduleChildren':
			echo get_sub_extension_html($_GET['id']);//json_encode($s);
			break;
		case 'moduleInfo':
			if($_GET['type'] == 'json') {
				echo json_encode(get_extension_info($_GET['id']));
			}
			break;
		case 'modulePanes':
			if($_GET['type'] == 'json') {
				echo json_encode(get_extension_panes($_GET['id']));
			}
			break;
	}
}

exit();