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
	/** @var \phpbb\config\config */
	protected $config;

	protected $container;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \skouat\ppde\operators\donation_pages */
	protected $ppde_operator_donation_pages;

	/** @var \skouat\ppde\operators\currency */
	protected $ppde_operator_currency;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/** @var string donation_body */
	private $donation_body;

	/** @var array donation_body */
	private $donation_content_data;

	/** @var string mode_url */
	private $mode_url;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config                  $config                       Config object
	 * @param ContainerInterface                    $container                    Service container interface
	 * @param \phpbb\controller\helper              $helper                       Controller helper object
	 * @param \skouat\ppde\operators\donation_pages $ppde_operator_donation_pages Donation pages operator object
	 * @param \skouat\ppde\operators\currency       $ppde_operator_currency       Currency operator object
	 * @param \phpbb\request\request                $request                      Request object
	 * @param \phpbb\template\template              $template                     Template object
	 * @param \phpbb\user                           $user                         User object
	 * @param string                                $root_path                    phpBB root path
	 * @param string                                $php_ext                      phpEx
	 *
	 * @return \skouat\ppde\controller\main_controller
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, ContainerInterface $container, \phpbb\controller\helper $helper, \skouat\ppde\operators\donation_pages $ppde_operator_donation_pages, \skouat\ppde\operators\currency $ppde_operator_currency, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $root_path, $php_ext)
	{
		$this->config = $config;
		$this->container = $container;
		$this->helper = $helper;
		$this->ppde_operator_donation_pages = $ppde_operator_donation_pages;
		$this->ppde_operator_currency = $ppde_operator_currency;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	public function handle()
	{
		$entity = $this->container->get('skouat.ppde.entity.donation_pages');
		$this->get_return_url_mode($this->request->variable('mode', 'body'));

		// When this extension is disabled, redirect users back to the forum index
		if (empty($this->config['ppde_enable']))
		{
			redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
		}

		// Get data from the database
		$default_currency_data = $this->get_default_currency_data($this->config['ppde_default_currency']);

		// Prepare message for display
		if ($this->get_donation_content_data($this->mode_url))
		{
			$entity->get_vars();
			$this->donation_body = $entity->replace_template_vars($entity->get_message_for_display(
				$this->donation_content_data[0]['page_content'],
				$this->donation_content_data[0]['page_content_bbcode_uid'],
				$this->donation_content_data[0]['page_content_bbcode_bitfield'],
				$this->donation_content_data[0]['page_content_bbcode_options']
			));
		}

		// Generate statistics percent for display
		if ($this->config['ppde_goal_enable'] && (int) $this->config['ppde_goal'] > 0)
		{
			$this->generate_stats_percent((int) $this->config['ppde_raised'], (int) $this->config['ppde_goal'], 'GOAL_NUMBER');
		}

		if ($this->config['ppde_used_enable'] && (int) $this->config['ppde_raised'] > 0 && (int) $this->config['ppde_used'] > 0)
		{
			$this->generate_stats_percent((int) $this->config['ppde_used'], (int) $this->config['ppde_raised'], 'USED_NUMBER');
		}

		$this->template->assign_vars(array(
			'PPDE_GOAL_ENABLE'   => $this->config['ppde_goal_enable'],
			'PPDE_RAISED_ENABLE' => $this->config['ppde_raised_enable'],
			'PPDE_USED_ENABLE'   => $this->config['ppde_used_enable'],

			'L_PPDE_GOAL'        => $this->get_ppde_goal_langkey($default_currency_data[0]['currency_symbol']),
			'L_PPDE_RAISED'      => $this->get_ppde_raised_langkey($default_currency_data[0]['currency_symbol']),
			'L_PPDE_USED'        => $this->get_ppde_used_langkey($default_currency_data[0]['currency_symbol']),

			'DONATION_BODY'      => $this->donation_body,
			'PPDE_DEFAULT_VALUE' => $this->config['ppde_default_value'] ? $this->config['ppde_default_value'] : 0,
			'PPDE_LIST_VALUE'    => $this->build_currency_value_select_menu(),
			'DEFAULT_CURRENCY'   => $this->build_currency_select_menu($this->config['ppde_default_currency']),

			'S_HIDDEN_FIELDS'    => $this->paypal_hidden_fields(),
			'S_PPDE_FORM_ACTION' => $this->generate_form_action($this->is_sandbox()),
		));

		// Send all data to the template file
		return $this->send_data_to_template();
	}

	/**
	 * @param $mode
	 */
	private function get_return_url_mode($mode)
	{
		switch ($mode)
		{
			case 'cancel':
			case 'success':
				$this->mode_url = $mode;
			default:
				$this->mode_url = 'body';
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
		return $this->ppde_operator_currency->get_currency_data($id, true);
	}

	/**
	 * Get content of current donation pages
	 *
	 * @param string $mode
	 *
	 * @return array
	 * @access private
	 */
	private function get_donation_content_data($mode)
	{
		return $this->donation_content_data = $this->ppde_operator_donation_pages->get_pages_data($this->user->get_iso_lang_id(), $mode);
	}

	/**
	 * Generate statistics percent for display
	 *
	 * @param string $type
	 * @param        $multiplicand
	 * @param        $dividend
	 *
	 * @access public
	 */
	public function generate_stats_percent($multiplicand, $dividend, $type = '')
	{
		$percent = ($multiplicand * 100) / $dividend;

		$this->template->assign_vars(array(
			'PPDE_' . $type => round($percent, 2),
			'S_' . $type    => !empty($type) ? true : false,
		));
	}

	/**
	 * Retrieve the language key for donation goal
	 *
	 * @param string $currency_symbol Currency symbol
	 *
	 * @return string
	 * @access public
	 */
	public function get_ppde_goal_langkey($currency_symbol)
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
			$l_ppde_goal = $this->user->lang('DONATE_GOAL_RAISE', (int) $this->config['ppde_goal'], $currency_symbol);
		}

		return $l_ppde_goal;
	}

	/**
	 * Retrieve the language key for donation raised
	 *
	 * @param string $currency_symbol Currency symbol
	 *
	 * @return string
	 * @access public
	 */
	public function get_ppde_raised_langkey($currency_symbol)
	{
		if ((int) $this->config['ppde_raised'] <= 0)
		{
			$l_ppde_raised = $this->user->lang['DONATE_NOT_RECEIVED'];
		}
		else
		{
			$l_ppde_raised = $this->user->lang('DONATE_RECEIVED', (int) $this->config['ppde_raised'], $currency_symbol);
		}

		return $l_ppde_raised;
	}

	/**
	 * Retrieve the language key for donation used
	 *
	 * @param string $currency_symbol Currency symbol
	 *
	 * @return string
	 * @access public
	 */
	public function get_ppde_used_langkey($currency_symbol)
	{
		if ((int) $this->config['ppde_used'] <= 0)
		{
			$l_ppde_used = $this->user->lang['DONATE_NOT_USED'];
		}
		else if ((int) $this->config['ppde_used'] < (int) $this->config['ppde_raised'])
		{
			$l_ppde_used = $this->user->lang('DONATE_USED', (int) $this->config['ppde_used'], $currency_symbol, (int) $this->config['ppde_raised']);
		}
		else
		{
			$l_ppde_used = $this->user->lang('DONATE_USED_EXCEEDED', (int) $this->config['ppde_used'], $currency_symbol);
		}

		return $l_ppde_used;
	}

	/**
	 * Build pull down menu options of available currency value
	 *
	 * @return string List of currency value set in ACP for dropdown menu
	 * @access private
	 */
	private function build_currency_value_select_menu()
	{
		// Retrieve donation value for drop-down list
		$list_donation_value = '';

		if ($this->config['ppde_dropbox_enable'] && $this->config['ppde_dropbox_value'])
		{
			$donation_ary_value = explode(',', $this->config['ppde_dropbox_value']);

			foreach ($donation_ary_value as $value)
			{
				$int_value = (int) $value;
				if (!empty($int_value) && ($int_value == $value))
				{
					$list_donation_value .= '<option value="' . $int_value . '">' . $int_value . '</option>';
				}
			}
			unset($value);
		}

		return $list_donation_value;
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
		$currency_items = $this->ppde_operator_currency->get_currency_data(0, true);

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
	 * Build PayPal hidden fields
	 *
	 * @return string PayPal hidden field needed to fill PayPal forms
	 * @access private
	 */
	private function paypal_hidden_fields()
	{
		//
		return build_hidden_fields(array(
			'cmd'           => '_donations',
			'business'      => $this->get_account_id(),
			'item_name'     => $this->user->lang['PPDE_DONATION_TITLE_HEAD'] . ' ' . $this->config['sitename'],
			'no_shipping'   => 1,
			'return'        => $this->generate_paypal_return_url('success'),
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
	 * @param bool $is_sandbox
	 *
	 * @return string $this Paypal account Identifier
	 * @access private
	 */
	private function get_account_id($is_sandbox = false)
	{
		return $is_sandbox ? $this->config['ppde_sandbox_address'] : $this->config['ppde_account_id'];
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
		return append_sid(generate_board_url(true) . $this->user->page['script_path'] . $this->user->page['page_name'], 'mode=' . $arg);
	}

	/**
	 * Generate PayPal form action
	 *
	 * @param bool $is_sandbox
	 *
	 * @return string
	 * @access private
	 */
	private function generate_form_action($is_sandbox = false)
	{
		return $is_sandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
	}

	/**
	 * Check if Sandbox is enable
	 *
	 * @return bool
	 * @access private
	 */
	private function is_sandbox()
	{
		return !empty($this->config['ppde_sandbox_enable']) && (!empty($this->config['ppde_sandbox_founder_enable']) && ($this->user->data['user_type'] == USER_FOUNDER) || empty($this->config['ppde_sandbox_founder_enable']));
	}

	/**
	 * Send data to the template file
	 *
	 * @param $mode
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @access private
	 */
	private function send_data_to_template()
	{
		switch ($this->mode_url)
		{
			case 'cancel':
			case 'success':
				return $this->helper->render('donate_body.html', $this->user->lang('PPDE_' . strtoupper($this->mode_url) . '_TITLE'));
			default;
				return $this->helper->render('donate_body.html', $this->user->lang('PPDE_DONATION_TITLE'));
		}
	}
}
