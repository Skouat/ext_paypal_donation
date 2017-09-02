<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\acp;

class ppde_module
{
	/** @var array */
	private static $available_mode = array(
		array('module_name' => 'currency', 'lang_key_prefix' => 'PPDE_DC_', 'id_prefix_name' => 'currency'),
		array('module_name' => 'donation_pages', 'lang_key_prefix' => 'PPDE_DP_', 'id_prefix_name' => 'page'),
		array('module_name' => 'overview'),
		array('module_name' => 'paypal_features'),
		array('module_name' => 'settings'),
		array('module_name' => 'transactions'),
	);
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
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @type \phpbb\language\language $language Language object */
		$language = $phpbb_container->get('language');

		/** @type \phpbb\request\request $request Request object */
		$request = $phpbb_container->get('request');

		// Requests
		$action = $request->variable('action', '');

		if ($this->in_array_field($mode, 'module_name', $this::$available_mode))
		{
			$this->module_info = $this->array_value($mode, 'module_name', $this::$available_mode);

			// Load the module language file currently in use
			$language->add_lang('acp_' . $mode, 'skouat/ppde');

			// Get an instance of the admin controller
			/** @type \skouat\ppde\controller\admin_main $admin_controller */
			$admin_controller = $phpbb_container->get('skouat.ppde.controller.admin.' . $mode);

			// Make the $u_action url available in the admin controller
			$admin_controller->set_page_url($this->u_action);

			// Set the page title for our ACP page
			$this->page_title = 'PPDE_ACP_' . strtoupper($mode);

			// Load a template from adm/style for our ACP page
			$this->tpl_name = 'ppde_' . strtolower($mode);

			switch ($mode)
			{
				case 'currency':
				case 'donation_pages':
					// Get an instance of the entity
					$entity = $phpbb_container->get('skouat.ppde.entity.' . $mode);

					// Make the $u_action url available in entity
					$entity->set_page_url($this->u_action);

					// Request the ID
					$id = $request->variable($this->module_info['id_prefix_name'] . '_id', 0);

					$this->do_action($id, $mode, $action, $admin_controller);
				break;
				case 'paypal_features':
				case 'settings':
					// Load the display handle in the admin controller
					$admin_controller->display_settings();
				break;
				case 'overview':
					// Load the display overview handle in the admin controller
					/** @type \skouat\ppde\controller\admin_overview_controller $admin_controller */
					$admin_controller->display_overview($action);
				break;
				case 'transactions':
					// Load the display transactions log handle in the admin controller
					/** @type \skouat\ppde\controller\admin_transactions_controller $admin_controller */
					$admin_controller->display_transactions($id, $mode, $action);
				break;
			}
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
		unset($item);

		return array();
	}


	/**
	 * Performs action requested by the module
	 *
	 * @param int                                $id
	 * @param string                             $mode
	 * @param string                             $action
	 * @param \skouat\ppde\controller\admin_main $controller
	 *
	 */
	private function do_action($id, $mode, $action, $controller)
	{
		global $phpbb_container;

		/** @type \phpbb\language\language $language Language object */
		$language = $phpbb_container->get('language');

		switch ($action)
		{
			case 'add':
				// Set the page title for our ACP page
				$this->page_title = $this->module_info['lang_key_prefix'] . 'CONFIG';

				// Load the add handle in the admin controller
				$controller->add();

				// Return to stop execution of this script
				return;
			case 'edit':
				// Set the page title for our ACP page
				$this->page_title = $this->module_info['lang_key_prefix'] . 'CONFIG';

				// Load the edit handle in the admin controller
				$controller->edit($id);

				// Return to stop execution of this script
				return;
			case 'move_down':
			case 'move_up':
				// Move a item
				$controller->move($id, $action);
			break;
			case 'activate':
			case 'deactivate':
				// Enable/disable a item
				$controller->enable($id, $action);
			break;
			case 'delete':
				// Use a confirm box routine when deleting a item
				if (confirm_box(true))
				{
					// Delete a currency
					$controller->delete($id);
				}
				else
				{
					// Request confirmation from the user to delete the selected item
					confirm_box(false, $language->lang($this->module_info['lang_key_prefix'] . 'CONFIRM_DELETE'), build_hidden_fields(array(
						'id'     => $id,
						'mode'   => $mode,
						'action' => $action,
					)));
				}
			break;
		}

		// Load the display handle in the admin controller
		$controller->display();
	}
}
