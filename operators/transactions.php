<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\operators;

use phpbb\db\driver\driver_interface;

class transactions
{
	protected $container;
	protected $db;
	protected $ppde_transactions_log_table;

	/**
	 * Constructor
	 *
	 * @param driver_interface $db                          Database connection
	 * @param string           $ppde_transactions_log_table Table name
	 *
	 * @access public
	 */
	public function __construct(driver_interface $db, $ppde_transactions_log_table)
	{
		$this->db = $db;
		$this->ppde_transactions_log_table = $ppde_transactions_log_table;
	}

	/**
	 * SQL Query to return Transaction log data table
	 *
	 * @param $transaction_id
	 *
	 * @return string
	 * @access public
	 */
	public function build_sql_data($transaction_id = 0): string
	{
		// Build main sql request
		$sql_ary = [
			'SELECT'    => 'txn.*, u.username, u.user_colour',
			'FROM'      => [$this->ppde_transactions_log_table => 'txn'],
			'LEFT_JOIN' => [
				[
					'FROM' => [USERS_TABLE => 'u'],
					'ON'   => 'u.user_id = txn.user_id',
				],
			],
			'ORDER_BY'  => 'txn.transaction_id',
			'WHERE'     => ($transaction_id ? 'txn.transaction_id = ' . (int) $transaction_id : ''),
		];

		// Return all transactions entities
		return $this->db->sql_build_query('SELECT', $sql_ary);
	}

	/**
	 * SQL Query to count how many donated
	 *
	 * @param bool   $detailed
	 * @param string $order_by
	 *
	 * @return array
	 * @access public
	 */
	public function sql_donorlist_ary($detailed = false, $order_by = ''): array
	{
		// Build sql request
		$sql_donorslist_ary = [
			'SELECT'   => 'txn.user_id, txn.mc_currency',
			'FROM'     => [$this->ppde_transactions_log_table => 'txn'],
			'WHERE'    => 'txn.user_id <> ' . ANONYMOUS . "
							AND txn.payment_status = 'Completed'
							AND txn.test_ipn = 0",
			'GROUP_BY' => 'txn.user_id, txn.mc_currency',
			'ORDER_BY' => $order_by,
		];

		if ($detailed)
		{
			$sql_donorslist_ary['SELECT'] = 'txn.user_id, txn.mc_currency, MAX(txn.transaction_id) AS max_txn_id, SUM(txn.mc_gross) AS amount, MAX(u.username)';
			$sql_donorslist_ary['LEFT_JOIN'] = [
				[
					'FROM' => [USERS_TABLE => 'u'],
					'ON'   => 'u.user_id = txn.user_id',
				]];
		}

		return $sql_donorslist_ary;
	}

	/**
	 * SQL Query to return information of the last donation of the donor
	 *
	 * @param int $transaction_id
	 *
	 * @return array
	 * @access public
	 */
	public function sql_last_donation_ary($transaction_id): array
	{
		// Build sql request
		return [
			'SELECT' => 'txn.payment_date, txn.mc_gross, txn.mc_currency',
			'FROM'   => [$this->ppde_transactions_log_table => 'txn'],
			'WHERE'  => 'txn.transaction_id = ' . (int) $transaction_id,
		];
	}

	/**
	 * Build SQL Query to return the donors list
	 *
	 * @param array $sql_donorlist_ary
	 *
	 * @return string
	 * @access public
	 */
	public function build_sql_donorlist_data($sql_donorlist_ary): string
	{
		// Return all transactions entities
		return $this->db->sql_build_query('SELECT', $sql_donorlist_ary);
	}

	/**
	 * Returns total entries of selected field
	 *
	 * @param array  $count_sql_ary
	 * @param string $selected_field
	 *
	 * @return int
	 * @access public
	 */
	public function query_sql_count($count_sql_ary, $selected_field): int
	{
		$count_sql_ary['SELECT'] = 'COUNT(' . $selected_field . ') AS total_entries';

		if (array_key_exists('GROUP_BY', $count_sql_ary))
		{
			$count_sql_ary['SELECT'] = 'COUNT(DISTINCT ' . $count_sql_ary['GROUP_BY'] . ') AS total_entries';
		}
		unset($count_sql_ary['ORDER_BY'], $count_sql_ary['GROUP_BY']);

		$result = $this->db->sql_query($this->db->sql_build_query('SELECT', $count_sql_ary));
		$field = (int) $this->db->sql_fetchfield('total_entries');
		$this->db->sql_freeresult($result);

		return $field;
	}

	/**
	 * Returns the SQL Query for displaying simple transactions details
	 *
	 * @param string $keywords
	 * @param string $sort_by
	 * @param int    $log_time
	 *
	 * @return array
	 * @access public
	 */
	public function get_logs_sql_ary($keywords, $sort_by, $log_time): array
	{
		$sql_keywords = '';
		if (!empty($keywords))
		{
			// Get the SQL condition for our keywords
			$sql_keywords = $this->generate_sql_keyword($keywords);
		}

		$get_logs_sql_ary = [
			'SELECT'   => 'txn.transaction_id, txn.txn_id, txn.test_ipn, txn.confirmed, txn.txn_errors, txn.payment_date, txn.payment_status, txn.user_id, u.username, u.user_colour',
			'FROM'     => [
				$this->ppde_transactions_log_table => 'txn',
				USERS_TABLE                        => 'u',
			],
			'WHERE'    => 'txn.user_id = u.user_id ' . $sql_keywords,
			'ORDER_BY' => $sort_by,
		];

		if ($log_time)
		{
			$get_logs_sql_ary['WHERE'] = 'txn.payment_date >= ' . (int) $log_time . '
					AND ' . $get_logs_sql_ary['WHERE'];
		}

		return $get_logs_sql_ary;
	}

	/**
	 * Generates a sql condition for the specified keywords
	 *
	 * @param string $keywords           The keywords the user specified to search for
	 * @param string $statement_operator The operator used to prefix the statement ('AND' by default)
	 *
	 * @return string Returns the SQL condition searching for the keywords
	 * @access private
	 */
	private function generate_sql_keyword($keywords, $statement_operator = 'AND'): string
	{
		// Use no preg_quote for $keywords because this would lead to sole
		// backslashes being added. We also use an OR connection here for
		// spaces and the | string. Currently, regex is not supported for
		// searching (but may come later).
		$keywords = preg_split('#[\s|]+#u', utf8_strtolower($keywords), 0, PREG_SPLIT_NO_EMPTY);
		$sql_keywords = '';

		if (!empty($keywords))
		{
			// Build pattern and keywords...
			$keywords = array_map(function ($keyword) {
				return $this->db->sql_like_expression($this->db->get_any_char() . $keyword . $this->db->get_any_char());
			}, $keywords);

			$sql_keywords = ' ' . $statement_operator . ' (';
			$columns = ['txn.txn_id', 'u.username'];
			$sql_lowers = array();

			foreach ($columns as $column_name)
			{
				$sql_lower = $this->db->sql_lower_text($column_name);
				$sql_lowers[] = $sql_lower . ' ' . implode(' OR ' . $sql_lower . ' ', $keywords);
			}
			unset($columns);

			$sql_keywords .= implode(' OR ', $sql_lowers) . ')';
		}

		return $sql_keywords;
	}

	/**
	 * Returns user information based on the donor ID or email
	 *
	 * @param string     $type
	 * @param int|string $arg
	 *
	 * @return array|bool
	 * @access public
	 */
	public function query_donor_user_data($type = 'user', $arg = 1)
	{
		$sql_where = '';

		switch ($type)
		{
			case 'user':
				$sql_where = ' WHERE user_id = ' . (int) $arg;
			break;
			case 'username':
				$sql_where = " WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($arg)) . "'";
			break;
			case 'email':
				$sql_where = " WHERE user_email = '" . $this->db->sql_escape(strtolower($arg)) . "'";
			break;
		}

		$sql = 'SELECT user_id, username, user_ppde_donated_amount
			FROM ' . USERS_TABLE .
			$sql_where;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: [];
	}

	/**
	 * Returns simple details of all PayPal transactions logged in the database
	 *
	 * @param array $get_logs_sql_ary
	 * @param array $url_ary
	 * @param int   $limit
	 * @param int   $last_page_offset
	 *
	 * @return array $log
	 * @access public
	 */
	public function build_log_entries($get_logs_sql_ary, $url_ary, $limit = 0, $last_page_offset = 0): array
	{
		$sql = $this->db->sql_build_query('SELECT', $get_logs_sql_ary);
		$result = $this->db->sql_query_limit($sql, $limit, $last_page_offset);

		$log_entries = [];

		while ($row = $this->db->sql_fetchrow($result))
		{
			$log_entries[] = [
				'confirmed'      => $row['confirmed'],
				'payment_date'   => $row['payment_date'],
				'payment_status' => $row['payment_status'],
				'test_ipn'       => $row['test_ipn'],
				'transaction_id' => $row['transaction_id'],
				'txn_errors'     => $row['txn_errors'],
				'txn_id'         => $this->build_transaction_url($row['transaction_id'], $row['txn_id'], $url_ary['txn_url'], $row['confirmed']),
				'username_full'  => get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], false, $url_ary['profile_url']),
			];
		}

		$this->db->sql_freeresult($result);

		return $log_entries;
	}

	/**
	 * Build transaction url for placing into templates.
	 *
	 * @param int    $id         The user's transaction id
	 * @param string $txn_id     The txn number id
	 * @param string $custom_url optional parameter to specify a profile url. The transaction id get appended to this
	 *                           url as &amp;id={id}
	 * @param bool   $colour     If false, the color #FF0000 will be applied on the URL.
	 *
	 * @return string A string consisting of what is wanted.
	 * @access private
	 */
	private function build_transaction_url($id, $txn_id, $custom_url = '', $colour = false): string
	{
		// We cache some common variables we need within this function
		$transaction_templates = [
			'tpl_nourl'      => '{{ TRANSACTION }}',
			'tpl_url'        => '<a href="{{ TXN_URL }}">{{ TRANSACTION }}</a>',
			'tpl_url_colour' => '<a href="{{ TXN_URL }}" style="{{ TXN_COLOUR }}">{{ TRANSACTION }}</a>',
		];

		// Returns the correct transaction url

		if (!$txn_id)
		{
			return str_replace('{{ TRANSACTION }}', $txn_id, $transaction_templates['tpl_nourl']);
		}

		$txn_url = ($custom_url !== '') ? $custom_url . '&amp;action=view&amp;id=' . $id : $txn_id;
		if ($colour)
		{
			return str_replace(
				['{{ TXN_URL }}', '{{ TRANSACTION }}'],
				[$txn_url, $txn_id],
				$transaction_templates['tpl_url']
			);
		}
		return str_replace(
			['{{ TXN_URL }}', '{{ TXN_COLOUR }}', '{{ TRANSACTION }}'],
			[$txn_url, 'color: #ff0000;', $txn_id],
			$transaction_templates['tpl_url_colour']
		);
	}

	/**
	 * Builds the SQL WHERE clause for marked transactions.
	 *
	 * @param array $marked The array of marked transaction IDs.
	 *
	 * @return string The SQL WHERE clause.
	 */
	public function build_marked_where_sql($marked): string
	{
		if (!is_array($marked) || empty($marked))
		{
			return '';
		}

		return ' WHERE ' . $this->db->sql_in_set('transaction_id', array_map('intval', $marked));
	}

	/**
	 * Returns the count result for updating stats
	 *
	 * @param string $type     The type of query to be executed.
	 * @param bool   $test_ipn The value indicating whether to use test IPN.
	 *
	 * @return int
	 * @access public
	 */
	public function sql_query_count_result(string $type, bool $test_ipn): int
	{
		$is_transactions_count = strpos($type, 'transactions_count') !== false;
		$is_known_donors_count = strpos($type, 'known_donors_count') !== false;
		$is_anonymous_donors_count = strpos($type, 'anonymous_donors_count') !== false;

		$field_name = $is_transactions_count ? 'txn_id' : 'payer_id';
		$sql_ary = $this->sql_select_stats_main($field_name);
		$test_ipn_str = (int) $test_ipn;

		if ($is_transactions_count)
		{
			$sql_ary['WHERE'] = "confirmed = 1 AND payment_status = 'Completed' AND txn.test_ipn = " . $test_ipn_str;
		}
		else if ($is_known_donors_count)
		{
			$sql_ary['LEFT_JOIN'] = [
				[
					'FROM' => [USERS_TABLE => 'u'],
					'ON'   => 'txn.user_id = u.user_id',
				],
			];
			$sql_ary['WHERE'] = '(u.user_type = ' . USER_NORMAL . ' OR u.user_type = ' . USER_FOUNDER . ') AND txn.test_ipn = ' . $test_ipn_str;
		}
		else if ($is_anonymous_donors_count)
		{
			$sql_ary['WHERE'] = 'txn.user_id = ' . ANONYMOUS . ' AND txn.test_ipn = ' . $test_ipn_str;
		}

		$result = $this->db->sql_query($this->db->sql_build_query('SELECT', $sql_ary));
		$count = (int) $this->db->sql_fetchfield('count_result');
		$this->db->sql_freeresult($result);

		return $count;
	}

	/**
	 * Make body of SQL query for stats calculation.
	 *
	 * @param string $field_name Name of the field
	 *
	 * @return array
	 * @access private
	 */
	private function sql_select_stats_main(string $field_name): array
	{
		return [
			'SELECT' => 'COUNT(DISTINCT txn.' . $field_name . ') AS count_result',
			'FROM'   => [$this->ppde_transactions_log_table => 'txn'],
		];
	}

	/**
	 * Updates the user donated amount
	 *
	 * @param int    $user_id
	 * @param string $value
	 *
	 * @return void
	 * @access public
	 */
	public function sql_update_user_stats($user_id, $value): void
	{
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_ppde_donated_amount = ' . (float) $value . '
			WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);
	}

	/**
	 * Prepare data array before send it to $entity
	 *
	 * @param array $data
	 *
	 * @return array
	 * @access public
	 */
	public function build_transaction_data_ary($data): array
	{
		return [
			'business'          => $data['business'],
			'confirmed'         => (bool) $data['confirmed'],
			'custom'            => $data['custom'],
			'exchange_rate'     => $data['exchange_rate'],
			'first_name'        => $data['first_name'],
			'item_name'         => $data['item_name'],
			'item_number'       => $data['item_number'],
			'last_name'         => $data['last_name'],
			'mc_currency'       => $data['mc_currency'],
			'mc_gross'          => (float) $data['mc_gross'],
			'mc_fee'            => (float) $data['mc_fee'],
			'net_amount'        => (float) $data['net_amount'],
			'parent_txn_id'     => $data['parent_txn_id'],
			'payer_email'       => $data['payer_email'],
			'payer_id'          => $data['payer_id'],
			'payer_status'      => $data['payer_status'],
			'payment_date'      => $data['payment_date'],
			'payment_status'    => $data['payment_status'],
			'payment_type'      => $data['payment_type'],
			'memo'              => $data['memo'],
			'receiver_id'       => $data['receiver_id'],
			'receiver_email'    => $data['receiver_email'],
			'residence_country' => $data['residence_country'],
			'settle_amount'     => (float) $data['settle_amount'],
			'settle_currency'   => $data['settle_currency'],
			'test_ipn'          => (bool) $data['test_ipn'],
			'txn_errors'        => $data['txn_errors'],
			'txn_id'            => $data['txn_id'],
			'txn_type'          => $data['txn_type'],
			'user_id'           => (int) $data['user_id'],
		];
	}
}
