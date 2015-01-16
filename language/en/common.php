<?php
/**
 *
 * @package       phpBB Extension - Acme Demo
 * @copyright (c) 2013 phpBB Group
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

if (!defined('IN_PHPBB')) {
	exit;
}

if (empty($lang) || !is_array($lang)) {
	$lang = array();
}

$lang = array_merge($lang, array(

	'ENABLE_MYSPOT_NEW_POSTS' => 'Enable New Posts Module on My Spot page?',

	'MYSPOT'                   => 'My Spot',

	'TSN8_MODS_SETTINGS'       => 'tsn8 Settings',
	'TSN8_MODS_SETTINGS_SAVED' => 'tsn8 Settings have been saved successfully!',
	'TSN8_MODS_TITLE'          => 'tsn8 Modifications',
));
