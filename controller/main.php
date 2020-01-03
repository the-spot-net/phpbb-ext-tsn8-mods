<?php

namespace tsn\tsn\controller;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\db\driver\factory;
use phpbb\language\language;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class main
 * @package tsn\tsn\controller
 */
class main
{
    /** @var \phpbb\auth\auth */
    protected $auth;
    /* @var \phpbb\config\config */
    protected $config;
    /** @var \phpbb\db\driver\driver */
    protected $db;
    /* @var \phpbb\controller\helper */
    protected $helper;
    /** @var \phpbb\language\language */
    protected $language;
    /* @var \phpbb\template\template */
    protected $template;
    /* @var \phpbb\user */
    protected $user;

    /** @var string */
    private static $boardUrl;
    /** @var string|null */
    private static $phpbbRootPath = null;
    /** @var false|string */
    private static $phpEx = 'php';

    /**
     * main constructor.
     *
     * @param \phpbb\auth\auth         $auth
     * @param \phpbb\config\config     $config
     * @param \phpbb\db\driver\factory $db
     * @param \phpbb\controller\helper $helper
     * @param \phpbb\language\language $language
     * @param \phpbb\template\template $template
     * @param \phpbb\user              $user
     */
    public function __construct(auth $auth, config $config, factory $db, helper $helper, language $language, template $template, user $user)
    {
        $this->auth = $auth;
        $this->config = $config;
        $this->db = $db;
        $this->helper = $helper;
        $this->language = $language;
        $this->template = $template;
        $this->user = $user;

        self::$phpbbRootPath = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
        self::$phpEx = substr(strrchr(__FILE__, '.'), 1);
        self::$boardUrl = generate_board_url() . '/';
    }

    /**
     * @param string $route the Variable URI in /tsn/ajax/{route}
     *
     * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
     * @see \tsn\tsn\controller\constants::ROUTE_AJAX_SPECIAL_REPORT
     */
    public function ajax($route)
    {

        switch ($route) {
            case constants::SLUG_SPECIAL_REPORT:
                $output = $this->moduleSpecialReport();
                break;
            default:
                $output = new Response('Unsupported route', 404);
                break;
        }

//        $l_message = !$this->config['acme_demo_goodbye'] ? 'DEMO_HELLO' : 'DEMO_GOODBYE';
//        $this->template->assign_var('DEMO_MESSAGE', $this->user->lang($l_message, $name));
//
//        return $this->helper->render('sync_settings.html', $name);

        return $output;
    }

    /**
     * @param null|string $field
     *
     * @return mixed|\phpbb\config\config
     */
    public function getConfig($field = null)
    {
        return (is_null($field))
            ? $this->config
            : (isset($this->config[$field]))
                ? $this->config[$field]
                : null;
    }

    /**
     * @return \phpbb\db\driver\driver|\phpbb\db\driver\factory
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * https://outlook.office.com/webhook/78c7947c-7ffe-439a-b3ff-dcaa47e301cf@e80bb92b-bccd-4462-80c7-ead62e3ab04b/IncomingWebhook/23a636d08fef4e55babe020f9334bef4/0c9e2c2d-619f-4085-b58c-04a54ab19289
     * @return \Symfony\Component\HttpFoundation\Response
     * @see \tsn\tsn\controller\constants::ROUTE_TSN
     */
    public function index()
    {
        $this->initUserAuthentication();

        // TODO Render the miniforums
        $this->template->assign_vars([
            'S_ALLOW_MINI_PROFILE'   => !empty($this->config['tsn8_activate_mini_profile']),
            'S_ALLOW_MYSPOT_LOGIN'   => !empty($this->config['tsn8_activate_myspot_login']),
            'S_ALLOW_MINI_FORUMS'    => !empty($this->config['tsn8_activate_mini_forums']),
            'S_ALLOW_SPECIAL_REPORT' => !empty($this->config['tsn8_activate_special_report']),
            'S_ALLOW_NEW_POSTS'      => !empty($this->config['tsn8_activate_newposts']),
            'S_USER_ID'              => $this->user->data['user_id'],
        ]);

        return $this->helper->render('@tsn_tsn/tsn_myspot.html', $this->language->lang('MYSPOT'));
    }

    /**
     * Sets up user permissions commonly for all pages & modules
     */
    private function initUserAuthentication()
    {
        // Setup the permissions...
        $this->user->session_begin();
        $this->auth->acl($this->user->data);
        $this->user->setup(['viewforum', 'memberlist', 'groups']);

        $this->template->assign_vars([
            'SERVER_PROTOCOL' => $this->config['server_protocol'],
            'SERVER_DOMAIN'   => $this->config['server_name'],
            'SERVER_PORT'     => (!in_array((int)$this->config['server_port'], [0, 80, 443])) ? ':' . $this->config['server_port'] : '',
            'T_EXT_PATH'      => '/phorums/ext/tsn/tsn/styles/all/theme',

            'U_TSN_MYSPOT' => self::$boardUrl . constants::ROUTE_TSN,
        ]);

        // Setup constant shell data...
        $this->moduleMiniForums();
    }

    /**
     * Run the work to generate the profile fields
     */
    private function moduleMiniForums()
    {
        if (!function_exists('display_forums')) {
            include_once('includes/functions_display.' . self::$phpEx);
        }

        display_forums('', $this->config['load_moderators']);

        $this->submoduleUserGroupLegend();
        $this->submoduleUserBirthdays();
        $this->submoduleUsersOnline();

        $this->template->assign_vars([
            'TOTAL_POSTS'             => $this->language->lang('TOTAL_POSTS_COUNT', (int)$this->getConfig('num_posts')),
            'TOTAL_FORUM_POSTS'       => number_format((int)$this->getConfig('num_posts'), 0),
            'TOTAL_TOPICS'            => $this->language->lang('TOTAL_TOPICS', (int)$this->getConfig('num_topics')),
            'TOTAL_FORUM_TOPICS'      => number_format((int)$this->getConfig('num_topics'), 0),
            'TOTAL_USERS'             => $this->language->lang('TOTAL_USERS', (int)$this->getConfig('num_users')),
            'TOTAL_FORUM_USERS'       => number_format((int)$this->getConfig('num_users'), 0),
            'NEWEST_USER'             => $this->language->lang('NEWEST_USER', get_username_string('full', $this->getConfig('newest_user_id'), $this->getConfig('newest_username'), $this->getConfig('newest_user_colour'))),
            'FORUM_IMG'               => $this->user->img('forum_read', 'NO_UNREAD_POSTS'),
            'FORUM_UNREAD_IMG'        => $this->user->img('forum_unread', 'UNREAD_POSTS'),
            'FORUM_LOCKED_IMG'        => $this->user->img('forum_read_locked', 'NO_UNREAD_POSTS_LOCKED'),
            'FORUM_UNREAD_LOCKED_IMG' => $this->user->img('forum_unread_locked', 'UNREAD_POSTS_LOCKED'),
            'S_LOGIN_ACTION'          => append_sid(self::$phpbbRootPath . 'ucp.' . self::$phpEx, 'mode=login'),
            'U_SEND_PASSWORD'         => ($this->getConfig('email_enable'))
                ? append_sid(self::$phpbbRootPath . 'ucp.' . self::$phpEx, 'mode=sendpassword')
                : '',
            'S_DISPLAY_BIRTHDAY_LIST' => (bool)$this->getConfig('load_birthdays'),
            'S_INDEX'                 => true, // Not sure what this is for...
            'S_MYSPOT_LOGIN_REDIRECT' => '<input type="hidden" name="redirect" value="' . append_sid(constants::ROUTE_TSN, '', true, $this->user->session_id) . '">',
            'U_MARK_FORUMS'           => ($this->user->data['is_registered'] || $this->getConfig('load_anon_lastread'))
                ? append_sid(self::$phpbbRootPath . 'index.' . self::$phpEx, 'hash=' . generate_link_hash('global') . '&amp;mark=forums&amp;mark_time=' . time())
                : '',
            'U_MCP'                   => ($this->auth->acl_get('m_') || $this->auth->acl_getf_global('m_'))
                ? append_sid(self::$phpbbRootPath . 'mcp.' . self::$phpEx, 'i=main&amp;mode=front', true, $this->user->session_id)
                : '',
        ]);
    }

    /**
     * Handles the render of the Special Report AJAX request from tsn/special-report
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function moduleSpecialReport()
    {
        $this->initUserAuthentication();

        // Get the minimal Latest Topic Info, if any
        if ($topicRow = query::checkForSpecialReportLatestTopic($this)) {

            // Get the necessary topic info, since it exists

            if ($topicRow = query::getSpecialReportLatestTopicInfo($this, $topicRow['topic_id'])) {

                /**
                 * Available Variable List:
                 * $topicRow['topic_id'];
                 * $topicRow['post_id'];
                 * $topicRow['topic_title'];
                 * $topicRow['topic_poster'];
                 * $topicRow['post_text'];
                 * $topicRow['bbcode_uid'];
                 * $topicRow['bbcode_bitfield'];
                 * $topicRow['enable_smilies'];
                 * $topicRow['poster_id'];
                 * $topicRow['username'];
                 * $topicRow['topic_views'];
                 * $topicRow['topic_posts_approved'];
                 * $topicRow['topic_time'];
                 */

                $topicStatusRow = query::getTopicReadStatus($this, $this->user->data['user_id'], $topicRow['topic_id'], $this->config['tsn_specialreport_forumid']);

                // Determine if this is an unread topic, based on the timestamps
                $markTime = $topicStatusRow['f_mark_time'];
                $rowSet = [
                    $topicRow['topic_id'] => $topicStatusRow,
                    'mark_time'           => $markTime,
                ];

                $topicTrackingInfo = get_topic_tracking($this->config['tsn_specialreport_forumid'], [$topicRow['topic_id']], $rowSet, [$this->config['tsn_specialreport_forumid'] => $markTime]);
                $isUnreadTopic = (isset($topicTrackingInfo[$topicRow['topic_id']]) && $topicRow['topic_time'] > $topicTrackingInfo[$topicRow['topic_id']]);

                // Prepare the post content; Replaces UIDs with BBCode and then convert the Post Content to an excerpt...
                $words = explode(' ', generate_text_for_display($topicRow['post_text'], $topicRow['bbcode_uid'], $topicRow['bbcode_bitfield'], 1));

                $this->template->assign_vars([
                    'L_HEADLINE'         => censor_text($topicRow['topic_title']),
                    'L_POST_AUTHOR'      => get_username_string('full', $topicRow['topic_poster'], $topicRow['username'], $topicRow['user_colour']),
                    'L_POST_BODY'        => (count($words) > $this->config['tsn_specialreport_excerpt_words'])
                        ? implode(' ', array_slice($words, 0, $this->config['tsn_specialreport_excerpt_words'])) . '... '
                        : implode(' ', $words),
                    'L_POST_DATE'        => $this->user->format_date($topicRow['topic_time']),
                    'L_POST_META'        => $this->language->lang('SPECIAL_REPORT_VIEWS_COMMENTS_COUNT', $topicRow['topic_views'], (int)$topicRow['topic_posts_approved'] - 1),
                    'S_UNREAD_TOPIC'     => $isUnreadTopic,
                    'U_CONTINUE_READING' => append_sid(self::$phpbbRootPath . 'viewtopic.' . self::$phpEx, "p=" . $topicRow['post_id']),
                    'U_HEADLINE'         => append_sid(self::$phpbbRootPath . 'viewtopic.' . self::$phpEx, "p=" . $topicRow['post_id']),
                    'U_USER_PROFILE'     => append_sid(self::$phpbbRootPath . 'memberlist.' . self::$phpEx, "mode=viewprofile&u=" . $topicRow['topic_poster']),
                ]);

                $this->submoduleUserAvatar($topicRow['topic_poster']);

                $output = $this->helper->render('@tsn_tsn/tsn_special_report.html', $this->language->lang('TSNSPECIALREPORT'));

            } else {
                $output = new Response('Could not find topic with the requested topic ID', 200);
            }
        } else {
            $output = new Response('No topics posted to the Special Report forum', 200);
        }

        return $output;
    }

    /**
     * Run the submodule work for a User Avatar
     *
     * @param int $userId
     */
    private function submoduleUserAvatar(int $userId)
    {
        if ($avatarRow = query::getUserAvatar($this, $userId)) {

            // Prepare the avatar image...
            $avatarImage = preg_replace('/(\.\.\/)+?/', './', phpbb_get_user_avatar([
                'avatar'        => $avatarRow['user_avatar'],
                'avatar_type'   => $avatarRow['user_avatar_type'],
                'avatar_width'  => $avatarRow['user_avatar_width'],
                'avatar_height' => $avatarRow['user_avatar_height'],
            ]));

            $this->template->assign_vars([
                'I_AVATAR_IMG' => $avatarImage,
            ]);
//        } else {
//             new Response('Unable to find user avatar settings', 200);
        }
    }

    /**
     * Generates the Birthday List, if required.
     */
    private function submoduleUserBirthdays()
    {
        if ($this->getConfig('load_birthdays') && $this->getConfig('allow_birthdays') && $this->auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel')) {

            $time = $this->user->create_datetime();
            $cursor = query::getUserBirthdaysCursor($this, $time);

            while ($userRow = $this->db->sql_fetchrow($cursor)) {

                // Some users may not put a year in their profile
                $birthday_year = (int)substr($userRow['user_birthday'], -4);

                $this->template->assign_block_vars('birthdays', [
                    'USERNAME' => get_username_string('full', $userRow['user_id'], $userRow['username'], $userRow['user_colour']),
                    'AGE'      => ($birthday_year)
                        ? max(0, (int)$time->format('Y') - $birthday_year)
                        : '',
                ]);

                // 3.0 Compatibility... maybe a CSV?
//                if ($age = (int)substr($row['user_birthday'], -4)) {
//                    $birthday_list[] = $birthday_username . (($birthday_year) ? ' (' . $birthday_age . ')' : '');
//                }
            }
            query::freeCursor($this, $cursor);
        }
    }

    /**
     * Generate a Template Block Var array of User Group Legend Data
     */
    private function submoduleUserGroupLegend()
    {
        $cursor = query::getUserGroupLegendCursor($this, $this->user->data['user_id'], $this->auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel'));

        while ($userGroupRow = $this->db->sql_fetchrow($cursor)) {

            $showUrl = ($userGroupRow['group_name'] != 'BOTS' && $this->user->data['user_id'] == ANONYMOUS && $this->auth->acl_get('u_viewprofile'));

            // Add the User Group Legends to a block var in a loop
            $this->template->assign_block_vars('groupLegend', [
                'COLOR' => ($userGroupRow['group_colour']) ? '#' . $userGroupRow['group_colour'] : '',
                'NAME'  => ($userGroupRow['group_type'] == GROUP_SPECIAL)
                    ? $this->language->lang('G_' . $userGroupRow['group_name'])
                    : $userGroupRow['group_name'],
                'URL'   => ($showUrl)
                    ? append_sid(self::$phpbbRootPath . constants::ROUTE_GROUP . '/' . $userGroupRow['group_id'])
                    : '',
            ]);
        }
        query::freeCursor($this, $cursor);
    }

    /**
     * Generate the block variables for online users
     */
    private function submoduleUsersOnline()
    {
        $online_users = obtain_users_online();

        $this->template->assign_vars([
            'TOTAL_USERS_VALUE'   => $online_users['total_online'],
            'VISIBLE_USERS_VALUE' => $online_users['visible_online'],
            'HIDDEN_USERS_VALUE'  => $online_users['hidden_online'],
            'GUEST_USERS_VALUE'   => $online_users['guests_online'],
        ]);
    }
}
