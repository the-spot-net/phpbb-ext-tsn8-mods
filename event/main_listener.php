<?php
/**
 *
 * @package       phpBB Extension - Acme Demo
 * @copyright (c) 2013 phpBB Group
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace tsn\tsn8\event;

/**
 * @ignore
 */
use bbcode;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
 */
class main_listener implements EventSubscriberInterface
{
    static public function getSubscribedEvents()
    {
        return array(
            'core.user_setup'                           => 'load_language_on_setup',
            'core.search_get_topic_data'                => 'fetch_extended_new_post_data',
            'core.search_modify_tpl_ary'                => 'template_add_extended_new_post_data',
            'core.display_forums_modify_sql'            => 'fetch_extended_forum_row_data',
            'core.display_forums_modify_forum_rows'     => 'modify_extended_forum_row_data',
            'core.display_forums_modify_template_vars'  => 'template_add_forum_last_post_author_avatar',
            'core.viewforum_get_topic_data'             => 'fetch_extended_topic_row_data',
            'core.viewforum_modify_topicrow'            => 'template_add_topic_last_post_author_avatar',
            'core.memberlist_view_profile'              => 'modify_memberlist_profile_data',
            'core.memberlist_prepare_profile_data'      => 'template_add_profile_avatar_background',
//            'core.memberlist_team_modify_query'         => 'modify_memberlist_team_data',
//            'core.memberlist_team_modify_template_vars' => 'template_add_memberlist_team_user_avatar',
        );
    }

    /* @var \phpbb\controller\helper */
    protected $helper;

    /* @var \phpbb\template\template */
    protected $template;

    /**
     * Constructor
     *
     * @param \phpbb\controller\helper $helper   Controller helper object
     * @param \phpbb\template\template $template Template object
     */
    public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template)
    {
        $this->helper = $helper;
        $this->template = $template;
    }

    /**
     * Include custom language packs
     *
     * @param $event
     */
    public function load_language_on_setup($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = array(
            'ext_name' => 'tsn/tsn8',
            'lang_set' => 'common',
        );
        $lang_set_ext[] = array(
            'ext_name' => 'tsn/tsn8',
            'lang_set' => 'tsn8',
        );
        $event['lang_set_ext'] = $lang_set_ext;
    }

    /**
     * Modify SQL Query to include avatar meta data, username, and post message body
     *
     * @param $event
     */
    public function fetch_extended_new_post_data($event)
    {
        // Pull the avatar dimensions and post text
        $sql_select = $event['sql_select'];
        $sql_select .= ', u.username, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, p.post_text, p.bbcode_uid, p.bbcode_bitfield';

        // Add the user and post tables for the extended data
        $sql_from = $event['sql_from'];
        $sql_from = POSTS_TABLE . ' p, ' . $sql_from . ' LEFT JOIN ' . USERS_TABLE . ' u ON (u.user_id = t.topic_last_poster_id) ';

        // link the user table to the topic last poster id, and the post data to the last topic post id
        $sql_where = $event['sql_where'];
        $sql_where .= ' AND p.post_id = t.topic_last_post_id';

        // Save all the modifications back to the event
        $event['sql_select'] = $sql_select;
        $event['sql_from'] = $sql_from;
        $event['sql_where'] = $sql_where;
    }

    /**
     * Modify SQL Query to include avatar meta data and username
     *
     * @param $event
     */
    public function fetch_extended_forum_row_data($event)
    {
        $sql_ary = $event['sql_ary'];

        // Add the select fields...
        $sql_ary['SELECT'] .= ', u.username, u.user_avatar_type, u.user_avatar, u.user_avatar_height, u.user_avatar_width';

        // LEFT JOIN the users table...
        $sql_ary['LEFT_JOIN'][] = array(
            'FROM' => array(USERS_TABLE => 'u'),
            'ON'   => 'f.forum_last_poster_id = u.user_id'
        );

        // Put the query back...
        $event['sql_ary'] = $sql_ary;
    }

    /**
     * Modify SQL Query to include avatar meta data and username
     *
     * @param $event
     */
    public function fetch_extended_topic_row_data($event)
    {
        $sql_ary = $event['sql_array'];

        // Add the select fields
        $sql_ary['SELECT'] .= ', u.username, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height';

        // LEFT JOIN the users table
        $sql_ary['LEFT_JOIN'][] = array(
            'FROM' => array(USERS_TABLE => 'u'),
            'ON'   => 't.topic_last_poster_id = u.user_id'
        );

        // Put the query back...
        $event['sql_array'] = $sql_ary;
    }

    public function modify_extended_forum_row_data($event)
    {
        $forum_rows = $event['forum_rows'];
        $row = $event['row'];
        $parent_id = $event['parent_id'];
        $forum_id = $row['forum_id'];

        // Take the logic from the functions_display.php file...
        if ($row['forum_type'] != FORUM_CAT) {
            // But since the last post time was already updated if legit, do an equality comparison for a match
            // And also validate against the forum_id where the last post was made, so we don't have wrong avatar for last user
            if ((int)$row['forum_last_post_time'] == (int)$forum_rows[$parent_id]['forum_last_post_time'] && $forum_rows[$parent_id]['forum_id_last_post'] == $forum_id) {
                $forum_rows[$parent_id]['username'] = $row['username'];
                $forum_rows[$parent_id]['user_avatar_type'] = $row['user_avatar_type'];
                $forum_rows[$parent_id]['user_avatar'] = $row['user_avatar'];
                $forum_rows[$parent_id]['user_avatar_width'] = $row['user_avatar_width'];
                $forum_rows[$parent_id]['user_avatar_height'] = $row['user_avatar_height'];
            }
        }
        $event['forum_rows'] = $forum_rows;
    }

//    public function modify_memberlist_member_data($event)
//    {
//        $sql_array = $event['sql_ary'];
//
//        $sql_array['SELECT'] = $sql_array['SELECT'] . ' u.user_avatar_type, u.user_avatar, u.user_avatar_width, u.user_avatar_height';
//
//        $event['sql_ary'] = $sql_array;
//    }

//    public function template_add_memberlist_team_user_avatar($event)
//    {
//        $template_vars = $event['template_vars'];
//        $row = $event['row'];
//
//        $template_vars['USER_AVATAR'] = $this->get_avatar($row, 0.75);
//
//        $event['template_vars'] = $template_vars;
//    }

    /** Modify the View Profile data
     *
     * @param $event
     */
    public function modify_memberlist_profile_data($event)
    {
        // Initialization
        $member = $event['member'];

        // Get the avatar's source path
        preg_match_all('/src="\.(.+?)"/', $this->get_avatar($member), $avatar_data);

        // Prepare the url for exif capture
        $avatar_path = array_shift($avatar_data[1]);
        $avatar_path = (strstr($avatar_path,
                '/http/') === false) ? 'https://the-spot.net/phorums' . $avatar_path : $avatar_path;

        $exif_imagetype = @exif_imagetype($avatar_path);

        $color = $this->get_dominant_color_from_image($avatar_path, $exif_imagetype);

        $member['background_color'] = $color;
        $event['member'] = $member;
    }

    public function template_add_profile_avatar_background($event)
    {
        $template_data = $event['template_data'];
        $data = $event['data'];

        // Created from ->modify_memberlist_profile_data();
        $template_data['AVATAR_BACKGROUND_COLOR'] = $data['background_color'];

        $event['template_data'] = $template_data;
    }

    /**
     * Insert the New Posts template data for My Spot "New Posts" module
     *
     * @param $event
     */
    public function template_add_extended_new_post_data($event)
    {
        global $phpbb_root_path, $phpEx;

        // Includes
        include_once($phpbb_root_path . 'includes/functions_content.' . $phpEx);

        $tpl_array = $event['tpl_ary'];
        $row = $event['row'];

        // Prepare the last post's text...
        $message = $row['post_text'];
        // Remove paragraphs
        $message = $this->collapse_spaces($message);
        // Replace BBcode UIDs with BBCode syntax
        $message = generate_text_for_display($message, $row['bbcode_uid'], $row['bbcode_bitfield'], 1);
        // Remove BBcode syntax
        // $message passed by reference
        strip_bbcode($message);
        // Get first 50 words
        $message = $this->smart_excerpt($message, 50);

        $event['tpl_ary'] = array_merge($tpl_array, array(
            'LAST_POST_TEXT'          => $message,
            'LAST_POST_AUTHOR_AVATAR' => $this->get_avatar($row, 0.75),
        ));
    }

    /**
     * Insert the Last Post Author's Avatar into the Forum Index
     * Forum Row template variables
     *
     * @param $event
     */
    public function template_add_forum_last_post_author_avatar($event)
    {
        global $phpbb_root_path, $phpEx;

        // Includes
        include_once($phpbb_root_path . 'includes/functions.'.$phpEx);
        include_once($phpbb_root_path . 'includes/functions_content.' . $phpEx);

        $forum_row = $event['forum_row'];
        $row = $event['row'];

        $event['forum_row'] = array_merge($forum_row, array(
            'LAST_POSTER_AVATAR' => $this->get_avatar($row, 0.25),
            'U_LAST_POSTER_LINK' => append_sid("memberlist.$phpEx", "mode=viewprofile&u=".$row['forum_last_poster_id']),

        ));
    }

    /**
     * Insert the Last Post Author's Avatar into the View Forum
     * Topic Row template variables
     *
     * @param $event
     */
    public function template_add_topic_last_post_author_avatar($event)
    {
        global $phpbb_root_path, $phpEx, $user, $config;

        // Includes
        include_once($phpbb_root_path . 'includes/functions.'.$phpEx);
        include_once($phpbb_root_path . 'includes/functions_content.' . $phpEx);

        $topic_row = $event['topic_row'];
        $row = $event['row'];

        $event['topic_row'] = array_merge($topic_row, array(
            'LAST_POST_AUTHOR_AVATAR' => $this->get_avatar($row, 0.25),
            'U_LAST_POST_AUTHOR_LINK' => append_sid("memberlist.$phpEx", "mode=viewprofile&u=".$row['topic_last_poster_id']),

        ));
    }

    /**
     * Removes all the directory traversal from the extension directory
     * to match the forum root directory
     *
     * @param string $avatar_html Avatar HTML string
     *
     * @return string Avatar HTML String with image from phpbb root
     */
    private function collapse_avatar_path($avatar_html)
    {
        return preg_replace('/(\.\.\/)+?/', './', $avatar_html);
    }

    /**
     * Prepare the dimensions of the avatar, then pass the data to the phpbb avatar function
     *
     * @param array  $row   Database row containing the avatar data
     * @param float  $scale Factor to scale the avatar up (>1) or down (<1)
     * @param string $alt   Alt Text for the avatar image
     *
     * @return string HTML of the avatar image
     */
    private function get_avatar($row, $scale = 1.0, $alt = 'USER_AVATAR')
    {
        global $phpbb_root_path, $phpEx;

        include_once($phpbb_root_path . 'includes/functions.' . $phpEx);

        // Calculate the results of scaling...
        $temp_scaled_width = (float)$row['user_avatar_width'] * $scale;
        $temp_scaled_height = (float)$row['user_avatar_height'] * $scale;

        // Avatars are assumed to be 100px by 100px
        $control_scaled_side = (float)100 * $scale;

        if ($temp_scaled_height && $temp_scaled_width) {

            // Will scaling it cause one side to be bigger than the control?
            $isScaledTooBig = ($temp_scaled_height > $control_scaled_side || $temp_scaled_width > $control_scaled_side);
            // Will scaling it cause both sides to be smaller than the control?
            $isScaledTooSmall = ($temp_scaled_height < $control_scaled_side && $temp_scaled_width < $control_scaled_side);

            // The scaled dimensions are insufficient and need to be further scaled to a control...
            if ($isScaledTooBig || $isScaledTooSmall) {
                // If the width is largest, max it at the control width,
                // and scale the height to match...
                if ($temp_scaled_width >= $temp_scaled_height) {
                    $scaled_width = $control_scaled_side;
                    $scaled_height = ($temp_scaled_height * $control_scaled_side) / $temp_scaled_width;
                } else {
                    // Height is largest, scale on the width to match
                    $scaled_height = $control_scaled_side;
                    $scaled_width = ($temp_scaled_width * $control_scaled_side) / $temp_scaled_height;
                }
            } else {
                // Scaling resulted in sufficient dimensions, use them
                $scaled_width = $temp_scaled_width;
                $scaled_height = $temp_scaled_height;
            }

            $avatar_info = array(
                'avatar_type'   => $row['user_avatar_type'],
                'avatar'        => $row['user_avatar'],
                'avatar_height' => $scaled_height,
                'avatar_width'  => $scaled_width,
            );
        } else {
            $avatar_info = array(
                'avatar_type'   => 'avatar.driver.local',
                'avatar'        => 'novelties/tsn_icon_avatar.png',
                'avatar_height' => $control_scaled_side,
                'avatar_width'  => $control_scaled_side,
            );
        }

        $avatar_info['avatar_title'] = (!empty($row['username'])) ? $row['username'] : '';

        return $this->collapse_avatar_path(phpbb_get_avatar($avatar_info, $alt, false));
    }

    /**
     * Excerpt whole words from a body of text
     *
     * @param $text
     * @param $allowed_words
     *
     * @return string
     */
    private function smart_excerpt($text, $allowed_words)
    {
        $used_words = explode(' ', $text);

        if (sizeof($used_words) > $allowed_words) {
            $excerpt = implode(' ', array_slice($used_words, 0, $allowed_words)) . '... ';
        } else {
            $excerpt = implode(' ', $used_words) . ' ';
        }

        return $excerpt;
    }

    /**
     * Replace all whitespace characters with a single space.
     *
     * @param $text
     *
     * @return mixed
     */
    private function collapse_spaces($text)
    {
        return preg_replace('/\s+?/', ' ', $text);
    }

    private function get_dominant_color_from_image($source_file, $exif_imagetype)
    {
        switch ($exif_imagetype) {
//            case IMAGETYPE_GIF:
//                $im = imagecreatefromgif($source_file);
//                break;
            case IMAGETYPE_JPEG:
                $im = imagecreatefromjpeg($source_file);
                break;
            case IMAGETYPE_PNG:
                $im = imagecreatefrompng($source_file);
                break;
            default:
                $im = null;
                break;
        }

        // False if error, null if unsupported
        if ($im) {
            $imgw = imagesx($im);
            $imgh = imagesy($im);

            $rgb_colors = $rgb_counts = array();
            for ($i = 0; $i < $imgw; $i++) {
                for ($j = 0; $j < $imgh; $j++) {

                    // get the rgb value for current pixel
                    $rgb = ImageColorAt($im, $i, $j);

                    // extract each value for r, g, b
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    $key = $r . '_' . $g . '_' . $b;

                    if (!isset($rgb_counts[$key])) {
                        $rgb_counts[$key] = 0;
                        $rgb_colors[$key] = '';
                    }

                    $rgb_counts[$key]++;
                    $rgb_colors[$key] = $key;
                }
            }

            // Sort by rgb count, biggest to smallest
            arsort($rgb_counts);

            // Ditch the white color
            unset($rgb_counts['255_255_255'], $rgb_colors['255_255_255']);
            unset($rgb_counts['0_0_0'], $rgb_colors['0_0_0']);
            // Slice off the 2nd most abundant color
            $max_color = array_slice($rgb_counts, 0, 1);
            // Flip to make the rgb key into the value
            $max_color = array_flip($max_color);
            // Extract that rgb value
            $rgb = array_shift($max_color);

            // create the rgb CSS color
            $color = 'rgb(' . str_replace('_', ', ', $rgb_colors[$rgb]) . ')';
        } else {
            $color = '#003366';
        }

        return $color;
    }
}


