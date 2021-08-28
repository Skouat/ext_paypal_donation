<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\acp;

class ppde_module
{
	/** @var array */
	private static $available_mode = [
		['module_name' => 'currency', 'lang_key_prefix' => 'PPDE_DC_', 'id_prefix_name' => 'currency'],
		['module_name' => 'donation_pages', 'lang_key_prefix' => 'PPDE_DP_', 'id_prefix_name' => 'page'],
		['module_name' => 'overview'],
		['module_name' => 'paypal_features'],
		['module_name' => 'settings'],
		['module_name' => 'transactions', 'lang_key_prefix' => 'PPDE_DT_', 'id_prefix_name' => 'transaction'],
	];
	/** @var string */
	public $u_action;
	/** @var string */
	public $page_title;
	/** @var string */
	public $tpl_name;
	/** @var array */
	private $module_info;

	/**
	 * @param string $id
	 * @param string $mode
	 *
	 * @return void
	 * @throws \Exception
	 * @access public
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @type \phpbb\language\language $language Language object */
		$language = $phpbb_container->get('language');

		if ($this->in_array_field($mode, 'module_name', $this::$available_mode))
		{
			$this->module_info = $this->array_value($mode, 'module_name', $this::$available_mode);

			// Load the module language file currently in use
			$language->add_lang('acp_' . $mode, 'skouat/ppde');

			// Get an instance of the admin controller
			/** @type \skouat\ppde\controller\admin\admin_main $admin_controller */
			$admin_controller = $phpbb_container->get('skouat.ppde.controller.admin.' . $mode);

			// Make the $u_action url available in the admin controller
			$admin_controller->set_page_url($this->u_action);

			// Set the page title for our ACP page
			$this->page_title = 'PPDE_ACP_' . strtoupper($mode);

			// Load a template from adm/style for our ACP page
			$this->tpl_name = 'ppde_' . strtolower($mode);

			$this->switch_mode($id, $mode, $admin_controller);
		}
		else
		{
			trigger_error('NO_MODE', E_USER_ERROR);
		}
	}

	/**
	 * Check if value is in array
	 *
	 * @param mixed $needle
	 * @param mixed $needle_field
	 * @param array $haystack
	 *
	 * @return bool
	 * @access private
	 */
	private function in_array_field($needle, $needle_field, $haystack)
	{
		foreach ($haystack as $item)
		{
			if (isset($item[$needle_field]) && $item[$needle_field] === $needle)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Return the selected array if value is in array
	 *
	 * @param mixed $needle
	 * @param mixed $needle_field
	 * @param array $haystack
	 *
	 * @return array
	 * @access private
	 */
	private function array_value($needle, $needle_field, $haystack)
	{
		foreach ($haystack as $item)
		{
			if (isset($item[$needle_field]) && $item[$needle_field] === $needle)
			{
				return $item;
			}
		}

		return [];
	}

	/**
	 * Switch to the mode selected
	 *
	 * @param int                                      $id
	 * @param string                                   $mode
	 * @param \skouat\ppde\controller\admin\admin_main $admin_controller
	 *
	 * @return void
	 * @throws \Exception
	 * @access private
	 */
	private function switch_mode($id, $mode, $admin_controller)
	{
		global $phpbb_container;

		/** @type \phpbb\request\request $request Request object */
		$request = $phpbb_container->get('request');

		// Requests vars
		$action = $request->variable('action', '');

		switch ($mode)
		{
			case 'currency':
			case 'donation_pages':
				// Get an instance of the entity
				$entity = $phpbb_container->get('skouat.ppde.entity.' . $mode);

				// Make the $u_action url available in entity
				$entity->set_page_url($this->u_action);
			// no break;
			case 'transactions':
				// Request the item ID
				$admin_controller->set_item_id($request->variable($this->module_info['id_prefix_name'] . '_id', 0));

				// Send module IDs to the controller
				$admin_controller->set_hidden_fields($id, $mode, $action);

				$this->do_action($admin_controller->get_action(), $admin_controller);
			break;
			case 'paypal_features':
			case 'settings':
				// Load the display handle in the admin controller
				/** @type \skouat\ppde\controller\admin\settings_controller|\skouat\ppde\controller\admin\paypal_features_controller $admin_controller */
				$admin_controller->display_settings();
			break;
			case 'overview':
				// Load the display overview handle in the admin controller
				/** @type \skouat\ppde\controller\admin\overview_controller $admin_controller */
				$admin_controller->display_overview($action);
			break;
		}
	}

	/**
	 * Performs action requested by the module
	 *
	 * @param string                                   $action
	 * @param \skouat\ppde\controller\admin\admin_main $controller
	 *
	 * @return void
	 * @throws \Exception
	 * @access private
	 */
	private function do_action($action, $controller)
	{
		global $phpbb_container;

		/** @type \phpbb\language\language $language Language object */
		$language = $phpbb_container->get('language');

		switch ($action)
		{
			case 'add':
			case 'change':
			case 'edit':
			case 'view':
				// Set the page title for our ACP page
				$this->page_title = $this->module_info['lang_key_prefix'] . 'CONFIG';

				// Call the method in the admin controller based on the $action value
				$controller->$action();

				// Return to stop execution of this script
				return;
			case 'move_down':
			case 'move_up':
				$controller->move();
			break;
			case 'activate':
			case 'deactivate':
				$controller->enable();
			break;
			case 'approve':
			case 'delete':
				// Use a confirm box routine when approving/deleting an item
				if (confirm_box(true))
				{
					$controller->$action();
					break;
				}

				// Request confirmation from the user to do the action for selected item
				confirm_box(false, $language->lang($this->module_info['lang_key_prefix'] . 'CONFIRM_OPERATION'), build_hidden_fields($controller->get_hidden_fields()));

				// Clear $action status
				$controller->set_action($action);
			break;
		}

		// Load the display handle in the admin controller
		$controller->display();
	}
}
