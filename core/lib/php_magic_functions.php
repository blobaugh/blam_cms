<?php
/*
 * This file contains PHP Magic Functions
 * For more info on magic functions see http://php.net
 *
 * Author: Ben Lobaugh (ben@lobaugh.net)
 */

php_mystify_clients(); // Hehe, this is a joke ;)

/**
 * PHP Magic function.
 * When a class is instantiated($b = new TeddyBear())
 * without a required file, this method is automagically
 * called in an attempt to autoload the class file.
 * This function searches through the library and attempts
 * to find the file
 *
 * @param String $class_name
 **/
function __autoload($class_name) {
        global $Db, $dbprefix;

		$ret = $Db->query("SELECT * FROM `{$dbprefix}ClassIndex` WHERE Class='$class_name'");
	
		if($ret->num_rows == 0 ){
			// refresh class listing in the db
			php_update_class_table();
			// Hit this function again. We have a new list of classes to look through
			__autoload($class_name);
		}
		
		$row = $ret->fetch_assoc();
		
		// Finally, now that we know everything is kosher, include the file!
		require_once($row['Location']);
}

/**
 * Find a file in a directory and return the path to that file
 *
 * This needs to be rolled into some sort of object that can hold an index
 *
 * @param String $file
 * @param String $dir
 * @return String
 **/
function php_update_class_table($dir = BLAM_LIB) {
	global $Db;
	$dir = new DirectoryIterator($dir);
// figure out how to wait until all queries have been run for the following line to work propelryh
//	$Db->query("TRUNCATE `".DB_PREFIX."ClassIndex`");
	foreach($dir AS $f) {

		if(!$f->isDot() && $f->isDir()) {
			// Looking at a directory. Descend into it
			php_update_class_table($f->getPathname());
		} else if(!$f->isDot()){
			// Ensure that this is a class file. All class files end in .class.php
			$file = explode('.', $f->getFileName());
			if($file[1] == 'class') {
				$Db->query("INSERT INTO `".DB_PREFIX."ClassIndex` SET Class='{$file[0]}', Location='".$f->getPathname()."'");
			}
		}

	}
}

/**
 * I mostly made this function to amuse myself by changing 
 * server variables around :D Ben Lobaugh
 **/
function php_mystify_clients() {
	global $blam_cms_info;
	$_SERVER["SERVER_SIGNATURE"] = $blam_cms_info['createdby'];
	$_SERVER["SERVER_SOFTWARE"] = $blam_cms_info['name'] . ' ' . $blam_cms_info['version'] . ' ' . $blam_cms_info['codename'] . ' (' . $blam_cms_info['site'] . ')';
	$_SERVER["SERVER_ADMIN"] = "Ben Lobaugh (http://blamdesigns.com)";
}