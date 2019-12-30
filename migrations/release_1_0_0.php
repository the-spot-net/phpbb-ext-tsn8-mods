<?php
/**
 * @package       phpBB Extension - Acme Demo
 * @copyright (c) 2013 phpBB Group
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace tsn\tsn8\migrations;

use phpbb\db\migration\migration;

class release_1_0_0 extends migration
{
    static public function depends_on()
    {
        return ['\phpbb\db\migration\data\v310\alpha2'];
    }

    public function effectively_installed()
    {
        return isset($this->config['tsn8_activate_newposts']);
    }

    public function update_data()
    {
        return [
            ['config.add', ['tsn8_activate_newposts', 1]],
            ['config.add', ['tsn8_activate_myspot_login', 1]],
            ['config.add', ['tsn8_activate_mini_forums', 1]],
            ['config.add', ['tsn8_activate_mini_profile', 1]],
            ['config.add', ['tsn8_activate_special_report', 1]],
            [
                'module.add',
                ['acp', 'ACP_CAT_DOT_MODS', 'TSN8_MODS_TITLE'],
            ],
            [
                'module.add',
                [
                    'acp',
                    'TSN8_MODS_TITLE',
                    [
                        'module_basename' => '\tsn\tsn8\acp\main_module',
                        'modes'           => ['settings'],
                    ],
                ],
            ],
        ];
    }
}
