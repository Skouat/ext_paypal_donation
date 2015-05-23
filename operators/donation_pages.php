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
class donation_pages implements donation_pages_interface
{
	protected $data;

	protected $container;
	protected $db;
	protected $ppde_donation_pages_table;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface                $container                 Service container interface
	 * @param \phpbb\db\driver\driver_interface $db                        Database connection
	 * @param string                            $ppde_donation_pages_table Table name
	 *
	 * @access public
	 */
	public function __construct(ContainerInterface $container, \phpbb\db\driver\driver_interface $db, $ppde_donation_pages_table)
	{
		$this->container = $container;
		$this->db = $db;
		$this->ppde_donation_pages_table = $ppde_donation_pages_table;
	}

	/**
	 * Get data from donation pages table
	 *
	 * @param int $lang_id
	 *
	 * @return array Array of page data entities
	 * @access public
	 */
	public function get_pages_data($lang_id = 0)
	{
		$entities = array();

		// Load all page data from the database
		// Build sql query with alias field
		$sql = 'SELECT *
				FROM ' . $this->ppde_donation_pages_table . '
				WHERE page_lang_id = ' . (int) ($lang_id) . '
				ORDER BY page_title';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Import each donation page row into an entity
			$entities[] = $this->container->get('skouat.ppde.entity.donation_pages')->import($row);
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
	 *
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
				'name' => $row['lang_local_name'],
				'id'   => (int) $row['lang_id'],
			);
		}
		$this->db->sql_freeresult($result);

		// Return all available languages
		return $langs;
	}

	/**
	 * Add a Page
	 *
	 * @param object $entity Page entity with new data to insert
	 *
	 * @return donation_pages_interface Added page entity
	 * @access public
	 */
	public function add_pages_data($entity)
	{
		// Insert the data to the database
		$entity->insert();

		// Get the newly inserted identifier
		$page_id = $entity->get_id();

		// Reload the data to return a fresh page entity
		return $entity->load($page_id);
	}

	/**
	 * Delete a page
	 *
	 * @param int $page_id The page identifier to delete
	 *
	 * @return bool True if row was deleted, false otherwise
	 * @access public
	 */
	public function delete_page($page_id)
	{
		// Delete the donation page from the database
		$sql = 'DELETE FROM ' . $this->ppde_donation_pages_table . '
			WHERE page_id = ' . (int) $page_id;
		$this->db->sql_query($sql);

		// Return true/false if a donation page was deleted
		return (bool) $this->db->sql_affectedrows();
	}
}
