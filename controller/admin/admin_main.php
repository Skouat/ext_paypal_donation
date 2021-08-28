<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller\admin;

use skouat\ppde\controller\ipn_paypal;

abstract class admin_main
{
	/** @var array */
	protected $args = [];
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
	/** @var \skouat\ppde\actions\locale_icu */
	protected $ppde_actions_locale;
	/** @var ipn_paypal */
	protected $ppde_ipn_paypal;
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

	/** @var string */
	public $u_action;

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
	public function set_page_url($u_action): void
	{
		$this->u_action = $u_action;
	}

	/**
	 * Gets vars from POST then build a array of them
	 *
	 * @param string $id     Module id
	 * @param string $mode   Module categorie
	 * @param string $action Action name
	 *
	 * @return void
	 * @access private
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

	public function get_hidden_fields(): array
	{
		return count($this->args) ? array_merge(
			['i'                           => $this->args['i'],
			 'mode'                        => $this->args['mode'],
			 'action'                      => $this->args['action'],
			 $this->id_prefix_name . '_id' => $this->args[$this->id_prefix_name . '_id']],
			$this->args['hidden_fields']) : ['id' => '', 'mode' => '', 'action' => ''];
	}

	public function set_action($action): void
	{
		$this->args['action'] = $action;
	}

	public function get_action(): string
	{
		return (string) ($this->args['action'] ?? '');
	}

	public function set_item_id($item_id): void
	{
		$this->args[$this->id_prefix_name . '_id'] = (int) $item_id;
	}

	/**
	 * Display items of the called controller
	 *
	 * @return void
	 * @access public
	 */
	public function display(): void
	{
	}

	/**
	 * Add item for the called controller
	 *
	 * @return void
	 * @access public
	 */
	public function add(): void
	{
	}

	public function approve(): void
	{
	}

	/**
	 * Change item details for the called controller
	 *
	 * @return void
	 * @access public
	 */
	public function change(): void
	{
	}

	/**
	 * Delete item for the called controller
	 *
	 * @return void
	 * @access public
	 */
	public function delete(): void
	{
	}

	/**
	 * Edit item on the called controller
	 *
	 * @return void
	 * @access public
	 */
	public function edit(): void
	{
	}

	/**
	 * Enable/disable item on the called controller
	 *
	 * @return void
	 * @access public
	 */
	public function enable(): void
	{
	}

	/**
	 * Move up/down an item on the called controller
	 *
	 * @return void
	 * @access   public
	 */
	public function move(): void
	{
	}

	/**
	 * View a selected item on the called controller
	 *
	 * @return void
	 * @access   public
	 */
	public function view(): void
	{
	}

	/**
	 * Build pull down menu options of available remote URI
	 *
	 * @param int    $default ID of the selected value.
	 * @param string $type    Can be 'live' or 'sandbox'
	 *
	 * @return void
	 * @access public
	 */
	public function build_remote_uri_select_menu($default, $type): void
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
	 * Enforce the type of remote provided
	 *
	 * @param string $type
	 *
	 * @return string
	 * @access private
	 */
	private function force_type($type): string
	{
		return $type === 'live' || $type === 'sandbox' ? (string) $type : 'live';
	}

	/**
	 * The form submitting if 'submit' is true
	 *
	 * @return void
	 * @access protected
	 */
	protected function submit_settings(): void
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
	protected function is_invalid_form($form_name, $submit_or_preview = false): array
	{
		if ($submit_or_preview && !check_form_key($form_name))
		{
			return [$this->language->lang('FORM_INVALID')];
		}

		return [];
	}

	/**
	 * @param array $errors
	 *
	 * @return bool
	 * @access protected
	 */
	protected function can_submit_data(array $errors): bool
	{
		return $this->submit && empty($errors) && !$this->preview;
	}

	/**
	 * Set the options for called controller
	 *
	 * @return void
	 * @access protected
	 */
	protected function set_settings(): void
	{
	}

	/**
	 * Trigger error message if data already exists
	 *
	 * @param \skouat\ppde\entity\main $entity The entity object
	 *
	 * @return void
	 * @access protected
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
	 * @param \skouat\ppde\entity\main $entity The entity object
	 *
	 * @return bool
	 * @access protected
	 */
	protected function is_added_data_exists(\skouat\ppde\entity\main $entity): bool
	{
		return $entity->data_exists($entity->build_sql_data_exists()) && $this->request->variable('action', '') === 'add';
	}

	/**
	 * Check some settings before submitting data
	 *
	 * @param \skouat\ppde\entity\main $entity            The entity object
	 * @param string                   $field_name        Name of the entity function to call
	 * @param string|int               $value_cmp         Default value to compare with the return value of the called function
	 * @param bool                     $submit_or_preview Form submit or preview status
	 *
	 * @return array $errors
	 * @access protected
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
	 * Get result of submit and preview expression
	 *
	 * @param bool $submit
	 * @param bool $preview
	 *
	 * @return bool
	 * @access protected
	 */
	protected function submit_or_preview($submit = false, $preview = false): bool
	{
		return $submit || $preview;
	}

	/**
	 * Show user a result message if AJAX was used
	 *
	 * @param string $message Text message to show to the user
	 *
	 * @return void
	 * @access protected
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
	 * Set u_action output vars for display in the template
	 *
	 * @return void
	 * @access protected
	 */
	protected function u_action_assign_template_vars(): void
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
	 * Set error output vars for display in the template
	 *
	 * @param array $errors
	 *
	 * @return void
	 * @access protected
	 */
	protected function s_error_assign_template_vars($errors): void
	{
		$this->template->assign_vars([
			'S_ERROR'   => (bool) count($errors),
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

		return $config ?: $default;
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
		if (empty($settings) && (bool) $depend_on === true)
		{
			trigger_error($this->language->lang($this->lang_key_prefix . '_MISSING') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		return $settings;
	}

	/**
	 * Run system checks if config 'ppde_first_start' is true
	 *
	 * @return void
	 * @throws \ReflectionException
	 * @access protected
	 */
	protected function ppde_first_start(): void
	{
		if ($this->config['ppde_first_start'])
		{
			$this->ppde_ipn_paypal->set_curl_info();
			$this->ppde_ipn_paypal->set_remote_detected();
			$this->ppde_ipn_paypal->check_tls();
			$this->ppde_actions_locale->set_intl_info();
			$this->ppde_actions_locale->set_intl_detected();
			$this->config->set('ppde_first_start', '0');
		}
	}
}
