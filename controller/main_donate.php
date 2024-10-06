<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2024 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller;

class main_donate extends main_controller
{
	private const RETURN_BODY = 'body';
	private const RETURN_CANCEL = 'cancel';
	private const RETURN_SUCCESS = 'success';
	private const RETURN_DONORLIST = 'donorlist';

	/** @var \skouat\ppde\actions\vars */
	protected $actions_vars;

	/** @var \skouat\ppde\controller\main_display_stats */
	protected $controller_display_stats;

	/** @var \skouat\ppde\entity\donation_pages */
	protected $donation_pages_entity;

	/** @var \skouat\ppde\operators\donation_pages */
	protected $donation_pages_operator;

	public function set_actions_vars(\skouat\ppde\actions\vars $actions_vars): void
	{
		$this->actions_vars = $actions_vars;
	}

	public function set_display_stats(\skouat\ppde\controller\main_display_stats $controller_display_stats): void
	{
		$this->controller_display_stats = $controller_display_stats;
	}

	public function set_entity_donation_pages(\skouat\ppde\entity\donation_pages $donation_pages_entity): void
	{
		$this->donation_pages_entity = $donation_pages_entity;
	}

	public function set_operator_donation_pages(\skouat\ppde\operators\donation_pages $donation_pages_operator): void
	{
		$this->donation_pages_operator = $donation_pages_operator;
	}

	public function handle()
	{
		$this->check_extension_enabled();
		$this->check_user_permission();

		$return_args = $this->request->variable('return', self::RETURN_BODY);
		$this->set_return_args($return_args);

		$this->prepare_donation_page_content($return_args);
		$this->prepare_donation_form();
		$this->controller_display_stats->display_stats();

		// Send all data to the template file
		return $this->helper->render('donate_body.html', $this->get_page_title($return_args));
	}

	/**
	 * Check if PPDE is enabled
	 *
	 * @throws \phpbb\exception\http_exception
	 */
	private function check_extension_enabled(): void
	{
		if (empty($this->config['ppde_enable']))
		{
			redirect(append_sid($this->root_path . 'index.' . $this->php_ext));
		}
	}

	/**
	 * Check if the user has permission to use PPDE
	 *
	 * @throws \phpbb\exception\http_exception
	 */
	private function check_user_permission(): void
	{
		if (!$this->actions_auth->can_use_ppde())
		{
			trigger_error('NOT_AUTHORISED');
		}
	}

	/**
	 * Set return arguments for the template
	 *
	 * @param string $return_args Return arguments
	 */
	private function set_return_args(string $return_args): void
	{
		$this->template->assign_vars([
			'S_RETURN_ARGS' => $return_args,
		]);

		if (in_array($return_args, [self::RETURN_CANCEL, self::RETURN_SUCCESS, self::RETURN_DONORLIST]))
		{
			$this->template->assign_var(
				'L_PPDE_' . strtoupper($return_args) . '_TITLE',
				$this->language->lang('PPDE_' . strtoupper($return_args) . '_TITLE')
			);
		}
	}

	/**
	 * Prepare the donation page content
	 *
	 * @param string $return_args Return arguments
	 */
	private function prepare_donation_page_content(string $return_args): void
	{
		$content_data = $this->get_donation_content_data($return_args);
		if (!empty($content_data))
		{
			$this->actions_vars->get_vars();
			$content = $this->actions_vars->replace_template_vars($this->donation_pages_entity->get_message_for_display());
			$this->template->assign_var('DONATION_BODY', $content);
		}
	}

	/**
	 * Get content of current donation pages
	 *
	 * @param string $return_args Return arguments
	 * @return array
	 */
	private function get_donation_content_data(string $return_args): array
	{
		return $this->donation_pages_entity->get_data(
			$this->donation_pages_operator->build_sql_data($this->user->get_iso_lang_id(), $return_args)
		);
	}

	/**
	 * Prepare the donation form
	 */
	private function prepare_donation_form(): void
	{
		$this->actions_currency->build_currency_select_menu((int) $this->config['ppde_default_currency']);

		$this->template->assign_vars([
			'PPDE_DEFAULT_VALUE' => (int) ($this->config['ppde_default_value'] ?? 0),
			'PPDE_LIST_VALUE'    => $this->config['ppde_dropbox_enable'] && $this->config['ppde_dropbox_value']
				? $this->actions_currency->build_currency_value_select_menu($this->config['ppde_dropbox_value'], (int) $this->config['ppde_default_value'])
				: '',
			'S_HIDDEN_FIELDS'    => $this->get_paypal_hidden_fields(),
			'S_PPDE_FORM_ACTION' => $this->get_paypal_uri(),
			'S_SANDBOX'          => $this->use_sandbox(),
		]);
	}

	/**
	 * Get PayPal hidden fields
	 *
	 * @return string
	 */
	private function get_paypal_hidden_fields(): string
	{
		return build_hidden_fields([
			'cmd'           => '_donations',
			'business'      => $this->get_account_id(),
			'item_name'     => $this->language->lang('PPDE_DONATION_TITLE_HEAD', $this->config['sitename']),
			'no_shipping'   => 1,
			'return'        => generate_board_url(true) . $this->helper->route('skouat_ppde_donate', ['return' => self::RETURN_SUCCESS]),
			'notify_url'    => generate_board_url(true) . $this->helper->route('skouat_ppde_ipn_listener'),
			'cancel_return' => generate_board_url(true) . $this->helper->route('skouat_ppde_donate', ['return' => self::RETURN_CANCEL]),
			'item_number'   => 'uid_' . $this->user->data['user_id'] . '_' . time(),
			'custom'        => 'uid_' . $this->user->data['user_id'] . '_' . time(),
			'tax'           => 0,
			'bn'            => 'Board_Donate_WPS',
			'charset'       => 'utf-8',
		]);
	}

	/**
	 * Get PayPal account id
	 *
	 * @return string PayPal account Identifier
	 */
	private function get_account_id(): string
	{
		return $this->use_sandbox() ? $this->config['ppde_sandbox_address'] : $this->config['ppde_account_id'];
	}

	/**
	 * Get the page title
	 *
	 * @param string $return_args Return arguments
	 * @return string
	 */
	private function get_page_title($return_args)
	{
		$title_lang_var = 'PPDE_DONATION_TITLE';
		if (in_array($return_args, [self::RETURN_CANCEL, self::RETURN_SUCCESS]))
		{
			$title_lang_var = 'PPDE_' . strtoupper($return_args) . '_TITLE';
		}
		return $this->language->lang($title_lang_var);
	}
}
