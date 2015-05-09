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
		$page_id = $request->variable('page_id', 0);

		// Make the $u_action url available in the admin controller and ppde_operator
		$admin_controller->set_page_url($this->u_action);

		switch ($mode)
		{
			case 'overview':
				// Set the page title for our ACP page
				$this->page_title = 'PPDE_ACP_OVERVIEW';

				// Load a template from adm/style for our ACP page
				$this->tpl_name = 'ppde_overview';

				// Display pages
				$admin_controller->display_overview($id, $mode, $action);
			break;

			case 'settings':
				// Set the page title for our ACP page
				$this->page_title = 'PPDE_ACP_SETTINGS';

				// Load a template from adm/style for our ACP page
				$this->tpl_name = 'ppde_settings';

				// Load the display options handle in the admin controller
				$admin_controller->display_settings();
			break;

			case 'donation_pages':
				// Get an instance of the admin controller
				$admin_donation_pages_controller = $phpbb_container->get('skouat.ppde.controller.admin.donation_pages');
				$entity_donations_pages = $phpbb_container->get('skouat.ppde.entity.pages');

				// Make the $u_action url available in the admin donation pages controller
				$admin_donation_pages_controller->set_page_url($this->u_action);
				$entity_donations_pages->set_page_url($this->u_action);

				// Load a template from adm/style for our ACP page
				$this->tpl_name = 'ppde_donation_pages';

				// Set the page title for our ACP page
				$this->page_title = 'PPDE_ACP_DONATION_PAGES';

				// Perform any actions submitted by the user
				switch ($action)
				{
					case 'add':
						// Set the page title for our ACP page
						$this->page_title = 'PPDE_DP_CONFIG';

						// Load the add rule handle in the admin controller
						$admin_donation_pages_controller->add_donation_page();

					// Return to stop execution of this script
					return;
					case 'edit':
						// Set the page title for our ACP page
						$this->page_title = 'PPDE_DP_CONFIG';

						// Load the edit donation pages handle in the admin controller
						$admin_donation_pages_controller->edit_donation_page($page_id);

					// Return to stop execution of this script
					return;
					case 'delete':
						// Delete a donation page
						$admin_donation_pages_controller->delete_donation_page($page_id);
					break;
				}

				// Display module main page
				$admin_donation_pages_controller->display_donation_pages();
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}
	}
}
