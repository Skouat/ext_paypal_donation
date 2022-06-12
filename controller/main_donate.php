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

class main_donate extends main_controller
{
	/** @var \skouat\ppde\controller\main_display_stats */
	protected $ppde_controller_display_stats;
	/** @var \skouat\ppde\entity\donation_pages */
	protected $ppde_entity_donation_pages;
	/** @var \skouat\ppde\operators\donation_pages */
	protected $ppde_operator_donation_pages;
	/** @var string */
	private $donation_body;
	/** @var string */
	private $return_args_url;

	public function set_display_stats(\skouat\ppde\controller\main_display_stats $ppde_controller_display_stats)
	{
		$this->ppde_controller_display_stats = $ppde_controller_display_stats;
	}

	public function set_entity_donation_pages(\skouat\ppde\entity\donation_pages $ppde_entity_donation_pages)
	{
		$this->ppde_entity_donation_pages = $ppde_entity_donation_pages;
	}

	public function set_operator_donation_pages(\skouat\ppde\operators\donation_pages $ppde_operator_donation_pages)
	{
		$this->ppde_operator_donation_pages = $ppde_operator_donation_pages;
	}

	public function handle()
	{
		// When this extension is disabled, redirect users back to the forum index
		// Else if user is not allowed to use it, disallow access to the extension main page
		if (empty($this->config['ppde_enable']))
		{
			redirect(append_sid($this->root_path . 'index.' . $this->php_ext));
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

		$this->ppde_actions_currency->build_currency_select_menu((int) $this->config['ppde_default_currency']);

		$this->template->assign_vars(array(
			'DONATION_BODY'      => $this->donation_body,
			'PPDE_DEFAULT_VALUE' => $this->config['ppde_default_value'] ? $this->config['ppde_default_value'] : 0,
			'PPDE_LIST_VALUE'    => $this->build_currency_value_select_menu($this->config['ppde_default_value']),

			'S_HIDDEN_FIELDS'    => $this->paypal_hidden_fields(),
			'S_PPDE_FORM_ACTION' => $this->get_paypal_uri(),
			'S_RETURN_ARGS'      => $this->return_args_url,
			'S_SANDBOX'          => $this->use_sandbox(),
		));

		$this->ppde_controller_display_stats->display_stats();

		// Send all data to the template file
		return $this->send_data_to_template();
	}

	/**
	 * @param string $set_return_args_url
	 *
	 * @return void
	 * @access private
	 */
	private function set_return_args_url($set_return_args_url)
	{
		$this->return_args_url = $set_return_args_url;

		switch ($set_return_args_url)
		{
			case 'cancel':
			case 'success':
				$this->template->assign_vars(array(
					'L_PPDE_DONATION_TITLE' => $this->language->lang('PPDE_' . strtoupper($set_return_args_url) . '_TITLE'),
				));
			break;
			case 'donorlist':
				$this->template->assign_vars(array(
					'L_PPDE_DONORLIST_TITLE' => $this->language->lang('PPDE_DONORLIST_TITLE'),
				));
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
				$list_donation_value .= !empty($int_value) ? '<option value="' . $int_value . '"' . $this->is_value_selected($int_value, $default_value) . '>' . $int_value . '</option>' : '';
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
	 * Define if the status of the attribute "selected"
	 *
	 * @param mixed $value
	 * @param mixed $default
	 *
	 * @return string
	 * @access private
	 */
	private function is_value_selected($value, $default)
	{
		if ($default == $value)
		{
			return ' selected';
		}

		return '';
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
			'item_name'     => $this->language->lang('PPDE_DONATION_TITLE_HEAD', $this->config['sitename']),
			'no_shipping'   => 1,
			'return'        => $this->generate_paypal_return_url('success'),
			'notify_url'    => $this->generate_paypal_notify_return_url(),
			'cancel_return' => $this->generate_paypal_return_url('cancel'),
			'item_number'   => 'uid_' . $this->user->data['user_id'] . '_' . time(),
			'custom'        => 'uid_' . $this->user->data['user_id'] . '_' . time(),
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
				return $this->helper->render('donate_body.html', $this->language->lang('PPDE_' . strtoupper($this->return_args_url) . '_TITLE'));
			default:
				return $this->helper->render('donate_body.html', $this->language->lang('PPDE_DONATION_TITLE'));
		}
	}
}
