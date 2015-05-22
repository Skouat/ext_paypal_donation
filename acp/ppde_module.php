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

/**
 * @property string tpl_name
 * @property string page_title
 */
class ppde_module
{
	/** @var string */
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container, $request;

		// Requests
		$action = $request->variable('action', '');
		$page_id = $request->variable('page_id', 0);
		$currency_id = $request->variable('currency_id', 0);

		switch ($mode)
		{
			case 'overview':
			case 'settings':
				// Get an instance of the admin controller
				$admin_controller = $phpbb_container->get('skouat.ppde.controller.admin.' . $mode);

				// Make the $u_action url available in the admin overview controller
				$admin_controller->set_page_url($this->u_action);

				// Set the page title for our ACP page
				$this->page_title = 'PPDE_ACP_' . strtoupper($mode);

				// Load a template from adm/style for our ACP page
				$this->tpl_name = 'ppde_' . strtolower($mode);

				if ($mode == 'overview')
				{
					// Load the display overview handle in the admin controller
					$admin_controller->display_overview($id, $mode, $action);
				}
				else if ($mode == 'settings')
				{
					// Load the display options handle in the admin controller
					$admin_controller->display_settings();
				}
				break;

			case 'donation_pages':
				// Get an instance of the admin controller and the entity
				$admin_donation_pages_controller = $phpbb_container->get('skouat.ppde.controller.admin.donation_pages');
				$donation_pages_entity = $phpbb_container->get('skouat.ppde.entity.donation_pages');

				// Make the $u_action url available in controller and entity
				$admin_donation_pages_controller->set_page_url($this->u_action);
				$donation_pages_entity->set_page_url($this->u_action);

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

						// Load the add donation page handle in the admin controller
						$admin_donation_pages_controller->add_donation_page();

						// Return to stop execution of this script
						return;
					case 'edit':
						// Set the page title for our ACP page
						$this->page_title = 'PPDE_DP_CONFIG';

						// Load the edit donation page handle in the admin controller
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

			case 'currency':
				// Get an instance of the admin controller and the entity
				$admin_currency_controller = $phpbb_container->get('skouat.ppde.controller.admin.currency');
				$currency_entity = $phpbb_container->get('skouat.ppde.entity.currency');

				// Make the $u_action url available in controller and entity
				$admin_currency_controller->set_page_url($this->u_action);
				$currency_entity->set_page_url($this->u_action);

				// Load a template from adm/style for our ACP page
				$this->tpl_name = 'ppde_currency';

				// Set the page title for our ACP page
				$this->page_title = 'PPDE_ACP_CURRENCY';

				// Perform any actions submitted by the user
				switch ($action)
				{
					case 'add':
						// Set the page title for our ACP page
						$this->page_title = 'PPDE_DC_CONFIG';

						// Load the add currency handle in the admin controller
						$admin_currency_controller->add_currency();

						// Return to stop execution of this script
						return;
					case 'edit':
						// Set the page title for our ACP page
						$this->page_title = 'PPDE_DC_CONFIG';

						// Load the edit donation pages handle in the admin controller
						$admin_currency_controller->edit_currency($currency_id);

						// Return to stop execution of this script
						return;
					case 'move_down':
					case 'move_up':
						// Move a currency
						$admin_currency_controller->move_currency($currency_id, $action);
						break;
					case 'enable':
					case 'disable':
						// Enable/disable a currency
						$admin_currency_controller->enable_currency($currency_id, $action);
						break;
					case 'delete':
						// Delete a donation page
						$admin_currency_controller->delete_currency($currency_id);
						break;
				}
				// Display module main page
				$admin_currency_controller->display_currency();
				break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
				break;
		}
	}
}
