<?php
/* 
 * This file contains functions specific to the cms backend
 *
 * Author: Ben Lobaugh (ben@lobaugh.net)
 */


/*
 * This function needs to check to ensure a valid user is logged in.
 * If not it should show the login page, if so it should place the
 * user's info somewhere useful
 *
 * @return Boolean
 */
function cms_is_user_logged_in() {
	global $Db;
	$ret = false;
// 38eba72bd5f84e5d7c1736a3ee47db61	
	if(isset($_SESSION['User'])) {
		// Supposedly a user is logged in. Make sure they are valid
		//$r = $Db->query("SELECT MD5(MD5(CONCAT(email,password))) FROM `".DB_PREFIX."User` WHERE MD5(MD5(CONCAT(email,password))) <> '" . $_SESSION['User']['Hash'] . "'");
		$r = $Db->query("SELECT TRUE FROM `".DB_PREFIX."User` WHERE md5(md5(concat(email,password))) = '".$_SESSION['User']['Hash']."'");
		$r = $r->fetch_assoc();
		if(isset($r['TRUE'])) $ret = true;
	}
	return $ret;
}

/*
 * Attempts to validate a user.
 * If successful a $_SESSION['User'] will be created with the
 * user's information
 */
function cms_validate_user($user, $pass) {
	global $Db;
	$ret = false;
	$user = $Db->escapeString($user);
	$pass = $Db->escapeString($pass);
	
	$r = $Db->query("SELECT *, md5(md5(concat(email,password))) AS Hash FROM `".DB_PREFIX."User` WHERE md5(md5(concat(email,password))) = md5(md5(concat('$user', md5(md5('$pass')))))");
	if($r->num_rows > 0) {
		$_SESSION['User'] = $r->fetch_assoc();
		unset($_SESSION['User']['Password']);
		$ret = true;
	} else {
		unset($_SESSION['User']);
	}
	
	return $ret;
}

function get_extension_html() {
	global $Db, $Reg;
	
	$ret = $Db->query("SELECT * FROM `".DB_PREFIX."Extensions` WHERE ParentModuleId='0' ORDER BY `order`");
	$s = ''; 
	$js = '';
	while($r = $ret->fetch_assoc()) {
		$s .= "<span class=\"tab\" href=\"".HTTP_CMS_API."?type=html&q=moduleChildren&id={$r['ModuleId']}\">{$r['Name']}</span> ";

		// If we need to add any javascript let's do it here
		if($r['WorkspaceLink'] || $r['FiletreeLink']) {
			$js .= "if($(this).text() == '{$r['Name']}') {";
			if($r['WorkspaceLink'] != '') {
				$js .= "$('#workspace').load('".$Reg->get('http_link')."modules/".strtolower($r['Name'])."/{$r['WorkspaceLink']}');";
			}
			if($r['FiletreeLink'] != '') {
				$js .= "\n$('#browser').load('".$Reg->get('http_link')."modules/".strtolower($r['Name'])."/{$r['FiletreeLink']}');";
			}
			$js .= "}";
		}
	}
	return $s;
}

function get_sub_extension_html($ParentId) {
	global $Db;
	
	$ret = $Db->query("SELECT * FROM `".DB_PREFIX."Extensions` WHERE ParentModuleId='{$ParentId}' ORDER BY `order`");
	$s = '<script type="text/javascript">
		$(\'span.sub_module\').click( function()  { load_module($(this).attr(\'id\'))   });
	</script>'; 
	$js = '';
	$s .= "";
	while($r = $ret->fetch_assoc()) {
		$s .= "<span class=\"clickable sub_module\"  id=\"{$r['ModuleId']}\">{$r['Name']}</span>";
	}
	return $s;
}

function get_extension_panes($ModuleId) {
	$data = array();
	
	$info = get_extension_info($ModuleId);

	if($info['WorkspaceLink'] != '') { 
		$data['Workspace'] = file_get_contents(BLAM_EXTENSIONS . $info['Identifier'] . '/' . $info['WorkspaceLink']);
	}
	if($info['FiletreeLink'] != '') {
		$data['Filetree'] = file_get_contents(BLAM_EXTENSIONS . $info['Identifier'] . '/' . $info['FiletreeLink']);
	}
	
	return $data;
}