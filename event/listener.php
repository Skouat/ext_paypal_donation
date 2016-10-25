<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{
	protected $config;
	protected $controller_helper;
	protected $ppde_controller_main;
	protected $template;
	protected $user;
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config                    $config               Config object
	 * @param \phpbb\controller\helper                $controller_helper    Controller helper object
	 * @param \skouat\ppde\controller\main_controller $ppde_controller_main Donation pages main controller object
	 * @param \phpbb\template\template                $template             Template object
	 * @param \phpbb\user                             $user                 User object
	 * @param string                                  $php_ext              phpEx
	 *
	 * @return \skouat\ppde\event\listener
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $controller_helper, \skouat\ppde\controller\main_controller $ppde_controller_main, \phpbb\template\template $template, \phpbb\user $user, $php_ext)
	{
		$this->config = $config;
		$this->controller_helper = $controller_helper;
		$this->ppde_controller_main = $ppde_controller_main;
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
	public static function getSubscribedEvents()
	{
		return array(
			'core.index_modify_page_title'       => 'load_index_data',
			'core.page_header'                   => 'add_page_header_link',
			'core.permissions'                   => 'add_permissions',
			'core.user_setup'                    => 'load_language_on_setup',
			'core.viewonline_overwrite_location' => 'viewonline_page',
		);
	}

	/**
	 * Load data for donations statistics
	 *
	 * @return void
	 * @access public
	 */
	public function load_index_data()
	{
		if ($this->config['ppde_enable'] && $this->config['ppde_stats_index_enable'])
		{
			$this->template->assign_vars(array(
				'PPDE_STATS_INDEX_ENABLE' => $this->config['ppde_stats_index_enable'],
			));

			//Assign statistics vars to the template
			$this->ppde_controller_main->display_stats();
		}
	}

	/**
	 * Create a URL to the donation pages controller file for the header linklist
	 *
	 * @return void
	 * @access public
	 */
	public function add_page_header_link()
	{
		$this->template->assign_vars(array(
			'S_PPDE_LINK_ENABLED'           => $this->ppde_controller_main->can_use_ppde() && ($this->config['ppde_enable'] && $this->config['ppde_header_link']) ? true : false,
			'S_PPDE_LINK_DONORLIST_ENABLED' => $this->ppde_controller_main->can_view_ppde_donorlist() && $this->ppde_controller_main->use_ipn() && $this->config['ppde_ipn_donorlist_enable'] ? true : false,
			'U_PPDE_DONATE'                 => $this->controller_helper->route('skouat_ppde_donate'),
			'U_PPDE_DONORLIST'              => $this->controller_helper->route('skouat_ppde_donorlist'),
		));
	}

	/**
	 * Load language files during user setup
	 *
	 * @param object $event The event object
	 *
	 * @return void
	 * @access public
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'skouat/ppde',
			'lang_set' => array('donate', 'exceptions'),
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Show users as viewing the Donation page on Who Is Online page
	 *
	 * @param object $event The event object
	 *
	 * @return void
	 * @access public
	 */
	public function viewonline_page($event)
	{
		if ($event['on_page'][1] == 'app')
		{
			if (strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/donate') === 0)
			{
				$event['location'] = $this->user->lang('PPDE_VIEWONLINE');
				$event['location_url'] = $this->controller_helper->route('skouat_ppde_donate');
			}

			if (strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/donorlist') === 0)
			{
				$event['location'] = $this->user->lang('PPDE_VIEWONLINE_DONORLIST');
				$event['location_url'] = $this->controller_helper->route('skouat_ppde_donorlist');
			}
		}
	}

	/**
	 * Add extension permissions
	 *
	 * @param object $event The event object
	 *
	 * @return void
	 * @access public
	 */
	public function add_permissions($event)
	{
		$categories = $event['categories'];
		$categories = array_merge($categories, array('ppde' => 'ACL_CAT_PPDE'));
		$event['categories'] = $categories;

		$permissions = $event['permissions'];
		$permissions = array_merge($permissions, array(
			'a_ppde_manage'         => array('lang' => 'ACL_A_PPDE_MANAGE', 'cat' => 'ppde'),
			'u_ppde_use'            => array('lang' => 'ACL_U_PPDE_USE', 'cat' => 'ppde'),
			'u_ppde_view_donorlist' => array('lang' => 'ACL_U_PPDE_VIEW_DONORLIST', 'cat' => 'ppde'),
		));
		$event['permissions'] = $permissions;
	}
}
