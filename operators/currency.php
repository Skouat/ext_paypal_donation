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
}
