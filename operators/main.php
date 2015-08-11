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
 * Operator for a set of pages
 */
abstract class main
{
	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct()
	{
	}

	/**
	 * Add a data
	 *
	 * @param object $entity Data entity with new data to insert
	 *
	 * @return mixed
	 * @access public
	 */
	public function add_data($entity)
	{
		// Insert the data to the database
		$entity->insert();

		// Get the newly inserted identifier
		$id = $entity->get_id();

		// Reload the data to return a fresh currency entity
		return $entity->load($id);
	}
}
