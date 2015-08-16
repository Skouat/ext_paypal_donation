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

/**
 * Interface for our transaction/ipn operator
 */
interface transactions_interface
{
	/**
	 * Returns simple details of all PayPal transactions logged in the database
	 *
	 * @param array $get_logs_sql_ary
	 * @param array $url_ary
	 * @param int   $limit
	 * @param int   $last_page_offset
	 *
	 * @return array $log
	 */
	public function build_log_ary($get_logs_sql_ary, $url_ary, $limit = 0, $last_page_offset = 0);

	/**
	 * SQL Query to return Transaction log data table
	 *
	 * @param $transaction_id
	 *
	 * @return string
	 * @access public
	 */
	public function build_sql_data($transaction_id = 0);

	/**
	 * Returns the SQL Query for displaying simple transactions details
	 *
	 * @param $keywords
	 * @param $sort_by
	 * @param $log_time
	 *
	 * @return array
	 */
	public function get_logs_sql_ary($keywords, $sort_by, $log_time);

	/**
	 * Returns SQL WHERE clause for all marked items
	 *
	 * @param $marked
	 *
	 * @return string
	 * @access public
	 */
	public function build_marked_where_sql($marked);

	/**
	 * Returns total entries in the transactions log
	 *
	 * @param $count_logs_sql_ary
	 *
	 * @return int
	 * @access public
	 */
	public function query_sql_count_logs($count_logs_sql_ary);
}
