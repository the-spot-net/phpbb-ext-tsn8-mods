<?php
/**
 * @package       phpBB Extension - Acme Demo
 * @copyright (c) 2013 phpBB Group
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace tsn\tsn8\controller;

use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\template\template;
use phpbb\user;

class main
{
    /* @var \phpbb\config\config */
    protected $config;

    /* @var \phpbb\controller\helper */
    protected $helper;

    /* @var \phpbb\template\template */
    protected $template;

    /* @var \phpbb\user */
    protected $user;

    /**
     * Constructor
     *
     * @param \phpbb\config\config     $config
     * @param \phpbb\controller\helper $helper
     * @param \phpbb\template\template $template
     * @param \phpbb\user              $user
     */
    public function __construct(
        config $config,
        helper $helper,
        template $template,
        user $user
    ) {
        $this->config = $config;
        $this->helper = $helper;
        $this->template = $template;
        $this->user = $user;
    }

//	/**
//	* Demo controller for route /tsn/{name}
//	*
//	* @param string		$name
//	* @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
//	*/
//	public function handle($name)
//	{
//		$l_message = !$this->config['acme_demo_goodbye'] ? 'DEMO_HELLO' : 'DEMO_GOODBYE';
//		$this->template->assign_var('DEMO_MESSAGE', $this->user->lang($l_message, $name));
//
//		return $this->helper->render('sync_settings.html', $name);
//	}
}
