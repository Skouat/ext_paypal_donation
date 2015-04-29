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
		global $phpbb_container, $request, $user;

		// Get an instance of the admin controller
		$admin_controller = $phpbb_container->get('skouat.ppde.admin.controller');

		// Requests
		$action = $request->variable('action', '');
		$submit = ($request->is_set_post('submit')) ? true : false;

		// Make the $u_action url available in the admin controller
		$admin_controller->set_page_url($this->u_action);

		// Load a template from adm/style for our ACP page
		$this->tpl_name = 'acp_donation';

		// Set the page title for our ACP page
		$this->page_title = 'ACP_DONATION_MOD';

		// Define the name of the form for use as a form key
		$form_name = 'acp_donation';
		add_form_key($form_name);

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

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}
	}
}
