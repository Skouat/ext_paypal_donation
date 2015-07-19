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

class main_controller
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \skouat\ppde\operators\donation_pages */
	protected $ppde_operator_donation_pages;

	/** @var \skouat\ppde\operators\currency */
	protected $ppde_operator_currency;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config                  $config                       Config object
	 * @param \skouat\ppde\operators\donation_pages $ppde_operator_donation_pages Donation pages operator object
	 * @param \skouat\ppde\operators\currency       $ppde_operator_currency       Currency operator object
	 * @param \phpbb\controller\helper              $helper                       Controller helper object
	 * @param \phpbb\template\template              $template                     Template object
	 * @param \phpbb\user                           $user                         User object
	 * @param string                                $root_path                    phpBB root path
	 * @param string                                $php_ext                      phpEx
	 *
	 * @return \skouat\ppde\controller\main_controller
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, \skouat\ppde\operators\donation_pages $ppde_operator_donation_pages, \skouat\ppde\operators\currency $ppde_operator_currency, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, $root_path, $php_ext)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->ppde_operator_donation_pages = $ppde_operator_donation_pages;
		$this->ppde_operator_currency = $ppde_operator_currency;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	public function handle()
	{
		// When this extension is disabled, redirect users back to the forum index
		if (empty($this->config['ppde_enable']))
		{
			redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
		}

		// Get data from the database
		$donation_content_data = $this->ppde_operator_donation_pages->get_pages_data($this->user->get_iso_lang_id());

		// Prepare message for display
		$donation_body = generate_text_for_display(
			$donation_content_data[0]['page_content'],
			$donation_content_data[0]['page_content_bbcode_uid'],
			$donation_content_data[0]['page_content_bbcode_bitfield'],
			$donation_content_data[0]['page_content_bbcode_options']
		);

		$this->template->assign_vars(array(
			'PPDE_GOAL_ENABLE'   => $this->config['ppde_goal_enable'],
			'PPDE_RAISED_ENABLE' => $this->config['ppde_raised_enable'],
			'PPDE_USED_ENABLE'   => $this->config['ppde_used_enable'],

			'DONATION_BODY'      => $donation_body,
			'DEFAULT_CURRENCY'   => $this->build_currency_select_menu($this->config['ppde_default_currency']),

			'S_HIDDEN_FIELDS'    => $this->paypal_hidden_fields(),
			'S_PPDE_FORM_ACTION' => $this->generate_form_action($this->is_sandbox()),
		));

		// Send all data to the template file
		return $this->helper->render('donate_body.html', $this->user->lang('PPDE_DONATION_TITLE'));
	}

	/**
	 * Build pull down menu options of available currency
	 *
	 * @param int $config_value Currency identifier; default: 0
	 *
	 * @return null
	 * @access protected
	 */
	private function build_currency_select_menu($config_value = 0)
	{
		// Grab the list of currency data
		$currency_items = $this->ppde_operator_currency->get_currency_data();

		// Process each rule menu item for pull-down
		foreach ($currency_items as $currency_item)
		{
			// Set output block vars for display in the template
			$this->template->assign_block_vars('options', array(
				'CURRENCY_ID'        => (int) $currency_item['currency_id'],
				'CURRENCY_ISO_CODE'  => $currency_item['currency_iso_code'],
				'CURRENCY_SYMBOL'    => $currency_item['currency_symbol'],

				'S_CURRENCY_DEFAULT' => $config_value ? $config_value : 0,
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
}
