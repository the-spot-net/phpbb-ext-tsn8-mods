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

	'ENABLE_MYSPOT_NEW_POSTS'    => 'Enable New Posts Module on My Spot page?',
	'ENABLE_MYSPOT_LOGIN'        => 'Display Login Module on My Spot page?',
	'ENABLE_MYSPOT_MINI_FORUMS'  => 'Display mini Forum Index Module on My Spot page?',

	'MYSPOT'                     => 'My Spot',

	'TSN8_MODS_TITLE'            => 'tsn8 Modifications',
	'TSN8_MYSPOT_SETTINGS'       => 'My Spot Settings',
	'TSN8_MYSPOT_SETTINGS_SAVED' => 'My Spot Settings have been saved successfully!',
));
