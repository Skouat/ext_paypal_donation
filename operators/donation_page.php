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
class donation_page implements donation_page_interface
{
	protected $data;

	protected $container;
	protected $db;
	protected $ppde_item_table;

	/**
	* Constructor
	*
	* @param ContainerInterface                   $container          Service container interface
	* @param \phpbb\db\driver\driver_interface    $db                 Database connection
	* @param string                               $ppde_item_table    Table name
	* @access public
	*/
	public function __construct(ContainerInterface $container, \phpbb\db\driver\driver_interface $db, $ppde_item_table)
	{
		$this->container = $container;
		$this->db = $db;
		$this->ppde_item_table = $ppde_item_table;
	}

	/**
	* Get data from item_data table
	*
	* @param string $item_type
	* @param int    $lang_id
	* @return array Array of page data entities
	* @access public
	*/
	public function get_item_data($item_type, $lang_id = 0)
	{
		$entities = array();

		// Load all page data from the database
		// Build sql query with alias field
		$sql = 'SELECT *
				FROM ' . $this->ppde_item_table . "
				WHERE item_type = '" . $this->db->sql_escape($item_type) . "'
				AND item_iso_code = " . (int) ($lang_id);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Import each donatino page row into an entity
			$entities[] = $this->container->get('skouat.ppde.entity')->import($row);
		}
		$this->db->sql_freeresult($result);

		// Return all page entities
		return $entities;
	}

	/**
	* Get language packs data
	*
	* Used to return all data for a specific language.
	* If not defined, all available language are returned.
	*
	* @param int $lang_id 
	* @return array $langs
	* @access public
	*/
	public function get_languages($lang_id = 0)
	{
		// Request by id if provided, otherwise default to request all
		$sql_where = ($lang_id <> 0) ? 'WHERE lang_id = ' . (int) $lang_id : '';

		$langs = array();

		$sql = 'SELECT * FROM ' . LANG_TABLE . ' ' . $sql_where;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$langs[$row['lang_iso']] = array(
				'name'	=> $row['lang_local_name'],
				'id'	=> (int) $row['lang_id'],
			);
		}
		$this->db->sql_freeresult($result);

		// Return all available languages
		return $langs;
	}

	/**
	* Add a Item
	*
	* @param object $entity Item entity with new data to insert
	* @return page_interface Added page entity
	* @access public
	*/
	public function add_item_data($entity)
	{
		// Insert the page data to the database
		$entity->insert();

		// Get the newly inserted page's identifier
		$item_id = $entity->get_id();

		// Reload the data to return a fresh page entity
		return $entity->load($item_id);
	}
}
