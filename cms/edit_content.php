<?php

error_reporting(E_ALL);
require_once('Bootstrap.php');


$Tpl->loadFile(BLAM_CMS . 'templates/classic/template.html');


$Tpl->setTag('title', 'Blam CMS');


/*
 * Load up all the modules
 *
 * Modules will display tabs and have sub-functionality
 */
$ret = $Db->query("SELECT * FROM `".DB_PREFIX."Modules` WHERE ParentModuleId='0' ORDER BY `order`");
$s = '';
$js = '';
while($r = $ret->fetch_assoc()) {
	$s .= "<span class=\"tab\">{$r['Name']}</span> ";
	
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
$Tpl->setById('tabs', $s);
$Tpl->setSpecialTag('tab_modules', $js);

/*
 * Load up the initial sub-module list from the Sites tab
 * Should be ModuleId=1
 */
$ret = $Db->query("SELECT * FROM `".DB_PREFIX."Modules` WHERE ParentModuleId='1' ORDER BY `order`");
$s = '';
while($r = $ret->fetch_assoc()) {
	$s .= "<span class=\"clickable\">{$r['Name']}</span> ";
}
$Tpl->setById('top-bar', $s);

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
