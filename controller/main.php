<?php

namespace tsn\tsn8\controller;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\db\driver\driver;
use phpbb\language\language;
use phpbb\template\template;
use phpbb\user;

/**
 * Class main
 * @package tsn\tsn8\controller
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

    /** @var string|null */
    private static $phpbbRootPath = null;
    /** @var false|string */
    private static $phpEx = 'php';

    /**
     * main constructor.
     *
     * @param \phpbb\auth\auth         $auth
     * @param \phpbb\config\config     $config
     * @param \phpbb\db\driver\driver  $db
     * @param \phpbb\controller\helper $helper
     * @param \phpbb\language\language $language
     * @param \phpbb\template\template $template
     * @param \phpbb\user              $user
     */
    public function __construct(auth $auth, config $config, driver $db, helper $helper, language $language, template $template, user $user)
    {
        $this->auth = $auth;
        $this->config = $config;
        $this->db = $db;
        $this->helper = $helper;
        $this->language = $language;
        $this->template = $template;
        $this->user = $user;

        self::$phpbbRootPath = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../../../';
        self::$phpEx = substr(strrchr(__FILE__, '.'), 1);
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
            case constants::URL_SPECIAL_REPORT:
                $output = $this->moduleSpecialReport();
                break;
            default:
                $output = '';
                break;
        }

//        $l_message = !$this->config['acme_demo_goodbye'] ? 'DEMO_HELLO' : 'DEMO_GOODBYE';
//        $this->template->assign_var('DEMO_MESSAGE', $this->user->lang($l_message, $name));
//
//        return $this->helper->render('sync_settings.html', $name);

        return $output;
    }

    /**
     * Handles the render of the Special Report AJAX request from tsn/special-report
     * @return string|\Symfony\Component\HttpFoundation\Response
     */
    private function moduleSpecialReport()
    {
        $output = '';

        // Setup the permissions...
        $this->user->session_begin();
        $this->auth->acl($this->user->data);
        $this->user->setup(['memberlist', 'groups']);

        // Check for a topic id...
        $cursor = $this->db->sql_query(str_replace(constants::TOKEN_FORUM_ID, constants::FORUM_SPECIAL_REPORT_ID, constants::SQL_SPECIAL_REPORT_NEWEST_TOPIC));
        $topicRow = $this->db->sql_fetchrow($cursor);

        // Memory free...
        $this->db->sql_freeresult($cursor);

        if (!empty($topicRow)) {

            // Get the post information and put it to an array...
            $cursor = $this->db->sql_query(str_replace(constants::TOKEN_TOPIC_ID, $topicRow['topic_id'], constants::SQL_SPECIAL_REPORT_FIRST_POST_DETAILS));
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
                    $query = str_replace(constants::TOKEN_FORUM_ID, constants::FORUM_SPECIAL_REPORT_ID, $query);

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

                    $topicTrackingInfo = get_topic_tracking(constants::FORUM_SPECIAL_REPORT_ID, [$topicRow['topic_id']], $rowSet, [constants::FORUM_SPECIAL_REPORT_ID => $markTime]);
                    $isUnreadTopic = (isset($topicTrackingInfo[$topicRow['topic_id']]) && $topicRow['topic_time'] > $topicTrackingInfo[$topicRow['topic_id']]);

                    // Prepare the post content; Replaces UIDs with BBCode and then convert the Post Content to an excerpt...
                    $words = explode(' ', generate_text_for_display($topicRow['post_text'], $topicRow['bbcode_uid'], $topicRow['bbcode_bitfield'], 1));

                    $this->template->assign_vars([
                        'I_AVATAR_IMG'       => $avatarImage,
                        'L_HEADLINE'         => censor_text($topicRow['topic_title']),
                        'L_POST_AUTHOR'      => get_username_string('full', $topicRow['topic_poster'], $topicRow['username'], $topicRow['user_colour']),
                        'L_POST_BODY'        => (count($words) > constants::EXCEPRT_WORD_LIMIT)
                            ? implode(' ', array_slice($words, 0, constants::EXCEPRT_WORD_LIMIT)) . '... '
                            : implode(' ', $words),
                        'L_POST_DATE'        => $this->user->format_date($topicRow['topic_time']),
                        'L_POST_META'        => $this->language->lang('SPECIAL_REPORT_VIEWS_COMMENTS_COUNT', $topicRow['topic_views'], (int)$topicRow['topic_posts_approved'] - 1),
                        'S_UNREAD_TOPIC'     => $isUnreadTopic,
                        'U_CONTINUE_READING' => append_sid(self::$phpbbRootPath . 'viewtopic.' . self::$phpEx, "p=" . $topicRow['post_id']),
                        'U_HEADLINE'         => append_sid(self::$phpbbRootPath . 'viewtopic.' . self::$phpEx, "p=" . $topicRow['post_id']),
                        'U_USER_PROFILE'     => append_sid(self::$phpbbRootPath . 'memberlist.' . self::$phpEx, "mode=viewprofile&u=" . $topicRow['topic_poster']),
                    ]);

                    $output = $this->helper->render('modules/special_report.html', $this->language->lang('MYSPOT'));

                } else {
                    // TODO Check that this is valid
                    trigger_error('NO_TSNSR_4');
                }
            } else {
                // TODO Check that this is valid
                trigger_error('NO_TSNSR_3');
            }
        } else {
            // TODO Check that this is valid
            trigger_error('NO_TSNSR_1');
        }

        return $output;
    }
}
