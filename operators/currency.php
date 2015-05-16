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

	protected $container;
	protected $db;
	protected $ppde_currency_table;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface                   $container              Service container interface
	 * @param \phpbb\db\driver\driver_interface    $db                     Database connection
	 * @param string                               $ppde_currency_table    Table name
	 * @access public
	 */
	public function __construct(ContainerInterface $container, \phpbb\db\driver\driver_interface $db, $ppde_currency_table)
	{
		$this->container = $container;
		$this->db = $db;
		$this->ppde_currency_table = $ppde_currency_table;
	}

	/**
	 * Get data from currency table
	 *
	 * @param int    $currency_id
	 * @return array Array of currency data entities
	 * @access public
	 */
	public function get_currency_data($currency_id = 0)
	{
		$entities = array();

		// Use WHERE clause when $currency_id is different from 0
		$sql_where = $currency_id ? ' WHERE currency = ' . (int) $currency_id : '';
		// Load all page data from the database
		// Build sql query with alias field
		$sql = 'SELECT *
				FROM ' . $this->ppde_currency_table .
				$sql_where . '
				ORDER BY currency_left_id';
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
}
