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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \bbcode;

/**
 * Event listener
 */
class main_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'            => 'load_language_on_setup',
			'core.search_get_topic_data' => 'fetch_extended_new_post_data',
			'core.search_modify_tpl_ary' => 'template_add_extended_new_post_data',
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

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'tsn/tsn8',
			'lang_set' => 'common',
		);
		$lang_set_ext[] = array(
			'ext_name' => 'tsn/tsn8',
			'lang_set' => 'myspot',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function fetch_extended_new_post_data($event)
	{
		// Pull the avatar dimensions and post text
		$sql_select = $event['sql_select'];
		$sql_select .= ', u.username, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, p.post_text, p.bbcode_uid, p.bbcode_bitfield';

		// Add the user and post tables for the extended data
		$sql_from = $event['sql_from'];
		$sql_from = POSTS_TABLE . ' p, ' . $sql_from . ' LEFT JOIN ' . USERS_TABLE . ' u ON (u.user_id = t.topic_poster) ';

		// link the user table to the topic last poster id, and the post data to the last topic post id
		$sql_where = $event['sql_where'];
		$sql_where .= ' AND u.user_id = t.topic_last_poster_id AND p.post_id = t.topic_last_post_id';

		// Save all the modifications back to the event
		$event['sql_select'] = $sql_select;
		$event['sql_from'] = $sql_from;
		$event['sql_where'] = $sql_where;
	}

	public function template_add_extended_new_post_data($event)
	{
		global $phpbb_root_path, $phpEx;

		// Includes
		include_once($phpbb_root_path . 'includes/functions_content.' . $phpEx);

		$tpl_array = $event['tpl_ary'];
		$row = $event['row'];

		$avatar_html = $this->get_avatar($row, 0.75, 0.75);

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

		$tpl_array = array_merge($tpl_array, array(
			'LAST_POST_TEXT'          => $message,
			'LAST_POST_AUTHOR_AVATAR' => $avatar_html,
		));

		$event['tpl_ary'] = $tpl_array;
	}

	private function get_avatar($row, $width_mod = 1, $height_mod = 1, $alt = 'USER_AVATAR')
	{
		global $phpbb_root_path, $phpEx;

		include_once($phpbb_root_path . 'includes/functions.' . $phpEx);

		// TODO Add code to scale the avatar down to 75px by 75px

		$avatar_info = array(
			'avatar_type'   => $row['user_avatar_type'],
			'avatar'        => $row['user_avatar'],
			'avatar_height' => ((float)$row['user_avatar_height'] * $height_mod),
			'avatar_width'  => ((float)$row['user_avatar_width'] * $width_mod),
		);

		$avatar_html = phpbb_get_avatar($avatar_info, $alt, false);

		return preg_replace('/(\.\.\/)+?/', './', $avatar_html);
	}

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

	private function collapse_spaces($text)
	{
		return preg_replace('/\s+?/', ' ', $text);
	}
}


