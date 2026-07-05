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
		// Disabled: back to index. Otherwise, enforce the view permission.
		if (!$this->donorlist_is_enabled())
		{
			redirect(append_sid($this->root_path . 'index.' . $this->php_ext));
		}
		else if (!$this->ppde_actions_auth->can_view_ppde_donorlist())
		{
			trigger_error('NOT_AUTHORISED');
		}

		$sort_key = $this->request->variable('sk', 'd');
		$sort_dir = $this->request->variable('sd', 'd');
		$start    = $this->request->variable('start', 0);

		$sorting  = $this->resolve_sorting($sort_key, $sort_dir);
		$sort_key = $sorting['key'];
		$order_by = $sorting['order_by'];

		// Build pagination_url and sort_url (only the set variables are kept).
		$check_params = [
			'sk'    => ['sk', 'd'],
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

		$this->template->assign_vars([
			'L_PPDE_DONORLIST_TITLE' => $this->language->lang('PPDE_DONORLIST_TITLE'),
			'TOTAL_DONORS'           => $this->language->lang('PPDE_DONORS', $total_donors),
			'U_SORT_AMOUNT'          => $sort_url . 'sk=a&amp;sd=' . $this->set_sort_key($sort_key, 'a', $sort_dir),
			'U_SORT_DONATED'         => $sort_url . 'sk=d&amp;sd=' . $this->set_sort_key($sort_key, 'd', $sort_dir),
			'U_SORT_USERNAME'        => $sort_url . 'sk=u&amp;sd=' . $this->set_sort_key($sort_key, 'u', $sort_dir),
		]);

		// Fields added to the table schema for entity->import().
		$donorlist_table_schema = [
			'item_amount'      => ['name' => 'amount', 'type' => 'float'],
			'item_user_id'     => ['name' => 'user_id', 'type' => 'integer'],
			'item_mc_currency' => ['name' => 'mc_currency', 'type' => 'string'],
		];

		$sql_donorlist_ary = $this->ppde_operator_transactions->sql_donorlist_ary(true, $order_by);
		$data_ary = $this->ppde_entity_transactions->get_data($this->ppde_operator_transactions->build_sql_donorlist_data($sql_donorlist_ary), $donorlist_table_schema, (int) $this->config['topics_per_page'], $start, true);

		$this->assign_donor_rows($data_ary);

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

	/**
	 * Assign donor rows, batching usernames, last donations and currencies.
	 *
	 * @param array $data_ary The grouped donor rows
	 *
	 * @return void
	 * @access private
	 */
	private function assign_donor_rows(array $data_ary): void
	{
		if (empty($data_ary))
		{
			return;
		}

		$user_ids = array_column($data_ary, 'user_id');

		$this->user_loader->load_users($user_ids);
		$last_donations = $this->get_last_donations($user_ids);

		$currency_cache = [];

		foreach ($data_ary as $data)
		{
			$key = $data['user_id'] . '_' . $data['mc_currency'];

			if (!isset($last_donations[$key]))
			{
				continue;
			}

			$last     = $last_donations[$key];
			$currency = $this->resolve_currency($data['mc_currency'], $currency_cache);

			$this->template->assign_block_vars('donorrow', [
				'PPDE_DONOR_USERNAME'       => $this->user_loader->get_username($data['user_id'], 'full', false, false, true),
				'PPDE_LAST_DONATED_AMOUNT'  => $this->ppde_actions_currency->format_currency((float) $last['mc_gross'], $currency['currency_iso_code'], $currency['currency_symbol'], (bool) $currency['currency_on_left']),
				'PPDE_LAST_PAYMENT_DATE'    => $this->user->format_date($last['payment_date']),
				'PPDE_TOTAL_DONATED_AMOUNT' => $this->ppde_actions_currency->format_currency((float) $data['amount'], $currency['currency_iso_code'], $currency['currency_symbol'], (bool) $currency['currency_on_left']),
			]);
		}
	}

	/**
	 * Fetch the last donations for the given donors, keyed by "user_id_currency".
	 *
	 * @param int[] $user_ids
	 *
	 * @return array
	 * @access private
	 */
	private function get_last_donations(array $user_ids): array
	{
		$schema = [
			'item_transaction_id' => ['name' => 'transaction_id', 'type' => 'integer'],
			'item_user_id'        => ['name' => 'user_id', 'type' => 'integer'],
			'item_mc_currency'    => ['name' => 'mc_currency', 'type' => 'string'],
			'item_payment_date'   => ['name' => 'payment_date', 'type' => 'integer'],
			'item_mc_gross'       => ['name' => 'mc_gross', 'type' => 'float'],
		];

		$sql_ary = $this->ppde_operator_transactions->sql_last_donations_ary($user_ids);
		$rows = $this->ppde_entity_transactions->get_data(
			$this->ppde_operator_transactions->build_sql_donorlist_data($sql_ary),
			$schema, 0, 0, true
		);

		$indexed = [];
		foreach ($rows as $row)
		{
			$indexed[$row['user_id'] . '_' . $row['mc_currency']] = $row;
		}

		return $indexed;
	}

	/**
	 * Resolve currency data once per ISO code.
	 *
	 * @param string $iso_code
	 * @param array  $cache
	 *
	 * @return array
	 * @access private
	 */
	private function resolve_currency(string $iso_code, array &$cache): array
	{
		if (!isset($cache[$iso_code]))
		{
			$data = $this->ppde_actions_currency->get_currency_data($iso_code);
			$cache[$iso_code] = $data[0];
		}

		return $cache[$iso_code];
	}

	/**
	 * Resolve the sort column and direction from user input.
	 *
	 * @param string $sort_key Requested sort key ('a', 'd' or 'u')
	 * @param string $sort_dir Requested direction ('a' or 'd')
	 *
	 * @return array{key: string, order_by: string}
	 * @access public
	 */
	public function resolve_sorting(string $sort_key, string $sort_dir): array
	{
		$default_key = 'd';
		$sort_key_sql = ['a' => 'amount', 'd' => 'txn.payment_date', 'u' => 'u.username_clean'];

		if (!isset($sort_key_sql[$sort_key]))
		{
			$sort_key = $default_key;
		}

		$column = $sort_key_sql[$sort_key];
		$direction = ($sort_dir === 'a') ? 'ASC' : 'DESC';

		// 'amount' is a bare aggregate alias; the others need MAX() under ONLY_FULL_GROUP_BY.
		$order_by = ($sort_key === 'a' ? $column : 'MAX(' . $column . ')') . ' ' . $direction;

		return ['key' => $sort_key, 'order_by' => $order_by];
	}
}
