<?php

namespace tsn\tsn\acp;

/**
 * Class main_module
 * @package tsn\tsn\acp
 */
class main_module
{
    public $u_action;

    /**
     * @param $id
     * @param $mode
     */
    public function main($id, $mode)
    {
        global $user, $template, $language, $request, $config;

        $language->add_lang('acp/common');
        $this->tpl_name = 'acp_settings';
        $this->page_title = $user->lang('TSN_EXTENSION_TITLE');
        add_form_key('tsn/tsn');

        if ($request->is_set_post('submit')) {
            if (!check_form_key('tsn/tsn')) {
                trigger_error('FORM_INVALID');
            }

            $config->set('tsn_enable_extension', $request->variable('tsn_enable_extension', 1));
            $config->set('tsn_enable_myspot', $request->variable('tsn_enable_myspot', 1));
            $config->set('tsn_enable_miniprofile', $request->variable('tsn_enable_miniprofile', 1));
            $config->set('tsn_enable_miniforums', $request->variable('tsn_enable_miniforums', 1));
            $config->set('tsn_enable_newposts', $request->variable('tsn_enable_newposts', 1));
            $config->set('tsn_enable_specialreport', $request->variable('tsn_enable_specialreport', 1));
            $config->set('tsn_specialreport_forumid', $request->variable('tsn_specialreport_forumid', 1));
            $config->set('tsn_specialreport_excerpt_words', $request->variable('tsn_specialreport_excerpt_words', 140));

            trigger_error($user->lang('TSN_SETTINGS_SAVED') . adm_back_link($this->u_action));
        }

        $template->assign_vars([
            'U_ACTION'                      => $this->u_action,
            'TSN_ENABLE_EXTENSION'          => $config['tsn_enable_extension'],
            'TSN_ENABLE_MYSPOT'             => $config['tsn_enable_myspot'],
            'TSN_ENABLE_MINIPROFILE'        => $config['tsn_enable_miniprofile'],
            'TSN_ENABLE_MINIFORUMS'         => $config['tsn_enable_miniforums'],
            'TSN_ENABLE_NEWPOSTS'           => $config['tsn_enable_newposts'],
            'TSN_ENABLE_SPECIALREPORT'      => $config['tsn_enable_specialreport'],
            'V_TSN_SPECIALREPORT_FORUMID'   => $config['tsn_specialreport_forumid'],
            'V_TSN_SPECIALREPORT_WORDCOUNT' => $config['tsn_specialreport_excerpt_words'],

        ]);
    }
}
