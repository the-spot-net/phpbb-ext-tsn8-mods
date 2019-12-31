<?php
/**
 * Created by thepizzy.net
 * User: @neotsn
 * Date: 12/30/19
 * Time: 2:07 PM
 */

namespace tsn\tsn8\controller;

/**
 * Class constants
 * Handles some constant values used frequently
 * @package tsn\tsn8\controller
 */
class constants
{

    // Misc Constants
    const EXCEPRT_WORD_LIMIT = 140;

    // URL Stubs
    const URL_SPECIAL_REPORT = 'special-report';

    // Database Values
    const FORUM_SPECIAL_REPORT_ID = 14;

    // Query Replacement Tokens
    const TOKEN_FORUM_ID = '{FORUM_ID}';
    const TOKEN_TOPIC_ID = '{TOPIC_ID}';
    const TOKEN_USER_ID = '{USER_ID}';

    // SQL Queries
    const SQL_SPECIAL_REPORT_NEWEST_TOPIC = 'SELECT MAX(topic_id) AS topic_id FROM ' . TOPICS_TABLE . ' WHERE forum_id = ' . self::TOKEN_FORUM_ID;
    const SQL_SPECIAL_REPORT_FIRST_POST_DETAILS = 'SELECT t.topic_id, t.topic_title, t.topic_views, t.topic_posts_approved, t.topic_time, t.topic_poster, p.enable_smilies, p.post_id, p.post_text, p.bbcode_uid, p.bbcode_bitfield, u.username, u.user_colour FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u WHERE t.forum_id = ' . self::FORUM_SPECIAL_REPORT_ID . ' AND t.topic_id = ' . self::TOKEN_TOPIC_ID . ' AND p.topic_id = t.topic_id AND p.post_id = t.topic_first_post_id AND u.user_id = t.topic_poster';
    const SQL_SPECIAL_REPORT_UNREAD_STATUS = 'SELECT t.*, f.forum_id, f.forum_name, tp.topic_posted, tt.mark_time, ft.mark_time AS f_mark_time, u.username, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, p.post_text, p.bbcode_uid, p.bbcode_bitfield FROM phpbb_posts p, phpbb_topics t LEFT JOIN phpbb_forums f ON (f.forum_id = t.forum_id) LEFT JOIN phpbb_topics_posted tp ON (tp.user_id = ' . self::TOKEN_USER_ID . ' AND t.topic_id = tp.topic_id) LEFT JOIN phpbb_topics_track tt ON (tt.user_id = ' . self::TOKEN_USER_ID . ' AND t.topic_id = tt.topic_id) LEFT JOIN phpbb_forums_track ft ON (ft.user_id = ' . self::TOKEN_USER_ID . ' AND ft.forum_id = f.forum_id) LEFT JOIN phpbb_users u ON (u.user_id = t.topic_last_poster_id) WHERE t.topic_id = ' . self::TOKEN_TOPIC_ID . ' AND f.forum_id = ' . self::TOKEN_FORUM_ID . ' AND t.forum_id = ' . self::TOKEN_FORUM_ID . ' AND t.topic_visibility = 1 AND p.post_id = t.topic_last_post_id ORDER BY t.topic_last_post_time DESC';
    const SQL_USER_AVATAR = 'SELECT u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height FROM ' . USERS_TABLE . ' u WHERE u.user_id = {USER_ID}';
}
