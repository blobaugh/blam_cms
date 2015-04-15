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
$Tpl->setSpecialTag('CMS_HTTP_TEMPLATE_DIR', $Reg->get('http_link') . 'cms/templates/' . $ret);
$Tpl->loadFile(BLAM_CMS_TEMPLATES . $ret . 'template.html');

$Tpl->setTag('title', 'Blam CMS');


/*
 * Load up all the modules
 *
 * Modules will display tabs and have sub-functionality
 */
 $Tpl->setById('tabs', get_extension_html());
//$Tpl->setSpecialTag('tab_modules', $js);


/*
 * Load up initial content for file tree
 * This comes from the content in the Content table
 */

$ret = $Db->query("SELECT * FROM `".DB_PREFIX."Content` WHERE ParentContentId='0'");
$s = '';
while($r = $ret->fetch_assoc()) {
	if($sub = tree_content($r['ContentId'])) {
		 $class='folder';
		 $li = 'class="closed"';
	} else {
		$class='file';
		$li = '';
	}
	$s .= "<li $li><span class=\" $class clickable\" href=\"edit_content.php?id={$r['ContentId']}\">{$r['Title']}</span> ";
	
	$s .= $sub;
	
	$s .= "</li>";
}
$Tpl->setById('browser', $s);



//$Tpl->display();
echo $Tpl;

function tree_content($parent_id) {
	global $Db;
	$s = '';
	$ret = $Db->query("SELECT * FROM `".DB_PREFIX."Content` WHERE ParentContentId='$parent_id'");
	if($ret->num_rows > 0) { 
		$s = '<ul>';
		while($r = $ret->fetch_assoc()) {
			if($sub = tree_content($r['ContentId'])) {
				 $class='folder';
				 $li = 'class="closed"';
			} else {
				$class='file';
				$li = '';
			}
			$s .= "<li $li><span class=\" file clickable\" href=\"edit_content.php?id={$r['ContentId']}\">{$r['Title']}</span> ";

			$s .= $sub;
			$s .= "</li>";
		}
		$s .= "</ul>";
	} 
	return $s;
}
