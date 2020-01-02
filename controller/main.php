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
     * Route controller for route /tsn/{name}
     *
     * @param string $name
     *
     * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
     */
    public function handle($name)
    {

        switch ($name) {
            case constants::ROUTE_MY_SPOT:
                $output = $this->pageMySpot();
                break;
            case constants::ROUTE_SPECIAL_REPORT:
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

            'U_TSN_MYSPOT' => self::$boardUrl . constants::URL_MY_SPOT,
        ]);
    }

    /**
     * Handles the render of the Special Report AJAX request from tsn/special-report
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function moduleSpecialReport()
    {
        $this->initUserAuthentication();

        // Check for a topic id...
        $cursor = $this->db->sql_query(str_replace(constants::TOKEN_FORUM_ID, $this->config['tsn_specialreport_forumid'], constants::SQL_SPECIAL_REPORT_NEWEST_TOPIC));
        $topicRow = $this->db->sql_fetchrow($cursor);

        // Memory free...
        $this->db->sql_freeresult($cursor);

        if (!empty($topicRow)) {

            // Get the post information and put it to an array...
            $query = str_replace(constants::TOKEN_TOPIC_ID, $topicRow['topic_id'], constants::SQL_SPECIAL_REPORT_FIRST_POST_DETAILS);
            $query = str_replace(constants::TOKEN_FORUM_ID, $this->config['tsn_specialreport_forumid'], $query);
            $cursor = $this->db->sql_query($query);
            $topicRow = $this->db->sql_fetchrow($cursor);

            // Memory free...
            $this->db->sql_freeresult($cursor);

            if (!empty($topicRow)) {

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

                // Grab the topic poser's avatar details...
                $cursor = $this->db->sql_query(str_replace(constants::TOKEN_USER_ID, $topicRow['topic_poster'], constants::SQL_USER_AVATAR));
                $avatarRow = $this->db->sql_fetchrow($cursor);

                // Memory free...
                $this->db->sql_freeresult($cursor);

                if (!empty($avatarRow)) {

                    // Prepare the avatar image...
                    $avatarImage = preg_replace('/(\.\.\/)+?/', './', phpbb_get_user_avatar([
                        'avatar'        => $avatarRow['user_avatar'],
                        'avatar_type'   => $avatarRow['user_avatar_type'],
                        'avatar_width'  => $avatarRow['user_avatar_width'],
                        'avatar_height' => $avatarRow['user_avatar_height'],
                    ]));

                    // Get the current user's unread state for this topic...
                    $query = str_replace(constants::TOKEN_USER_ID, $this->user->data['user_id'], constants::SQL_SPECIAL_REPORT_UNREAD_STATUS);
                    $query = str_replace(constants::TOKEN_TOPIC_ID, $topicRow['topic_id'], $query);
                    $query = str_replace(constants::TOKEN_FORUM_ID, $this->config['tsn_specialreport_forumid'], $query);

                    $cursor = $this->db->sql_query($query);
                    $topicStatusRow = $this->db->sql_fetchrow($cursor);

                    // Memory free...
                    $this->db->sql_freeresult($cursor);

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
                        'I_AVATAR_IMG'       => $avatarImage,
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

                    $output = $this->helper->render('@tsn_tsn/tsn_special_report.html', $this->language->lang('TSNSPECIALREPORT'));

                } else {
                    $output = new Response('Unable to find user avatar settings', 200);
                }
            } else {
                $output = new Response('Could not find topic with the requested topic ID', 200);
            }
        } else {
            $output = new Response('No topics posted to the Special Report forum', 200);
        }

        return $output;
    }

    /**
     * Handles the base page for the My Spot feature
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function pageMySpot()
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
}
