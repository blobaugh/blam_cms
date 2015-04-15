<?php
/*
 * This file sets up the cms portion of the site. 
 * The cms backend is completely separate from the frontend display.
 * Only the core folder is shared between the two
 *
 * Author: Ben Lobaugh (ben@lobaugh.net)
 */

require_once('../core/lib/blam_magic_functions.php'); // Should check to make sure the site is installed. Has class autoloader
require_once('lib/cms_functions.php');




/*
 * Ensures that a user is in fact logged in and can use the cms.
 * If not, the user will be bumped to the login page and the cms will die.
 * If so, the users information will be stored in $User which is a Registry
 */
//cms_check_user_login();