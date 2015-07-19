<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpEx */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config     $config            Config object
	 * @param \phpbb\controller\helper $controller_helper Controller helper object
	 * @param \phpbb\template\template $template          Template object
	 * @param \phpbb\user              $user              User object
	 * @param string                   $php_ext           phpEx
	 *
	 * @return \skouat\ppde\event\listener
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $controller_helper, \phpbb\template\template $template, \phpbb\user $user, $php_ext)
	{
		$this->config = $config;
		$this->controller_helper = $controller_helper;
		$this->template = $template;
		$this->user = $user;
		$this->php_ext = $php_ext;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core
	 *
	 * @return array
	 * @static
	 * @access public
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header'                   => 'add_page_header_link',
			'core.viewonline_overwrite_location' => 'viewonline_page',
			'core.user_setup'                    => 'load_language_on_setup',
		);
	}

	/**
	 * Create a URL to the donation pages controller file for the header linklist
	 *
	 * @param object $event The event object
	 *
	 * @return null
	 * @access public
	 */
	public function add_page_header_link($event)
	{
		$this->template->assign_vars(array(
			'S_PPDE_LINK_ENABLED' => (!empty($this->config['ppde_enable']) && !empty($this->config['ppde_header_link'])) ? true : false,
			'U_PPDE_DONATE'       => $this->controller_helper->route('skouat_ppde_main_controller'),
		));
	}

	/**
	 * Load language files during user setup
	 *
	 * @param object $event The event object
	 *
	 * @return null
	 * @access public
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'skouat/ppde',
			'lang_set' => 'donate',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Show users as viewing the Donation page on Who Is Online page
	 *
	 * @param object $event The event object
	 *
	 * @return null
	 * @access public
	 */
	public function viewonline_page($event)
	{
		if ($event['on_page'][1] == 'app')
		{
			if (strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/donate') === 0)
			{
				$event['location'] = $this->user->lang('PPDE_VIEWONLINE');
				$event['location_url'] = $this->controller_helper->route('skouat_ppde_main_controller');
			}
		}
	}
}
