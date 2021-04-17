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

use phpbb\config\config;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use skouat\ppde\controller\ipn_paypal;
use skouat\ppde\controller\main_controller;

/**
 * @property config     config          Config object
 * @property string     id_prefix_name  Prefix name for identifier in the URL
 * @property string     lang_key_prefix Prefix for the messages thrown by exceptions
 * @property language   language        Language object
 * @property log        log             The phpBB log system
 * @property string     module_name     Name of the module currently used
 * @property ipn_paypal ppde_ipn_paypal IPN PayPal object
 * @property request    request         Request object
 * @property bool       submit          State of submit $_POST variable
 * @property template   template        Template object
 * @property string     u_action        Action URL
 * @property user       user            User object
 */
class paypal_features_controller extends admin_main
{
	protected $ppde_controller_main;

	/**
	 * Constructor
	 *
	 * @param config          $config               Config object
	 * @param language        $language             Language object
	 * @param log             $log                  The phpBB log system
	 * @param main_controller $ppde_controller_main Main controller object
	 * @param ipn_paypal      $ppde_ipn_paypal      IPN PayPal object
	 * @param request         $request              Request object
	 * @param template        $template             Template object
	 * @param user            $user                 User object
	 *
	 * @access public
	 */
	public function __construct(
		config $config,
		language $language,
		log $log,
		main_controller $ppde_controller_main,
		ipn_paypal $ppde_ipn_paypal,
		request $request,
		template $template,
		user $user
	)
	{
		$this->config = $config;
		$this->language = $language;
		$this->log = $log;
		$this->ppde_controller_main = $ppde_controller_main;
		$this->ppde_ipn_paypal = $ppde_ipn_paypal;
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
	 * @throws \ReflectionException
	 * @access public
	 */
	public function display_settings(): void
	{
		$this->ppde_first_start();

		// Define the name of the form for use as a form key
		add_form_key('ppde_paypal_features');

		// Create an array to collect errors that will be output to the user
		$errors = [];

		$this->submit_settings();

		// Set output vars for display in the template
		$this->s_error_assign_template_vars($errors);
		$this->u_action_assign_template_vars();
		$this->build_remote_uri_select_menu((int) $this->config['ppde_sandbox_remote'], 'sandbox');
		$this->template->assign_vars([
			// PayPal IPN vars
			'PPDE_IPN_AG_MIN_BEFORE_GROUP'   => $this->check_config($this->config['ppde_ipn_min_before_group'], 'integer', 0),
			'S_PPDE_IPN_AG_ENABLE'           => $this->check_config($this->config['ppde_ipn_autogroup_enable']),
			'S_PPDE_IPN_AG_GROUP_AS_DEFAULT' => $this->check_config($this->config['ppde_ipn_group_as_default']),
			'S_PPDE_IPN_DL_ALLOW_GUEST'      => $this->check_config($this->config['ppde_ipn_dl_allow_guest'], 'boolean', false),
			'S_PPDE_IPN_DL_ENABLE'           => $this->check_config($this->config['ppde_ipn_donorlist_enable']),
			'S_PPDE_IPN_ENABLE'              => $this->check_config($this->config['ppde_ipn_enable']),
			'S_PPDE_IPN_GROUP_OPTIONS'       => group_select_options($this->config['ppde_ipn_group_id']),
			'S_PPDE_IPN_LOGGING'             => $this->check_config($this->config['ppde_ipn_logging']),
			'S_PPDE_IPN_NOTIFICATION_ENABLE' => $this->check_config($this->config['ppde_ipn_notification_enable']),

			// Sandbox Settings vars
			'PPDE_SANDBOX_ADDRESS'           => $this->check_config($this->config['ppde_sandbox_address'], 'string'),
			'S_PPDE_SANDBOX_ENABLE'          => $this->check_config($this->config['ppde_sandbox_enable']),
			'S_PPDE_SANDBOX_FOUNDER_ENABLE'  => $this->check_config($this->config['ppde_sandbox_founder_enable']),
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function set_settings(): void
	{
		// Set options for PayPal IPN
		$this->config->set('ppde_ipn_autogroup_enable', $this->request->variable('ppde_ipn_autogroup_enable', false));
		$this->config->set('ppde_ipn_dl_allow_guest', $this->request->variable('ppde_ipn_dl_allow_guest', false));
		$this->config->set('ppde_ipn_donorlist_enable', $this->request->variable('ppde_ipn_donorlist_enable', false));
		$this->config->set('ppde_ipn_enable', $this->request->variable('ppde_ipn_enable', false));
		$this->config->set('ppde_ipn_group_as_default', $this->request->variable('ppde_ipn_group_as_default', false));
		$this->config->set('ppde_ipn_group_id', $this->request->variable('ppde_ipn_group_id', 0));
		$this->config->set('ppde_ipn_logging', $this->request->variable('ppde_ipn_logging', false));
		$this->config->set('ppde_ipn_min_before_group', $this->request->variable('ppde_ipn_min_before_group', 0));
		$this->config->set('ppde_ipn_notification_enable', $this->request->variable('ppde_ipn_notification_enable', false));

		// Set options for Sandbox Settings
		$this->config->set('ppde_sandbox_enable', $this->request->variable('ppde_sandbox_enable', false));
		$this->config->set('ppde_sandbox_founder_enable', $this->request->variable('ppde_sandbox_founder_enable', true));
		$this->config->set('ppde_sandbox_remote', $this->request->variable('ppde_sandbox_remote', 1));

		// Set misc settings
		$this->ppde_ipn_paypal->set_curl_info();
		$this->ppde_ipn_paypal->set_remote_detected();
		$this->ppde_ipn_paypal->check_tls();
		if (!$this->ppde_controller_main->is_ipn_requirement_satisfied())
		{
			$this->config->set('ppde_ipn_enable', (string) false);
			trigger_error($this->language->lang($this->lang_key_prefix . '_NOT_ENABLEABLE') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Settings with dependencies are the last to be set.
		$this->config->set('ppde_sandbox_address', $this->required_settings($this->request->variable('ppde_sandbox_address', ''), (bool) $this->config['ppde_sandbox_enable']));
		$this->ppde_controller_main->ppde_actions_auth->set_guest_acl();
	}
}
