<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller;

abstract class admin_main
{
	/** @var object Symfony\Component\DependencyInjection\ContainerInterface */
	protected $container;
	/** @var string */
	protected $id_prefix_name;
	/** @var string */
	protected $lang_key_prefix;
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
	 * Parse data to the entity
	 *
	 * @param \skouat\ppde\entity\main $entity The entity object
	 * @param string                   $run_before_insert
	 *
	 * @return string $log_action
	 * @access public
	 */
	public function add_edit_data($entity, $run_before_insert = '')
	{
		if ($entity->get_id())
		{
			// Save the edited item entity to the database
			$entity->save($entity->check_required_field());
			$log_action = 'UPDATED';
		}
		else
		{
			// Insert the data to the database
			$entity->insert($run_before_insert);

			// Get the newly inserted identifier
			$id = $entity->get_id();

			// Reload the data to return a fresh entity
			$entity->load($id);
			$log_action = 'ADDED';
		}

		return $log_action;
	}

	/**
	 * Set data in the $entity object.
	 * Use call_user_func_array() to call $entity function
	 *
	 * @param \skouat\ppde\entity\main $entity The entity object
	 * @param array                    $data_ary
	 *
	 * @return array
	 * @access public
	 */
	public function set_entity_data($entity, $data_ary)
	{
		$errors = array();

		foreach ($data_ary as $entity_function => $data)
		{
			try
			{
				// Calling the set_$entity_function on the entity and passing it $currency_data
				call_user_func_array(array($entity, 'set_' . $entity_function), array($data));
			}
			catch (\skouat\ppde\exception\base $e)
			{
				// Catch exceptions and add them to errors array
				$errors[] = $e->get_message($this->user);
			}
		}
		unset($data_ary, $entity_function, $data);

		return $errors;
	}

	/**
	 * Set page url
	 *
	 * @param string $u_action Custom form action
	 *
	 * @return null
	 * @access public
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
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
	protected function trigger_error_data_already_exists($entity)
	{
		if ($this->is_added_data_exists($entity))
		{
			// Show user warning for an already exist page and provide link back to the edit page
			$message = $this->user->lang[$this->lang_key_prefix . '_EXISTS'];
			$message .= '<br /><br />';
			$message .= $this->user->lang($this->lang_key_prefix . '_GO_TO_PAGE', '<a href="' . $this->u_action . '&amp;action=edit&amp;' . $this->id_prefix_name . '_id=' . $entity->get_id() . '">&raquo; ', '</a>');
			trigger_error($message . adm_back_link($this->u_action), E_USER_WARNING);
		}
	}

	/**
	 * @param \skouat\ppde\entity\main $entity The entity object
	 *
	 * @return bool
	 * @access protected
	 */
	protected function is_added_data_exists($entity)
	{
		return $entity->data_exists($entity->build_sql_data_exists()) && $this->request->variable('action', '') === 'add';
	}

	/**
	 * Check some settings before submitting data
	 *
	 * @param \skouat\ppde\entity\main $entity            The entity object
	 * @param string                   $field_name        Name of the entity function to call
	 * @param string|int               $value_cmp         Default value to compare with the call_user_func() return
	 *                                                    value
	 * @param bool                     $submit_or_preview Form submit or preview status
	 *
	 * @return array $errors
	 * @access protected
	 */
	protected function is_empty_data($entity, $field_name, $value_cmp, $submit_or_preview = false)
	{
		$errors = array();

		if (call_user_func(array($entity, 'get_' . $field_name)) == $value_cmp && $submit_or_preview)
		{
			$errors[] = $this->user->lang[$this->lang_key_prefix . '_EMPTY_' . strtoupper($field_name)];
		}

		return $errors;
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
		return (!check_form_key($form_name) && $submit_or_preview) ? array($this->user->lang['FORM_INVALID']) : array();
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
		return (bool) $submit || $preview;
	}

	/**
	 * Show user a result message if AJAX was used
	 *
	 * @param string $message Text message to show to the user
	 *
	 * @return null
	 * @access protected
	 */
	protected function ajax_delete_result_message($message = '')
	{
		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response;
			$json_response->send(array(
				'MESSAGE_TITLE' => $this->user->lang['INFORMATION'],
				'MESSAGE_TEXT'  => $message,
				'REFRESH_DATA'  => array(
					'time' => 3
				)
			));
		}
	}

	/**
	 * Return the entity ContainerInterface used by the ACP module in use
	 *
	 * @return object
	 * @access protected
	 */
	protected function get_container_entity()
	{
		return $this->container->get('skouat.ppde.entity.' . $this->module_name);
	}

	/**
	 * Set u_action output vars for display in the template
	 *
	 * @return null
	 * @access protected
	 */
	protected function u_action_assign_template_vars()
	{
		$this->template->assign_vars(array(
			'U_ACTION' => $this->u_action,
		));
	}
}
