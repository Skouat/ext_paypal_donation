<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license       GNU General Public License, version 2 (GPL-2.0)
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
class admin_paypal_features_controller extends admin_main
{
	protected $ppde_controller_main;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config                    $config               Config object
	 * @param ContainerInterface                      $container            Service container interface
	 * @param \skouat\ppde\controller\main_controller $ppde_controller_main Main controller object
	 * @param \phpbb\request\request                  $request              Request object
	 * @param \phpbb\template\template                $template             Template object
	 * @param \phpbb\user                             $user                 User object
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
			'paypal_features',
			'PPDE_PAYPAL_FEATURES',
			''
		);
	}

	/**
	 * Display the settings a user can configure for this extension
	 *
	 * @return void
	 * @access public
	 */
	public function display_settings()
	{
		$this->ppde_controller_main->first_start();

		// Define the name of the form for use as a form key
		add_form_key('ppde_paypal_features');

		// Create an array to collect errors that will be output to the user
		$errors = array();

		$this->submit_settings();

		// Set output vars for display in the template
		$this->s_error_assign_template_vars($errors);
		$this->u_action_assign_template_vars();
		$this->template->assign_vars(array(
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
		$this->config->set('ppde_sandbox_founder_enable', $this->request->variable('ppde_sandbox_founder_enable', true));

		// Set misc settings
		$this->ppde_controller_main->set_curl_info();
		$this->ppde_controller_main->set_remote_detected();

		// Settings with dependencies are the last to be set.
		$this->config->set('ppde_sandbox_address', $this->required_settings($this->request->variable('ppde_sandbox_address', ''), $this->depend_on('ppde_sandbox_enable')));
	}
}
