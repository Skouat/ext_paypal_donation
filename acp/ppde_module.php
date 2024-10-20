<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2024 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\acp;

class ppde_module
{
	public $u_action;
	public $page_title;
	public $tpl_name;
	private $module_config;
	private $module_info;

	public function __construct()
	{
		$this->module_config = [
			'currency'        => ['lang_key_prefix' => 'PPDE_DC', 'id_prefix_name' => 'currency'],
			'donation_pages'  => ['lang_key_prefix' => 'PPDE_DP', 'id_prefix_name' => 'page'],
			'overview'        => ['lang_key_prefix' => 'PPDE'],
			'paypal_features' => ['lang_key_prefix' => 'PPDE_PAYPAL_FEATURES'],
			'settings'        => ['lang_key_prefix' => 'PPDE_SETTINGS'],
			'transactions'    => ['lang_key_prefix' => 'PPDE_DT', 'id_prefix_name' => 'transaction'],];
	}

	/**
	 * @param string $id
	 * @param string $mode
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @type \phpbb\request\request $request Request object */
		$request = $phpbb_container->get('request');
		/** @type \phpbb\language\language $language Language object */
		$language = $phpbb_container->get('language');

		if (!isset($this->module_config[$mode]))
		{
			trigger_error('NO_MODE', E_USER_ERROR);
		}

		$this->module_info = $this->module_config[$mode];

		$language->add_lang('acp_' . $mode, 'skouat/ppde');

		/** @type \skouat\ppde\controller\admin\admin_main $controller */
		$controller = $phpbb_container->get('skouat.ppde.controller.admin.' . $mode);
		$controller->set_page_url($this->u_action);
		$controller->set_module_info($this->module_info, $mode);

		$this->set_page_title_and_template($mode);
		if (in_array($mode, ['currency', 'donation_pages', 'transactions']))
		{
			$this->handle_item_actions($controller, $request);
		}
		elseif ($mode === 'overview')
		{
			$action = $request->variable('action', '');
			/** @type \skouat\ppde\controller\admin\overview_controller $controller */
			$controller->display_overview($action);
		}
		else
		{
			/** @type \skouat\ppde\controller\admin\settings_controller|\skouat\ppde\controller\admin\paypal_features_controller $controller */
			$controller->display_settings();
		}
	}

	private function set_page_title_and_template($mode): void
	{
		$this->page_title = 'PPDE_ACP_' . strtoupper($mode);
		$this->tpl_name = 'ppde_' . strtolower($mode);
	}

	/**
	 * @param \skouat\ppde\controller\admin\admin_main $controller
	 * @param \phpbb\request\request                   $request
	 */
	private function handle_item_actions($controller, $request): void
	{
		$action = $request->variable('action', '');
		$id_prefix_name = $this->module_info['id_prefix_name'];
		$item_id = $request->variable($id_prefix_name . '_id', 0);
		$module_id = $request->variable('i', '');
		$mode = $request->variable('mode', '');

		$controller->set_item_id($item_id);
		$controller->set_hidden_fields($module_id, $mode, $action);
		$controller->main();
	}
}
