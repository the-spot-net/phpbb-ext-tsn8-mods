<?php
/**
 *
 * @package       phpBB Extension - Acme Demo
 * @copyright (c) 2013 phpBB Group
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace tsn\tsn8\migrations;

class release_1_0_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['tsn8_activate_newposts']);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\alpha2');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('tsn8_activate_newposts', 1)),
			array('config.add', array('tsn8_activate_myspot_login', 1)),
			array('config.add', array('tsn8_activate_mini_forums', 1)),
			array('config.add', array('tsn8_activate_special_report', 1)),
			array(
				'module.add',
				array('acp', 'ACP_CAT_DOT_MODS', 'TSN8_MODS_TITLE')
			),
			array(
				'module.add',
				array(
					'acp', 'TSN8_MODS_TITLE',
					array(
						'module_basename' => '\tsn\tsn8\acp\main_module',
						'modes'           => array('settings'),
					),
				)
			),
		);
	}
}
