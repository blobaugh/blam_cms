<?php
/**
 * This file controls the loading of cms modules
 *
 * It desperately needs to be done a better way
 *
 * Author: Ben Lobaugh (ben@lobaugh.net)
 **/

require_once("Bootstrap.php");

// Get the info on this module from the db
$ret = $Db->query("SELECT * FROM `".DB_PREFIX."Modules` WHERE Name='{$_GET['what']}'");
$ret = $ret->fetch_assoc();


if($_GET['where'] == 'workspace' && $ret['WorkspaceLink'] != '') require_once(BLAM_MODULES . str_replace(' ', '_', strtolower($_GET['what'])) . '/' . $ret['WorkspaceLink']);

if($_GET['where'] == 'browser' && $ret['FiletreeLink'] != '') require_once(BLAM_MODULES . str_replace(' ', '_', strtolower($_GET['what'])) . '/' . $ret['FiletreeLink']);

