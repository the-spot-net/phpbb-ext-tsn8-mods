<?php
/**
 *
 * @package       phpBB Extension - Acme Demo
 * @copyright (c) 2013 phpBB Group
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace tsn\tsn8\acp;

class main_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $cache, $request;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang('acp/common');
		$this->tpl_name = 'acp_settings';
		$this->page_title = $user->lang('TSN8_MODS_TITLE');
		add_form_key('tsn/tsn8');

		if ($request->is_set_post('submit')) {
			if (!check_form_key('tsn/tsn8')) {
				trigger_error('FORM_INVALID');
			}

			$config->set('tsn8_activate_newposts', $request->variable('tsn8_activate_newposts', 1));
			$config->set('tsn8_activate_myspot_login', $request->variable('tsn8_activate_myspot_login', 1));
			$config->set('tsn8_activate_mini_forums', $request->variable('tsn8_activate_mini_forums', 1));
			$config->set('tsn8_activate_special_report', $request->variable('tsn8_activate_special_report', 1));

			trigger_error($user->lang('TSN8_SETTINGS_SAVED') . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'U_ACTION'                => $this->u_action,
			'TSN8_ACTIVATE_NEW_POSTS' => $config['tsn8_activate_newposts'],
			'TSN8_ACTIVATE_MYSPOT_LOGIN' => $config['tsn8_activate_myspot_login'],
			'TSN8_ACTIVATE_MINI_FORUMS' => $config['tsn8_activate_mini_forums'],
			'TSN8_ACTIVATE_SPECIAL_REPORT' => $config['tsn8_activate_special_report'],
		));
	}
}
