<?php
/*
 * This file contains all of the default available template
 * variables available to designers
 *
 * Author: Ben Lobaugh (ben@lobaugh.net)
 */

$vars = array(

'CMS_TEMPLATE_DIR' => BLAM_CMS . 'templates/classic/',
'HTTP_MODULES' => $Reg ->get('http_link') . 'modules/',
'HTTP_CMS' => $Reg->get('http_link') . 'cms/',
'SITE_TITLE' => $Reg->get('SiteTitle'),
'SITE_NAME' => $Reg->get('SiteTitle')
	
);
$Tpl->setSpecialTag('Array', $vars);
unset($vars);