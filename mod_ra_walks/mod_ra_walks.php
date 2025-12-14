<?php

/**
 * @module	RA Sidebar
 * @author	Charlie Bigley
 * @website	https://demo.stokeandnewcastleramblers.org.uk
 * @copyleft  Copyleft 2021 Charlie Bigley webmaster@stokeandnewcastleramblers.org.uk All rights reserved.
 * @license	http://www.gnu.org/licenses/gpl.html GNU/GPL
 * 15/01/21 CB created
*/

// no direct access
defined( "_JEXEC" ) or die( "Restricted access" );

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require(JModuleHelper::getLayoutPath('mod_ra_sidebar', $params->get('layout', 'default'))); 
