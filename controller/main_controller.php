<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller;

use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\user_loader;
use Symfony\Component\DependencyInjection\ContainerInterface;

class main_controller
{
	protected $config;
	protected $container;
	protected $helper;
	protected $language;
	protected $ppde_actions_currency;
	protected $request;
	protected $template;
	protected $user;
	protected $user_loader;
	protected $root_path;
	protected $php_ext;

	public $ppde_actions_auth;

	/**
	 * Constructor
	 *
	 * @param config                        $config                Config object
	 * @param ContainerInterface            $container             Service container interface
	 * @param helper                        $helper                Controller helper object
	 * @param language                      $language              Language user object
	 * @param \skouat\ppde\actions\auth     $ppde_actions_auth     PPDE auth actions object
	 * @param \skouat\ppde\actions\currency $ppde_actions_currency PPDE currency actions object
	 * @param request                       $request               Request object
	 * @param template                      $template              Template object
	 * @param user                          $user                  User object
	 * @param user_loader                   $user_loader           User loader object
	 * @param string                        $root_path             phpBB root path
	 * @param string                        $php_ext               phpEx
	 *
	 * @access public
	 */
	public function __construct(
		config $config,
		ContainerInterface $container,
		helper $helper,
		language $language,
		\skouat\ppde\actions\auth $ppde_actions_auth,
		\skouat\ppde\actions\currency $ppde_actions_currency,
		request $request,
		template $template,
		user $user,
		user_loader $user_loader,
		string $root_path,
		string $php_ext
	)
	{
		$this->config = $config;
		$this->container = $container;
		$this->helper = $helper;
		$this->language = $language;
		$this->ppde_actions_auth = $ppde_actions_auth;
		$this->ppde_actions_currency = $ppde_actions_currency;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->user_loader = $user_loader;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	public function handle()
	{
		// We stop the execution of the code because nothing needs to be returned to phpBB.
		garbage_collection();
		exit_handler();
	}

	/**
	 * Check if the donation feature is enabled.
	 *
	 * @return bool
	 * @access public
	 */
	public function is_donation_active(): bool
	{
		return !empty($this->config['ppde_enable']);
	}

	/**
	 * Check if the donors list is enabled.
	 *
	 * @return bool
	 * @access public
	 */
	public function donorlist_is_enabled(): bool
	{
		return $this->is_donation_active() && !empty($this->config['ppde_ipn_donorlist_enable']);
	}

	/**
	 * Check if the Sandbox environment must be used for the current user.
	 *
	 * @return bool
	 * @access public
	 */
	public function use_sandbox(): bool
	{
		return $this->is_donation_active() && !empty($this->config['ppde_sandbox_enable']) && $this->is_sandbox_founder_enable();
	}

	/**
	 * Check if Sandbox can be used by the current user, based on the founder setting.
	 *
	 * @return bool
	 * @access public
	 */
	public function is_sandbox_founder_enable(): bool
	{
		return (!empty($this->config['ppde_sandbox_founder_enable']) && ((int) $this->user->data['user_type'] === USER_FOUNDER)) || empty($this->config['ppde_sandbox_founder_enable']);
	}
}
