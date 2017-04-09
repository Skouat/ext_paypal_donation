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

use Symfony\Component\DependencyInjection\ContainerInterface;

class main_controller
{
	protected $auth;
	protected $config;
	protected $container;
	protected $extension_manager;
	protected $helper;
	protected $language;
	protected $ppde_entity_currency;
	protected $ppde_operator_currency;
	protected $request;
	protected $template;
	protected $user;
	protected $root_path;
	protected $php_ext;
	/** @var array */
	protected $ext_meta = array();
	/** @var string */
	protected $ext_name;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth                $auth                   Auth object
	 * @param \phpbb\config\config            $config                 Config object
	 * @param ContainerInterface              $container              Service container interface
	 * @param \phpbb\extension\manager        $extension_manager      An instance of the phpBB extension manager
	 * @param \phpbb\controller\helper        $helper                 Controller helper object
	 * @param \phpbb\language\language        $language               Language user object
	 * @param \skouat\ppde\entity\currency    $ppde_entity_currency   Currency entity object
	 * @param \skouat\ppde\operators\currency $ppde_operator_currency Currency operator object
	 * @param \phpbb\request\request          $request                Request object
	 * @param \phpbb\template\template        $template               Template object
	 * @param \phpbb\user                     $user                   User object
	 * @param string                          $root_path              phpBB root path
	 * @param string                          $php_ext                phpEx
	 *
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, ContainerInterface $container, \phpbb\extension\manager $extension_manager, \phpbb\controller\helper $helper, \phpbb\language\language $language, \skouat\ppde\entity\currency $ppde_entity_currency, \skouat\ppde\operators\currency $ppde_operator_currency, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->container = $container;
		$this->extension_manager = $extension_manager;
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
		return !empty($this->config['ppde_enable']) && !empty($this->config['ppde_ipn_enable']) && $this->is_remote_detected();
	}

	/**
	 * Check if remote is detected based on config value
	 *
	 * @return bool
	 * @access public
	 */
	public function is_remote_detected()
	{
		return !empty($this->config['ppde_curl_detected']) || !empty($this->config['ppde_fsockopen_detected']);
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
	 * Get PayPal URL
	 * Used in form and in IPN process
	 *
	 * @param bool $is_test_ipn
	 *
	 * @return string
	 * @access public
	 */
	public function get_paypal_url($is_test_ipn = false)
	{
		return ($is_test_ipn || $this->use_sandbox()) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
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
	 * @param int $id
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
	 *
	 * @return string
	 * @access public
	 */
	public function currency_on_left($value, $currency, $on_left = true)
	{
		return $on_left ? $currency . round($value, 2) : round($value, 2) . $currency;
	}

	/**
	 * Do action if it's the first time the extension is accessed
	 *
	 * @return void
	 * @access public
	 */
	public function first_start()
	{
		if ($this->config['ppde_first_start'])
		{
			$this->set_curl_info();
			$this->set_remote_detected();
			$this->config->set('ppde_first_start', false);
		}
	}

	/**
	 * Set config value for cURL version
	 *
	 * @return void
	 * @access public
	 */
	public function set_curl_info()
	{
		// Get cURL version informations
		if ($curl_info = $this->check_curl(true))
		{
			$this->config->set('ppde_curl_version', $curl_info['version']);
			$this->config->set('ppde_curl_ssl_version', $curl_info['ssl_version']);
		}
	}

	/**
	 * Set config value for cURL and fsockopen
	 *
	 * @return void
	 * @access public
	 */
	public function set_remote_detected()
	{
		$this->config->set('ppde_curl_detected', $this->check_curl());
		$this->config->set('ppde_fsock_detected', $this->check_fsockopen());
	}

	/**
	 * Check if cURL is available
	 *
	 * @param bool $check_version
	 *
	 * @return array|bool
	 * @access public
	 */
	public function check_curl($check_version = false)
	{
		if (function_exists('curl_version') && $check_version)
		{
			return curl_version();
		}

		if (function_exists('curl_init') && function_exists('curl_exec'))
		{
			$this->get_ext_meta();

			$ch = curl_init($this->ext_meta['extra']['version-check']['host']);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$response = curl_exec($ch);
			$response_status = strval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

			curl_close($ch);

			return ($response !== false || $response_status !== '0') ? true : false;
		}

		return false;
	}

	/**
	 * Check if fsockopen is available
	 *
	 * @return bool
	 * @access public
	 */
	public function check_fsockopen()
	{
		if (function_exists('fsockopen'))
		{
			$this->get_ext_meta();

			$url = parse_url($this->ext_meta['extra']['version-check']['host']);

			$fp = @fsockopen($url['path'], 80);

			return ($fp !== false) ? true : false;
		}

		return false;
	}

	/**
	 * Get extension metadata
	 *
	 * @return void
	 * @access protected
	 */
	protected function get_ext_meta()
	{
		if (empty($this->ext_meta))
		{
			$this->load_metadata();
		}
	}

	/**
	 * Load metadata for this extension
	 *
	 * @return array
	 * @access public
	 */
	public function load_metadata()
	{
		// Retrieve the extension name based on the namespace of this file
		$this->retrieve_ext_name();

		// If they've specified an extension, let's load the metadata manager and validate it.
		if ($this->ext_name)
		{
			$md_manager = new \phpbb\extension\metadata_manager($this->ext_name, $this->config, $this->extension_manager, $this->root_path);

			try
			{
				$this->ext_meta = $md_manager->get_metadata('all');
			}
			catch (\phpbb\extension\exception $e)
			{
				trigger_error($e, E_USER_WARNING);
			}
		}

		return $this->ext_meta;
	}

	/**
	 * Retrieve the extension name
	 *
	 * @return void
	 * @access protected
	 */
	protected function retrieve_ext_name()
	{
		$namespace_ary = explode('\\', __NAMESPACE__);
		$this->ext_name = $namespace_ary[0] . '/' . $namespace_ary[1];
	}
}
