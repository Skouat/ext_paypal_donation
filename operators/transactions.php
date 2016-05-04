<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\operators;

use Symfony\Component\DependencyInjection\ContainerInterface;

class transactions
{
	protected $container;
	protected $db;
	protected $ppde_transactions_log_table;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface                $container                   Service container interface
	 * @param \phpbb\db\driver\driver_interface $db                          Database connection
	 * @param string                            $ppde_transactions_log_table Table name
	 *
	 * @access public
	 */
	public function __construct(ContainerInterface $container, \phpbb\db\driver\driver_interface $db, $ppde_transactions_log_table)
	{
		$this->container = $container;
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
	public function build_sql_data($transaction_id = 0)
	{
		// Build main sql request
		$sql_ary = array(
			'SELECT'    => '*, u.username',
			'FROM'      => array($this->ppde_transactions_log_table => 'txn'),
			'LEFT_JOIN' => array(
				array(
					'FROM' => array(USERS_TABLE => 'u'),
					'ON'   => 'u.user_id = txn.user_id',
				),
			),
			'ORDER_BY'  => 'txn.transaction_id',
		);

		// Use WHERE clause when $currency_id is different from 0
		if ((int) $transaction_id)
		{
			$sql_ary['WHERE'] = 'txn.transaction_id = ' . (int) $transaction_id;
		}

		// Return all transactions entities
		return $this->db->sql_build_query('SELECT', $sql_ary);
	}

	/**
	 * Returns the SQL Query for generation the donors list
	 *
	 * @param int    $max_txn_id Identifier of the transaction logged in the DB
	 * @param string $order_by
	 *
	 * @return array
	 * @access public
	 */
	public function get_sql_donorlist_ary($max_txn_id = 0, $order_by = '')
	{
		// Build main sql request
		$donorlist_sql_ary = array(
			'SELECT'    => 'txn.*, MAX(txn.transaction_id) AS max_txn_id, SUM(txn.mc_gross) AS amount, u.username, u.user_colour',
			'FROM'      => array($this->ppde_transactions_log_table => 'txn'),
			'LEFT_JOIN' => array(
				array(
					'FROM' => array(USERS_TABLE => 'u'),
					'ON'   => 'u.user_id = txn.user_id',
				),
			),
			'WHERE'     => 'txn.user_id <> ' . ANONYMOUS . "
							AND txn.payment_status = 'Completed'
							AND txn.test_ipn = 0",
			'GROUP_BY'  => 'txn.user_id',
			'ORDER_BY'  => 'txn.transaction_id DESC',
		);

		if ($order_by)
		{
			$donorlist_sql_ary['ORDER_BY'] = $order_by;
		}

		if ($max_txn_id)
		{
			$donorlist_sql_ary['WHERE'] = 'txn.transaction_id = ' . $max_txn_id;
			unset($donorlist_sql_ary['GROUP_BY'], $donorlist_sql_ary['ORDER_BY']);
		}

		// Return all transactions entities
		return $donorlist_sql_ary;
	}

	/**
	 * SQL Query to return donors list details
	 *
	 * @param array $sql_donorlist_ary
	 *
	 * @return string
	 * @access public
	 */
	public function build_sql_donorlist_data($sql_donorlist_ary)
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
	public function query_sql_count($count_sql_ary, $selected_field)
	{
		$count_sql_ary['SELECT'] = 'COUNT(' . $selected_field . ') AS total_entries';

		if (array_key_exists('GROUP_BY', $count_sql_ary))
		{
			$count_sql_ary['SELECT'] = 'COUNT(DISTINCT ' . $count_sql_ary['GROUP_BY'] . ') AS total_entries';
		}
		unset($count_sql_ary['ORDER_BY'], $count_sql_ary['GROUP_BY']);

		$sql = $this->db->sql_build_query('SELECT', $count_sql_ary);
		$this->db->sql_query($sql);

		return (int) $this->db->sql_fetchfield('total_entries');
	}

	/**
	 * Returns the SQL Query for displaying simple transactions details
	 *
	 * @param string  $keywords
	 * @param string  $sort_by
	 * @param integer $log_time
	 *
	 * @return array
	 * @access public
	 */
	public function get_logs_sql_ary($keywords, $sort_by, $log_time)
	{
		$sql_keywords = '';
		if (!empty($keywords))
		{
			// Get the SQL condition for our keywords
			$sql_keywords = $this->generate_sql_keyword($keywords);
		}

		$get_logs_sql_ary = array(
			'SELECT'   => 'txn.transaction_id, txn.txn_id, txn.test_ipn, txn.confirmed, txn.payment_date, txn.payment_status, txn.user_id, u.username, u.user_colour',
			'FROM'     => array(
				$this->ppde_transactions_log_table => 'txn',
				USERS_TABLE                        => 'u',
			),
			'WHERE'    => 'txn.user_id = u.user_id ' . $sql_keywords,
			'ORDER_BY' => $sort_by,
		);

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
	private function generate_sql_keyword($keywords, $statement_operator = 'AND')
	{
		// Use no preg_quote for $keywords because this would lead to sole
		// backslashes being added. We also use an OR connection here for
		// spaces and the | string. Currently, regex is not supported for
		// searching (but may come later).
		$keywords = preg_split('#[\s|]+#u', utf8_strtolower($keywords), 0, PREG_SPLIT_NO_EMPTY);
		$sql_keywords = '';

		if (!empty($keywords))
		{
			$keywords_pattern = array();

			// Build pattern and keywords...
			for ($i = 0, $num_keywords = sizeof($keywords); $i < $num_keywords; $i++)
			{
				$keywords_pattern[] = preg_quote($keywords[$i], '#');
				$keywords[$i] = $this->db->sql_like_expression($this->db->get_any_char() . $keywords[$i] . $this->db->get_any_char());
			}

			$sql_keywords = ' ' . $statement_operator . ' (';
			$sql_lower = $this->db->sql_lower_text('txn.txn_id');
			$sql_keywords .= ' ' . $sql_lower . ' ' . implode(' OR ' . $sql_lower . ' ', $keywords) . ')';
		}

		return $sql_keywords;
	}

	/**
	 * Returns user information based on the ID of the donor or they email
	 *
	 * @param string $type
	 * @param int    $arg
	 *
	 * @return array|bool
	 * @access public
	 */
	public function query_donor_user_data($type = 'user', $arg = 1)
	{

		switch ($type)
		{
			case 'user':
				$sql_where = ' WHERE user_id = ' . (int) $arg;
				break;
			case 'email':
				$sql_where = ' WHERE user_email_hash = ' . crc32(strtolower($arg)) . strlen($arg);
				break;
			default:
				$sql_where = '';
		}

		$sql = 'SELECT user_id, username
			FROM ' . USERS_TABLE .
			$sql_where;
		$result = $this->db->sql_query($sql);

		return $this->db->sql_fetchrow($result);
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
	public function build_log_ary($get_logs_sql_ary, $url_ary, $limit = 0, $last_page_offset = 0)
	{
		$sql = $this->db->sql_build_query('SELECT', $get_logs_sql_ary);
		$result = $this->db->sql_query_limit($sql, $limit, $last_page_offset);

		$i = 0;
		$log = array();

		while ($row = $this->db->sql_fetchrow($result))
		{
			$log[$i] = array(
				'transaction_id' => $row['transaction_id'],
				'txn_id'         => $this->build_transaction_url($row['transaction_id'], $row['txn_id'], $url_ary['txn_url'], $row['confirmed']),
				'test_ipn'       => $row['test_ipn'],
				'confirmed'      => $row['confirmed'],
				'payment_status' => $row['payment_status'],
				'payment_date'   => $row['payment_date'],

				'username_full'  => get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], false, $url_ary['profile_url']),
			);

			$i++;
		}

		return $log;
	}

	/**
	 * Build transaction url for placing into templates.
	 *
	 * @param int    $id         The users transaction id
	 * @param string $txn_id     The txn number id
	 * @param string $custom_url optional parameter to specify a profile url. The transaction id get appended to this
	 *                           url as &amp;id={id}
	 * @param bool   $colour
	 *
	 * @return string A string consisting of what is wanted.
	 * @access private
	 */
	private function build_transaction_url($id, $txn_id, $custom_url = '', $colour = false)
	{
		static $_profile_cache;

		// We cache some common variables we need within this function
		if (empty($_profile_cache))
		{
			$_profile_cache['tpl_nourl'] = '{TRANSACTION}';
			$_profile_cache['tpl_url'] = '<a href="{TXN_URL}">{TRANSACTION}</a>';
			$_profile_cache['tpl_url_colour'] = '<a href="{TXN_URL}" style="{TXN_COLOUR};">{TRANSACTION}</a>';
		}

		// Build correct transaction url
		$txn_url = '';
		if ($txn_id)
		{
			$txn_url = ($custom_url !== '') ? $custom_url . '&amp;action=view&amp;id=' . $id : $txn_id;
		}

		// Return

		if (!$txn_url)
		{
			return str_replace('{TRANSACTION}', $txn_id, $_profile_cache['tpl_nourl']);
		}

		return str_replace(array('{TXN_URL}', '{TXN_COLOUR}', '{TRANSACTION}'), array($txn_url, '#FF0000', $txn_id), (!$colour) ? $_profile_cache['tpl_url'] : $_profile_cache['tpl_url_colour']);
	}

	/**
	 * Returns SQL WHERE clause for all marked items
	 *
	 * @param $marked
	 *
	 * @return string
	 * @access public
	 */
	public function build_marked_where_sql($marked)
	{
		$sql_in = array();
		foreach ($marked as $mark)
		{
			$sql_in[] = $mark;
		}

		return ' WHERE ' . $this->db->sql_in_set('transaction_id', $sql_in);
	}

	/**
	 * Build SQL query for updating stats
	 *
	 * @param string $type
	 * @param bool   $test_ipn
	 *
	 * @return string
	 * @access public
	 */
	public function sql_build_update_stats($type, $test_ipn)
	{
		switch ($type)
		{
			case 'ppde_transactions_count':
			case 'ppde_transactions_count_ipn':
				$sql_ary = $this->sql_select_stats_main('txn_id');
				$sql_ary['WHERE'] = "confirmed = 1 AND payment_status = 'Completed' AND txn.test_ipn = " . (int) $test_ipn;
				break;
			case 'ppde_known_donors_count':
			case 'ppde_known_donors_count_ipn':
				$sql_ary = $this->sql_select_stats_main('payer_id');
				$sql_ary{'LEFT_JOIN'} = array(
					array(
						'FROM' => array(USERS_TABLE => 'u'),
						'ON'   => 'txn.user_id = u.user_id',
					),
				);
				$sql_ary['WHERE'] = '(u.user_type = ' . USER_NORMAL . ' OR u.user_type = ' . USER_FOUNDER . ') AND txn.test_ipn = ' . (int) $test_ipn;
				break;
			case 'ppde_anonymous_donors_count':
			case 'ppde_anonymous_donors_count_ipn':
				$sql_ary = $this->sql_select_stats_main('payer_id');
				$sql_ary['WHERE'] = 'txn.user_id = ' . ANONYMOUS . ' AND txn.test_ipn = ' . (int) $test_ipn;
				break;
			default:
				$sql_ary = $this->sql_select_stats_main('txn_id');
		}

		return $this->db->sql_build_query('SELECT', $sql_ary);
	}

	/**
	 * Make body of SQL query for stats calculation.
	 *
	 * @param string $field_name Name of the field
	 *
	 * @return array
	 * @access private
	 */
	private function sql_select_stats_main($field_name)
	{
		return array(
			'SELECT' => 'COUNT(DISTINCT txn.' . $field_name . ') AS count_result',
			'FROM'   => array($this->ppde_transactions_log_table => 'txn'),
		);
	}
}
