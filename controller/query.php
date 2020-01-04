<?php
/**
 * Created by thepizzy.net
 * User: @neotsn
 * Date: 1/3/20
 * Time: 1:54 PM
 */

namespace tsn\tsn\controller;

use DateTime;

/**
 * Class query
 * Handles running specific queries
 * @package tsn\tsn\controller
 */
class query
{
    // SQL Query Replacement Tokens
    const TOKEN_DATE = '{DATE}';
    const TOKEN_FORUM_ID = '{FORUM_ID}';
    const TOKEN_LEAP_DATE = '{LEAP_DATE}';
    const TOKEN_TOPIC_ID = '{TOPIC_ID}';
    const TOKEN_USER_ID = '{USER_ID}';

    // SQL Queries
    const SQL_SPECIAL_REPORT_NEWEST_TOPIC_ID = 'SELECT MAX(topic_id) AS topic_id FROM ' . TOPICS_TABLE . ' WHERE forum_id = ' . self::TOKEN_FORUM_ID;
    const SQL_SPECIAL_REPORT_NEWEST_TOPIC_DETAILS = 'SELECT t.topic_id, t.topic_title, t.topic_views, t.topic_posts_approved, t.topic_time, t.topic_poster, p.enable_smilies, p.post_id, p.post_text, p.bbcode_uid, p.bbcode_bitfield, u.username, u.user_colour FROM ' . TOPICS_TABLE . ' t LEFT JOIN ' . POSTS_TABLE . ' p ON (t.topic_id = p.topic_id AND t.topic_first_post_id = p.post_id) LEFT JOIN ' . USERS_TABLE . ' u ON (t.topic_poster = u.user_id) WHERE t.forum_id = ' . self::TOKEN_FORUM_ID . ' AND t.topic_id = ' . self::TOKEN_TOPIC_ID;
    const SQL_TOPIC_UNREAD_STATUS = 'SELECT t.*, f.forum_id, f.forum_name, tp.topic_posted, tt.mark_time, ft.mark_time AS f_mark_time, u.username, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, p.post_text, p.bbcode_uid, p.bbcode_bitfield FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t LEFT JOIN ' . FORUMS_TABLE . ' f ON (f.forum_id = t.forum_id) LEFT JOIN ' . TOPICS_POSTED_TABLE . ' tp ON (tp.user_id = ' . self::TOKEN_USER_ID . ' AND t.topic_id = tp.topic_id) LEFT JOIN ' . TOPICS_TRACK_TABLE . ' tt ON (tt.user_id = ' . self::TOKEN_USER_ID . ' AND t.topic_id = tt.topic_id) LEFT JOIN ' . FORUMS_TRACK_TABLE . ' ft ON (ft.user_id = ' . self::TOKEN_USER_ID . ' AND ft.forum_id = f.forum_id) LEFT JOIN ' . USERS_TABLE . ' u ON (u.user_id = t.topic_last_poster_id) WHERE t.topic_id = ' . self::TOKEN_TOPIC_ID . ' AND f.forum_id = ' . self::TOKEN_FORUM_ID . ' AND t.forum_id = ' . self::TOKEN_FORUM_ID . ' AND t.topic_visibility = 1 AND p.post_id = t.topic_last_post_id ORDER BY t.topic_last_post_time DESC';
    const SQL_USER_AVATAR = 'SELECT u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height FROM ' . USERS_TABLE . ' u WHERE u.user_id = ' . self::TOKEN_USER_ID;
    const SQL_USER_BIRTHDAYS = 'SELECT u.user_id, u.username, u.user_colour, u.user_birthday FROM ' . USERS_TABLE . ' u LEFT JOIN ' . BANLIST_TABLE . ' b ON (u.user_id = b.ban_userid) WHERE (b.ban_id IS NULL OR b.ban_exclude = 1) AND (u.user_birthday LIKE "' . self::TOKEN_DATE . '%" ' . self::TOKEN_LEAP_DATE . ') AND u.user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')';
    const SQL_USER_GROUPS_LEGEND_ALL = 'SELECT group_id, group_name, group_colour, group_type, group_legend FROM ' . GROUPS_TABLE . ' WHERE group_legend > 0 ORDER BY group_legend';
    const SQL_USER_GROUPS_LEGEND_RESTRICTED = 'SELECT g.group_id, g.group_name, g.group_colour, g.group_type, g.group_legend FROM ' . GROUPS_TABLE . ' g LEFT JOIN ' . USER_GROUP_TABLE . ' ug ON (g.group_id = ug.group_id AND ug.user_id = ' . self::TOKEN_USER_ID . ' AND ug.user_pending = 0) WHERE g.group_legend > 0 AND (g.group_type <> ' . GROUP_HIDDEN . ' OR ug.user_id = ' . self::TOKEN_USER_ID . ') ORDER BY g.group_legend';
    const SQL_USER_SESSION_TIME = 'SELECT MAX(session_time) AS session_time, MIN(session_viewonline) AS session_viewonline FROM ' . SESSIONS_TABLE . ' WHERE session_user_id = ' . self::TOKEN_USER_ID;

    // SQL Query Injection phrases
    const SQL_INJECT_USER_LEAP_BIRTHDAYS = ' OR u.user_birthday LIKE "' . self::TOKEN_DATE . '%"';

    /**
     * Return the minimal data set for the latest topic from the Special Report Forum
     *
     * @param \tsn\tsn\controller\main $controller
     *
     * @return mixed
     */
    public static function checkForSpecialReportLatestTopic(main $controller)
    {
        $query = str_replace(self::TOKEN_FORUM_ID, $controller->getConfig('tsn_specialreport_forumid'), self::SQL_SPECIAL_REPORT_NEWEST_TOPIC_ID);

        return self::executeQuery($controller, $query);
    }

    /**
     * Release the DB Cursor
     *
     * @param \tsn\tsn\controller\main $controller
     * @param                          $cursor
     */
    public static function freeCursor(main $controller, $cursor)
    {
        $controller->getDb()->sql_freeresult($cursor);
    }

    /**
     * Get the core data set for the Latest Topic in Special Report Forum; return it as an array
     *
     * @param \tsn\tsn\controller\main $controller
     * @param int                      $topicId
     *
     * @return mixed
     */
    public static function getSpecialReportLatestTopicInfo(main $controller, int $topicId)
    {
        $query = str_replace(self::TOKEN_TOPIC_ID, $topicId, self::SQL_SPECIAL_REPORT_NEWEST_TOPIC_DETAILS);
        $query = str_replace(self::TOKEN_FORUM_ID, $controller->getConfig('tsn_specialreport_forumid'), $query);

        return self::executeQuery($controller, $query);
    }

    /**
     * For the User, Topic, and Forum, get the read/unread status details
     *
     * @param \tsn\tsn\controller\main $controller
     * @param int                      $userId
     * @param int                      $topicId
     * @param int                      $forumId
     *
     * @return mixed
     */
    public static function getTopicReadStatus(main $controller, int $userId, int $topicId, int $forumId)
    {
        // Get the current user's unread state for this topic...
        $query = str_replace(self::TOKEN_USER_ID, $userId, self::SQL_TOPIC_UNREAD_STATUS);
        $query = str_replace(self::TOKEN_TOPIC_ID, $topicId, $query);
        $query = str_replace(self::TOKEN_FORUM_ID, $forumId, $query);

        return self::executeQuery($controller, $query);
    }

    /**
     * Return the avatar row for a user id
     *
     * @param \tsn\tsn\controller\main $controller
     * @param int                      $userId
     *
     * @return mixed
     */
    public static function getUserAvatar(main $controller, int $userId)
    {
        $query = str_replace(self::TOKEN_USER_ID, $userId, self::SQL_USER_AVATAR);

        return self::executeQuery($controller, $query);
    }

    /**
     * @param \tsn\tsn\controller\main $controller
     * @param \DateTime                $time
     *
     * @return mixed
     */
    public static function getUserBirthdaysCursor(main $controller, DateTime $time)
    {
        $now = phpbb_gmgetdate($time->getTimestamp() + $time->getOffset());

        $includeLeapYear = ($now['mday'] == 28 && $now['mon'] == 2 && !$time->format('L'));

        $query = str_replace(self::TOKEN_DATE, $controller->getDb()->sql_escape(sprintf('%2d-%2d-', $now['mday'], $now['mon'])), self::SQL_USER_BIRTHDAYS);
        // Conditionally inject the leap year date onto the query if needed.
        $query = str_replace(self::TOKEN_LEAP_DATE, ($includeLeapYear)
            ? str_replace(self::TOKEN_DATE, $controller->getDb()->sql_escape(sprintf('%2d-%2d-', 29, 2)), self::SQL_INJECT_USER_LEAP_BIRTHDAYS)
            : '', $query);

        return $controller->getDb()->sql_query($query);

    }

    /**
     * @param \tsn\tsn\controller\main $controller
     * @param int                      $userId
     * @param bool                     $isRestricted
     *
     * @return mixed
     */
    public static function getUserGroupLegendCursor(main $controller, int $userId, bool $isRestricted = true)
    {
        $query = str_replace(self::TOKEN_USER_ID, $userId, ($isRestricted)
            ? self::SQL_USER_GROUPS_LEGEND_RESTRICTED
            : self::SQL_USER_GROUPS_LEGEND_ALL);

        return $controller->getDb()->sql_query($query);
    }

    /**
     * Get some basic Session details for the user for updating online time
     *
     * @param \tsn\tsn\controller\main $controller
     * @param int                      $userId
     *
     * @return mixed
     */
    public static function getUserSessionTime(main $controller, int $userId)
    {
        $query = str_replace(self::TOKEN_USER_ID, $userId, self::SQL_USER_SESSION_TIME);

        return self::executeQuery($controller, $query);
    }

    /**
     * Executes a query with a sql_fetchrow() call (single row return)
     *
     * @param \tsn\tsn\controller\main $controller
     * @param string                   $query
     *
     * @return mixed
     */
    private static function executeQuery(main $controller, string $query)
    {
        $cursor = $controller->getDb()->sql_query($query);
        $result = $controller->getDb()->sql_fetchrow($cursor);
        self::freeCursor($controller, $cursor);

        return $result;
    }
}
