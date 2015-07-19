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

class admin_settings_controller implements admin_settings_interface
{
	protected $u_action;

	protected $config;
	protected $container;
	protected $ppde_operator_currency;
	protected $request;
	protected $template;
	protected $user;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config            $config                 Config object
	 * @param ContainerInterface              $container              Service container interface
	 * @param \skouat\ppde\operators\currency $ppde_operator_currency Operator object
	 * @param \phpbb\request\request          $request                Request object
	 * @param \phpbb\template\template        $template               Template object
	 * @param \phpbb\user                     $user                   User object
	 *
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, ContainerInterface $container, \skouat\ppde\operators\currency $ppde_operator_currency, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->config = $config;
		$this->container = $container;
		$this->ppde_operator_currency = $ppde_operator_currency;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
	}

	/**
	 * Display the general settings a user can configure for this extension
	 *
	 * @return null
	 * @access public
	 */
	public function display_settings()
	{
		// Define the name of the form for use as a form key
		add_form_key('ppde_settings');

		// Create an array to collect errors that will be output to the user
		$errors = array();
		// Is the form being submitted to us?
		if ($this->request->is_set_post('submit'))
		{
			// Test if the submitted form is valid
			if (!check_form_key('ppde_settings'))
			{
				$errors[] = $this->user->lang('FORM_INVALID');
			}

			// If no errors, process the form data
			if (empty($errors))
			{
				// Set the options the user configured
				$this->set_settings();

				// Add option settings change action to the admin log
				$phpbb_log = $this->container->get('log');
				$phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_SETTINGS_UPDATED');

				// Option settings have been updated and logged
				// Confirm this to the user and provide link back to previous page
				trigger_error($this->user->lang('PPDE_SETTINGS_SAVED') . adm_back_link($this->u_action));
			}
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ERROR'                       => $this->check_config((sizeof($errors))),
			'ERROR_MSG'                     => (sizeof($errors)) ? implode('<br />', $errors) : '',

			'U_ACTION'                      => $this->u_action,

			// Global Settings vars
			'PPDE_ACCOUNT_ID'               => $this->check_config($this->config['ppde_account_id'], 'string', ''),
			'PPDE_DEFAULT_CURRENCY'         => $this->build_currency_select_menu($this->config['ppde_default_currency']),
			'PPDE_DEFAULT_VALUE'            => $this->check_config($this->config['ppde_default_value'], 'integer', 0),
			'PPDE_DROPBOX_VALUE'            => $this->check_config($this->config['ppde_dropbox_value'], 'string', '1,2,3,4,5,10,20,25,50,100'),

			'S_PPDE_DROPBOX_ENABLE'         => $this->check_config($this->config['ppde_dropbox_enable']),
			'S_PPDE_ENABLE'                 => $this->check_config($this->config['ppde_enable']),
			'S_PPDE_HEADER_LINK'            => $this->check_config($this->config['ppde_header_link']),

			// Sandbox Settings vars
			'PPDE_SANDBOX_ADDRESS'          => $this->check_config($this->config['ppde_sandbox_address'], 'string', ''),

			'S_PPDE_SANDBOX_ENABLE'         => $this->check_config($this->config['ppde_sandbox_enable']),
			'S_PPDE_SANDBOX_FOUNDER_ENABLE' => $this->check_config($this->config['ppde_sandbox_founder_enable']),

			// Statistics Settings vars
			'PPDE_RAISED'                   => $this->check_config($this->config['ppde_raised'], 'float', 0),
			'PPDE_GOAL'                     => $this->check_config($this->config['ppde_goal'], 'float', 0),
			'PPDE_USED'                     => $this->check_config($this->config['ppde_used'], 'float', 0),

			'S_PPDE_STATS_INDEX_ENABLE'     => $this->check_config($this->config['ppde_stats_index_enable']),
			'S_PPDE_RAISED_ENABLE'          => $this->check_config($this->config['ppde_raised_enable']),
			'S_PPDE_GOAL_ENABLE'            => $this->check_config($this->config['ppde_goal_enable']),
			'S_PPDE_USED_ENABLE'            => $this->check_config($this->config['ppde_used_enable']),
		));
	}

	/**
	 * Set the options a user can configure
	 *
	 * @return null
	 * @access protected
	 */
	protected function set_settings()
	{
		// Set options for Global settings
		$this->config->set('ppde_enable', $this->request->variable('ppde_enable', false));
		$this->config->set('ppde_header_link', $this->request->variable('ppde_header_link', false));
		$this->config->set('ppde_account_id', $this->request->variable('ppde_account_id', ''));
		$this->config->set('ppde_default_currency', $this->request->variable('ppde_default_currency', 0));
		$this->config->set('ppde_default_value', $this->request->variable('ppde_default_value', 0));
		$this->config->set('ppde_dropbox_enable', $this->request->variable('ppde_dropbox_enable', false));
		$this->config->set('ppde_dropbox_value', $this->clean_items_list($this->request->variable('ppde_dropbox_value', '1,2,3,4,5,10,20,25,50,100')));

		// Set options for Sandbox Settings
		$this->config->set('ppde_sandbox_enable', $this->request->variable('ppde_sandbox_enable', false));
		$this->config->set('ppde_sandbox_founder_enable', $this->request->variable('ppde_sandbox_founder_enable', false));
		$this->config->set('ppde_sandbox_address', $this->request->variable('ppde_sandbox_address', ''));

		// Set options for Statistics Settings
		$this->config->set('ppde_stats_index_enable', $this->request->variable('ppde_stats_index_enable', false));
		$this->config->set('ppde_raised_enable', $this->request->variable('ppde_raised_enable', false));
		$this->config->set('ppde_raised', $this->request->variable('ppde_raised', 0.0));
		$this->config->set('ppde_goal_enable', $this->request->variable('ppde_goal_enable', false));
		$this->config->set('ppde_goal', $this->request->variable('ppde_goal', 0.0));
		$this->config->set('ppde_used_enable', $this->request->variable('ppde_used_enable', false));
		$this->config->set('ppde_used', $this->request->variable('ppde_used', 0.0));
	}

	/**
	 * Clean items list to conserve only numeric values
	 *
	 * @param string $config_value
	 *
	 * @return string
	 * @access protected
	 */
	protected function clean_items_list($config_value)
	{
		$items_list = explode(',', $config_value);
		$merge_items = array();

		foreach ($items_list as $item)
		{
			if (settype($item, 'integer') && $item != 0)
			{
				$merge_items[] = $item;
			}
		}
		unset($items_list, $item);

		natsort($merge_items);

		return $this->check_config(implode(',', $merge_items), 'string', '');
	}

	/**
	 * Check if a config value is true
	 *
	 * @param mixed  $config Config value
	 * @param string $type   (see settype())
	 * @param mixed  $default
	 *
	 * @return mixed
	 * @access private
	 */
	private function check_config($config, $type = 'boolean', $default = '')
	{
		// We're using settype to enforce data types
		settype($config, $type);
		settype($default, $type);

		return $config ? $config : $default;
	}

	/**
	 * Build pull down menu options of available currency
	 *
	 * @param int $config_value Currency identifier; default: 0
	 *
	 * @return null
	 * @access protected
	 */
	protected function build_currency_select_menu($config_value = 0)
	{
		// Grab the list of currency data
		$currency_items = $this->ppde_operator_currency->get_currency_data();

		// Process each rule menu item for pull-down
		foreach ($currency_items as $currency_item)
		{
			// Set output block vars for display in the template
			$this->template->assign_block_vars('options', array(
				'CURRENCY_ID'        => (int) $currency_item['currency_id'],
				'CURRENCY_NAME'      => $currency_item['currency_name'],

				'S_CURRENCY_DEFAULT' => $this->check_config(($currency_item['currency_id'] == $config_value)),
			));
		}
		unset ($currency_items, $currency_item);
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
