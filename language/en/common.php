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

    'ENABLE_MYSPOT_NEW_POSTS'      => 'Display New Posts Module?',
    'ENABLE_MYSPOT_LOGIN'          => 'Display Login Module?',
    'ENABLE_MYSPOT_MINI_FORUMS'    => 'Display Mini Forum Index Module?',
    'ENABLE_MYSPOT_SPECIAL_REPORT' => 'Display Special Report Module?',
    'ENABLE_MYSPOT_MINI_PROFILE'   => 'Display Mini Profile Module?',
    'MODULE_SETTINGS'              => 'Module Settings',
    'MYSPOT'                       => 'My Spot',
    'MYSPOT_RECORD_USERS'          => 'Record Users',
    'MYSPOT_RECORD_ONLINE_USERS'   => '<strong>%1$s</strong> Users on<br />%2$s',
    'TSN8_MODS_TITLE'              => 'tsn8 Modifications',
    'TSN8_SETTINGS'                => 'tsn8 Settings',
    'TSN8_SETTINGS_SAVED'          => 'tsn8 Settings have been saved successfully!',
));
