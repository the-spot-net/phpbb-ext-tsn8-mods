<?php
/**
*
* @package phpBB Extension - Acme Demo
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tsn\tsn8\acp;

class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\tsn\tsn8\acp\main_module',
			'title'		=> 'ACP_DEMO_TITLE',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'settings'	=> array('title' => 'ACP_DEMO', 'auth' => 'ext_tsn/tsn8 && acl_a_board', 'cat' => array('ACP_DEMO_TITLE')),
			),
		);
	}
}
