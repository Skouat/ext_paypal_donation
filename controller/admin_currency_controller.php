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

/**
 * @property ContainerInterface       container         Service container interface
 * @property string                   id_prefix_name    Prefix name for identifier in the URL
 * @property string                   lang_key_prefix   Prefix for the messages thrown by exceptions
 * @property \phpbb\log\log           log               The phpBB log system
 * @property string                   module_name       Name of the module currently used
 * @property \phpbb\request\request   request           Request object
 * @property bool                     submit            State of submit $_POST variable
 * @property \phpbb\template\template template          Template object
 * @property string                   u_action          Action URL
 * @property \phpbb\user              user              User object
 */
class admin_currency_controller extends admin_main
{
	protected $ppde_operator;

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
		$this->ppde_operator = $ppde_operator_currency;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		parent::__construct(
			'currency',
			'PPDE_DC',
			'currency'
		);
	}

	/**
	 * Display the currency list
	 *
	 * @return void
	 * @access public
	 */
	public function display_currency()
	{
		// Check if currency_order is valid and fix it if necessary
		$this->ppde_operator->fix_currency_order();

		// Initiate an entity
		/** @type \skouat\ppde\entity\currency $entity */
		$entity = $this->get_container_entity();

		// Grab all the pages from the db
		$data_ary = $entity->get_data($this->ppde_operator->build_sql_data());

		foreach ($data_ary as $data)
		{
			$enable_lang = (!$data['currency_enable']) ? 'ENABLE' : 'DISABLE';
			$enable_value = (!$data['currency_enable']) ? 'activate' : 'deactivate';

			$this->template->assign_block_vars('currency', array(
				'CURRENCY_NAME'    => $data['currency_name'],
				'CURRENCY_ENABLED' => (bool) $data['currency_enable'],

				'U_DELETE'         => $this->u_action . '&amp;action=delete&amp;' . $this->id_prefix_name . '_id=' . $data['currency_id'],
				'U_EDIT'           => $this->u_action . '&amp;action=edit&amp;' . $this->id_prefix_name . '_id=' . $data['currency_id'],
				'U_ENABLE_DISABLE' => $this->u_action . '&amp;action=' . $enable_value . '&amp;' . $this->id_prefix_name . '_id=' . $data['currency_id'],
				'L_ENABLE_DISABLE' => $this->user->lang[$enable_lang],
				'U_MOVE_DOWN'      => $this->u_action . '&amp;action=move_down&amp;' . $this->id_prefix_name . '_id=' . $data['currency_id'] . '&amp;hash=' . generate_link_hash('ppde_move'),
				'U_MOVE_UP'        => $this->u_action . '&amp;action=move_up&amp;' . $this->id_prefix_name . '_id=' . $data['currency_id'] . '&amp;hash=' . generate_link_hash('ppde_move'),
			));
		}
		unset($data_ary, $page);

		$this->u_action_assign_template_vars();
	}

	/**
	 * Add a currency
	 *
	 * @return void
	 * @access public
	 */
	public function add_currency()
	{
		// Add form key
		add_form_key('add_edit_currency');

		// Initiate an entity
		/** @type \skouat\ppde\entity\currency $entity */
		$entity = $this->get_container_entity();

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
	 * @param \skouat\ppde\entity\currency $entity The currency entity object
	 * @param array                        $data   The form data to be processed
	 *
	 * @return void
	 * @access private
	 */
	private function add_edit_currency_data($entity, $data)
	{
		// Get form's POST actions (submit or preview)
		$this->submit = $this->request->is_set_post('submit');

		// Create an array to collect errors that will be output to the user
		$errors = array();

		// Set the currency's data in the entity
		$item_fields = array(
			'name'              => $data['currency_name'],
			'iso_code'          => $data['currency_iso_code'],
			'symbol'            => $data['currency_symbol'],
			'currency_position' => $data['currency_on_left'],
			'currency_enable'   => $data['currency_enable'],
		);

		$this->set_entity_data($entity, $item_fields);

		// Check some settings before submitting data
		$errors = array_merge($errors,
			$this->is_invalid_form('add_edit_' . $this->module_name, $this->submit_or_preview($this->submit)),
			$this->is_empty_data($entity, 'name', '', $this->submit_or_preview($this->submit)),
			$this->is_empty_data($entity, 'iso_code', '', $this->submit_or_preview($this->submit)),
			$this->is_empty_data($entity, 'symbol', '', $this->submit_or_preview($this->submit))
		);

		// Insert or update currency
		$this->submit_data($entity, $errors);

		// Set output vars for display in the template
		$this->s_error_assign_template_vars($errors);
		$this->template->assign_vars(array(
			'CURRENCY_NAME'     => $entity->get_name(),
			'CURRENCY_ISO_CODE' => $entity->get_iso_code(),
			'CURRENCY_SYMBOL'   => $entity->get_symbol(),
			'CURRENCY_POSITION' => $entity->get_currency_position(),
			'CURRENCY_ENABLE'   => $entity->get_currency_enable(),

			'S_HIDDEN_FIELDS'   => '<input type="hidden" name="' . $this->id_prefix_name . '_id" value="' . $entity->get_id() . '" />',
		));
	}

	/**
	 * Submit data to the database
	 *
	 * @param \skouat\ppde\entity\currency $entity The currency entity object
	 * @param array                        $errors
	 *
	 * @return void
	 * @access private
	 */
	private function submit_data(\skouat\ppde\entity\currency $entity, array $errors)
	{
		if (!$entity->get_id())
		{
			$this->trigger_error_data_already_exists($entity);
		}

		if ($this->can_submit_data($errors))
		{
			$log_action = $this->add_edit_data($entity, 'set_order');
			// Log and show user confirmation of the saved item and provide link back to the previous page
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_' . strtoupper($log_action), time(), array($entity->get_name()));
			trigger_error($this->user->lang[$this->lang_key_prefix . '_' . strtoupper($log_action)] . adm_back_link($this->u_action));
		}
	}

	/**
	 * Edit a Currency
	 *
	 * @param int $currency_id Currency Identifier
	 *
	 * @return void
	 * @access   public
	 */
	public function edit_currency($currency_id)
	{
		// Add form key
		add_form_key('add_edit_currency');

		// Initiate an entity
		/** @type \skouat\ppde\entity\currency $entity */
		$entity = $this->get_container_entity();
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
			'U_EDIT_ACTION' => $this->u_action . '&amp;action=edit&amp;' . $this->id_prefix_name . '_id=' . $currency_id,
			'U_BACK'        => $this->u_action,
		));
	}

	/**
	 * Move a currency up/down
	 *
	 * @param int    $currency_id The currency identifier to move
	 * @param string $direction   The direction (up|down)
	 *
	 * @return void
	 * @access   public
	 */
	public function move_currency($currency_id, $direction)
	{
		// Before moving the currency, with check the link hash.
		// If the hash, is invalid we return an error.
		if (!check_link_hash($this->request->variable('hash', ''), 'ppde_move'))
		{
			trigger_error($this->user->lang['PPDE_DC_INVALID_HASH'] . adm_back_link($this->u_action), E_USER_WARNING);
		};

		// Initiate an entity and load data
		/** @type \skouat\ppde\entity\currency $entity */
		$entity = $this->get_container_entity();
		$entity->load($currency_id);
		$current_order = $entity->get_currency_order();

		if ($current_order == 0 && $direction == 'move_up')
		{
			return;
		}

		// on move_down, switch position with next order_id...
		// on move_up, switch position with previous order_id...
		$switch_order_id = ($direction == 'move_down') ? $current_order + 1 : $current_order - 1;

		$move_executed = $this->ppde_operator->move($switch_order_id, $current_order, $entity->get_id());

		// Log action if data was moved
		if ($move_executed)
		{
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_' . strtoupper($direction), time(), array($entity->get_name()));
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
	 * @return void
	 * @access public
	 */
	public function enable_currency($currency_id, $action)
	{
		// Return an error if no currency
		if (!$currency_id)
		{
			trigger_error($this->user->lang[$this->lang_key_prefix . '_NO_CURRENCY'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Return an error if it's the last enabled currency
		if ($this->ppde_operator->last_currency_enabled($action) && ($action == 'deactivate'))
		{
			trigger_error($this->user->lang['PPDE_CANNOT_DISABLE_ALL_CURRENCIES'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Initiate an entity and load data
		/** @type \skouat\ppde\entity\currency $entity */
		$entity = $this->get_container_entity();
		$entity->load($currency_id);

		// Set the new status for this currency
		$entity->set_currency_enable(($action == 'activate') ? true : false);

		// Save data to the database
		$entity->save($entity->check_required_field());
		// Log action
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_' . strtoupper($action) . 'D', time(), array($entity->get_name()));

		if ($this->request->is_ajax() && ($action == 'activate' || $action == 'deactivate'))
		{
			$action_lang = ($action == 'activate') ? 'DISABLE' : 'ENABLE';
			$json_response = new \phpbb\json_response;
			$json_response->send(array(
				'text' => $this->user->lang[$action_lang],
			));
		}
	}

	/**
	 * Delete a currency
	 *
	 * @param int $currency_id
	 *
	 * @return void
	 * @access public
	 */
	public function delete_currency($currency_id)
	{
		// Initiate an entity and load data
		/** @type \skouat\ppde\entity\currency $entity */
		$entity = $this->get_container_entity();
		$entity->load($currency_id);
		$entity->delete($currency_id, 'check_currency_enable');

		// Log the action
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_DELETED', time(), array($entity->get_name()));
		trigger_error($this->user->lang[$this->lang_key_prefix . '_DELETED'] . adm_back_link($this->u_action));
	}
}
