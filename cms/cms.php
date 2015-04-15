<?php

error_reporting(E_ALL);
require_once('Bootstrap.php');

// Find the cms template folder and load up
$ret = $Db->query("SELECT * FROM `".DB_PREFIX."Settings` WHERE `Key`='cms_template'");
if($ret->num_rows > 0) { 
	$ret = $ret->fetch_assoc();
	$ret = $ret['Value'] . '/';
} else {
	$ret = 'classic/';
}
$Tpl->setSpecialTag('CMS_HTTP_TEMPLATE_DIR', $Reg->get('http_link') . 'cms/templates/' . $ret);

/*
 * Now that we know what folder to find the cms in we need to 
 * find out if the user is actually logged on and allowed access to the cms or show the login page.
 */ 
/*
if(false || !cms_is_user_logged_in()) { 
	header('HTTP/1.1 403 Forbidden');	
	$Tpl->setSpecialTag('CMS_HTTP_TEMPLATE_DIR', $Reg->get('http_link') . 'cms/templates/' . $ret);
	$Tpl->loadFile(BLAM_CMS_TEMPLATES . $ret . "login.html"); 
//	dBug($_SESSION);
	die($Tpl);
}*/

$Tpl->loadFile(BLAM_CMS_TEMPLATES . $ret . 'template.html');

$Tpl->setTag('title', 'Blam CMS');

echo $Tpl;