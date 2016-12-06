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
 * @property \phpbb\config\config     config             Config object
 * @property ContainerInterface       container          The phpBB log system
 * @property string                   id_prefix_name     Prefix name for identifier in the URL
 * @property string                   lang_key_prefix    Prefix for the messages thrown by exceptions
 * @property string                   module_name        Name of the module currently used
 * @property \phpbb\request\request   request            Request object
 * @property bool                     submit             State of submit $_POST variable
 * @property \phpbb\template\template template           Template object
 * @property string                   u_action           Action URL
 * @property \phpbb\user              user               User object
 */
class admin_settings_controller extends admin_main
{
	protected $ppde_controller_main;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config                    $config                 Config object
	 * @param ContainerInterface                      $container              Service container interface
	 * @param \skouat\ppde\controller\main_controller $ppde_controller_main   Main controller object
	 * @param \phpbb\request\request                  $request                Request object
	 * @param \phpbb\template\template                $template               Template object
	 * @param \phpbb\user                             $user                   User object
	 *
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, ContainerInterface $container, \skouat\ppde\controller\main_controller $ppde_controller_main, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->config = $config;
		$this->container = $container;
		$this->ppde_controller_main = $ppde_controller_main;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		parent::__construct(
			'settings',
			'PPDE_SETTINGS',
			''
		);
	}

	/**
	 * Display the general settings a user can configure for this extension
	 *
	 * @return void
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
		$this->s_error_assign_template_vars($errors);
		$this->u_action_assign_template_vars();
		$this->template->assign_vars(array(
			// Global Settings vars
			'PPDE_ACCOUNT_ID'                => $this->check_config($this->config['ppde_account_id'], 'string', ''),
			'PPDE_DEFAULT_CURRENCY'          => $this->container->get('skouat.ppde.controller')->build_currency_select_menu($this->config['ppde_default_currency']),
			'PPDE_DEFAULT_VALUE'             => $this->check_config($this->config['ppde_default_value'], 'integer', 0),
			'PPDE_DROPBOX_VALUE'             => $this->check_config($this->config['ppde_dropbox_value'], 'string', '1,2,3,4,5,10,20,25,50,100'),
			'S_PPDE_DROPBOX_ENABLE'          => $this->check_config($this->config['ppde_dropbox_enable']),
			'S_PPDE_ENABLE'                  => $this->check_config($this->config['ppde_enable']),
			'S_PPDE_HEADER_LINK'             => $this->check_config($this->config['ppde_header_link']),

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
	 * @return void
	 * @access private
	 */
	private function submit_settings()
	{
		$this->submit = $this->request->is_set_post('submit');

		// Test if the submitted form is valid
		$errors = $this->is_invalid_form('ppde_' . $this->module_name, $this->submit);

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
	 * @return void
	 * @access private
	 */
	private function set_settings()
	{
		// Set options for Global settings
		$this->config->set('ppde_default_currency', $this->request->variable('ppde_default_currency', 0));
		$this->config->set('ppde_default_value', $this->request->variable('ppde_default_value', 0));
		$this->config->set('ppde_dropbox_enable', $this->request->variable('ppde_dropbox_enable', false));
		$this->config->set('ppde_dropbox_value', $this->rebuild_items_list($this->request->variable('ppde_dropbox_value', '1,2,3,4,5,10,20,25,50,100'), $this->config['ppde_default_value']));
		$this->config->set('ppde_enable', $this->request->variable('ppde_enable', false));
		$this->config->set('ppde_header_link', $this->request->variable('ppde_header_link', false));

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
	}

	/**
	 * Rebuild items list to conserve only numeric values
	 *
	 * @param string $config_value
	 * @param string $added_value
	 *
	 * @return string
	 * @access private
	 */
	private function rebuild_items_list($config_value, $added_value = '')
	{
		$items_list = explode(',', $config_value);
		$merge_items = array();

		$this->add_int_data_in_array($merge_items, $added_value);

		foreach ($items_list as $item)
		{
			$this->add_int_data_in_array($merge_items, $item);
		}
		unset($items_list, $item);

		natsort($merge_items);

		return $this->check_config(implode(',', array_unique($merge_items)), 'string', '');
	}

	/**
	 * Add integer data in an array()
	 *
	 * @param array $array
	 * @param int   $var
	 *
	 * @return void
	 * @access private
	 */
	private function add_int_data_in_array(&$array, $var)
	{
		if (settype($var, 'integer') && $var != 0)
		{
			$array[] = $var;
		}
	}
}
