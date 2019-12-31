<?php

namespace tsn\tsn8\migrations;

use phpbb\db\migration\migration;

/**
 * Class release_2_0_0
 * Handles some changes to the database for release 2.0.0
 * @package tsn\tsn8\migrations
 */
class release_2_0_0 extends migration
{
    /**
     * @return array
     */
    static public function depends_on()
    {
        return ['\tsn\tsn8\migrations\release_1_0_0'];
    }

    /**
     * @return bool
     */
    public function effectively_installed()
    {
        return isset($this->config['tsn_activate_extension']);
    }

    /**
     * @return array
     */
    public function update_data()
    {
        return [
            // Replace the old setting with new generic settings
            ['config.add', ['tsn_enable_extension', 1]],
            ['config.add', ['tsn_enable_newposts', 1]],
            ['config.add', ['tsn_enable_myspot', 1]],
            ['config.add', ['tsn_enable_miniforums', 1]],
            ['config.add', ['tsn_enable_miniprofile', 1]],
            ['config.add', ['tsn_enable_specialreport', 1]],
            // Remove the old settings
            ['config.remove', ['tsn8_activate_newposts']],
            ['config.remove', ['tsn8_activate_myspot_login']],
            ['config.remove', ['tsn8_activate_mini_forums']],
            ['config.remove', ['tsn8_activate_mini_profile']],
            ['config.remove', ['tsn8_activate_special_report']],
        ];
    }
}
