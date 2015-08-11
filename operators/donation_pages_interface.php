<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\operators;

/**
 * Interface for our pages operator
 *
 * This describes all of the methods we'll have for working with a set of pages
 */
interface donation_pages_interface
{
	/**
	 * Get data from donation pages table
	 *
	 * @param int    $lang_id
	 * @param string $mode
	 *
	 * @return array Array of page data entities
	 * @access public
	 */
	public function get_pages_data($lang_id = 0, $mode = 'all_pages');

	/**
	 * Get list language packs
	 *
	 * @param int $lang_id
	 *
	 * @return array Array of page data entities
	 * @access public
	 */
	public function get_languages($lang_id = 0);

	/**
	 * Delete a page
	 *
	 * @param int $page_id The page identifier to delete
	 *
	 * @return bool True if row was deleted, false otherwise
	 * @access public
	 */
	public function delete_page($page_id);

}
