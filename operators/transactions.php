<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2024 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\operators;

use phpbb\db\driver\driver_interface;

class transactions
{
	protected $db;
	protected $ppde_transactions_log_table;

	/**
	 * Constructor
	 *
	 * @param driver_interface $db                          Database connection
	 * @param string           $ppde_transactions_log_table Table name
	 */
	public function __construct(driver_interface $db, string $ppde_transactions_log_table)
	{
		$this->db = $db;
		$this->ppde_transactions_log_table = $ppde_transactions_log_table;
	}

	/**
	 * Builds SQL Query to return Transaction log data
	 *
	 * @param int $transaction_id ID of the transaction to fetch (0 for all transactions)
	 * @return string SQL query string
	 */
	public function build_sql_data(int $transaction_id = 0): string
	{
		$sql_ary = [
			'SELECT'    => 'txn.*, u.username, u.user_colour',
			'FROM'      => [$this->ppde_transactions_log_table => 'txn'],
			'LEFT_JOIN' => [
				[
					'FROM' => [USERS_TABLE => 'u'],
					'ON'   => 'u.user_id = txn.user_id',
				],
			],
			'WHERE'     => $transaction_id ? 'txn.transaction_id = ' . $transaction_id : '',
			'ORDER_BY'  => 'txn.transaction_id',
		];

		// Return all transactions entities
		return $this->db->sql_build_query('SELECT', $sql_ary);
	}

	/**
	 * Builds SQL Query array for donor list
	 *
	 * @param bool   $detailed Whether to include detailed information
	 * @param string $order_by SQL ORDER BY clause
	 * @return array SQL query array
	 */
	public function sql_donorlist_ary(bool $detailed = false, string $order_by = ''): array
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
	 * Builds SQL Query array for the last donation of a donor
	 *
	 * @param int $transaction_id ID of the transaction
	 * @return array SQL query array
	 */
	public function sql_last_donation_ary(int $transaction_id): array
	{
		return [
			'SELECT' => 'txn.payment_date, txn.mc_gross, txn.mc_currency',
			'FROM'   => [$this->ppde_transactions_log_table => 'txn'],
			'WHERE'  => 'txn.transaction_id = ' . $transaction_id,
		];
	}

	/**
	 * Builds SQL Query to return the donors list
	 *
	 * @param array $sql_donorlist_ary SQL query array
	 * @return string SQL query string
	 */
	public function build_sql_donorlist_data(array $sql_donorlist_ary): string
	{
		return $this->db->sql_build_query('SELECT', $sql_donorlist_ary);
	}

	/**
	 * Executes a COUNT query and returns the result
	 *
	 * @param array  $count_sql_ary  SQL query array
	 * @param string $selected_field Field to count
	 * @return int Count result
	 */
	public function query_sql_count(array $count_sql_ary, string $selected_field): int
	{
		$count_sql_ary['SELECT'] = 'COUNT(' . $selected_field . ') AS total_entries';

		if (array_key_exists('GROUP_BY', $count_sql_ary))
		{
			$count_sql_ary['SELECT'] = 'COUNT(DISTINCT ' . $count_sql_ary['GROUP_BY'] . ') AS total_entries';
		}
		unset($count_sql_ary['ORDER_BY'], $count_sql_ary['GROUP_BY']);

		$sql = $this->db->sql_build_query('SELECT', $count_sql_ary);
		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('total_entries');
		$this->db->sql_freeresult($result);

		return $count;
	}

	/**
	 * Builds SQL Query array for displaying simple transactions details
	 *
	 * @param string $keywords Search keywords
	 * @param string $sort_by  SQL ORDER BY clause
	 * @param int    $log_time Timestamp to filter logs
	 * @return array SQL query array
	 */
	public function get_logs_sql_ary(string $keywords, string $sort_by, int $log_time): array
	{
		$sql_keywords = $this->generate_sql_keyword($keywords);

		$sql_ary = [
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
			$sql_ary['WHERE'] = 'txn.payment_date >= ' . (int) $log_time . ' AND ' . $sql_ary['WHERE'];
		}

		return $sql_ary;
	}

	/**
	 * Generates SQL condition for the specified keywords
	 *
	 * @param string $keywords           The keywords the user specified to search for
	 * @param string $statement_operator SQL operator to use ('AND' by default)
	 * @return string SQL condition string
	 */
	private function generate_sql_keyword(string $keywords, string $statement_operator = 'AND'): string
	{
		// Use no preg_quote for $keywords because this would lead to sole
		// backslashes being added. We also use an OR connection here for
		// spaces and the | string. Currently, regex is not supported for
		// searching (but may come later).
		$keywords = preg_split('#[\s|]+#u', utf8_strtolower($keywords), 0, PREG_SPLIT_NO_EMPTY);
		if (empty($keywords))
		{
			return '';
		}

		// Build pattern and keywords...
		$keywords = array_map(function ($keyword) {
			return $this->db->sql_like_expression($this->db->get_any_char() . $keyword . $this->db->get_any_char());
		}, $keywords);

		$sql_keywords = ' ' . $statement_operator . ' (';
		$columns = ['txn.txn_id', 'u.username'];

		$sql_clauses = [];
		foreach ($columns as $column_name)
		{
			$sql_lower = $this->db->sql_lower_text($column_name);
			$sql_clauses[] = $sql_lower . ' ' . implode(' OR ' . $sql_lower . ' ', $keywords);
		}

		$sql_keywords .= implode(' OR ', $sql_clauses) . ')';

		return $sql_keywords;
	}

	/**
	 * Retrieves user information based on the donor ID or email
	 *
	 * @param string     $type Type of identifier ('user', 'username', or 'email')
	 * @param int|string $arg  Identifier value
	 * @return array User data
	 */
	public function query_donor_user_data(string $type = 'user', $arg = 1): array
	{
		$sql_where = $this->build_donor_where_clause($type, $arg);
		return $this->fetch_donor_data($sql_where);
	}

	/**
	 * Builds SQL WHERE clause for donor query
	 *
	 * @param string $type Type of identifier
	 * @param mixed  $arg  Identifier value
	 * @return string SQL WHERE clause
	 */
	private function build_donor_where_clause(string $type, $arg): string
	{
		switch ($type)
		{
			case 'user':
				return ' WHERE user_id = ' . (int) $arg;
			case 'username':
				return " WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($arg)) . "'";
			case 'email':
				return " WHERE user_email = '" . $this->db->sql_escape(strtolower($arg)) . "'";
			default:
				return '';
		}
	}

	/**
	 * Fetches donor data from the database
	 *
	 * @param string $sql_where SQL WHERE clause
	 * @return array Donor data
	 */
	private function fetch_donor_data(string $sql_where): array
	{
		$sql = 'SELECT user_id, username, user_ppde_donated_amount
			FROM ' . USERS_TABLE . $sql_where;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: [];
	}

	/**
	 * Builds log entries for PayPal transactions
	 *
	 * @param array $get_logs_sql_ary SQL query array
	 * @param array $url_ary          Array of URLs for building links
	 * @param int   $limit            Maximum number of entries to return
	 * @param int   $last_page_offset Offset for pagination
	 * @return array Log entries
	 */
	public function build_log_entries(array $get_logs_sql_ary, array $url_ary, int $limit = 0, int $last_page_offset = 0): array
	{
		$sql = $this->db->sql_build_query('SELECT', $get_logs_sql_ary);
		$result = $this->db->sql_query_limit($sql, $limit, $last_page_offset);

		$log_entries = [];

		while ($row = $this->db->sql_fetchrow($result))
		{
			$log_entries[] = $this->build_log_entry($row, $url_ary);
		}
		$this->db->sql_freeresult($result);

		return $log_entries;
	}

	/**
	 * Builds a single log entry
	 *
	 * @param array $row     Database row data
	 * @param array $url_ary Array of URLs for building links
	 * @return array Formatted log entry
	 */
	private function build_log_entry(array $row, array $url_ary): array
	{
		return [
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

	/**
	 * Builds transaction URL for templates
	 *
	 * @param int    $id         Transaction ID
	 * @param string $txn_id     PayPal transaction ID
	 * @param string $custom_url Custom URL (optional)
	 * @param bool   $colour     Whether to apply color to the URL
	 * @return string Formatted transaction URL or plain transaction ID
	 */
	private function build_transaction_url(int $id, string $txn_id, string $custom_url = '', bool $colour = false): string
	{
		if (empty($custom_url))
		{
			return $txn_id;
		}

		$txn_url = $custom_url . '&amp;action=view&amp;id=' . $id;
		return $this->format_transaction_link($txn_url, $txn_id, $colour);
	}

	/**
	 * Formats the transaction link
	 *
	 * @param string $txn_url Transaction URL
	 * @param string $txn_id  PayPal transaction ID
	 * @param bool   $colour  Whether to apply color to the URL
	 * @return string Formatted transaction link
	 */
	private function format_transaction_link(string $txn_url, string $txn_id, bool $colour): string
	{
		$style = $colour ? '' : ' style="color: #ff0000;"';
		return sprintf('<a href="%s"%s>%s</a>', $txn_url, $style, $txn_id);
	}

	/**
	 * Builds SQL WHERE clause for marked transactions
	 *
	 * @param array $marked Array of marked transaction IDs
	 * @return string SQL WHERE clause
	 */
	public function build_marked_where_sql(array $marked): string
	{
		if (empty($marked))
		{
			return '';
		}

		return ' WHERE ' . $this->db->sql_in_set('transaction_id', array_map('intval', $marked));
	}

	/**
	 * Executes a query to count results for updating stats
	 *
	 * @param string $type     Type of count query
	 * @param bool   $test_ipn Whether to include test IPNs
	 * @return int Count result
	 */
	public function sql_query_count_result(string $type, bool $test_ipn): int
	{
		$field_name = strpos($type, 'transactions_count') !== false ? 'txn_id' : 'payer_id';
		$sql_ary = $this->sql_select_stats_main($field_name);
		$test_ipn_str = (int) $test_ipn;

		$this->add_where_clause($sql_ary, $type, $test_ipn_str);

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('count_result');
		$this->db->sql_freeresult($result);

		return $count;
	}

	/**
	 * Builds base SQL query array for stats calculation
	 *
	 * @param string $field_name Name of the field to count
	 * @return array SQL query array
	 */
	private function sql_select_stats_main(string $field_name): array
	{
		return [
			'SELECT' => 'COUNT(DISTINCT txn.' . $field_name . ') AS count_result',
			'FROM'   => [$this->ppde_transactions_log_table => 'txn'],
		];
	}

	/**
	 * Adds WHERE clause to the SQL query array for stats calculation
	 *
	 * @param array  &$sql_ary      SQL query array (passed by reference)
	 * @param string  $type         Type of count query
	 * @param int     $test_ipn_str Test IPN flag (as integer)
	 */
	private function add_where_clause(array &$sql_ary, string $type, int $test_ipn_str): void
	{
		if (strpos($type, 'transactions_count') !== false)
		{
			$sql_ary['WHERE'] = "confirmed = 1 AND payment_status = 'Completed' AND txn.test_ipn = " . $test_ipn_str;
		}
		else if (strpos($type, 'known_donors_count') !== false)
		{
			$sql_ary['LEFT_JOIN'] = [
				[
					'FROM' => [USERS_TABLE => 'u'],
					'ON'   => 'txn.user_id = u.user_id',
				],
			];
			$sql_ary['WHERE'] = '(u.user_type = ' . USER_NORMAL . ' OR u.user_type = ' . USER_FOUNDER . ') AND txn.test_ipn = ' . $test_ipn_str;
		}
		else if (strpos($type, 'anonymous_donors_count') !== false)
		{
			$sql_ary['WHERE'] = 'txn.user_id = ' . ANONYMOUS . ' AND txn.test_ipn = ' . $test_ipn_str;
		}
	}

	/**
	 * Updates the user's donated amount
	 *
	 * @param int   $user_id User ID
	 * @param float $value   New donated amount
	 */
	public function sql_update_user_stats(int $user_id, float $value): void
	{
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_ppde_donated_amount = ' . $value . '
			WHERE user_id = ' . $user_id;
		$this->db->sql_query($sql);
	}

	/**
	 * Prepares transaction data array for entity
	 *
	 * @param array $data Raw transaction data
	 * @return array Formatted transaction data
	 */
	public function build_transaction_data_ary(array $data): array
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
