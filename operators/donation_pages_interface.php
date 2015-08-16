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
	 * Get list language packs
	 *
	 * @param int $lang_id
	 *
	 * @return array Array of page data entities
	 * @access public
	 */
	public function get_languages($lang_id = 0);

	/**
	 * SQL Query to return donation pages data
	 *
	 * @param int    $lang_id
	 * @param string $mode
	 *
	 * @return string
	 * @access public
	 */
	public function build_sql_data($lang_id = 0, $mode = 'all_pages');
}
