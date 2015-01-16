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
			'core.page_header'           => 'add_page_header_link',
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
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function add_page_header_link($event)
	{
		$this->template->assign_vars(array(
			'U_DEMO_PAGE' => $this->helper->route('tsn_tsn8_controller', array('name' => 'world')),
		));
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

		$tpl_array = $event['tpl_ary'];
		$row = $event['row'];

		$avatar_html = $this->get_avatar($row, 0.75, 0.75);

		// Prepare the last post's text...
		$message = $row['post_text'];
		$message = $this->collapse_spaces($message);
		$message = $this->generate_text_for_display($message, $row['bbcode_uid'], $row['bbcode_bitfield'], 1);
		$message = $this->strip_bbcode($message);
		$message = $this->smart_excerpt($message, 50);

		$tpl_array = array_merge($tpl_array, array(
			'LAST_POST_TEXT'          => $message,
			'LAST_POST_AUTHOR_AVATAR' => $avatar_html,
		));

		$event['tpl_ary'] = $tpl_array;
	}

	private function get_avatar($row, $width_mod = 1, $height_mod = 1, $alt = 'USER_AVATAR')
	{
		global $phpbb_root_path;

		include_once($phpbb_root_path . 'includes/functions.php');

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

	/**
	 * For display of custom parsed text on user-facing pages
	 * Expects $text to be the value directly from the database (stored value)
	 */
	private function generate_text_for_display($text, $uid, $bitfield, $flags, $censor_text = true)
	{
		static $bbcode;
		global $phpbb_dispatcher;

		if ($text === '') {
			return '';
		}

		/**
		 * Use this event to modify the text before it is parsed
		 *
		 * @event core.modify_text_for_display_before
		 * @var string    text            The text to parse
		 * @var string    uid                The BBCode UID
		 * @var string    bitfield        The BBCode Bitfield
		 * @var int        flags            The BBCode Flags
		 * @var bool        censor_text        Whether or not to apply word censors
		 * @since 3.1.0-a1
		 */
		$vars = array('text', 'uid', 'bitfield', 'flags', 'censor_text');
		extract($phpbb_dispatcher->trigger_event('core.modify_text_for_display_before', compact($vars)));

		if ($censor_text) {
			$text = $this->censor_text($text);
		}

		// Parse bbcode if bbcode uid stored and bbcode enabled
		if ($uid && ($flags & OPTION_FLAG_BBCODE)) {
			if (!class_exists('bbcode')) {
				global $phpbb_root_path, $phpEx;
				include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
			}

			if (empty($bbcode)) {
				$bbcode = new bbcode($bitfield);
			} else {
				$bbcode->bbcode($bitfield);
			}

			$bbcode->bbcode_second_pass($text, $uid);
		}

		$text = $this->bbcode_nl2br($text);
		$text = $this->smiley_text($text, !($flags & OPTION_FLAG_SMILIES));

		/**
		 * Use this event to modify the text after it is parsed
		 *
		 * @event core.modify_text_for_display_after
		 * @var string    text        The text to parse
		 * @var string    uid            The BBCode UID
		 * @var string    bitfield    The BBCode Bitfield
		 * @var int        flags        The BBCode Flags
		 * @since 3.1.0-a1
		 */
		$vars = array('text', 'uid', 'bitfield', 'flags');
		extract($phpbb_dispatcher->trigger_event('core.modify_text_for_display_after', compact($vars)));

		return $text;
	}

	private function censor_text($text)
	{
		static $censors;

		// Nothing to do?
		if ($text === '') {
			return '';
		}

		// We moved the word censor checks in here because we call this function quite often - and then only need to do the check once
		if (!isset($censors) || !is_array($censors)) {
			global $config, $user, $auth, $cache;

			// We check here if the user is having viewing censors disabled (and also allowed to do so).
			if (!$user->optionget('viewcensors') && $config['allow_nocensors'] && $auth->acl_get('u_chgcensors')) {
				$censors = array();
			} else {
				$censors = $cache->obtain_word_list();
			}
		}

		if (sizeof($censors)) {
			return preg_replace($censors['match'], $censors['replace'], $text);
		}

		return $text;
	}

	/**
	 * custom version of nl2br which takes custom BBCodes into account
	 */
	private function bbcode_nl2br($text)
	{
		// custom BBCodes might contain carriage returns so they
		// are not converted into <br /> so now revert that
		$text = str_replace(array("\n", "\r"), array('<br />', "\n"), $text);
		return $text;
	}

	/**
	 * Smiley processing
	 */
	private function smiley_text($text, $force_option = false)
	{
		global $config, $user, $phpbb_path_helper;

		if ($force_option || !$config['allow_smilies'] || !$user->optionget('viewsmilies')) {
			return preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $text);
		} else {
			$root_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? generate_board_url() . '/' : $phpbb_path_helper->get_web_root_path();
			return preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/(.*?) \/><!\-\- s\1 \-\->#', '<img class="smilies" src="' . $root_path . $config['smilies_path'] . '/\2 />', $text);
		}
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

	/**
	 * Strips all bbcode from a text and returns the plain content
	 */
	private function strip_bbcode($text, $uid = '')
	{
		if (!$uid) {
			$uid = '[0-9a-z]{5,}';
		}

		$text = preg_replace("#\[\/?[a-z0-9\*\+\-]+(?:=(?:&quot;.*&quot;|[^\]]*))?(?::[a-z])?(\:$uid)\]#", ' ', $text);

		$match = $this->get_preg_expression('bbcode_htm');
		$replace = array('\1', '\1', '\2', '\1', '', '');

		$text = preg_replace($match, $replace, $text);

		return $text;
	}

	/**
	 * This function returns a regular expression pattern for commonly used expressions
	 * Use with / as delimiter for email mode and # for url modes
	 * mode can be: email|bbcode_htm|url|url_inline|www_url|www_url_inline|relative_url|relative_url_inline|ipv4|ipv6
	 */
	private function get_preg_expression($mode)
	{
		switch ($mode) {
			case 'email':
				// Regex written by James Watts and Francisco Jose Martin Moreno
				// http://fightingforalostcause.net/misc/2006/compare-email-regex.php
				return '([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*(?:[\w\!\#$\%\'\*\+\-\/\=\?\^\`{\|\}\~]|&amp;)+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,63})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)';
				break;

			case 'bbcode_htm':
				return array(
					'#<!\-\- e \-\-><a href="mailto:(.*?)">.*?</a><!\-\- e \-\->#',
					'#<!\-\- l \-\-><a (?:class="[\w-]+" )?href="(.*?)(?:(&amp;|\?)sid=[0-9a-f]{32})?">.*?</a><!\-\- l \-\->#',
					'#<!\-\- ([mw]) \-\-><a (?:class="[\w-]+" )?href="(.*?)">.*?</a><!\-\- \1 \-\->#',
					'#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/.*? \/><!\-\- s\1 \-\->#',
					'#<!\-\- .*? \-\->#s',
					'#<.*?>#s',
				);
				break;

			// Whoa these look impressive!
			// The code to generate the following two regular expressions which match valid IPv4/IPv6 addresses
			// can be found in the develop directory
			case 'ipv4':
				return '#^(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$#';
				break;

			case 'ipv6':
				return '#^(?:(?:(?:[\dA-F]{1,4}:){6}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:::(?:[\dA-F]{1,4}:){0,5}(?:[\dA-F]{1,4}(?::[\dA-F]{1,4})?|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:):(?:[\dA-F]{1,4}:){4}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,2}:(?:[\dA-F]{1,4}:){3}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,3}:(?:[\dA-F]{1,4}:){2}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,4}:(?:[\dA-F]{1,4}:)(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,5}:(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,6}:[\dA-F]{1,4})|(?:(?:[\dA-F]{1,4}:){1,7}:)|(?:::))$#i';
				break;

			case 'url':
			case 'url_inline':
				$inline = ($mode == 'url') ? ')' : '';
				$scheme = ($mode == 'url') ? '[a-z\d+\-.]' : '[a-z\d+]'; // avoid automatic parsing of "word" in "last word.http://..."
				// generated with regex generation file in the develop folder
				return "[a-z]$scheme*:/{2}(?:(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})+|[0-9.]+|\[[a-z0-9.]+:[a-z0-9.]+:[a-z0-9.:]+\])(?::\d*)?(?:/(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?";
				break;

			case 'www_url':
			case 'www_url_inline':
				$inline = ($mode == 'www_url') ? ')' : '';
				return "www\.(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})+(?::\d*)?(?:/(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?";
				break;

			case 'relative_url':
			case 'relative_url_inline':
				$inline = ($mode == 'relative_url') ? ')' : '';
				return "(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})*(?:/(?:[a-z0-9\-._~!$&'($inline*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9\-._~!$&'($inline*+,;=:@/?|]+|%[\dA-F]{2})*)?";
				break;

			case 'table_prefix':
				return '#^[a-zA-Z][a-zA-Z0-9_]*$#';
				break;

			// Matches the predecing dot
			case 'path_remove_dot_trailing_slash':
				return '#^(?:(\.)?)+(?:(.+)?)+(?:([\\/\\\])$)#';
				break;
		}

		return '';
	}
}


