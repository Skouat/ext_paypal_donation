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

class donation_pages
{
	protected $db;
	protected $ppde_donation_pages_table;

	/**
	 * Constructor
	 *
	 * @param driver_interface $db                        Database connection
	 * @param string           $ppde_donation_pages_table Table name
	 */
	public function __construct(driver_interface $db, $ppde_donation_pages_table)
	{
		$this->db = $db;
		$this->ppde_donation_pages_table = $ppde_donation_pages_table;
	}

	/**
	 * SQL Query to return donation pages data table
	 *
	 * @param int    $lang_id Language Identifier
	 * @param string $mode    Could be 'success', 'cancel and 'body'. (Default: 'all_pages')
	 *
	 * @return string
	 */
	public function build_sql_data($lang_id = 0, $mode = 'all_pages'): string
	{
		$sql_ary = [
			'SELECT' => '*',
			'FROM'   => [$this->ppde_donation_pages_table => 'dp'],
			'WHERE'  => 'dp.page_lang_id = ' . (int) $lang_id,
			'ORDER_BY' => 'dp.page_title',
		];

		if (in_array($mode, ['body', 'cancel', 'success']))
		{
			$sql_ary['WHERE'] .= " AND page_title = 'donation_{$mode}'";
		}

		return $this->db->sql_build_query('SELECT', $sql_ary);
	}

	/**
	 * Get language packs data
	 *
	 * Used to return all data for a specific language.
	 * If not defined, all available language are returned.
	 *
	 * @param int $lang_id
	 *
	 * @return array $langs
	 */
	public function get_languages($lang_id = 0): array
	{
		$sql_ary = [
			'SELECT' => '*',
			'FROM'   => [LANG_TABLE => ''],
		];

		// Request by id if provided
		if (($lang_id !== 0))
		{
			$sql_ary['WHERE'] = 'lang_id = ' . (int) $lang_id;
		}

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);

		$langs = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$langs[$row['lang_iso']] = [
				'name' => $row['lang_local_name'],
				'id'   => (int) $row['lang_id'],
			];
		}
		$this->db->sql_freeresult($result);

		return $langs;
	}
}
