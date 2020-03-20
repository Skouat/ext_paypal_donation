<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller\admin;

class donors_controller extends admin_main
{
	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct()
	{
		parent::__construct(
			'donors',
			'PPDE_DD',
			'donor'
		);
	}

	/**
	 * Display the transactions list
	 *
	 * @param string $id     Module id
	 * @param string $mode   Module categorie
	 * @param string $action Action name
	 *
	 * @return void
	 * @access public
	 */
	public function display_donors($id, $mode, $action)
	{
	}
}
