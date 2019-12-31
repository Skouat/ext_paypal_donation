<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller\admin;

use skouat\ppde\controller\ipn_paypal;
use skouat\ppde\entity\main;

abstract class admin_main
{
	/** @var object \phpbb\config\config */
	protected $config;
	/** @var object Symfony\Component\DependencyInjection\ContainerInterface */
	protected $container;
	/** @var string */
	protected $id_prefix_name;
	/** @var string */
	protected $lang_key_prefix;
	/** @var \phpbb\language\language */
	protected $language;
	/** @var \phpbb\log\log */
	protected $log;
	/** @var string */
	protected $module_name;
	/** @var bool */
	protected $preview;
	/** @var \phpbb\request\request */
	protected $request;
	/** @var bool */
	protected $submit;
	/** @var \phpbb\template\template */
	protected $template;
	/** @var string */
	protected $u_action;
	/** @var \phpbb\user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param string $lang_key_prefix Prefix for the messages thrown by exceptions
	 * @param string $id_prefix_name  Prefix name for identifier in the URL
	 * @param string $module_name     Name of the module currently used
	 *
	 * @access public
	 */
	public function __construct($module_name, $lang_key_prefix, $id_prefix_name)
	{
		$this->module_name = $module_name;
		$this->lang_key_prefix = $lang_key_prefix;
		$this->id_prefix_name = $id_prefix_name;
	}

	/**
	 * Set page url
	 *
	 * @param string $u_action Custom form action
	 *
	 * @return void
	 * @access public
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}

	public function display()
	{
	}

	public function add()
	{
	}

	public function edit($id)
	{
	}

	public function delete($id)
	{
	}

	public function enable($id, $action)
	{
	}

	public function move($id, $action)
	{
	}

	protected function set_settings()
	{
	}

	/**
	 * The form submitting if 'submit' is true
	 *
	 * @return void
	 * @access protected
	 */
	protected function submit_settings()
	{
		$this->submit = $this->request->is_set_post('submit');

		// Test if the submitted form is valid
		$errors = $this->is_invalid_form('ppde_' . $this->module_name, $this->submit);

		if ($this->can_submit_data($errors))
		{
			// Set the options the user configured
			$this->set_settings();

			// Add option settings change action to the admin log
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_UPDATED');

			// Option settings have been updated and logged
			// Confirm this to the user and provide link back to previous page
			trigger_error($this->language->lang($this->lang_key_prefix . '_SAVED') . adm_back_link($this->u_action));
		}
	}

	/**
	 * Check if form is valid or not
	 *
	 * @param string $form_name
	 * @param bool   $submit_or_preview
	 *
	 * @return array
	 * @access protected
	 */
	protected function is_invalid_form($form_name, $submit_or_preview = false)
	{
		return (!check_form_key($form_name) && $submit_or_preview) ? [$this->language->lang('FORM_INVALID')] : [];
	}

	/**
	 * @param array $errors
	 *
	 * @return bool
	 * @access protected
	 */
	protected function can_submit_data(array $errors)
	{
		return $this->submit && empty($errors) && !$this->preview;
	}

	/**
	 * Trigger error message if data already exists
	 *
	 * @param \skouat\ppde\entity\main $entity The entity object
	 *
	 * @access protected
	 */
	protected function trigger_error_data_already_exists(main $entity)
	{
		if ($this->is_added_data_exists($entity))
		{
			// Show user warning for an already exist page and provide link back to the edit page
			$message = $this->language->lang($this->lang_key_prefix . '_EXISTS');
			$message .= '<br><br>';
			$message .= $this->language->lang($this->lang_key_prefix . '_GO_TO_PAGE', '<a href="' . $this->u_action . '&amp;action=edit&amp;' . $this->id_prefix_name . '_id=' . $entity->get_id() . '">&raquo; ', '</a>');
			trigger_error($message . adm_back_link($this->u_action), E_USER_WARNING);
		}
	}

	/**
	 * @param \skouat\ppde\entity\main $entity The entity object
	 *
	 * @return bool
	 * @access protected
	 */
	protected function is_added_data_exists(main $entity)
	{
		return $entity->data_exists($entity->build_sql_data_exists()) && $this->request->variable('action', '') === 'add';
	}

	/**
	 * Check some settings before submitting data
	 *
	 * @param \skouat\ppde\entity\main $entity            The entity object
	 * @param string                   $field_name        Name of the entity function to call
	 * @param string|int               $value_cmp         Default value to compare with the call_user_func() return value
	 * @param bool                     $submit_or_preview Form submit or preview status
	 *
	 * @return array $errors
	 * @access protected
	 */
	protected function is_empty_data(main $entity, $field_name, $value_cmp, $submit_or_preview = false)
	{
		$errors = [];

		if (call_user_func([$entity, 'get_' . $field_name]) == $value_cmp && $submit_or_preview)
		{
			$errors[] = $this->language->lang($this->lang_key_prefix . '_EMPTY_' . strtoupper($field_name));
		}

		return $errors;
	}

	/**
	 * Get result of submit and preview expression
	 *
	 * @param bool $submit
	 * @param bool $preview
	 *
	 * @return bool
	 * @access protected
	 */
	protected function submit_or_preview($submit = false, $preview = false)
	{
		return (bool) $submit || (bool) $preview;
	}

	/**
	 * Show user a result message if AJAX was used
	 *
	 * @param string $message Text message to show to the user
	 *
	 * @return void
	 * @access protected
	 */
	protected function ajax_delete_result_message($message = '')
	{
		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response;
			$json_response->send([
				'MESSAGE_TITLE' => $this->language->lang('INFORMATION'),
				'MESSAGE_TEXT'  => $message,
				'REFRESH_DATA'  => [
					'time' => 3,
				],
			]);
		}
	}

	/**
	 * Set u_action output vars for display in the template
	 *
	 * @return void
	 * @access protected
	 */
	protected function u_action_assign_template_vars()
	{
		$this->template->assign_vars([
			'U_ACTION' => $this->u_action,
		]);
	}

	/**
	 * Set add/edit action output vars for display in the template
	 *
	 * @param string  $type Action type: 'add' or 'edit'
	 * @param integer $id   Identifier to Edit. If action = add, then let to '0'.
	 *
	 * @return void
	 * @access protected
	 */
	protected function add_edit_action_assign_template_vars($type, $id = 0)
	{
		$id_action = !empty($id) ? '&amp;' . $this->id_prefix_name . '_id=' . (int) $id : '';

		$this->template->assign_vars([
			'S_ADD_EDIT' => true,
			'U_ACTION'   => $this->u_action . '&amp;action=' . $type . $id_action,
			'U_BACK'     => $this->u_action,
		]);
	}

	/**
	 * Set error output vars for display in the template
	 *
	 * @param array $errors
	 *
	 * @return void
	 * @access protected
	 */
	protected function s_error_assign_template_vars($errors)
	{
		$this->template->assign_vars([
			'S_ERROR'   => (count($errors)) ? true : false,
			'ERROR_MSG' => (count($errors)) ? implode('<br>', $errors) : '',
		]);
	}

	/**
	 * Check if a config value is true
	 *
	 * @param mixed  $config Config value
	 * @param string $type   (see settype())
	 * @param mixed  $default
	 *
	 * @return mixed
	 * @access protected
	 */
	protected function check_config($config, $type = 'boolean', $default = '')
	{
		// We're using settype to enforce data types
		settype($config, $type);
		settype($default, $type);

		return $config ? $config : $default;
	}

	/**
	 * Check if settings is required
	 *
	 * @param $settings
	 * @param $depend_on
	 *
	 * @return mixed
	 * @access protected
	 */
	protected function required_settings($settings, $depend_on)
	{
		if (empty($settings) && $depend_on == true)
		{
			trigger_error($this->language->lang($this->lang_key_prefix . '_MISSING') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		return $settings;
	}

	/**
	 * Check if a settings depend on another.
	 *
	 * @param $config_name
	 *
	 * @return bool
	 * @access protected
	 */
	protected function depend_on($config_name)
	{
		return !empty($this->config[$config_name]) ? (bool) $this->config[$config_name] : false;
	}

	/**
	 * Build pull down menu options of available currency
	 *
	 * @param mixed  $default ID of the selected value.
	 * @param string $type    Can be 'live' or 'sandbox'
	 *
	 * @return void
	 * @access public
	 */
	public function build_remote_uri_select_menu($default, $type)
	{
		$type = $this->force_type($type);

		// Grab the list of remote uri for selected type
		$remote_list = ipn_paypal::get_remote_uri();

		// Process each menu item for pull-down
		foreach ($remote_list as $id => $remote)
		{
			if ($remote['type'] !== $type)
			{
				continue;
			}

			// Set output block vars for display in the template
			$this->template->assign_block_vars('remote_options', [
				'REMOTE_ID'   => $id,
				'REMOTE_NAME' => $remote['hostname'],
				'S_DEFAULT'   => $default == $id,
			]);
		}
		unset ($remote_list, $remote);
	}

	/**
	 * Enforce the type of remote provided
	 *
	 * @param string $type
	 *
	 * @return string
	 * @access private
	 */
	private function force_type($type)
	{
		return $type === 'live' || $type === 'sandbox' ? (string) $type : 'live';
	}
}
