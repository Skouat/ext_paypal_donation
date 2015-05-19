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
	protected $u_action;

	protected $container;
	protected $ppde_operator_currency;
	protected $request;
	protected $template;
	protected $user;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface              $container              Service container interface
	 * @param \skouat\ppde\operators\currency $ppde_operator_currency Operator object
	 * @param \phpbb\request\request          $request                Request object
	 * @param \phpbb\template\template        $template               Template object
	 * @param \phpbb\user                     $user                   User object
	 *
	 * @access public
	 */
	public function __construct(ContainerInterface $container, \skouat\ppde\operators\currency $ppde_operator_currency, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->container = $container;
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
			// Do not treat the item whether language identifier does not match
			$this->template->assign_block_vars('currency', array(
				'CURRENCY_NAME'    => $entity['currency_name'],
				'CURRENCY_ENABLED' => $entity['currency_enable'] ? true : false,

				'U_ENABLE'         => $this->u_action . '&amp;action=enable&amp;currency_id=' . $entity['currency_id'],
				'U_DISABLE'        => $this->u_action . '&amp;action=disable&amp;currency_id=' . $entity['currency_id'],
				'U_MOVE_DOWN'      => $this->u_action . '&amp;action=move_down&amp;currency_id=' . $entity['currency_id'],
				'U_MOVE_UP'        => $this->u_action . '&amp;action=move_up&amp;currency_id=' . $entity['currency_id'],
				'U_EDIT'           => $this->u_action . '&amp;action=edit&amp;currency_id=' . $entity['currency_id'],
				'U_DELETE'         => $this->u_action . '&amp;action=delete&amp;currency_id=' . $entity['currency_id'],
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

		// Initiate a page donation entity
		$entity = $this->container->get('skouat.ppde.entity.currency');

		// Collect the form data
		$data = array(
			'currency_name'     => $this->request->variable('currency_name', '', true),
			'currency_iso_code' => $this->request->variable('currency_iso_code', '', true),
			'currency_symbol'   => $this->request->variable('currency_symbol', '', true),
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
		$submit = $this->request->is_set_post('submit');

		// Create an array to collect errors that will be output to the user
		$errors = array();

		// Grab the form's data fields
		$item_fields = array(
			'name'            => $data['currency_name'],
			'iso_code'        => $data['currency_iso_code'],
			'symbol'          => $data['currency_symbol'],
			'currency_enable' => $data['currency_enable'],
		);

		// Set the currency's data in the entity
		foreach ($item_fields as $entity_function => $currency_data)
		{
			// Calling the set_$entity_function on the entity and passing it $currency_data
			call_user_func_array(array($entity, 'set_' . $entity_function), array($currency_data));
		}
		unset($item_fields, $entity_function, $currency_data);

		// If the form has been submitted or previewed
		if ($submit)
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

		// Insert or update currency
		if ($submit && empty($errors))
		{
			if ($entity->currency_exists() && $this->request->variable('action', '') === 'add')
			{
				// Show user warning for an already exist page and provide link back to the edit page
				$message = $this->user->lang('PPDE_CURRENCY_EXISTS');
				$message .= '<br /><br />';
				$message .= $this->user->lang('PPDE_DC_GO_TO_PAGE', '<a href="' . $this->u_action . '&amp;action=edit&amp;currency_id=' . $entity->get_id() . '">&raquo; ', '</a>');
				trigger_error($message . adm_back_link($this->u_action), E_USER_WARNING);
			}

			if ($entity->get_id())
			{
				// Save the edited item entity to the database
				$entity->save();

				// Show user confirmation of the saved item and provide link back to the previous page
				trigger_error($this->user->lang('PPDE_DC_UPDATED') . adm_back_link($this->u_action));
			}
			else
			{
				// Add a new item entity to the database
				$this->ppde_operator_currency->add_currency_data($entity);

				// Show user confirmation of the added item and provide link back to the previous page
				trigger_error($this->user->lang('PPDE_DC_ADDED') . adm_back_link($this->u_action));
			}
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ERROR'           => (sizeof($errors)) ? true : false,
			'ERROR_MSG'         => (sizeof($errors)) ? implode('<br />', $errors) : '',

			'CURRENCY_NAME'     => $entity->get_name(),
			'CURRENCY_ISO_CODE' => $entity->get_iso_code(),
			'CURRENCY_SYMBOL'   => $entity->get_symbol(),
			'CURRENCY_ENABLE'   => $entity->get_currency_enable(),

			'S_HIDDEN_FIELDS'   => '<input type="hidden" name="currency_id" value="' . $entity->get_id() . '" />',
		));
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
		$entity = $this->container->get('skouat.ppde.entity.currency')->load($currency_id);

		// Collect the form data
		$data = array(
			'currency_id'       => $entity->get_id(),
			'currency_name'     => $this->request->variable('currency_name', $entity->get_name(), true),
			'currency_iso_code' => $this->request->variable('currency_iso_code', $entity->get_iso_code(), true),
			'currency_symbol'   => $this->request->variable('currency_symbol', $entity->get_symbol(), true),
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
		// Initiate an entity
		$entity = $this->container->get('skouat.ppde.entity.currency')->load($currency_id);
		$current_order = $entity->get_currency_order();

		if ($current_order == 0 && $direction == 'move_up')
		{
			return;
		}

		// on move_down, switch position with next order_id...
		// on move_up, switch position with previous order_id...
		$switch_order_id = ($direction == 'move_down') ? $current_order + 1 : $current_order - 1;

		$move_executed = $this->ppde_operator_currency->move($switch_order_id, $current_order, $entity->get_id());

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
		// Return error if no currency
		if (!$currency_id)
		{
			trigger_error($this->user->lang('PPDE_NO_CURRENCY') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Load selected currency
		$entity = $this->container->get('skouat.ppde.entity.currency')->load($currency_id);

		// Set the new status for this currency
		$entity->set_currency_enable(($action == 'enable') ? 1 : 0);

		// Save data to the database
		$entity->save();
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
		// Use a confirmation box routine when deleting a currency
		if (confirm_box(true))
		{
			// Delete the currency on confirmation
			$this->ppde_operator_currency->delete_currency_data($currency_id);

			// Show user confirmation of the deleted currency and provide link back to the previous page
			trigger_error($this->user->lang('PPDE_DC_DELETED') . adm_back_link($this->u_action));
		}
		else
		{
			// Request confirmation from the user to delete the currency
			confirm_box(false, $this->user->lang('PPDE_DC_CONFIRM_DELETE'), build_hidden_fields(array(
				'mode'        => 'currency',
				'action'      => 'delete',
				'currency_id' => $currency_id,
			)));

			// Use a redirect to take the user back to the previous page
			// if the user chose not delete the currency from the confirmation page.
			redirect($this->u_action);
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
