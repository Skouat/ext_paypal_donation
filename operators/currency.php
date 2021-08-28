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

class currency
{
	protected $cache;
	protected $db;
	protected $ppde_currency_table;

	/**
	 * Constructor
	 *
	 * @param \phpbb\cache\driver\driver_interface $cache               Cache object
	 * @param \phpbb\db\driver\driver_interface    $db                  Database connection
	 * @param string                               $ppde_currency_table Table name
	 *
	 * @access public
	 */
	public function __construct(
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\db\driver\driver_interface $db,
		$ppde_currency_table
	)
	{
		$this->cache = $cache;
		$this->db = $db;
		$this->ppde_currency_table = $ppde_currency_table;
	}

	/**
	 * SQL Query to return currency data table
	 *
	 * @param int  $currency_id  Identifier of currency; Set to 0 to get all currencies
	 * @param bool $only_enabled Status of currency (Default: false)
	 *
	 * @return string
	 * @access public
	 */
	public function build_sql_data($currency_id = 0, $only_enabled = false): string
	{
		// Build main sql request
		$sql_ary = [
			'SELECT'   => '*',
			'FROM'     => [$this->ppde_currency_table => 'c'],
			'ORDER_BY' => 'c.currency_order',
		];

		// Use WHERE clause when $currency_id is different from 0
		if ((int) $currency_id)
		{
			$sql_ary['WHERE'] = 'c.currency_id = ' . (int) $currency_id;
		}

		// Use WHERE clause when $only_enabled is true
		if ($only_enabled)
		{
			$sql_ary['WHERE'] = !empty($sql_ary['WHERE']) ? $sql_ary['WHERE'] . ' AND c.currency_enable = 1' : 'c.currency_enable = 1';
		}

		// Return all page entities
		return $this->db->sql_build_query('SELECT', $sql_ary);
	}

	/**
	 * Check all items order and fix them if necessary
	 *
	 * @return void
	 * @access public
	 */
	public function fix_currency_order(): void
	{
		$result = $this->db->sql_query($this->sql_currency_order());
		$order = 0;

		while ($row = $this->db->sql_fetchrow($result))
		{
			++$order;

			if ((int) $row['currency_order'] !== $order)
			{
				$this->db->sql_query('UPDATE ' . $this->ppde_currency_table . '
						SET currency_order = ' . $order . '
						WHERE currency_id = ' . (int) $row['currency_id']);
			}
		}

		$this->db->sql_freeresult($result);
	}

	/**
	 * Returns SQL Query
	 *
	 * @return string
	 * @access private
	 */
	private function sql_currency_order(): string
	{
		// By default, check that image_order is valid and fix it if necessary
		return 'SELECT currency_id, currency_order
				FROM ' . $this->ppde_currency_table . '
				ORDER BY currency_order';
	}

	/**
	 * Move a currency up/down
	 *
	 * @param int $switch_order_id The next value of the order
	 * @param int $current_order   The current order identifier
	 * @param int $id              The currency identifier to move
	 *
	 * @return bool
	 * @access public
	 */
	public function move($switch_order_id, $current_order, $id): bool
	{
		// Update the entry
		$sql = 'UPDATE ' . $this->ppde_currency_table . '
					SET currency_order = ' . (int) $current_order . '
					WHERE currency_order = ' . (int) $switch_order_id . '
						AND currency_id <> ' . (int) $id;
		$this->db->sql_query($sql);

		$move_executed = (bool) $this->db->sql_affectedrows();

		// Only update the other entry too if the previous entry got updated
		if ($move_executed)
		{
			$sql = 'UPDATE ' . $this->ppde_currency_table . '
						SET currency_order = ' . (int) $switch_order_id . '
						WHERE currency_order = ' . (int) $current_order . '
							AND currency_id = ' . (int) $id;
			$this->db->sql_query($sql);
		}

		$this->cache->destroy('sql', $this->ppde_currency_table);

		return $move_executed;
	}
}
