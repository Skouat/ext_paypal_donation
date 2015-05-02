<?php
/**
*
* PayPal Donation extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Skouat
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace skouat\ppde\acp;

class ppde_module
{
	/** @var string */
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container, $request;

		// Get an instance of the admin controller
		$admin_controller = $phpbb_container->get('skouat.ppde.admin.controller');

		// Requests
		$action = $request->variable('action', '');

		// Make the $u_action url available in the admin controller
		$admin_controller->set_page_url($this->u_action);

		switch ($mode)
		{
			case 'overview':
				// Set the page title for our ACP page
				$this->page_title = 'PPDE_OVERVIEW';

				// Load a template from adm/style for our ACP page
				$this->tpl_name = 'acp_donation';

				// Display pages
				$admin_controller->display_overview($id, $mode, $action);
			break;

			case 'settings':
				// Set the page title for our ACP page
				$this->page_title = 'PPDE_SETTINGS';

				// Load a template from adm/style for our ACP page
				$this->tpl_name = 'ppde_settings';

				// Load the display options handle in the admin controller
				$admin_controller->display_settings();
			break;

			case 'donation_pages':
				// Load a template from adm/style for our ACP page
				$this->tpl_name = 'ppde_donation_pages';

				// Set the page title for our ACP page
				$this->page_title = 'PPDE_ACP_DONATION_PAGE';

				// Display module main page
				$admin_controller->display_donation_pages();
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}
	}
}
