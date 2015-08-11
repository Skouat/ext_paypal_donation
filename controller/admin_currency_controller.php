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

use Symfony\Component\DependencyInjection\ContainerInterface;

class admin_currency_controller implements admin_currency_interface
{
	protected $container;
	protected $log;
	protected $ppde_operator_currency;
	protected $request;
	protected $template;
	protected $user;

	protected $u_action;
	protected $submit;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface              $container              Service container interface
	 * @param \phpbb\log\log                  $log                    The phpBB log system
	 * @param \skouat\ppde\operators\currency $ppde_operator_currency Operator object
	 * @param \phpbb\request\request          $request                Request object
	 * @param \phpbb\template\template        $template               Template object
	 * @param \phpbb\user                     $user                   User object
	 *
	 * @access public
	 */
	public function __construct(ContainerInterface $container, \phpbb\log\log $log, \skouat\ppde\operators\currency $ppde_operator_currency, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->container = $container;
		$this->log = $log;
		$this->ppde_operator_currency = $ppde_operator_currency;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
	}

	/**
	 * Display the currency list
	 *
	 * @return null
	 * @access public
	 */
	public function display_currency()
	{
		// Check that currency_order is valid and fix it if necessary
		$this->ppde_operator_currency->fix_currency_order();

		// Grab all the pages from the db
		$entities = $this->ppde_operator_currency->get_currency_data();

		foreach ($entities as $entity)
		{
			$enable_lang = (!$entity['currency_enable']) ? 'ENABLE' : 'DISABLE';
			$enable_value = (!$entity['currency_enable']) ? 'enable' : 'disable';

			$this->template->assign_block_vars('currency', array(
				'CURRENCY_NAME'    => $entity['currency_name'],
				'CURRENCY_ENABLED' => $entity['currency_enable'] ? true : false,

				'U_DELETE'         => $this->u_action . '&amp;action=delete&amp;currency_id=' . $entity['currency_id'],
				'U_EDIT'           => $this->u_action . '&amp;action=edit&amp;currency_id=' . $entity['currency_id'],
				'U_ENABLE_DISABLE' => $this->u_action . '&amp;action=' . $enable_value . '&amp;currency_id=' . $entity['currency_id'],
				'L_ENABLE_DISABLE' => $this->user->lang[$enable_lang],
				'U_MOVE_DOWN'      => $this->u_action . '&amp;action=move_down&amp;currency_id=' . $entity['currency_id'],
				'U_MOVE_UP'        => $this->u_action . '&amp;action=move_up&amp;currency_id=' . $entity['currency_id'],
			));
		}

		unset($entities, $page);

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'U_ACTION' => $this->u_action,
		));
	}

	/**
	 * Add a currency
	 *
	 * @return null
	 * @access public
	 */
	public function add_currency()
	{
		// Add form key
		add_form_key('add_edit_currency');

		// Initiate an entity
		$entity = $this->container->get('skouat.ppde.entity.currency');

		// Collect the form data
		$data = array(
			'currency_name'     => $this->request->variable('currency_name', '', true),
			'currency_iso_code' => $this->request->variable('currency_iso_code', '', true),
			'currency_symbol'   => $this->request->variable('currency_symbol', '', true),
			'currency_on_left'  => $this->request->variable('currency_on_left', true),
			'currency_enable'   => $this->request->variable('currency_enable', false),
		);

		// Process the new page
		$this->add_edit_currency_data($entity, $data);

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ADD'        => true,

			'U_ADD_ACTION' => $this->u_action . '&amp;action=add',
			'U_BACK'       => $this->u_action,
		));
	}

	/**
	 * Process currency data to be added or edited
	 *
	 * @param object $entity The currency entity object
	 * @param array  $data   The form data to be processed
	 *
	 * @return null
	 * @access protected
	 */
	protected function add_edit_currency_data($entity, $data)
	{
		// Get form's POST actions (submit or preview)
		$this->submit = $this->request->is_set_post('submit');

		// Create an array to collect errors that will be output to the user
		$errors = array();

		// Grab the form's data fields
		$item_fields = array(
			'name'              => $data['currency_name'],
			'iso_code'          => $data['currency_iso_code'],
			'symbol'            => $data['currency_symbol'],
			'currency_position' => $data['currency_on_left'],
			'currency_enable'   => $data['currency_enable'],
		);

		// Set the currency's data in the entity
		foreach ($item_fields as $entity_function => $currency_data)
		{
			try
			{
				// Calling the set_$entity_function on the entity and passing it $currency_data
				call_user_func_array(array($entity, 'set_' . $entity_function), array($currency_data));
			}
			catch (\skouat\ppde\exception\base $e)
			{
				// Catch exceptions and add them to errors array
				$errors[] = $e->get_message($this->user);
			}
		}
		unset($item_fields, $entity_function, $currency_data);

		// If the form has been submitted
		$errors = $this->check_submit($entity);

		// Insert or update currency
		$this->submit_data($entity, $errors);

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ERROR'           => (sizeof($errors)) ? true : false,
			'ERROR_MSG'         => (sizeof($errors)) ? implode('<br />', $errors) : '',

			'CURRENCY_NAME'     => $entity->get_name(),
			'CURRENCY_ISO_CODE' => $entity->get_iso_code(),
			'CURRENCY_SYMBOL'   => $entity->get_symbol(),
			'CURRENCY_POSITION' => $entity->get_currency_position(),
			'CURRENCY_ENABLE'   => $entity->get_currency_enable(),

			'S_HIDDEN_FIELDS'   => '<input type="hidden" name="currency_id" value="' . $entity->get_id() . '" />',
		));
	}

	/**
	 * Check some settings before submitting data
	 *
	 * @param object $entity The currency entity object
	 *
	 * @return array $errors
	 * @access protected
	 */
	protected function check_submit($entity)
	{
		$errors = array();

		if ($this->submit)
		{
			// Test if the form is valid
			if (!check_form_key('add_edit_currency'))
			{
				$errors[] = $this->user->lang('FORM_INVALID');
			}

			// Do not allow an empty currency name
			if ($entity->get_name() == '')
			{
				$errors[] = $this->user->lang('PPDE_DC_EMPTY_NAME');
			}

			// Do not allow an empty ISO code
			if ($entity->get_iso_code() == '')
			{
				$errors[] = $this->user->lang('PPDE_DC_EMPTY_ISO_CODE');
			}

			// Do not allow an empty symbol
			if ($entity->get_symbol() == '')
			{
				$errors[] = $this->user->lang('PPDE_DC_EMPTY_SYMBOL');
			}
		}

		return $errors;
	}

	/**
	 * Submit data to the database
	 *
	 * @param object $entity The currency entity object
	 * @param array  $errors
	 *
	 * @return null
	 * @access protected
	 */
	protected function submit_data($entity, array $errors)
	{
		if ($this->submit && empty($errors))
		{
			if ($entity->currency_exists() && $this->request->variable('action', '') === 'add')
			{
				// Show user warning for an already exist currency and provide link back to the edit page
				$message = $this->user->lang('PPDE_CURRENCY_EXISTS');
				$message .= '<br /><br />';
				$message .= $this->user->lang('PPDE_DC_GO_TO_PAGE', '<a href="' . $this->u_action . '&amp;action=edit&amp;currency_id=' . $entity->get_id() . '">&raquo; ', '</a>');
				trigger_error($message . adm_back_link($this->u_action), E_USER_WARNING);
			}

			if ($entity->get_id())
			{
				// Save the edited item entity to the database
				$entity->save();
				$log_action = 'UPDATED';
			}
			else
			{
				// Add a new item entity to the database
				$this->ppde_operator_currency->add_data($entity);
				$log_action = 'ADDED';
			}
			// Log and show user confirmation of the saved item and provide link back to the previous page
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_DC_' . strtoupper($log_action), time(), array($entity->get_name()));
			trigger_error($this->user->lang('PPDE_DC_' . strtoupper($log_action)) . adm_back_link($this->u_action));
		}
	}

	/**
	 * Edit a Currency
	 *
	 * @param int $currency_id Currency Identifier
	 *
	 * @return null
	 * @access   public
	 */
	public function edit_currency($currency_id)
	{
		// Add form key
		add_form_key('add_edit_currency');

		// Initiate an entity
		$entity = $this->container->get('skouat.ppde.entity.currency');
		$entity->set_page_url($this->u_action);
		$entity->load($currency_id);

		// Collect the form data
		$data = array(
			'currency_id'       => $entity->get_id(),
			'currency_name'     => $this->request->variable('currency_name', $entity->get_name(), true),
			'currency_iso_code' => $this->request->variable('currency_iso_code', $entity->get_iso_code(), true),
			'currency_symbol'   => $this->request->variable('currency_symbol', $entity->get_symbol(), true),
			'currency_on_left'  => $this->request->variable('currency_on_left', $entity->get_currency_position()),
			'currency_enable'   => $this->request->variable('currency_enable', $entity->get_currency_enable()),
		);

		// Process the new page
		$this->add_edit_currency_data($entity, $data);

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_EDIT'        => true,

			'U_EDIT_ACTION' => $this->u_action . '&amp;action=edit&amp;currency_id=' . $currency_id,
			'U_BACK'        => $this->u_action,
		));
	}

	/**
	 * Move a currency up/down
	 *
	 * @param int    $currency_id The currency identifier to move
	 * @param string $direction   The direction (up|down)
	 *
	 * @return null
	 * @access   public
	 */
	public function move_currency($currency_id, $direction)
	{
		// Initiate an entity and load data
		$entity = $this->container->get('skouat.ppde.entity.currency');
		$entity->load($currency_id);
		$current_order = $entity->get_currency_order();

		if ($current_order == 0 && $direction == 'move_up')
		{
			return;
		}

		// on move_down, switch position with next order_id...
		// on move_up, switch position with previous order_id...
		$switch_order_id = ($direction == 'move_down') ? $current_order + 1 : $current_order - 1;

		$move_executed = $this->ppde_operator_currency->move($switch_order_id, $current_order, $entity->get_id());

		// Log action if data was moved
		if ($move_executed)
		{
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_DC_' . strtoupper($direction), time(), array(strtolower($this->user->lang('LOG_CURRENCY')), $entity->get_name()));
		}

		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response;
			$json_response->send(array(
				'success' => $move_executed,
			));
		}
	}

	/**
	 * Enable/disable a currency
	 *
	 * @param int    $currency_id
	 * @param string $action
	 *
	 * @return null
	 * @access public
	 */
	public function enable_currency($currency_id, $action)
	{
		// Return an error if no currency
		if (!$currency_id)
		{
			trigger_error($this->user->lang('PPDE_NO_CURRENCY') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Return an error if it's the last enabled currency
		if ($this->ppde_operator_currency->last_currency_enabled($action) && ($action == 'disable'))
		{
			trigger_error($this->user->lang('PPDE_CANNOT_DISABLE_ALL_CURRENCIES') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Load selected currency
		$entity = $this->container->get('skouat.ppde.entity.currency');
		$entity->load($currency_id);

		// Set the new status for this currency
		$entity->set_currency_enable(($action == 'enable') ? 1 : 0);

		// Save data to the database
		$entity->save();
		// Log action
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_DC_' . strtoupper($action) . 'D', time(), array($entity->get_name()));

		if ($this->request->is_ajax() && ($action == 'enable' || $action == 'disable'))
		{
			$json_response = new \phpbb\json_response;
			$json_response->send(array(
				'text'	=> $this->user->lang[($action == 'enable') ? 'DISABLE' : 'ENABLE'],
			));
		}
	}

	/**
	 * Delete a currency
	 *
	 * @param int $currency_id
	 *
	 * @return null
	 * @access public
	 */
	public function delete_currency($currency_id)
	{
		// Initiate an entity and load data
		$entity = $this->container->get('skouat.ppde.entity.currency');
		$entity->load($currency_id);

		/** @type bool $data_disabled */
		$data_disabled = $this->ppde_operator_currency->delete_currency_data($currency_id);

		if (!$data_disabled)
		{
			// Return an error if the currency is enabled
			trigger_error($this->user->lang('PPDE_DISABLE_BEFORE_DELETION') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Log the action
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_DC_DELETED', time(), array($entity->get_name()));

		// If AJAX was used, show user a result message
		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response;
			$json_response->send(array(
				'MESSAGE_TITLE' => $this->user->lang['INFORMATION'],
				'MESSAGE_TEXT'  => $this->user->lang('PPDE_DC_DELETED'),
				'REFRESH_DATA'  => array(
					'time' => 3
				)
			));
		}
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
}
