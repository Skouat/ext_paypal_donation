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
	protected $ppde_entity_currency;
	protected $ppde_entity_donation_pages;
	protected $ppde_entity_transactions;
	protected $ppde_operator_currency;
	protected $ppde_operator_donation_pages;
	protected $ppde_operator_transactions;
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
	/** @var string */
	private $return_args_url;
	/** @var string */
	private $u_action;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth                      $auth                         Auth object
	 * @param \phpbb\config\config                  $config                       Config object
	 * @param ContainerInterface                    $container                    Service container interface
	 * @param \phpbb\extension\manager              $extension_manager            An instance of the phpBB extension manager
	 * @param \phpbb\controller\helper              $helper                       Controller helper object
	 * @param \skouat\ppde\entity\currency          $ppde_entity_currency         Currency entity object
	 * @param \skouat\ppde\entity\donation_pages    $ppde_entity_donation_pages   Donation pages entity object
	 * @param \skouat\ppde\entity\transactions      $ppde_entity_transactions     Transactions log entity object
	 * @param \skouat\ppde\operators\currency       $ppde_operator_currency       Currency operator object
	 * @param \skouat\ppde\operators\donation_pages $ppde_operator_donation_pages Donation pages operator object
	 * @param \skouat\ppde\operators\transactions   $ppde_operator_transactions   Transactions log operator object
	 * @param \phpbb\request\request                $request                      Request object
	 * @param \phpbb\template\template              $template                     Template object
	 * @param \phpbb\user                           $user                         User object
	 * @param string                                $root_path                    phpBB root path
	 * @param string                                $php_ext                      phpEx
	 *
	 * @return \skouat\ppde\controller\main_controller
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, ContainerInterface $container, \phpbb\extension\manager $extension_manager, \phpbb\controller\helper $helper, \skouat\ppde\entity\currency $ppde_entity_currency, \skouat\ppde\entity\donation_pages $ppde_entity_donation_pages, \skouat\ppde\entity\transactions $ppde_entity_transactions, \skouat\ppde\operators\currency $ppde_operator_currency, \skouat\ppde\operators\donation_pages $ppde_operator_donation_pages, \skouat\ppde\operators\transactions $ppde_operator_transactions, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->container = $container;
		$this->extension_manager = $extension_manager;
		$this->helper = $helper;
		$this->ppde_entity_currency = $ppde_entity_currency;
		$this->ppde_entity_donation_pages = $ppde_entity_donation_pages;
		$this->ppde_entity_transactions = $ppde_entity_transactions;
		$this->ppde_operator_currency = $ppde_operator_currency;
		$this->ppde_operator_donation_pages = $ppde_operator_donation_pages;
		$this->ppde_operator_transactions = $ppde_operator_transactions;
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

		$this->set_return_args_url($this->request->variable('return', 'body'));

		// Prepare message for display
		if ($this->get_donation_content_data($this->return_args_url))
		{
			$this->ppde_entity_donation_pages->get_vars();
			$this->donation_body = $this->ppde_entity_donation_pages->replace_template_vars($this->ppde_entity_donation_pages->get_message_for_display());
		}

		$this->build_currency_select_menu($this->config['ppde_default_currency']);

		$this->template->assign_vars(array(
			'DONATION_BODY'      => $this->donation_body,
			'PPDE_DEFAULT_VALUE' => $this->config['ppde_default_value'] ? $this->config['ppde_default_value'] : 0,
			'PPDE_LIST_VALUE'    => $this->build_currency_value_select_menu($this->config['ppde_default_value']),

			'S_HIDDEN_FIELDS'    => $this->paypal_hidden_fields(),
			'S_PPDE_FORM_ACTION' => $this->get_paypal_url(),
			'S_RETURN_ARGS'      => $this->return_args_url,
			'S_SANDBOX'          => $this->use_sandbox(),
		));

		$this->display_stats();

		// Send all data to the template file
		return $this->send_data_to_template();
	}

	public function donorlist_handle()
	{
		// If the donorlist is not enabled, redirect users back to the forum index
		// Else if user is not allowed to view the donors list, disallow access to the extension page
		if (!$this->donorlist_is_enabled())
		{
			redirect(append_sid($this->root_path . 'index.' . $this->php_ext));
		}
		else if (!$this->can_view_ppde_donorlist())
		{
			trigger_error('NOT_AUTHORISED');
		}

		// Get needed container
		/** @type \phpbb\pagination $pagination */
		$pagination = $this->container->get('pagination');
		/** @type \phpbb\path_helper $path_helper */
		$path_helper = $this->container->get('path_helper');

		// Set up general vars
		$default_key = 'd';
		$sort_key = $this->request->variable('sk', $default_key);
		$sort_dir = $this->request->variable('sd', 'd');
		$start = $this->request->variable('start', 0);

		// Sorting and order
		$sort_key_sql = array('a' => 'amount', 'd' => 'txn.payment_date', 'u' => 'u.username_clean');

		if (!isset($sort_key_sql[$sort_key]))
		{
			$sort_key = $default_key;
		}

		$order_by = $sort_key_sql[$sort_key] . ' ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');

		// Build a relevant pagination_url and sort_url
		// We do not use request_var() here directly to save some calls (not all variables are set)
		$check_params = array(
			'sk'    => array('sk', $default_key),
			'sd'    => array('sd', 'a'),
			'start' => array('start', 0),
		);

		$params = $this->check_params($check_params, array('start'));
		$sort_params = $this->check_params($check_params, array('sk', 'sd'));

		// Set '$this->u_action'
		$use_page = ($this->u_action) ? $this->u_action : $this->user->page['page_name'];
		$this->u_action = reapply_sid($path_helper->get_valid_page($use_page, $this->config['enable_mod_rewrite']));

		$pagination_url = append_sid($this->u_action, implode('&amp;', $params), true, false, true);
		$sort_url = $this->set_url_delim(append_sid($this->u_action, implode('&amp;', $sort_params), true, false, true), $sort_params);

		$get_donorlist_sql_ary = $this->ppde_operator_transactions->get_sql_donorlist_ary(false, $order_by);
		$total_donors = $this->ppde_operator_transactions->query_sql_count($get_donorlist_sql_ary, 'txn.user_id');
		$start = $pagination->validate_start($start, $this->config['topics_per_page'], $total_donors);

		$pagination->generate_template_pagination($pagination_url, 'pagination', 'start', $total_donors, $this->config['topics_per_page'], $start);

		// adds fields to the table schema needed by entity->import()
		$additional_table_schema = array(
			'item_username'    => array('name' => 'username', 'type' => 'string'),
			'item_user_colour' => array('name' => 'user_colour', 'type' => 'string'),
			'item_amount'      => array('name' => 'amount', 'type' => 'float'),
			'item_max_txn_id'  => array('name' => 'max_txn_id', 'type' => 'integer'),
		);

		$data_ary = $this->ppde_entity_transactions->get_data($this->ppde_operator_transactions->build_sql_donorlist_data($get_donorlist_sql_ary), $additional_table_schema, $this->config['topics_per_page'], $start);

		// Get default currency data from the database
		$default_currency_data = $this->get_default_currency_data($this->config['ppde_default_currency']);
		$this->template->assign_vars(array(
			'TOTAL_DONORS'    => $this->user->lang('PPDE_DONORS', $total_donors),
			'U_SORT_AMOUNT'   => $sort_url . 'sk=a&amp;sd=' . $this->set_sort_key($sort_key, 'a', $sort_dir),
			'U_SORT_DONATED'  => $sort_url . 'sk=d&amp;sd=' . $this->set_sort_key($sort_key, 'd', $sort_dir),
			'U_SORT_USERNAME' => $sort_url . 'sk=u&amp;sd=' . $this->set_sort_key($sort_key, 'u', $sort_dir),
		));

		foreach ($data_ary as $data)
		{
			$get_last_transaction_sql_ary = $this->ppde_operator_transactions->get_sql_donorlist_ary($data['max_txn_id']);
			$last_donation_data = $this->ppde_entity_transactions->get_data($this->ppde_operator_transactions->build_sql_donorlist_data($get_last_transaction_sql_ary));
			$this->template->assign_block_vars('donorrow', array(
				'PPDE_DONOR_USERNAME'       => get_username_string('full', $data['user_id'], $data['username'], $data['user_colour']),
				'PPDE_LAST_DONATED_AMOUNT'  => $this->currency_on_left(number_format($last_donation_data[0]['mc_gross'], 2), $default_currency_data[0]['currency_symbol'], (bool) $default_currency_data[0]['currency_on_left']),
				'PPDE_LAST_PAYMENT_DATE'    => $this->user->format_date($last_donation_data[0]['payment_date']),
				'PPDE_TOTAL_DONATED_AMOUNT' => $this->currency_on_left(number_format($data['amount'], 2), $default_currency_data[0]['currency_symbol'], (bool) $default_currency_data[0]['currency_on_left']),
			));
		}

		// Set "return_args_url" object before sending data to template
		$this->set_return_args_url('donorlist');

		// Send all data to the template file
		return $this->send_data_to_template();
	}

	/**
	 * Set the sort key value
	 *
	 * @param string $sk
	 * @param string $sk_comp
	 * @param string $sd
	 *
	 * @return string
	 * @access private
	 */
	private function set_sort_key($sk, $sk_comp, $sd)
	{
		return ($sk == $sk_comp && $sd == 'a') ? 'd' : 'a';
	}

	/**
	 * Simply adds an url or &amp; delimiter to the url when params is empty
	 *
	 * @param $url
	 * @param $params
	 *
	 * @return string
	 * @access private
	 */
	private function set_url_delim($url, $params)
	{
		return (empty($params)) ? $url . '?' : $url . '&amp;';
	}

	/**
	 * @param array    $params_ary
	 * @param string[] $excluded_keys
	 *
	 * @return array
	 * @access private
	 */
	private function check_params($params_ary, $excluded_keys)
	{
		$params = array();

		foreach ($params_ary as $key => $call)
		{
			if (!$this->request->is_set($key))
			{
				continue;
			}

			$param = call_user_func_array('request_var', $call);
			$param = urlencode($key) . '=' . ((is_string($param)) ? urlencode($param) : $param);

			if (!in_array($key, $excluded_keys))
			{
				$params[] = $param;
			}
		}

		return $params;
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
	private function donorlist_is_enabled()
	{
		return $this->use_ipn() && $this->config['ppde_ipn_donorlist_enable'];
	}

	/**
	 * @param string $set_return_args_url
	 *
	 * @return void
	 * @access private
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
			case 'donorlist':
				$this->template->assign_vars(array(
					'L_PPDE_DONORLIST_TITLE' => $this->user->lang['PPDE_DONORLIST_TITLE'],
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
		return $this->ppde_entity_donation_pages->get_data(
				$this->ppde_operator_donation_pages->build_sql_data($this->user->get_iso_lang_id(), $return_args_url)
		);
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
	 * Build pull down menu options of available currency value
	 *
	 * @param int $default_value
	 *
	 * @return string List of currency value set in ACP for dropdown menu
	 * @access private
	 */
	private function build_currency_value_select_menu($default_value = 0)
	{
		$list_donation_value = '';

		if ($this->get_dropbox_status())
		{
			$donation_ary_value = explode(',', $this->config['ppde_dropbox_value']);

			foreach ($donation_ary_value as $value)
			{
				$int_value = $this->settype_dropbox_int_value($value);
				$selected = ($int_value == $default_value) && ($default_value != 0) ? ' selected="selected"' : '';
				$list_donation_value .= !empty($int_value) ? '<option value="' . $int_value . '"' . $selected . '>' . $int_value . '</option>' : '';
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
	 * @access private
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
	 * @return void
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
			$l_ppde_goal = $this->user->lang['PPDE_DONATE_NO_GOAL'];
		}
		else if ((int) $this->config['ppde_goal'] < (int) $this->config['ppde_raised'])
		{
			$l_ppde_goal = $this->user->lang['PPDE_DONATE_GOAL_REACHED'];
		}
		else
		{
			$l_ppde_goal = $this->user->lang('PPDE_DONATE_GOAL_RAISE', $this->currency_on_left((float) $this->config['ppde_goal'], $currency_symbol, $on_left));
		}

		return $l_ppde_goal;
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
			$l_ppde_raised = $this->user->lang['PPDE_DONATE_NOT_RECEIVED'];
		}
		else
		{
			$l_ppde_raised = $this->user->lang('PPDE_DONATE_RECEIVED', $this->currency_on_left((float) $this->config['ppde_raised'], $currency_symbol, $on_left));
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
			$l_ppde_used = $this->user->lang['PPDE_DONATE_NOT_USED'];
		}
		else if ((int) $this->config['ppde_used'] < (int) $this->config['ppde_raised'])
		{
			$l_ppde_used = $this->user->lang('PPDE_DONATE_USED', $this->currency_on_left((float) $this->config['ppde_used'], $currency_symbol, $on_left), $this->currency_on_left((float) $this->config['ppde_raised'], $currency_symbol, $on_left));
		}
		else
		{
			$l_ppde_used = $this->user->lang('PPDE_DONATE_USED_EXCEEDED', $this->currency_on_left((float) $this->config['ppde_used'], $currency_symbol, $on_left));
		}

		return $l_ppde_used;
	}

	/**
	 * Generate statistics percent for display
	 *
	 * @return void
	 * @access private
	 */
	private function generate_stats_percent()
	{
		if ($this->is_ppde_goal_stats())
		{
			$percent = $this->percent_value((float) $this->config['ppde_raised'], (float) $this->config['ppde_goal']);
			$this->assign_vars_stats_percent($percent, 'GOAL_NUMBER');
			$this->template->assign_var('PPDE_CSS_GOAL_NUMBER', $this->ppde_css_classname($percent));
		}

		if ($this->is_ppde_used_stats())
		{
			$percent = $this->percent_value((float) $this->config['ppde_used'], (float) $this->config['ppde_raised']);
			$this->assign_vars_stats_percent($percent, 'USED_NUMBER');
			$this->template->assign_var('PPDE_CSS_USED_NUMBER', $this->ppde_css_classname($percent, true));
		}
	}

	/**
	 * Checks if stats can be displayed
	 *
	 * @return bool
	 * @access private
	 */
	private function is_ppde_goal_stats()
	{
		return $this->config['ppde_goal_enable'] && (int) $this->config['ppde_goal'] > 0;
	}

	/**
	 * Checks if stats can be displayed
	 *
	 * @return bool
	 * @access private
	 */
	private function is_ppde_used_stats()
	{
		return $this->config['ppde_used_enable'] && (int) $this->config['ppde_raised'] > 0 && (int) $this->config['ppde_used'] > 0;
	}


	/**
	 * Returns percent value
	 *
	 * @param float $multiplicand
	 * @param float $dividend
	 *
	 * @return float
	 * @access private
	 */
	private function percent_value($multiplicand, $dividend)
	{
		return ($multiplicand * 100) / $dividend;
	}

	/**
	 * Assign statistics percent vars to template
	 *
	 * @param float  $percent
	 * @param string $varname
	 *
	 * @return void
	 * @access private
	 */
	private function assign_vars_stats_percent($percent, $varname)
	{
		// Force $varname to be in upper case
		$varname = strtoupper($varname);

		$this->template->assign_vars(array(
			'PPDE_' . $varname => ($percent < 100) ? round($percent, 2) : round($percent, 0),
			'S_' . $varname    => true,
		));
	}

	/**
	 * Returns the CSS class name based on the percent of stats
	 *
	 * @param float $value
	 * @param bool  $reverse
	 *
	 * @return string
	 * @access private
	 */
	private function ppde_css_classname($value, $reverse = false)
	{
		$css_reverse = '';

		if ($reverse && $value < 100)
		{
			$value = 100 - $value;
			$css_reverse = '-reverse';
		}

		switch ($value)
		{
			case ($value <= 10):
				return 'ten' . $css_reverse;
			case ($value <= 20):
				return 'twenty' . $css_reverse;
			case ($value <= 30):
				return 'thirty' . $css_reverse;
			case ($value <= 40):
				return 'forty' . $css_reverse;
			case ($value <= 50):
				return 'fifty' . $css_reverse;
			case ($value <= 60):
				return 'sixty' . $css_reverse;
			case ($value <= 70):
				return 'seventy' . $css_reverse;
			case ($value <= 80):
				return 'eighty' . $css_reverse;
			case ($value <= 90):
				return 'ninety' . $css_reverse;
			case ($value < 100):
				return 'hundred' . $css_reverse;
			default:
				return $reverse ? 'red' : 'green';
		}
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
			case 'donorlist':
				return $this->helper->render('donorlist_body.html', $this->user->lang('PPDE_DONORLIST_TITLE'));
			default:
				return $this->helper->render('donate_body.html', $this->user->lang('PPDE_DONATION_TITLE'));
		}
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
			$this->config['ppde_first_start'] = false;
		}
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
	 * @return void
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
