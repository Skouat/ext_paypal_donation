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
	/** @var string */
	public $page_title;
	/** @var string */
	public $tpl_name;

	/**
	 * @param string $id
	 * @param string $mode
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @type \phpbb\request\request $request Request object */
		$request = $phpbb_container->get('request');

		/** @type \phpbb\user $user User object */
		$user = $phpbb_container->get('user');

		// Requests
		$action = $request->variable('action', '');
		$page_id = $request->variable('page_id', 0);
		$currency_id = $request->variable('currency_id', 0);

		switch ($mode)
		{
			case 'paypal_features':
			case 'overview':
			case 'settings':
			case 'transactions':
				// Get an instance of the admin controller
				/** @type \skouat\ppde\controller\admin_main $admin_controller */
				$admin_controller = $phpbb_container->get('skouat.ppde.controller.admin.' . $mode);

				// Make the $u_action url available in the admin overview controller
				$admin_controller->set_page_url($this->u_action);

				// Set the page title for our ACP page
				$this->page_title = 'PPDE_ACP_' . strtoupper($mode);

				// Load a template from adm/style for our ACP page
				$this->tpl_name = 'ppde_' . strtolower($mode);

				switch ($mode)
				{
					case 'overview':
						// Load the display overview handle in the admin controller
						/** @type \skouat\ppde\controller\admin_overview_controller $admin_controller */
						$admin_controller->display_overview($action);
						break;
					case 'paypal_features':
						// Load the display options handle in the admin controller
						/** @type \skouat\ppde\controller\admin_paypal_features_controller $admin_controller */
						$admin_controller->display_settings();
						break;
					case 'settings':
						// Load the display options handle in the admin controller
						/** @type \skouat\ppde\controller\admin_settings_controller $admin_controller */
						$admin_controller->display_settings();
						break;
					case 'transactions':
						// Load the display transactions log handle in the admin controller
						/** @type \skouat\ppde\controller\admin_transactions_controller $admin_controller */
						$admin_controller->display_transactions($id, $mode, $action);
				}
				break;
			case 'donation_pages':
				// Get an instance of the admin controller and the entity
				/** @type \skouat\ppde\controller\admin_donation_pages_controller $admin_donation_pages_controller */
				$admin_donation_pages_controller = $phpbb_container->get('skouat.ppde.controller.admin.donation_pages');
				/** @type \skouat\ppde\entity\donation_pages $donation_pages_entity */
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
						// Use a confirm box routine when deleting a donation page
						if (confirm_box(true))
						{
							// Delete a donation page
							$admin_donation_pages_controller->delete_donation_page($page_id);
						}
						else
						{
							// Request confirmation from the user to delete the donation page
							confirm_box(false, $user->lang('PPDE_DP_CONFIRM_DELETE'), build_hidden_fields(array(
								'autogroups_id' => $page_id,
								'mode'          => $mode,
								'action'        => $action,
							)));
						}

						break;
				}

				// Display module main page
				$admin_donation_pages_controller->display_donation_pages();
				break;
			case 'currency':
				// Get an instance of the admin controller and the entity
				/** @type \skouat\ppde\controller\admin_currency_controller $admin_currency_controller */
				$admin_currency_controller = $phpbb_container->get('skouat.ppde.controller.admin.currency');
				/** @type \skouat\ppde\entity\currency $currency_entity */
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
					case 'activate':
					case 'deactivate':
						// Enable/disable a currency
						$admin_currency_controller->enable_currency($currency_id, $action);
						break;
					case 'delete':
						// Use a confirm box routine when deleting a currency
						if (confirm_box(true))
						{
							// Delete a currency
							$admin_currency_controller->delete_currency($currency_id);
						}
						else
						{
							// Request confirmation from the user to delete the currency
							confirm_box(false, $user->lang('PPDE_DC_CONFIRM_DELETE'), build_hidden_fields(array(
								'autogroups_id' => $currency_id,
								'mode'          => $mode,
								'action'        => $action,
							)));
						}
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
