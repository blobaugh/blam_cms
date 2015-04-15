<?php
/*
 * Sets up the browser with the site contents
 *
 * Author: Ben Lobaugh (ben@lobaugh.net)
 */


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
//$Tpl->setById('browser', $s);
echo $s;



//$Tpl->display();
//echo $Tpl;

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