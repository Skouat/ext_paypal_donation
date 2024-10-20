<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2024 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller\admin;

use skouat\ppde\controller\esi_controller;
use skouat\ppde\controller\ipn_paypal;

abstract class admin_main
{
	protected const SECONDS_IN_A_DAY = 86400;

	/** @var string */
	public $u_action;

	/** @var array */
	protected $args = [];
	/** @var \phpbb\config\config */
	protected $config;
	/** @var \Symfony\Component\DependencyInjection\ContainerInterface */
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
	/** @var \skouat\ppde\actions\locale_icu */
	protected $ppde_actions_locale;
	/** @var esi_controller */
	protected $esi_controller;
	/** @var bool */
	protected $preview;
	/** @var \phpbb\request\request */
	protected $request;
	/** @var bool */
	protected $submit;
	/** @var \phpbb\template\template */
	protected $template;
	/** @var \phpbb\user */
	protected $user;

	public function main(): void
	{
		$this->u_action_assign_template_vars();
		$action = $this->args['action'];
		switch ($action)
		{
			case 'add':
			case 'change':
			case 'edit':
			case 'view':
				$this->{$action}();
			break;
			case 'move_up':
			case 'move_down':
				$this->move();
			break;
			case 'activate':
			case 'deactivate':
				$this->enable();
			break;
			case 'approve':
			case 'delete':
				// Use a confirm box routine when approving/deleting an item
				if (confirm_box(true))
				{
					$this->{$action}();
					break;
				}
				confirm_box(false, $this->language->lang($this->lang_key_prefix . '_CONFIRM_OPERATION'), build_hidden_fields($this->get_hidden_fields()));

				// Clear $action status
				$this->args['action'] = $action;
			break;
			default:
				$this->display();
		}
		if (!empty($this->args['action']))
		{
			$this->display();
		}
	}

	/**
	 * Assign u_action output vars for display in the template
	 */
	protected function u_action_assign_template_vars(): void
	{
		$this->template->assign_vars(['U_ACTION' => $this->u_action]);
	}

	/**
	 * Get hidden fields arguments.
	 *
	 * @return array Hidden fields arguments.
	 */
	protected function get_hidden_fields(): array
	{
		return count($this->args) ? array_merge(
			['i'                           => $this->args['i'],
			 'mode'                        => $this->args['mode'],
			 'action'                      => $this->args['action'],
			 $this->id_prefix_name . '_id' => $this->args[$this->id_prefix_name . '_id']],
			$this->args['hidden_fields']) : ['id' => '', 'mode' => '', 'action' => ''];
	}

	/**
	 * Set the form action URL.
	 *
	 * @param string $u_action Form action URL.
	 */
	public function set_page_url($u_action): void
	{
		$this->u_action = $u_action;
	}

	/**
	 * Set hidden fields for the form.
	 *
	 * @param string $id     Module ID.
	 * @param string $mode   Module category.
	 * @param string $action Form action.
	 */
	public function set_hidden_fields($id, $mode, $action): void
	{
		$this->args = array_merge($this->args, [
			'i'             => $id,
			'mode'          => $mode,
			'action'        => $action,
			'hidden_fields' => [],
		]);
	}

	public function set_module_info(array $module_info, string $module_name): void
	{
		$this->id_prefix_name = $module_info['id_prefix_name'] ?? '';
		$this->lang_key_prefix = $module_info['lang_key_prefix'] ?? '';
		$this->module_name = $module_name;
	}

	/**
	 * Set the item ID.
	 *
	 * @param int $item_id Item ID.
	 */
	public function set_item_id($item_id): void
	{
		$this->args[$this->id_prefix_name . '_id'] = (int) $item_id;
	}

	/**
	 * Add item for the called controller
	 * This method is intended to be overridden by child classes
	 */
	protected function add(): void
	{
	}

	/**
	 * Approve an item.
	 * This method is intended to be overridden by child classes
	 */
	protected function approve(): void
	{
	}

	/**
	 * Change item details for the called controller
	 * This method is intended to be overridden by child classes
	 */
	protected function change(): void
	{
	}

	/**
	 * Delete item for the called controller
	 * This method is intended to be overridden by child classes
	 */
	protected function delete(): void
	{
	}

	/**
	 * Display items of the called controller
	 * This method is intended to be overridden by child classes
	 */
	protected function display(): void
	{
	}

	/**
	 * Edit item on the called controller
	 * This method is intended to be overridden by child classes
	 */
	protected function edit(): void
	{
	}

	/**
	 * Enable/disable item on the called controller
	 * This method is intended to be overridden by child classes
	 */
	protected function enable(): void
	{
	}

	/**
	 * Move up/down an item on the called controller
	 * This method is intended to be overridden by child classes
	 */
	protected function move(): void
	{
	}

	/**
	 * View a selected item on the called controller
	 * This method is intended to be overridden by child classes
	 */
	protected function view(): void
	{
	}

	/**
	 * Build remote URI select menu options.
	 *
	 * @param int    $default ID of the default selected option.
	 * @param string $type    Type of remote URI ('live' or 'sandbox').
	 */
	protected function build_remote_uri_select_menu($default, $type): void
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
				'REMOTE_ID'   => (int) $id,
				'REMOTE_NAME' => $remote['hostname'],
				'S_DEFAULT'   => (int) $default === (int) $id,
			]);
		}
		unset ($remote_list, $id);
	}

	/**
	 * Enforce the remote URI type.
	 *
	 * @param string $type Remote URI type.
	 * @return string Validated remote URI type.
	 */
	private function force_type($type): string
	{
		return $type === 'live' || $type === 'sandbox' ? (string) $type : 'live';
	}

	/**
	 * Submit settings.
	 */
	protected function submit_settings(): void
	{
		$this->submit = $this->is_form_submitted();

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
	 * Check if form is submitted.
	 *
	 * @return bool True if form is submitted, false otherwise.
	 */
	protected function is_form_submitted(): bool
	{
		return $this->request->is_set_post('submit');
	}

	/**
	 * Check for invalid form submission.
	 *
	 * @param string $form_name         Name of the form.
	 * @param bool   $submit_or_preview Whether the form is submitted or previewed.
	 * @return array Errors encountered during form validation.
	 */
	protected function is_invalid_form($form_name, $submit_or_preview = false): array
	{
		if ($submit_or_preview && !check_form_key($form_name))
		{
			return [$this->language->lang('FORM_INVALID')];
		}

		return [];
	}

	/**
	 * Check if data can be submitted.
	 *
	 * @param array $errors Errors encountered during form validation.
	 * @return bool True if data can be submitted, false otherwise.
	 */
	protected function can_submit_data(array $errors): bool
	{
		return $this->submit && empty($errors) && !$this->preview;
	}

	/**
	 * Set the options for called controller
	 * This method is intended to be overridden by child classes
	 */
	protected function set_settings(): void
	{
	}

	/**
	 * Trigger error message if data already exists
	 *
	 * @param \skouat\ppde\entity\main $entity Entity object to check for existing data.
	 */
	protected function trigger_error_data_already_exists(\skouat\ppde\entity\main $entity): void
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
	 * Check if added data already exists.
	 *
	 * @param \skouat\ppde\entity\main $entity Entity object to check.
	 * @return bool True if the data exists, false otherwise.
	 */
	protected function is_added_data_exists(\skouat\ppde\entity\main $entity): bool
	{
		return $entity->data_exists($entity->build_sql_data_exists()) && $this->request->variable('action', '') === 'add';
	}

	/**
	 * Check some settings before submitting data
	 *
	 * @param \skouat\ppde\entity\main $entity            Entity object to check.
	 * @param string                   $field_name        Name of the field to check.
	 * @param string|int               $value_cmp         Value to compare against for emptiness.
	 * @param bool                     $submit_or_preview Whether the form has been submitted or previewed (default: false).
	 * @return array Errors encountered during empty data check.
	 */
	protected function is_empty_data(\skouat\ppde\entity\main $entity, $field_name, $value_cmp, $submit_or_preview = false): array
	{
		$errors = [];

		if ($submit_or_preview && $entity->{'get_' . $field_name}() == $value_cmp)
		{
			$errors[] = $this->language->lang($this->lang_key_prefix . '_EMPTY_' . strtoupper($field_name));
		}

		return $errors;
	}

	/**
	 * Check if the form has been submitted or previewed.
	 *
	 * @param bool $submit  Submission status (default: false).
	 * @param bool $preview Preview status (default: false).
	 * @return bool True if submitted or previewed, false otherwise.
	 */
	protected function submit_or_preview(bool $submit = false, bool $preview = false): bool
	{
		return $submit || $preview;
	}

	/**
	 * Send AJAX delete result message.
	 *
	 * @param string $message Message to send in the response.
	 */
	protected function ajax_delete_result_message($message = ''): void
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
	 * Assign add/edit action output vars for display in the template
	 *
	 * @param string  $type Action type: 'add' or 'edit'
	 * @param integer $id   Identifier to Edit. If action = add, then let to '0'.
	 */
	protected function add_edit_action_assign_template_vars($type, $id = 0): void
	{
		$id_action = !empty($id) ? '&amp;' . $this->id_prefix_name . '_id=' . (int) $id : '';

		$this->template->assign_vars([
			'S_ADD_EDIT' => true,
			'U_ACTION'   => $this->u_action . '&amp;action=' . $type . $id_action,
			'U_BACK'     => $this->u_action,
		]);
	}

	/**
	 * Assign error output vars for display in the template
	 *
	 * @param array $errors Array of error messages.
	 */
	protected function s_error_assign_template_vars($errors): void
	{
		$this->template->assign_vars([
			'S_ERROR'   => (bool) count($errors),
			'ERROR_MSG' => (count($errors)) ? implode('<br>', $errors) : '',
		]);
	}

	/**
	 * Check and return a config value with type enforcement.
	 *
	 * @param mixed  $config  The config value to check.
	 * @param string $type    The desired data type (e.g., 'boolean', 'integer', 'string').
	 * @param mixed  $default The default value to return if the config value is not set.
	 * @return mixed The config value or the default value if not set, with the enforced type.
	 */
	protected function check_config($config, string $type = 'boolean', $default = '')
	{
		// We're using settype to enforce data types
		settype($config, $type);
		settype($default, $type);

		return $config ?: $default;
	}

	/**
	 * Check required settings and trigger warning if missing.
	 *
	 * @param mixed $settings  The settings value to check.
	 * @param bool  $depend_on The condition that determines if the settings are required.
	 * @return mixed The settings value if the condition is met or settings are not empty; otherwise, triggers an error and terminates.
	 */
	protected function required_settings($settings, $depend_on)
	{
		if (empty($settings) && (bool) $depend_on === true)
		{
			trigger_error($this->language->lang($this->lang_key_prefix . '_MISSING') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		return $settings;
	}

	/**
	 * Perform first-start checks and setup for the extension.
	 *
	 * @throws \ReflectionException
	 */
	protected function ppde_first_start(): void
	{
		if ($this->config['ppde_first_start'])
		{
			$this->esi_controller->set_curl_info();
			$this->esi_controller->set_remote_detected();
			$this->esi_controller->check_tls();
			$this->ppde_actions_locale->set_intl_info();
			$this->ppde_actions_locale->set_intl_detected();
			$this->config->set('ppde_first_start', '0');
		}
	}
}
