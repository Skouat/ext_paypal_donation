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

use phpbb\config\config;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @property config             config             Config object
 * @property ContainerInterface container          Service container interface
 * @property string             id_prefix_name     Prefix name for identifier in the URL
 * @property string             lang_key_prefix    Prefix for the messages thrown by exceptions
 * @property language           language           Language user object
 * @property log                log                The phpBB log system
 * @property string             module_name        Name of the module currently used
 * @property request            request            Request object
 * @property bool               submit             State of submit $_POST variable
 * @property template           template           Template object
 * @property string             u_action           Action URL
 * @property user               user               User object
 */
class currency_controller extends admin_main
{
	protected $currency_entity;
	protected $currency_operator;
	protected $locale_icu;

	/**
	 * Constructor
	 *
	 * @param config                          $config             Config object.
	 * @param ContainerInterface              $container          Dependency Injection container.
	 * @param language                        $language           Language object.
	 * @param log                             $log                Log object.
	 * @param \skouat\ppde\actions\locale_icu $locale_icu Locale handler.
	 * @param \skouat\ppde\entity\currency    $currency_entity    Currency entity.
	 * @param \skouat\ppde\operators\currency $currency_operator  Currency operator.
	 * @param request                         $request            Request object.
	 * @param template                        $template           Template object.
	 * @param user                            $user               User object.
	 */
	public function __construct(
		config $config,
		ContainerInterface $container,
		language $language,
		log $log,
		\skouat\ppde\actions\locale_icu $locale_icu,
		\skouat\ppde\entity\currency $currency_entity,
		\skouat\ppde\operators\currency $currency_operator,
		request $request,
		template $template,
		user $user
	)
	{
		$this->config = $config;
		$this->container = $container;
		$this->language = $language;
		$this->log = $log;
		$this->locale_icu = $locale_icu;
		$this->currency_entity = $currency_entity;
		$this->currency_operator = $currency_operator;
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
	 * Display the currency management page.
	 */
	public function display(): void
	{
		// Check if currency_order is valid and fix it if necessary
		$this->currency_operator->fix_currency_order();

		// Grab all the currencies from the db
		$data_ary = $this->currency_entity->get_data($this->currency_operator->build_sql_data());

		array_map([$this, 'currency_assign_template_vars'], $data_ary);

		$this->u_action_assign_template_vars();
	}

	/**
	 * Add a new currency.
	 */
	public function add(): void
	{
		// Add form key
		add_form_key('add_edit_currency');

		// Collect the form data
		$data = [
			'currency_name'     => $this->request->variable('currency_name', '', true),
			'currency_iso_code' => $this->request->variable('currency_iso_code', '', true),
			'currency_symbol'   => $this->request->variable('currency_symbol', '', true),
			'currency_on_left'  => $this->request->variable('currency_on_left', true),
			'currency_enable'   => $this->request->variable('currency_enable', false),
		];

		// Process the new page
		$this->add_edit_currency_data($data);

		// Set output vars for display in the template
		$this->add_edit_action_assign_template_vars('add');
	}

	/**
	 * Process currency data for adding or editing.
	 *
	 * @param array $data The currency data to process.
	 */
	private function add_edit_currency_data(array $data): void
	{
		$this->submit = $this->is_form_submitted();

		$this->set_currency_entity_data($data);
		$errors = $this->validate_currency_data();
		$this->submit_data($errors);
		$this->assign_template_vars($errors);
	}

	/**
	 * Set currency entity data.
	 *
	 * @param array $data The currency data to set.
	 */
	private function set_currency_entity_data(array $data): void
	{
		if ($this->locale_icu->is_locale_configured())
		{
			$data['currency_symbol'] = $this->locale_icu->get_currency_symbol($data['currency_iso_code']);
		}

		$item_fields = [
			'name'              => $data['currency_name'],
			'iso_code'          => $data['currency_iso_code'],
			'symbol'            => $data['currency_symbol'],
			'currency_position' => $data['currency_on_left'],
			'currency_enable'   => $data['currency_enable'],
		];

		$this->currency_entity->set_entity_data($item_fields);
	}
	/**
	 * Validate currency data.
	 *
	 * @return array An array of error messages encountered during validation.
	 */
	private function validate_currency_data(): array
	{
		$errors = [];
		return array_merge($errors,
			$this->is_invalid_form('add_edit_' . $this->module_name, $this->submit_or_preview($this->submit)),
			$this->is_empty_data($this->currency_entity, 'name', '', $this->submit_or_preview($this->submit)),
			$this->is_empty_data($this->currency_entity, 'iso_code', '', $this->submit_or_preview($this->submit)),
			$this->is_empty_data($this->currency_entity, 'symbol', '', $this->submit_or_preview($this->submit))
		);
	}

	/**
	 * Assign template variables.
	 *
	 * @param array $errors An array of error messages.
	 */
	private function assign_template_vars(array $errors): void
	{
		$this->s_error_assign_template_vars($errors);
		$this->template->assign_vars([
			'CURRENCY_NAME'     => $this->currency_entity->get_name(),
			'CURRENCY_ISO_CODE' => $this->currency_entity->get_iso_code(),
			'CURRENCY_SYMBOL'   => $this->currency_entity->get_symbol(),
			'CURRENCY_POSITION' => $this->currency_entity->get_currency_position(),
			'CURRENCY_ENABLE'   => $this->currency_entity->get_currency_enable(),

			'S_HIDDEN_FIELDS'          => '<input type="hidden" name="' . $this->id_prefix_name . '_id" value="' . $this->currency_entity->get_id() . '">',
			'S_PPDE_LOCALE_AVAILABLE'  => $this->locale_icu->icu_requirements(),
			'S_PPDE_LOCALE_CONFIGURED' => $this->locale_icu->is_locale_configured(),
		]);
	}

	/**
	 * Submit currency data.
	 *
	 * @param array $errors An array of error messages.
	 */
	private function submit_data(array $errors): void
	{
		if (!$this->currency_entity->get_id())
		{
			$this->trigger_error_data_already_exists($this->currency_entity);
		}

		if (!$this->can_submit_data($errors))
		{
			return;
		}

		$log_action = $this->currency_entity->add_edit_data('set_order');
		// Log and show user confirmation of the saved item and provide link back to the previous page
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_' . strtoupper($log_action), time(), [$this->currency_entity->get_name()]);
		trigger_error($this->language->lang($this->lang_key_prefix . '_' . strtoupper($log_action)) . adm_back_link($this->u_action));
	}

	/**
	 * Edit an existing currency.
	 */
	public function edit(): void
	{
		$currency_id = (int) $this->args[$this->id_prefix_name . '_id'];
		// Add form key
		add_form_key('add_edit_currency');

		$this->currency_entity->set_page_url($this->u_action);
		$this->currency_entity->load($currency_id);

		// Collect the form data
		$data = [
			'currency_id'       => $this->currency_entity->get_id(),
			'currency_name'     => $this->request->variable('currency_name', $this->currency_entity->get_name(), true),
			'currency_iso_code' => $this->request->variable('currency_iso_code', $this->currency_entity->get_iso_code(), true),
			'currency_symbol'   => $this->request->variable('currency_symbol', $this->currency_entity->get_symbol(), true),
			'currency_on_left'  => $this->request->variable('currency_on_left', $this->currency_entity->get_currency_position()),
			'currency_enable'   => $this->request->variable('currency_enable', $this->currency_entity->get_currency_enable()),
		];

		// Process the new page
		$this->add_edit_currency_data($data);

		// Set output vars for display in the template
		$this->add_edit_action_assign_template_vars('edit', $currency_id);
	}

	/**
	 * Move a currency's position in the list.
	 */
	public function move(): void
	{
		$direction = $this->args['action'];

		// Before moving the currency, with check the link hash.
		// If the hash, is invalid we return an error.
		if (!check_link_hash($this->request->variable('hash', ''), 'ppde_move'))
		{
			trigger_error($this->language->lang('PPDE_DC_INVALID_HASH') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Load data
		$this->currency_entity->load($this->args[$this->id_prefix_name . '_id']);
		$current_order = $this->currency_entity->get_currency_order();

		if (($current_order === 0) && ($direction === 'move_up'))
		{
			return;
		}

		// on move_down, switch position with next order_id...
		// on move_up, switch position with previous order_id...
		$switch_order_id = ($direction === 'move_down') ? $current_order + 1 : $current_order - 1;

		$move_executed = $this->currency_operator->move($switch_order_id, $current_order, $this->currency_entity->get_id());

		// Log action if data was moved
		if ($move_executed)
		{
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_' . strtoupper($direction), time(), [$this->currency_entity->get_name()]);
		}

		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response;
			$json_response->send(['success' => $move_executed]);
		}
	}

	/**
	 * Enable or disable a currency.
	 */
	public function enable(): void
	{
		$action = $this->args['action'];
		$currency_id = (int) $this->args[$this->id_prefix_name . '_id'];

		// Return an error if no currency
		if (!$currency_id)
		{
			trigger_error($this->language->lang($this->lang_key_prefix . '_NO_CURRENCY') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Return an error if it's the default currency
		if (((int) $this->config['ppde_default_currency'] === $currency_id) && ($action === 'deactivate'))
		{
			trigger_error($this->language->lang('PPDE_CANNOT_DISABLE_DEFAULT_CURRENCY') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Load data
		$this->currency_entity->load($currency_id);

		// Set the new status for this currency
		$this->currency_entity->set_currency_enable($action === 'activate');

		// Save data to the database
		$this->currency_entity->save($this->currency_entity->check_required_field());
		// Log action
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_' . strtoupper($action) . 'D', time(), [$this->currency_entity->get_name()]);

		if ((($action === 'activate') || ($action === 'deactivate')) && $this->request->is_ajax())
		{
			$action_lang = ($action === 'activate') ? 'DISABLE' : 'ENABLE';
			$json_response = new \phpbb\json_response;
			$json_response->send(['text' => $this->language->lang($action_lang)]);
		}
	}

	/**
	 * Delete a currency.
	 */
	public function delete(): void
	{
		$currency_id = (int) $this->args[$this->id_prefix_name . '_id'];

		// Load data
		$this->currency_entity->load($currency_id);
		$this->currency_entity->delete($currency_id, 'check_currency_enable');

		// Log the action
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_DELETED', time(), [$this->currency_entity->get_name()]);
		trigger_error($this->language->lang($this->lang_key_prefix . '_DELETED') . adm_back_link($this->u_action));
	}

	/**
	 * Assign template variables for currency data.
	 *
	 * @param array $data Currency data to assign.
	 */
	protected function currency_assign_template_vars(array $data): void
	{
		if (!$data['currency_enable'])
		{
			$enable_lang = 'ENABLE';
			$enable_value = 'activate';
		}
		else
		{
			$enable_lang = 'DISABLE';
			$enable_value = 'deactivate';
		}

		$this->template->assign_block_vars('currency', [
			'CURRENCY_NAME'    => $data['currency_name'],
			'CURRENCY_ENABLED' => (bool) $data['currency_enable'],
			'L_ENABLE_DISABLE' => $this->language->lang($enable_lang),
			'S_DEFAULT'        => (int) $data['currency_id'] === (int) $this->config['ppde_default_currency'],
			'U_DELETE'         => $this->u_action . '&amp;action=delete&amp;' . $this->id_prefix_name . '_id=' . $data['currency_id'],
			'U_EDIT'           => $this->u_action . '&amp;action=edit&amp;' . $this->id_prefix_name . '_id=' . $data['currency_id'],
			'U_ENABLE_DISABLE' => $this->u_action . '&amp;action=' . $enable_value . '&amp;' . $this->id_prefix_name . '_id=' . $data['currency_id'],
			'U_MOVE_DOWN'      => $this->u_action . '&amp;action=move_down&amp;' . $this->id_prefix_name . '_id=' . $data['currency_id'] . '&amp;hash=' . generate_link_hash('ppde_move'),
			'U_MOVE_UP'        => $this->u_action . '&amp;action=move_up&amp;' . $this->id_prefix_name . '_id=' . $data['currency_id'] . '&amp;hash=' . generate_link_hash('ppde_move'),
		]);
	}
}
