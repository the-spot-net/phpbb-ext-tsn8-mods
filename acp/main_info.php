<?php
/**
 * @package       phpBB Extension - Acme Demo
 * @copyright (c) 2013 phpBB Group
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace tsn\tsn8\acp;

/**
 * Class main_info
 * @package tsn\tsn8\acp
 */
class main_info
{
    /**
     * @return array
     */
    public function module()
    {
        return [
            'filename' => '\tsn\tsn8\acp\main_module',
            'title'    => 'TSN_EXTENSION_TITLE',
            'version'  => '2.0.0',
            'modes'    => [
                'settings' => [
                    'title' => 'TSN_SETTINGS',
                    'auth'  => 'ext_tsn/tsn8 && acl_a_board',
                    'cat'   => ['TSN_EXTENSION_TITLE'],
                ],
            ],
        ];
    }
}
