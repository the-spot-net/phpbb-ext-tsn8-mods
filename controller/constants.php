<?php
/**
 * Created by thepizzy.net
 * User: @neotsn
 * Date: 12/30/19
 * Time: 2:07 PM
 */

namespace tsn\tsn\controller;

/**
 * Class constants
 * Handles some constant values used frequently
 * @package tsn\tsn\controller
 */
class constants
{
    // URL Stubs
    const URL_MY_SPOT = 'my-spot';
    const URL_SPECIAL_REPORT = 'special-report';

    // Database Values
    const FORUM_SPECIAL_REPORT_ID = 14;

    // Query Replacement Tokens
    const TOKEN_FORUM_ID = '{FORUM_ID}';
    const TOKEN_TOPIC_ID = '{TOPIC_ID}';
    const TOKEN_USER_ID = '{USER_ID}';

    // SQL Queries
    const SQL_SPECIAL_REPORT_NEWEST_TOPIC = 'SELECT MAX(topic_id) AS topic_id FROM ' . TOPICS_TABLE . ' WHERE forum_id = ' . self::TOKEN_FORUM_ID;
    const SQL_SPECIAL_REPORT_FIRST_POST_DETAILS = 'SELECT t.topic_id, t.topic_title, t.topic_views, t.topic_posts_approved, t.topic_time, t.topic_poster, p.enable_smilies, p.post_id, p.post_text, p.bbcode_uid, p.bbcode_bitfield, u.username, u.user_colour 
        FROM ' . TOPICS_TABLE . ' t  
        LEFT JOIN ' . POSTS_TABLE . ' p ON (t.topic_id = p.topic_id AND t.topic_first_post_id = p.post_id)
        LEFT JOIN ' . USERS_TABLE . ' u ON (t.topic_poster = u.user_id)
        WHERE t.forum_id = ' . self::TOKEN_FORUM_ID . ' 
            AND t.topic_id = ' . self::TOKEN_TOPIC_ID;

    const SQL_SPECIAL_REPORT_UNREAD_STATUS = 'SELECT t.*, f.forum_id, f.forum_name, tp.topic_posted, tt.mark_time, ft.mark_time AS f_mark_time, u.username, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, p.post_text, p.bbcode_uid, p.bbcode_bitfield 
    FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t 
    LEFT JOIN ' . FORUMS_TABLE . ' f ON (f.forum_id = t.forum_id) 
    LEFT JOIN ' . TOPICS_POSTED_TABLE . ' tp ON (tp.user_id = ' . self::TOKEN_USER_ID . ' AND t.topic_id = tp.topic_id) 
    LEFT JOIN ' . TOPICS_TRACK_TABLE . ' tt ON (tt.user_id = ' . self::TOKEN_USER_ID . ' AND t.topic_id = tt.topic_id) 
    LEFT JOIN ' . FORUMS_TRACK_TABLE . ' ft ON (ft.user_id = ' . self::TOKEN_USER_ID . ' AND ft.forum_id = f.forum_id) 
    LEFT JOIN ' . USERS_TABLE . ' u ON (u.user_id = t.topic_last_poster_id) 
    WHERE t.topic_id = ' . self::TOKEN_TOPIC_ID . ' 
    AND f.forum_id = ' . self::TOKEN_FORUM_ID . ' 
    AND t.forum_id = ' . self::TOKEN_FORUM_ID . ' 
    AND t.topic_visibility = 1 
    AND p.post_id = t.topic_last_post_id 
    ORDER BY t.topic_last_post_time DESC';

    const SQL_USER_AVATAR = 'SELECT u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height FROM ' . USERS_TABLE . ' u WHERE u.user_id = ' . self::TOKEN_USER_ID;
}
