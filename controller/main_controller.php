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

class main_controller implements main_interface
{
	protected $auth;
	protected $config;
	protected $container;
	protected $extension_manager;
	protected $helper;
	protected $ppde_entity_currency;
	protected $ppde_entity_donation_pages;
	protected $ppde_operator_currency;
	protected $ppde_operator_donation_pages;
	protected $request;
	protected $template;
	protected $user;
	protected $root_path;
	protected $php_ext;
	/** @var array */
	protected $ext_meta = array();
	/** @var string */
	protected $ext_name;
	/** @var string */
	private $donation_body;
	/** @var array */
	private $donation_content_data;
	/** @var string */
	private $return_args_url;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth                      $auth                         Auth object
	 * @param \phpbb\config\config                  $config                       Config object
	 * @param ContainerInterface                    $container                    Service container interface
	 * @param \phpbb\extension\manager              $extension_manager            An instance of the phpBB extension
	 *                                                                            manager
	 * @param \phpbb\controller\helper              $helper                       Controller helper object
	 * @param \skouat\ppde\entity\currency          $ppde_entity_currency         Currency entity object
	 * @param \skouat\ppde\entity\donation_pages    $ppde_entity_donation_pages   Donation pages entity object
	 * @param \skouat\ppde\operators\currency       $ppde_operator_currency       Currency operator object
	 * @param \skouat\ppde\operators\donation_pages $ppde_operator_donation_pages Donation pages operator object
	 * @param \phpbb\request\request                $request                      Request object
	 * @param \phpbb\template\template              $template                     Template object
	 * @param \phpbb\user                           $user                         User object
	 * @param string                                $root_path                    phpBB root path
	 * @param string                                $php_ext                      phpEx
	 *
	 * @return \skouat\ppde\controller\main_controller
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, ContainerInterface $container, \phpbb\extension\manager $extension_manager, \phpbb\controller\helper $helper, \skouat\ppde\entity\currency $ppde_entity_currency, \skouat\ppde\entity\donation_pages $ppde_entity_donation_pages, \skouat\ppde\operators\currency $ppde_operator_currency, \skouat\ppde\operators\donation_pages $ppde_operator_donation_pages, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->container = $container;
		$this->extension_manager = $extension_manager;
		$this->helper = $helper;
		$this->ppde_entity_currency = $ppde_entity_currency;
		$this->ppde_entity_donation_pages = $ppde_entity_donation_pages;
		$this->ppde_operator_currency = $ppde_operator_currency;
		$this->ppde_operator_donation_pages = $ppde_operator_donation_pages;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	public function handle()
	{
		// When this extension is disabled, redirect users back to the forum index
		// Else if user is not allowed to use it, disallow access to the extension main page
		if (empty($this->config['ppde_enable']))
		{
			redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
		}
		else if (!$this->can_use_ppde())
		{
			trigger_error('NOT_AUTHORISED');
		}

		$entity = $this->container->get('skouat.ppde.entity.donation_pages');
		$this->set_return_args_url($this->request->variable('return', 'body'));

		// Prepare message for display
		if ($this->get_donation_content_data($this->return_args_url))
		{
			$entity->get_vars();
			$this->donation_body = $entity->replace_template_vars($entity->get_message_for_display(
				$this->donation_content_data[0]['page_content'],
				$this->donation_content_data[0]['page_content_bbcode_uid'],
				$this->donation_content_data[0]['page_content_bbcode_bitfield'],
				$this->donation_content_data[0]['page_content_bbcode_options']
			));
		}

		$this->template->assign_vars(array(
			'DEFAULT_CURRENCY'   => $this->build_currency_select_menu($this->config['ppde_default_currency']),
			'DONATION_BODY'      => $this->donation_body,
			'IMG_LOADER'         => '<img src="' . $this->root_path . '../ext/skouat/ppde/images/loader.gif' . '" />',
			'PPDE_DEFAULT_VALUE' => $this->config['ppde_default_value'] ? $this->config['ppde_default_value'] : 0,
			'PPDE_LIST_VALUE'    => $this->build_currency_value_select_menu(),

			'S_HIDDEN_FIELDS'    => $this->paypal_hidden_fields(),
			'S_PPDE_FORM_ACTION' => $this->get_paypal_url(),
			'S_RETURN_ARGS'      => $this->return_args_url,
			'S_SANDBOX'          => $this->use_sandbox(),
		));

		$this->display_stats();

		// Send all data to the template file
		return $this->send_data_to_template();
	}

	/**
	 * @return bool
	 */
	public function can_use_ppde()
	{
		return $this->auth->acl_get('u_ppde_use');
	}

	/**
	 * @param string $set_return_args_url
	 */
	private function set_return_args_url($set_return_args_url)
	{
		switch ($set_return_args_url)
		{
			case 'cancel':
			case 'success':
				$this->template->assign_vars(array(
					'L_PPDE_DONATION_TITLE' => $this->user->lang['PPDE_' . strtoupper($set_return_args_url) . '_TITLE'],
				));
				$this->return_args_url = $set_return_args_url;
				break;
			default:
				$this->return_args_url = 'body';
		}

	}

	/**
	 * Get content of current donation pages
	 *
	 * @param string $return_args_url
	 *
	 * @return array
	 * @access private
	 */
	private function get_donation_content_data($return_args_url)
	{
		return $this->donation_content_data =
			$this->ppde_entity_donation_pages->get_data(
				$this->ppde_operator_donation_pages->build_sql_data($this->user->get_iso_lang_id(), $return_args_url));
	}

	/**
	 * Build pull down menu options of available currency
	 *
	 * @param int $config_value Currency identifier; default: 0
	 *
	 * @return null
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
	 * Build pull down menu options of available currency value
	 *
	 * @return string List of currency value set in ACP for dropdown menu
	 * @access private
	 */
	private function build_currency_value_select_menu()
	{
		$list_donation_value = '';

		if ($this->get_dropbox_status())
		{
			$donation_ary_value = explode(',', $this->config['ppde_dropbox_value']);

			foreach ($donation_ary_value as $value)
			{
				$int_value = $this->settype_dropbox_int_value($value);
				$list_donation_value .= !empty($int_value) ? '<option value="' . $int_value . '">' . $int_value . '</option>' : '';
			}
			unset($value);
		}

		return $list_donation_value;
	}

	/**
	 * Get dropbox config value
	 *
	 * @return bool
	 * @access private
	 */
	private function get_dropbox_status()
	{
		return $this->config['ppde_dropbox_enable'] && $this->config['ppde_dropbox_value'];
	}

	/**
	 * Force dropbox value to integer
	 *
	 * @param int $value
	 *
	 * @return int
	 */
	private function settype_dropbox_int_value($value = 0)
	{
		if (settype($value, 'integer') && $value != 0)
		{
			return $value;
		}

		return 0;
	}

	/**
	 * Build PayPal hidden fields
	 *
	 * @return string PayPal hidden field needed to fill PayPal forms
	 * @access private
	 */
	private function paypal_hidden_fields()
	{
		return build_hidden_fields(array(
			'cmd'           => '_donations',
			'business'      => $this->get_account_id(),
			'item_name'     => $this->user->lang['PPDE_DONATION_TITLE_HEAD'] . ' ' . $this->config['sitename'],
			'no_shipping'   => 1,
			'return'        => $this->generate_paypal_return_url('success'),
			'notify_url'    => $this->generate_paypal_notify_return_url(),
			'cancel_return' => $this->generate_paypal_return_url('cancel'),
			'item_number'   => 'uid_' . $this->user->data['user_id'] . '_' . time(),
			'tax'           => 0,
			'bn'            => 'Board_Donate_WPS',
			'charset'       => 'utf-8',
		));
	}

	/**
	 * Get PayPal account id
	 *
	 * @return string $this Paypal account Identifier
	 * @access private
	 */
	private function get_account_id()
	{
		return $this->use_sandbox() ? $this->config['ppde_sandbox_address'] : $this->config['ppde_account_id'];
	}

	/**
	 * Check if Sandbox is enable
	 *
	 * @return bool
	 * @access public
	 */
	public function use_sandbox()
	{
		return !empty($this->config['ppde_sandbox_enable']) && (!empty($this->config['ppde_sandbox_founder_enable']) && ($this->user->data['user_type'] == USER_FOUNDER) || empty($this->config['ppde_sandbox_founder_enable']));
	}

	/**
	 * Generate PayPal return URL
	 *
	 * @param string $arg
	 *
	 * @return string
	 * @access private
	 */
	private function generate_paypal_return_url($arg)
	{
		return generate_board_url(true) . $this->helper->route('skouat_ppde_donate', array('return' => $arg));
	}

	/**
	 * Generate PayPal return notify URL
	 *
	 * @return string
	 * @access private
	 */
	private function generate_paypal_notify_return_url()
	{
		return generate_board_url(true) . $this->helper->route('skouat_ppde_ipn_listener');
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
	 * Assign statistics vars to the template
	 *
	 * @return null
	 * @access public
	 */
	public function display_stats()
	{
		if ($this->config['ppde_goal_enable'] || $this->config['ppde_raised_enable'] || $this->config['ppde_used_enable'])
		{
			// Get data from the database
			$default_currency_data = $this->get_default_currency_data($this->config['ppde_default_currency']);

			$this->template->assign_vars(array(
				'PPDE_GOAL_ENABLE'   => $this->config['ppde_goal_enable'],
				'PPDE_RAISED_ENABLE' => $this->config['ppde_raised_enable'],
				'PPDE_USED_ENABLE'   => $this->config['ppde_used_enable'],

				'L_PPDE_GOAL'        => $this->get_ppde_goal_langkey($default_currency_data[0]['currency_symbol'], (bool) $default_currency_data[0]['currency_on_left']),
				'L_PPDE_RAISED'      => $this->get_ppde_raised_langkey($default_currency_data[0]['currency_symbol'], (bool) $default_currency_data[0]['currency_on_left']),
				'L_PPDE_USED'        => $this->get_ppde_used_langkey($default_currency_data[0]['currency_symbol'], (bool) $default_currency_data[0]['currency_on_left']),
			));

			// Generate statistics percent for display
			$this->generate_stats_percent();
		}
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
	 * Retrieve the language key for donation goal
	 *
	 * @param string $currency_symbol Currency symbol
	 * @param bool   $on_left         Symbol position
	 *
	 * @return string
	 * @access public
	 */
	public function get_ppde_goal_langkey($currency_symbol, $on_left = true)
	{
		if ((int) $this->config['ppde_goal'] <= 0)
		{
			$l_ppde_goal = $this->user->lang['DONATE_NO_GOAL'];
		}
		else if ((int) $this->config['ppde_goal'] < (int) $this->config['ppde_raised'])
		{
			$l_ppde_goal = $this->user->lang['DONATE_GOAL_REACHED'];
		}
		else
		{
			$l_ppde_goal = $this->user->lang('DONATE_GOAL_RAISE', $this->get_amount((int) $this->config['ppde_goal'], $currency_symbol, $on_left));
		}

		return $l_ppde_goal;
	}

	/**
	 * Put the currency on the left or on the right of the amount
	 *
	 * @param int    $value
	 * @param string $currency
	 * @param bool   $on_left
	 *
	 * @return string
	 */
	private function get_amount($value, $currency, $on_left = true)
	{
		return $on_left ? $currency . $value : $value . $currency;
	}

	/**
	 * Retrieve the language key for donation raised
	 *
	 * @param string $currency_symbol Currency symbol
	 * @param bool   $on_left         Symbol position
	 *
	 * @return string
	 * @access public
	 */
	public function get_ppde_raised_langkey($currency_symbol, $on_left = true)
	{
		if ((int) $this->config['ppde_raised'] <= 0)
		{
			$l_ppde_raised = $this->user->lang['DONATE_NOT_RECEIVED'];
		}
		else
		{
			$l_ppde_raised = $this->user->lang('DONATE_RECEIVED', $this->get_amount((int) $this->config['ppde_raised'], $currency_symbol, $on_left));
		}

		return $l_ppde_raised;
	}

	/**
	 * Retrieve the language key for donation used
	 *
	 * @param string $currency_symbol Currency symbol
	 * @param bool   $on_left         Symbol position
	 *
	 * @return string
	 * @access public
	 */
	public function get_ppde_used_langkey($currency_symbol, $on_left = true)
	{
		if ((int) $this->config['ppde_used'] <= 0)
		{
			$l_ppde_used = $this->user->lang['DONATE_NOT_USED'];
		}
		else if ((int) $this->config['ppde_used'] < (int) $this->config['ppde_raised'])
		{
			$l_ppde_used = $this->user->lang('DONATE_USED', $this->get_amount((int) $this->config['ppde_used'], $currency_symbol, $on_left), $this->get_amount((int) $this->config['ppde_raised'], $currency_symbol, $on_left));
		}
		else
		{
			$l_ppde_used = $this->user->lang('DONATE_USED_EXCEEDED', $this->get_amount((int) $this->config['ppde_used'], $currency_symbol, $on_left));
		}

		return $l_ppde_used;
	}

	/**
	 * Generate statistics percent for display
	 *
	 * @return null
	 * @access private
	 */
	private function generate_stats_percent()
	{
		if ($this->config['ppde_goal_enable'] && (int) $this->config['ppde_goal'] > 0)
		{
			$this->assign_vars_stats_percent((int) $this->config['ppde_raised'], (int) $this->config['ppde_goal'], 'GOAL_NUMBER');
		}

		if ($this->config['ppde_used_enable'] && (int) $this->config['ppde_raised'] > 0 && (int) $this->config['ppde_used'] > 0)
		{
			$this->assign_vars_stats_percent((int) $this->config['ppde_used'], (int) $this->config['ppde_raised'], 'USED_NUMBER');
		}
	}

	/**
	 * Assign statistics percent vars to template
	 *
	 * @param        $multiplicand
	 * @param        $dividend
	 * @param string $type
	 *
	 * @return null
	 * @access public
	 */
	private function assign_vars_stats_percent($multiplicand, $dividend, $type = '')
	{
		$this->template->assign_vars(array(
			'PPDE_' . $type => round(($multiplicand * 100) / $dividend, 2),
			'S_' . $type    => !empty($type) ? true : false,
		));
	}

	/**
	 * Send data to the template file
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @access private
	 */
	private function send_data_to_template()
	{
		switch ($this->return_args_url)
		{
			case 'cancel':
			case 'success':
				return $this->helper->render('donate_body.html', $this->user->lang('PPDE_' . strtoupper($this->return_args_url) . '_TITLE'));
			default:
				return $this->helper->render('donate_body.html', $this->user->lang('PPDE_DONATION_TITLE'));
		}
	}

	/**
	 * Check if cURL is available
	 *
	 * @return bool
	 * @access public
	 */
	public function check_curl()
	{
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
	 * Get extension metadata
	 *
	 * @return null
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
			$md_manager = new \phpbb\extension\metadata_manager($this->ext_name, $this->config, $this->extension_manager, $this->template, $this->user, $this->root_path);

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
	 * @return null
	 * @access protected
	 */
	protected function retrieve_ext_name()
	{
		$namespace_ary = explode('\\', __NAMESPACE__);
		$this->ext_name = $namespace_ary[0] . '/' . $namespace_ary[1];
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
}
