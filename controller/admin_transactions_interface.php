<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller;

/**
 * Interface for our admin transactions controller
 *
 * This describes all of the methods we'll use for the admin front-end of this extension
 */
interface admin_transactions_interface
{
	/**
	 * Display the pages
	 *
	 * @param string $id     Module id
	 * @param string $mode   Module categorie
	 * @param string $action Action name
	 *
	 * @return null
	 * @access public
	 */
	public function display_transactions($id, $mode, $action);
}
