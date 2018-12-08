<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\DependencyInjection\ContainerInterface;

class main_controller
{
	/** Production Postback URL */
	const VERIFY_URI = 'https://ipnpb.paypal.com/cgi-bin/webscr';
	/** Sandbox Postback URL */
	const SANDBOX_VERIFY_URI = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';

	protected $auth;
	protected $config;
	protected $container;
	protected $helper;
	protected $language;
	protected $ppde_entity_currency;
	protected $ppde_operator_currency;
	protected $request;
	protected $template;
	protected $user;
	protected $root_path;
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param auth                            $auth                   Auth object
	 * @param config                          $config                 Config object
	 * @param ContainerInterface              $container              Service container interface
	 * @param helper                          $helper                 Controller helper object
	 * @param language                        $language               Language user object
	 * @param \skouat\ppde\entity\currency    $ppde_entity_currency   Currency entity object
	 * @param \skouat\ppde\operators\currency $ppde_operator_currency Currency operator object
	 * @param request                         $request                Request object
	 * @param template                        $template               Template object
	 * @param user                            $user                   User object
	 * @param string                          $root_path              phpBB root path
	 * @param string                          $php_ext                phpEx
	 *
	 * @access public
	 */
	public function __construct(auth $auth, config $config, ContainerInterface $container, helper $helper, language $language, \skouat\ppde\entity\currency $ppde_entity_currency, \skouat\ppde\operators\currency $ppde_operator_currency, request $request, template $template, user $user, $root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->container = $container;
		$this->helper = $helper;
		$this->language = $language;
		$this->ppde_entity_currency = $ppde_entity_currency;
		$this->ppde_operator_currency = $ppde_operator_currency;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
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
	 * @access public
	 */
	public function can_use_ppde()
	{
		return $this->auth->acl_get('u_ppde_use');
	}

	/**
	 * @return bool
	 * @access public
	 */
	public function can_view_ppde_donorlist()
	{
		return $this->auth->acl_get('u_ppde_view_donorlist');
	}

	/**
	 * @return bool
	 * @access private
	 */
	public function donorlist_is_enabled()
	{
		return $this->use_ipn() && $this->config['ppde_ipn_donorlist_enable'];
	}

	/**
	 * Check if IPN is enabled based on config value
	 *
	 * @return bool
	 * @access public
	 */
	public function use_ipn()
	{
		return !empty($this->config['ppde_enable']) && !empty($this->config['ppde_ipn_enable']) && $this->is_ipn_requirement_satisfied();
	}

	/**
	 * Check if IPN requirements are satisfied based on config value
	 *
	 * @return bool
	 * @access public
	 */
	public function is_ipn_requirement_satisfied()
	{
		return !empty($this->config['ppde_curl_detected']) && !empty($this->config['ppde_tls_detected']);
	}

	/**
	 * Build pull down menu options of available currency
	 *
	 * @param int $config_value Currency identifier; default: 0
	 *
	 * @return void
	 * @access public
	 */
	public function build_currency_select_menu($config_value = 0)
	{
		// Grab the list of all enabled currencies; 0 is for all data
		$currency_items = $this->ppde_entity_currency->get_data($this->ppde_operator_currency->build_sql_data(0, true));

		// Process each rule menu item for pull-down
		foreach ($currency_items as $currency_item)
		{
			// Set output block vars for display in the template
			$this->template->assign_block_vars('options', array(
				'CURRENCY_ID'        => (int) $currency_item['currency_id'],
				'CURRENCY_ISO_CODE'  => $currency_item['currency_iso_code'],
				'CURRENCY_NAME'      => $currency_item['currency_name'],
				'CURRENCY_SYMBOL'    => $currency_item['currency_symbol'],
				'S_CURRENCY_DEFAULT' => $config_value == $currency_item['currency_id'],
			));
		}
		unset ($currency_items, $currency_item);
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
	public function get_paypal_uri($is_test_ipn = false)
	{
		return ($is_test_ipn || $this->use_sandbox()) ? self::SANDBOX_VERIFY_URI : self::VERIFY_URI;
	}

	/**
	 * Check if Sandbox is enabled based on config value
	 *
	 * @return bool
	 * @access public
	 */
	public function use_sandbox()
	{
		return $this->use_ipn() && !empty($this->config['ppde_sandbox_enable']) && $this->is_sandbox_founder_enable();
	}

	/**
	 * Check if Sandbox could be use by founders based on config value
	 *
	 * @return bool
	 * @access public
	 */
	public function is_sandbox_founder_enable()
	{
		return (!empty($this->config['ppde_sandbox_founder_enable']) && ($this->user->data['user_type'] == USER_FOUNDER)) || empty($this->config['ppde_sandbox_founder_enable']);
	}

	/**
	 * Get default currency symbol
	 *
	 * @param int $id Currency identifier; default: 0
	 *
	 * @return array
	 * @access public
	 */
	public function get_default_currency_data($id = 0)
	{
		return $this->ppde_entity_currency->get_data($this->ppde_operator_currency->build_sql_data($id, true));
	}

	/**
	 * Put the currency on the left or on the right of the amount
	 *
	 * @param int|float $value
	 * @param string    $currency
	 * @param bool      $on_left
	 * @param string    $dec_point
	 * @param string    $thousands_sep
	 *
	 * @return string
	 * @access public
	 */
	public function currency_on_left($value, $currency, $on_left = true, $dec_point = '.', $thousands_sep = '')
	{
		return $on_left ? $currency . number_format(round($value, 2), 2, $dec_point, $thousands_sep) : number_format(round($value, 2), 2, $dec_point, $thousands_sep) . $currency;
	}

	/**
	 * Get currency data based on currency ISO code
	 *
	 * @param string $iso_code
	 *
	 * @return array
	 * @access public
	 */
	public function get_currency_data($iso_code)
	{
		$this->ppde_entity_currency->data_exists($this->ppde_entity_currency->build_sql_data_exists($iso_code));

		return $this->get_default_currency_data($this->ppde_entity_currency->get_id());
	}
}
