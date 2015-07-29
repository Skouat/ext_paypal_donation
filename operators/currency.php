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

/**
 * Operator for a set of pages
 */
class currency implements currency_interface
{
	protected $data;

	protected $cache;
	protected $container;
	protected $db;
	protected $ppde_currency_table;

	/**
	 * Constructor
	 *
	 * @param \phpbb\cache\service              $cache               Cache object
	 * @param ContainerInterface                $container           Service container interface
	 * @param \phpbb\db\driver\driver_interface $db                  Database connection
	 * @param string                            $ppde_currency_table Table name
	 *
	 * @access public
	 */
	public function __construct(\phpbb\cache\service $cache, ContainerInterface $container, \phpbb\db\driver\driver_interface $db, $ppde_currency_table)
	{
		$this->cache = $cache;
		$this->container = $container;
		$this->db = $db;
		$this->ppde_currency_table = $ppde_currency_table;
	}

	/**
	 * Get data from currency table
	 *
	 * @param int  $currency_id  Identifier of currency; Set to 0 to get all currencies (Default: 0)
	 * @param bool $only_enabled Status of currency (Default: false)
	 *
	 * @return array Array of currency data entities
	 * @access public
	 */
	public function get_currency_data($currency_id = 0, $only_enabled = false)
	{
		$entities = array();

		// Build main sql request
		$sql_ary = array(
			'SELECT'   => '*',
			'FROM'     => array($this->ppde_currency_table => 'c'),
			'WHERE'    => '',
			'ORDER_BY' => 'c.currency_order',
		);

		// Use WHERE clause when $currency_id is different from 0
		$sql_ary['WHERE'] .= (int) $currency_id ? 'c.currency_id = ' . (int) $currency_id : '';

		// Use WHERE clause when $only_enabled is true
		if ($only_enabled)
		{
			$sql_ary['WHERE'] .= !empty($sql_ary['WHERE']) ? ' AND c.currency_enable = 1' : 'c.currency_enable = 1';
		}

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Import each currency page row into an entity
			$entities[] = $this->container->get('skouat.ppde.entity.currency')->import($row);
		}
		$this->db->sql_freeresult($result);

		// Return all page entities
		return $entities;
	}

	/**
	 * Add a currency
	 *
	 * @param object $entity Currency entity with new data to insert
	 *
	 * @return currency_interface Add currency entity
	 * @access public
	 */
	public function add_currency_data($entity)
	{
		// Insert the data to the database
		$entity->insert();

		// Get the newly inserted identifier
		$currency_id = $entity->get_id();

		// Reload the data to return a fresh currency entity
		return $entity->load($currency_id);
	}

	/**
	 * Delete a currency
	 *
	 * @param int $currency_id The currency identifier to delete
	 *
	 * @return bool True if row was deleted, false otherwise
	 * @access public
	 */
	public function delete_currency_data($currency_id)
	{
		// Delete the donation page from the database
		$sql = 'DELETE FROM ' . $this->ppde_currency_table . '
			WHERE currency_id = ' . (int) $currency_id;
		$this->db->sql_query($sql);

		// Return true/false if a donation page was deleted
		return (bool) $this->db->sql_affectedrows();
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
	public function move($switch_order_id, $current_order, $id)
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

	/**
	 * Check all items order and fix them if necessary
	 *
	 * @return null
	 * @access public
	 */
	public function fix_currency_order()
	{
		// By default, check that image_order is valid and fix it if necessary
		$sql = 'SELECT currency_id, currency_order
				FROM ' . $this->ppde_currency_table . '
				ORDER BY currency_order';
		$result = $this->db->sql_query($sql);

		if ($row = $this->db->sql_fetchrow($result))
		{
			$order = 0;
			do
			{
				++$order;
				if ($row['currency_order'] != $order)
				{
					$this->db->sql_query('UPDATE ' . $this->ppde_currency_table . '
						SET currency_order = ' . $order . '
						WHERE currency_id = ' . $row['currency_id']);
				}
			} while ($row = $this->db->sql_fetchrow($result));
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Checks if the currency is the last enabled.
	 *
	 * @param string $action
	 *
	 * @return bool
	 * @access public
	 */
	public function last_currency_enabled($action = '')
	{
		return $this->count_currency_enable($action) <= 1;
	}

	/**
	 * Count the number of available currencies
	 *
	 * @param string $action
	 *
	 * @return int
	 * @access private
	 */
	private function count_currency_enable($action = '')
	{
		// Count the number of available currencies
		$sql = 'SELECT COUNT(currency_id) AS cnt_currency
				FROM ' . $this->ppde_currency_table;
		$sql .= ($action == 'disable') ? ' WHERE currency_enable = 1' : '';

		$this->db->sql_query($sql);

		return $this->db->sql_fetchfield('cnt_currency');
	}
}
