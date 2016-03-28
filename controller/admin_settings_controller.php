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
 * @property ContainerInterface       container          The phpBB log system
 * @property string                   lang_key_prefix    Prefix for the messages thrown by exceptions
 * @property \phpbb\request\request   request            Request object
 * @property bool                     submit             State of submit $_POST variable
 * @property \phpbb\template\template template           Template object
 * @property string                   u_action           Action URL
 * @property \phpbb\user              user               User object
 */
class admin_settings_controller extends admin_main
{
	protected $config;
	protected $ppde_controller_main;
	protected $ppde_operator_currency;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config                    $config                 Config object
	 * @param ContainerInterface                      $container              Service container interface
	 * @param \skouat\ppde\controller\main_controller $ppde_controller_main   Main controller object
	 * @param \skouat\ppde\operators\currency         $ppde_operator_currency Operator object
	 * @param \phpbb\request\request                  $request                Request object
	 * @param \phpbb\template\template                $template               Template object
	 * @param \phpbb\user                             $user                   User object
	 *
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, ContainerInterface $container, \skouat\ppde\controller\main_controller $ppde_controller_main, \skouat\ppde\operators\currency $ppde_operator_currency, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->config = $config;
		$this->container = $container;
		$this->ppde_controller_main = $ppde_controller_main;
		$this->ppde_operator_currency = $ppde_operator_currency;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->lang_key_prefix = 'PPDE_SETTINGS';
	}

	/**
	 * Display the general settings a user can configure for this extension
	 *
	 * @return null
	 * @access public
	 */
	public function display_settings()
	{
		$this->ppde_controller_main->first_start();

		// Define the name of the form for use as a form key
		add_form_key('ppde_settings');

		// Create an array to collect errors that will be output to the user
		$errors = array();

		$this->submit_settings();

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ERROR'                        => $this->check_config((sizeof($errors))),
			'ERROR_MSG'                      => (sizeof($errors)) ? implode('<br />', $errors) : '',

			'U_ACTION'                       => $this->u_action,

			// Global Settings vars
			'PPDE_ACCOUNT_ID'                => $this->check_config($this->config['ppde_account_id'], 'string', ''),
			'PPDE_DEFAULT_CURRENCY'          => $this->container->get('skouat.ppde.controller')->build_currency_select_menu($this->config['ppde_default_currency']),
			'PPDE_DEFAULT_VALUE'             => $this->check_config($this->config['ppde_default_value'], 'integer', 0),
			'PPDE_DROPBOX_VALUE'             => $this->check_config($this->config['ppde_dropbox_value'], 'string', '1,2,3,4,5,10,20,25,50,100'),
			'S_PPDE_DROPBOX_ENABLE'          => $this->check_config($this->config['ppde_dropbox_enable']),
			'S_PPDE_ENABLE'                  => $this->check_config($this->config['ppde_enable']),
			'S_PPDE_HEADER_LINK'             => $this->check_config($this->config['ppde_header_link']),

			// PayPal IPN vars
			'S_PPDE_IPN_AG_ENABLE'           => $this->check_config($this->config['ppde_ipn_autogroup_enable']),
			'S_PPDE_IPN_AG_GROUP_AS_DEFAULT' => $this->check_config($this->config['ppde_ipn_group_as_default']),
			'S_PPDE_IPN_DL_ENABLE'           => $this->check_config($this->config['ppde_ipn_donorlist_enable']),
			'S_PPDE_IPN_ENABLE'              => $this->check_config($this->config['ppde_ipn_enable']),
			'S_PPDE_IPN_GROUP_OPTIONS'       => group_select_options($this->config['ppde_ipn_group_id']),
			'S_PPDE_IPN_LOGGING'             => $this->check_config($this->config['ppde_ipn_logging']),
			'S_PPDE_IPN_NOTIFICATION_ENABLE' => $this->check_config($this->config['ppde_ipn_notification_enable']),

			// Sandbox Settings vars
			'PPDE_SANDBOX_ADDRESS'           => $this->check_config($this->config['ppde_sandbox_address'], 'string', ''),
			'S_PPDE_SANDBOX_ENABLE'          => $this->check_config($this->config['ppde_sandbox_enable']),
			'S_PPDE_SANDBOX_FOUNDER_ENABLE'  => $this->check_config($this->config['ppde_sandbox_founder_enable']),

			// Statistics Settings vars
			'PPDE_RAISED'                    => $this->check_config($this->config['ppde_raised'], 'float', 0),
			'PPDE_GOAL'                      => $this->check_config($this->config['ppde_goal'], 'float', 0),
			'PPDE_USED'                      => $this->check_config($this->config['ppde_used'], 'float', 0),
			'S_PPDE_STATS_INDEX_ENABLE'      => $this->check_config($this->config['ppde_stats_index_enable']),
			'S_PPDE_RAISED_ENABLE'           => $this->check_config($this->config['ppde_raised_enable']),
			'S_PPDE_GOAL_ENABLE'             => $this->check_config($this->config['ppde_goal_enable']),
			'S_PPDE_USED_ENABLE'             => $this->check_config($this->config['ppde_used_enable']),
		));
	}

	/**
	 * The form submitting if 'submit' is true
	 *
	 * @return null
	 * @access private
	 */
	private function submit_settings()
	{
		$this->submit = $this->request->is_set_post('submit');

		// Test if the submitted form is valid
		$errors = $this->is_invalid_form('ppde_settings', $this->submit);

		if ($this->can_submit_data($errors))
		{
			// Set the options the user configured
			$this->set_settings();

			// Add option settings change action to the admin log
			$phpbb_log = $this->container->get('log');
			$phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_UPDATED');

			// Option settings have been updated and logged
			// Confirm this to the user and provide link back to previous page
			trigger_error($this->user->lang($this->lang_key_prefix . '_SAVED') . adm_back_link($this->u_action));
		}
	}

	/**
	 * Set the options a user can configure
	 *
	 * @return null
	 * @access private
	 */
	private function set_settings()
	{
		// Set options for Global settings
		$this->config->set('ppde_default_currency', $this->request->variable('ppde_default_currency', 0));
		$this->config->set('ppde_default_value', $this->request->variable('ppde_default_value', 0));
		$this->config->set('ppde_dropbox_enable', $this->request->variable('ppde_dropbox_enable', false));
		$this->config->set('ppde_dropbox_value', $this->clean_items_list($this->request->variable('ppde_dropbox_value', '1,2,3,4,5,10,20,25,50,100')));
		$this->config->set('ppde_enable', $this->request->variable('ppde_enable', false));
		$this->config->set('ppde_header_link', $this->request->variable('ppde_header_link', false));

		// Set options for PayPal IPN
		$this->config->set('ppde_ipn_autogroup_enable', $this->request->variable('ppde_ipn_autogroup_enable', false));
		$this->config->set('ppde_ipn_donorlist_enable', $this->request->variable('ppde_ipn_donorlist_enable', false));
		$this->config->set('ppde_ipn_enable', $this->request->variable('ppde_ipn_enable', false));
		$this->config->set('ppde_ipn_group_as_default', $this->request->variable('ppde_ipn_group_as_default', false));
		$this->config->set('ppde_ipn_group_id', $this->request->variable('ppde_ipn_group_id', 0));
		$this->config->set('ppde_ipn_logging', $this->request->variable('ppde_ipn_logging', false));
		$this->config->set('ppde_ipn_notification_enable', $this->request->variable('ppde_ipn_notification_enable', false));

		// Set options for Sandbox Settings
		$this->config->set('ppde_sandbox_enable', $this->request->variable('ppde_sandbox_enable', false));
		$this->config->set('ppde_sandbox_founder_enable', $this->request->variable('ppde_sandbox_founder_enable', false));

		// Set options for Statistics Settings
		$this->config->set('ppde_stats_index_enable', $this->request->variable('ppde_stats_index_enable', false));
		$this->config->set('ppde_raised_enable', $this->request->variable('ppde_raised_enable', false));
		$this->config->set('ppde_raised', $this->request->variable('ppde_raised', 0.0));
		$this->config->set('ppde_goal_enable', $this->request->variable('ppde_goal_enable', false));
		$this->config->set('ppde_goal', $this->request->variable('ppde_goal', 0.0));
		$this->config->set('ppde_used_enable', $this->request->variable('ppde_used_enable', false));
		$this->config->set('ppde_used', $this->request->variable('ppde_used', 0.0));

		// Set misc settings
		$this->ppde_controller_main->set_curl_info();
		$this->ppde_controller_main->set_remote_detected();

		// Settings with dependencies are the last to be set.
		$this->config->set('ppde_account_id', $this->required_settings($this->request->variable('ppde_account_id', ''), $this->depend_on('ppde_enable')));
		$this->config->set('ppde_sandbox_address', $this->required_settings($this->request->variable('ppde_sandbox_address', ''), $this->depend_on('ppde_sandbox_enable')));
	}

	/**
	 * Clean items list to conserve only numeric values
	 *
	 * @param string $config_value
	 *
	 * @return string
	 * @access private
	 */
	private function clean_items_list($config_value)
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

		return $this->check_config(implode(',', array_unique($merge_items)), 'string', '');
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
	 * Check if settings is required
	 *
	 * @param $settings
	 * @param $depend_on
	 *
	 * @return mixed
	 * @access private
	 */
	private function required_settings($settings, $depend_on)
	{
		if (empty($settings) && $depend_on == true)
		{
			trigger_error($this->user->lang($this->lang_key_prefix . '_MISSING') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		return $settings;
	}

	/**
	 * Check if a settings depend on another.
	 *
	 * @param $config_name
	 *
	 * @return bool
	 * @access private
	 */
	private function depend_on($config_name)
	{
		return !empty($this->config[$config_name]) ? (bool) $this->config[$config_name] : false;
	}
}
