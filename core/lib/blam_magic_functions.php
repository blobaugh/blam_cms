<?php
/*
 * This file contains several  blam 'magic functions'. They are not quite
 * as magic as PHP magic functions, but they are awesome in their own right
 * never the less
 * 
 * Author: Ben Lobaugh (ben@lobaugh.net)
 */
session_start();

require_once('dBug.php'); // Love this debugger!


// Files and folders required to exist in the system
$base_files = array();
$base_dirs = array('cache', 'core');

// Just to make things a bit easier, some path constants
define('DOC_ROOT', blam_doc_root());
define('BLAM_CORE', DOC_ROOT . 'core/');
define('BLAM_LIB', BLAM_CORE . 'lib/');
define('BLAM_CMS', DOC_ROOT . 'cms/');
define('BLAM_CMS_TEMPLATES', BLAM_CMS . 'templates/');
define('BLAM_CACHE', DOC_ROOT . 'cache/');
define('BLAM_EXTENSIONS', DOC_ROOT . 'extensions/');

// More useful constants
define('HTTP_ROOT', blam_http_root());
define('HTTP_CMS', HTTP_ROOT . 'cms/');
define('HTTP_CMS_API', HTTP_CMS . 'api.php');

// This can be used to make sure a file is running inside the site
define('BLAM_SITE_RUNNING', 1);

// Check the site to make sure everything is cosher
blam_site_sanity_check();

// Pull in the config file here. Don't forget to erase the contents later
require_once(BLAM_CORE . 'blam_cms_info.php');
require_once(DOC_ROOT . 'config.php');
define('DB_PREFIX', $dbprefix);

// Setup the database connection
require_once(BLAM_LIB . 'Database/Database.class.php');
$Db = Database::getDatabase();
unset($dblocation, $dbuser, $dbpass, $dbname); // Rid ourselves of this pesky password problem right away

// Pull in some magic to help with class loading
require_once('php_magic_functions.php');

//require_once(BLAM_LIB . 'Registry.class.php');
//require_once(BLAM_LIB . 'Encryption.class.php');
$Reg = Registry::getRegistry();

// Find the SiteId for this site. Based off HTTP_HOST
$ret = blam_site_info();
define('SITE_ID', $ret['SiteId']);
$Reg->set('http_link', blam_http_root());
$Reg->set('SiteId', $ret['SiteId']);
$Reg->set('SiteTitle', $ret['Title']);



// Setup the encryption object. This uses 'encryption_key' from the Settings table
$ret = $Db->query("SELECT Value FROM `".DB_PREFIX."Settings` WHERE `Key`='encryption_key' AND `SiteId`='".SITE_ID."' ");
$ret = $ret->fetch_assoc();
$Enc = new Encryption($ret['Value']);

$Tpl = new TemplateManager();

/**
 * Find the document root on the local file system.
 * This is accomplished by looking at $_SERVER['DOCUMENT_ROOT'].
 * Starting from the last folder in the list and going backwards
 * until all the items in the base array are found in one directory
 *
 * @return String - Path of Document Root
 */
function blam_doc_root() {
        // These items will be looked for in the current dir. If present it must be base
		global $base_files, $base_dirs;

        $path = $_SERVER['SCRIPT_FILENAME']; // Where the current script is executing
        while(strlen($path) > 0) { // As long as we have a path
                $count = 0; // If this is 6 the base is found
                if (is_dir($path)) { // If we are in a directory
                        foreach($base_dirs AS $dir) { // Look through the base dirs
                                if(is_dir("$path/$dir")) $count++; // Count up the dirs to make sure the exist
                        }

                        if($count == count($base_dirs)) { // If all the dirs aren't there don't bother looking at the files
                                foreach($base_files AS $file) {// Look through the base files
                                        if(is_file("$path/$file")) $count++;
                                }
                        }
                }

                if ($count == count($base_dirs)+count($base_files)) { // Have all the files been found?
                        return $path;
                }


                $path = preg_replace("#/$#", '', $path);
                $path = preg_replace("#[^/]+$#", '', $path);
            }




        // If have reached this point something really bad happened!
        $error_message = "The site base could not be found. Please check your installation for the following files or contact your site administrator. The following files MUST be in the root of your site:
                          <ul>";
                                foreach($base_files AS $r) $error_message .= "\n<li>$r</li>";
								foreach($base_dirs AS $r) $error_message .= "\n<li>$r</li>";
                          $error_message .= "</ul>
                          <br /><br />global_functions:find_doc_root";
      //  echo ($error_message);
}

function blam_http_root() {
	$doc_root = explode('/', $_SERVER['DOCUMENT_ROOT']);
	$blam_root = explode('/', DOC_ROOT);
	$path = array_diff($blam_root,$doc_root);
	
	$addy = "http://" . $_SERVER['HTTP_HOST'] . "/";
	
	foreach($path AS $p) {
		$addy .= "$p/";
	}
	return $addy;
}

/*
 * Checks the site to ensure some key files and directories are present.
 * Files and Directories to check come from $base_files and $base_dirs
 * which can be found at the top of this file.
 * If something is missing the site will throw a fatal error and just stop 
 * working :D
 */
function blam_site_sanity_check() {
	global $base_files, $base_dirs;
	$err = '';
	// Check for config file. If it is not there then the site probably is not installed
	if(!is_file(DOC_ROOT . 'config.php')) $err = ('<li><span style="color: red; font-weight: bold">No Config File Found!</span> Please create a config file and try again.</li>');
	
	// Check for required directories and make sure the permissions are correct
	foreach($base_files AS $r) {
		if(!is_file(DOC_ROOT . $r)) $err .= ('<li><span style="color: red; font-weight: bold">' . $r . ' File Not Found!</span> Please check your install and try again.</li>');
	}
	
	foreach($base_dirs AS $r) {
		if(!is_dir(DOC_ROOT . $r)) $err .= ('<li><span style="color: red; font-weight: bold">' . $r . ' Directory Not Found!</span> Please check your install and try again.</li>');
	}
	
	if($err != '') {
		blam_throw_fatal_error('<ul>' . $err . '</ul>');
	}
}

function blam_site_info() {
	global $Db, $dbprefix;
	
	$ret = $Db->query("SELECT * FROM `{$dbprefix}Sites` WHERE Address='{$_SERVER['HTTP_HOST']}'");
	
	if($ret->num_rows == 0) blam_throw_fatal_error("SiteId could not be found! This site has not been configured. Please check your install and try again. blam_magic_functions:~51 ");
	$ret = $ret->fetch_assoc();
	
	// Check to see if this site is an alias to another. If it is then we need to find that sites address
	if($ret['AliasTo'] != 0) {
		$aliased = $Db->query("SELECT * FROM `{$dbprefix}Sites` WHERE SiteId='{$ret['AliasTo']}'"); 
		$aliased = $aliased->fetch_assoc();
		$aliased['AliasedFrom'] = $ret;
		$ret = $aliased;
	}
	
	return $ret;
}

/**
 * Displays an error message then exits the running script
 */
function blam_throw_fatal_error($error_message) {
        $error_message = "<html><body style=\"background-color:grey\"><center><div style=\"background-color:yellow; border: 1px solid red; width: 60%; text-align: left; padding: 5px\"><h2 style=\"color: red;\">Fatal Error!</h2><br />" . $error_message . "</div></center></body></html>";
        die($error_message);
}

/**
 * Simpler to use wrapper for dBug
 **/
function dBug($what) {
	new dBug($what);
}

// Cleanup
unset($base_files);
unset($base_dirs);
unset($ret);

// These files should be included everywhere, but after the magic has happened
require_once('global_functions.php');
require_once('default_template_vars.php');