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

class main_donor_list extends main_controller
{
	/** @var \skouat\ppde\entity\transactions */
	protected $ppde_entity_transactions;
	/** @var \skouat\ppde\operators\transactions */
	protected $ppde_operator_transactions;
	/** @var  \phpbb\pagination */
	protected $pagination;
	/** @var  \phpbb\path_helper */
	protected $path_helper;
	/** @var string */
	private $u_action;

	public function set_entity_transactions(\skouat\ppde\entity\transactions $ppde_entity_transactions): void
	{
		$this->ppde_entity_transactions = $ppde_entity_transactions;
	}

	public function set_operator_transactions(\skouat\ppde\operators\transactions $ppde_operator_transactions): void
	{
		$this->ppde_operator_transactions = $ppde_operator_transactions;
	}

	public function set_pagination(\phpbb\pagination $pagination): void
	{
		$this->pagination = $pagination;
	}

	public function set_path_helper(\phpbb\path_helper $path_helper): void
	{
		$this->path_helper = $path_helper;
	}

	public function handle()
	{
		// If the donorlist is not enabled, redirect users back to the forum index.
		// Else if user is not allowed to view the donors list, disallow access to the extension page.
		if (!$this->donorlist_is_enabled())
		{
			redirect(append_sid($this->root_path . 'index.' . $this->php_ext));
		}
		else if (!$this->ppde_actions_auth->can_view_ppde_donorlist())
		{
			trigger_error('NOT_AUTHORISED');
		}

		// Set up general vars
		$default_key = 'd';
		$sort_key = $this->request->variable('sk', $default_key);
		$sort_dir = $this->request->variable('sd', 'd');
		$start = $this->request->variable('start', 0);

		// Sorting and order
		$sort_key_sql = ['a' => 'amount', 'd' => 'txn.payment_date', 'u' => 'u.username_clean'];

		if (!isset($sort_key_sql[$sort_key]))
		{
			$sort_key = $default_key;
		}

		$order_by = $sort_key_sql[$sort_key] . ' ' . (($sort_dir === 'a') ? 'ASC' : 'DESC');

		// Build a relevant pagination_url and sort_url.
		// We do not use request_var() here directly to save some calls (not all variables are set)
		$check_params = [
			'sk'    => ['sk', $default_key],
			'sd'    => ['sd', 'a'],
			'start' => ['start', 0],
		];

		$params = $this->check_params($check_params, ['start']);
		$sort_params = $this->check_params($check_params, ['sk', 'sd']);

		// Set '$this->u_action'
		$use_page = $this->u_action ?: $this->user->page['page_name'];
		$this->u_action = reapply_sid($this->path_helper->get_valid_page($use_page, (bool) $this->config['enable_mod_rewrite']));

		$pagination_url = append_sid($this->u_action, implode('&amp;', $params), true, false, true);
		$sort_url = $this->set_url_delim(append_sid($this->u_action, implode('&amp;', $sort_params), true, false, true), $sort_params);

		$sql_count_donors = $this->ppde_operator_transactions->sql_donorlist_ary();
		$total_donors = $this->ppde_operator_transactions->query_sql_count($sql_count_donors, 'txn.user_id');
		$start = $this->pagination->validate_start($start, (int) $this->config['topics_per_page'], $total_donors);

		$this->pagination->generate_template_pagination($pagination_url, 'pagination', 'start', $total_donors, (int) $this->config['topics_per_page'], $start);

		// Assign vars to the template
		$this->template->assign_vars([
			'L_PPDE_DONORLIST_TITLE' => $this->language->lang('PPDE_DONORLIST_TITLE'),
			'TOTAL_DONORS'           => $this->language->lang('PPDE_DONORS', $total_donors),
			'U_SORT_AMOUNT'          => $sort_url . 'sk=a&amp;sd=' . $this->set_sort_key($sort_key, 'a', $sort_dir),
			'U_SORT_DONATED'         => $sort_url . 'sk=d&amp;sd=' . $this->set_sort_key($sort_key, 'd', $sort_dir),
			'U_SORT_USERNAME'        => $sort_url . 'sk=u&amp;sd=' . $this->set_sort_key($sort_key, 'u', $sort_dir),
		]);

		// Adds fields to the table schema needed by entity->import()
		$donorlist_table_schema = [
			'item_amount'      => ['name' => 'amount', 'type' => 'float'],
			'item_max_txn_id'  => ['name' => 'max_txn_id', 'type' => 'integer'],
			'item_user_id'     => ['name' => 'user_id', 'type' => 'integer'],
			'item_mc_currency' => ['name' => 'mc_currency', 'type' => 'string'],
		];

		$sql_donorlist_ary = $this->ppde_operator_transactions->sql_donorlist_ary(true, $order_by);
		$data_ary = $this->ppde_entity_transactions->get_data($this->ppde_operator_transactions->build_sql_donorlist_data($sql_donorlist_ary), $donorlist_table_schema, (int) $this->config['topics_per_page'], $start, true);

		// Adds fields to the table schema needed by entity->import()
		$last_donation_table_schema = [
			'item_payment_date' => ['name' => 'payment_date', 'type' => 'integer'],
			'item_mc_gross'     => ['name' => 'mc_gross', 'type' => 'float'],
			'item_mc_currency' => ['name' => 'mc_currency', 'type' => 'string'],
		];

		foreach ($data_ary as $data)
		{
			$get_last_transaction_sql_ary = $this->ppde_operator_transactions->sql_last_donation_ary($data['max_txn_id']);
			$last_donation_data = $this->ppde_entity_transactions->get_data($this->ppde_operator_transactions->build_sql_donorlist_data($get_last_transaction_sql_ary), $last_donation_table_schema, 0, 0, true);
			$currency_mc_data = $this->ppde_actions_currency->get_currency_data($last_donation_data[0]['mc_currency']);
			$this->template->assign_block_vars('donorrow', [
				'PPDE_DONOR_USERNAME'       => $this->user_loader->get_username($data['user_id'], 'full', false, false, true),
				'PPDE_LAST_DONATED_AMOUNT'  => $this->ppde_actions_currency->format_currency((float) $last_donation_data[0]['mc_gross'], $currency_mc_data[0]['currency_iso_code'], $currency_mc_data[0]['currency_symbol'], (bool) $currency_mc_data[0]['currency_on_left']),
				'PPDE_LAST_PAYMENT_DATE'    => $this->user->format_date($last_donation_data[0]['payment_date']),
				'PPDE_TOTAL_DONATED_AMOUNT' => $this->ppde_actions_currency->format_currency((float) $data['amount'], $currency_mc_data[0]['currency_iso_code'], $currency_mc_data[0]['currency_symbol'], (bool) $currency_mc_data[0]['currency_on_left']),
			]);
		}

		// Send all data to the template file
		return $this->send_data_to_template();
	}

	/**
	 * @param array    $params_ary
	 * @param string[] $excluded_keys
	 *
	 * @return array
	 * @access private
	 */
	private function check_params($params_ary, $excluded_keys): array
	{
		$params = [];

		foreach ($params_ary as $key => $call)
		{
			if (!$this->request->is_set($key))
			{
				continue;
			}

			$param = call_user_func_array([$this->request, 'variable'], $call);
			$param = urlencode($key) . '=' . ((is_string($param)) ? urlencode($param) : $param);

			if (!in_array($key, $excluded_keys))
			{
				$params[] = $param;
			}
		}

		return $params;
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
	private function set_url_delim($url, $params): string
	{
		return empty($params) ? $url . '?' : $url . '&amp;';
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
	private function set_sort_key($sk, $sk_comp, $sd): string
	{
		return ($sk === $sk_comp && $sd === 'a') ? 'd' : 'a';
	}

	/**
	 * Send data to the template file
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @access private
	 */
	private function send_data_to_template()
	{
		return $this->helper->render('donorlist_body.html', $this->language->lang('PPDE_DONORLIST_TITLE'));
	}
}
