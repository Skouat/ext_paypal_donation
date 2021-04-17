<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
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
		// We stop the execution of the code because nothing need to be returned to phpBB.
		garbage_collection();
		exit_handler();
	}

	/**
	 * @return bool
	 * @access private
	 */
	public function donorlist_is_enabled(): bool
	{
		return $this->use_ipn() && $this->config['ppde_ipn_donorlist_enable'];
	}

	/**
	 * Check if IPN is enabled based on config value
	 *
	 * @return bool
	 * @access public
	 */
	public function use_ipn(): bool
	{
		return !empty($this->config['ppde_enable']) && !empty($this->config['ppde_ipn_enable']) && $this->is_ipn_requirement_satisfied();
	}

	/**
	 * Check if IPN requirements are satisfied based on config value
	 *
	 * @return bool
	 * @access public
	 */
	public function is_ipn_requirement_satisfied(): bool
	{
		return !empty($this->config['ppde_curl_detected']) && !empty($this->config['ppde_tls_detected']);
	}

	/**
	 * Get PayPal URI
	 * Used in form and in IPN process
	 *
	 * @param bool $is_test_ipn
	 *
	 * @return string
	 * @access public
	 */
	public function get_paypal_uri($is_test_ipn = false): string
	{
		$remote_list = ipn_paypal::get_remote_uri();

		if ($is_test_ipn || $this->use_sandbox())
		{
			return $remote_list[$this->config['ppde_sandbox_remote']]['uri'];
		}

		return $remote_list[$this->config['ppde_default_remote']]['uri'];
	}

	/**
	 * Check if Sandbox is enabled based on config value
	 *
	 * @return bool
	 * @access public
	 */
	public function use_sandbox(): bool
	{
		return $this->use_ipn() && !empty($this->config['ppde_sandbox_enable']) && $this->is_sandbox_founder_enable();
	}

	/**
	 * Check if Sandbox could be use by founders based on config value
	 *
	 * @return bool
	 * @access public
	 */
	public function is_sandbox_founder_enable(): bool
	{
		return (!empty($this->config['ppde_sandbox_founder_enable']) && ((int) $this->user->data['user_type'] === USER_FOUNDER)) || empty($this->config['ppde_sandbox_founder_enable']);
	}
}
