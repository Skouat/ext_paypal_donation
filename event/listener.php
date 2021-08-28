<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\event;

use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\language\language;
use phpbb\template\template;
use skouat\ppde\controller\main_controller;
use skouat\ppde\controller\main_display_stats;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{
	protected $config;
	protected $controller_helper;
	protected $language;
	protected $ppde_controller_main;
	protected $ppde_controller_display_stats;
	protected $template;
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param config             $config                        Config object
	 * @param helper             $controller_helper             Controller helper object
	 * @param language           $language                      Language user object
	 * @param main_controller    $ppde_controller_main          PPDE main controller object
	 * @param main_display_stats $ppde_controller_display_stats Display stats controller object
	 * @param template           $template                      Template object
	 * @param string             $php_ext                       phpEx
	 *
	 * @access public
	 */
	public function __construct(
		config $config,
		helper $controller_helper,
		language $language,
		main_controller $ppde_controller_main,
		main_display_stats $ppde_controller_display_stats,
		template $template,
		string $php_ext
	)
	{
		$this->config = $config;
		$this->controller_helper = $controller_helper;
		$this->language = $language;
		$this->ppde_controller_main = $ppde_controller_main;
		$this->ppde_controller_display_stats = $ppde_controller_display_stats;
		$this->template = $template;
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
		return [
			'core.index_modify_page_title'       => 'load_index_data',
			'core.page_header'                   => 'add_page_header_link',
			'core.permissions'                   => 'add_permissions',
			'core.user_setup'                    => 'load_language_on_setup',
			'core.viewonline_overwrite_location' => 'viewonline_page',
		];
	}

	/**
	 * Load data for donations statistics
	 *
	 * @return void
	 * @access public
	 */
	public function load_index_data(): void
	{
		if ($this->config['ppde_enable'] && $this->config['ppde_stats_index_enable'])
		{
			$this->template->assign_vars([
				'PPDE_STATS_INDEX_ENABLE' => $this->config['ppde_stats_index_enable'],
				'PPDE_STATS_POSITION'     => $this->config['ppde_stats_position'],
			]);

			//Assign statistics vars to the template
			$this->ppde_controller_display_stats->display_stats();
		}
	}

	/**
	 * Create a URL to the donation pages controller file for the header linklist
	 *
	 * @return void
	 * @access public
	 */
	public function add_page_header_link(): void
	{
		$this->template->assign_vars([
			'S_PPDE_LINK_ENABLED'           => $this->is_donate_link_allowed(),
			'S_PPDE_LINK_DONORLIST_ENABLED' => $this->is_donors_list_link_allowed(),
			'U_PPDE_DONATE'                 => $this->controller_helper->route('skouat_ppde_donate'),
			'U_PPDE_DONORLIST'              => $this->controller_helper->route('skouat_ppde_donorlist'),
		]);
	}

	/**
	 * Checks if the donate link can be displayed on the header
	 *
	 * @return bool
	 * @access private
	 */
	private function is_donate_link_allowed(): bool
	{
		return $this->ppde_controller_main->ppde_actions_auth->can_use_ppde() && $this->config['ppde_enable'] && $this->config['ppde_header_link'];
	}

	/**
	 * Checks if the donors list link can be displayed on the header
	 *
	 * @return bool
	 * @access private
	 */
	private function is_donors_list_link_allowed(): bool
	{
		return $this->ppde_controller_main->ppde_actions_auth->can_view_ppde_donorlist() && $this->ppde_controller_main->use_ipn() && $this->config['ppde_ipn_donorlist_enable'];
	}

	/**
	 * Load language files during user setup
	 *
	 * @param \phpbb\event\data $event The event object
	 *
	 * @return void
	 * @access public
	 */
	public function load_language_on_setup($event): void
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'skouat/ppde',
			'lang_set' => ['donate', 'exceptions'],
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Show users as viewing the Donation page on Who Is Online page
	 *
	 * @param \phpbb\event\data $event The event object
	 *
	 * @return void
	 * @access public
	 */
	public function viewonline_page($event): void
	{
		if ($event['on_page'][1] == 'app')
		{
			if (strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/donate') === 0)
			{
				$event['location'] = $this->language->lang('PPDE_VIEWONLINE');
				$event['location_url'] = $this->controller_helper->route('skouat_ppde_donate');
			}

			if (strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/donorlist') === 0)
			{
				$event['location'] = $this->language->lang('PPDE_VIEWONLINE_DONORLIST');
				$event['location_url'] = $this->controller_helper->route('skouat_ppde_donorlist');
			}
		}
	}

	/**
	 * Add extension permissions
	 *
	 * @param \phpbb\event\data $event The event object
	 *
	 * @return void
	 * @access public
	 */
	public function add_permissions($event): void
	{
		$event->update_subarray('categories', 'ppde', 'ACL_CAT_PPDE');
		$event->update_subarray('permissions', 'a_ppde_manage', ['lang' => 'ACL_A_PPDE_MANAGE', 'cat' => 'ppde']);
		$event->update_subarray('permissions', 'u_ppde_use', ['lang' => 'ACL_U_PPDE_USE', 'cat' => 'ppde']);
		$event->update_subarray('permissions', 'u_ppde_view_donorlist', ['lang' => 'ACL_U_PPDE_VIEW_DONORLIST', 'cat' => 'ppde']);
	}
}
