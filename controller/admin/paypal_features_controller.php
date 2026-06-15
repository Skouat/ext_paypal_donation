<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller\admin;

use phpbb\config\config;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use skouat\ppde\controller\main_controller;

/**
 * @property config     config          Config object
 * @property string     id_prefix_name  Prefix name for identifier in the URL
 * @property string     lang_key_prefix Prefix for the messages thrown by exceptions
 * @property language   language        Language object
 * @property log        log             The phpBB log system
 * @property string     module_name     Name of the module currently used
 * @property request    request         Request object
 * @property bool       submit          State of submit $_POST variable
 * @property template   template        Template object
 * @property string     u_action        Action URL
 * @property user       user            User object
 */
class paypal_features_controller extends admin_main
{
	protected $ppde_controller_main;
	protected $controller_helper;
	protected $ppde_client_factory;

	/**
	 * Constructor
	 *
	 * @param config          $config               Config object
	 * @param language        $language             Language object
	 * @param log             $log                  The phpBB log system
	 * @param main_controller $ppde_controller_main Main controller object
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
		\phpbb\controller\helper $controller_helper,
		\skouat\ppde\api\paypal\client_factory $ppde_client_factory,
		request $request,
		template $template,
		user $user
	)
	{
		$this->config = $config;
		$this->language = $language;
		$this->log = $log;
		$this->ppde_controller_main = $ppde_controller_main;
		$this->controller_helper = $controller_helper;
		$this->ppde_client_factory = $ppde_client_factory;
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
		// Handle the AJAX connection test before rendering the page
		if ($this->request->is_ajax() && $this->request->is_set_post('test_connection'))
		{
			$this->handle_connection_test();
		}

		// Define the name of the form for use as a form key
		add_form_key('ppde_paypal_features');

		// Create an array to collect errors that will be output to the user
		$errors = [];

		$this->submit_settings();

		// Set output vars for display in the template
		$this->s_error_assign_template_vars($errors);
		$this->u_action_assign_template_vars();
		$this->template->assign_vars([
			// REST API - Live
			'PPDE_REST_CLIENT_ID'            => $this->check_config($this->config['ppde_rest_client_id'], 'string'),
			'PPDE_WEBHOOK_ID'                => $this->check_config($this->config['ppde_webhook_id'], 'string'),
			'S_PPDE_REST_SECRET_SET'         => !empty($this->config['ppde_rest_secret']),

			// REST API - Sandbox
			'PPDE_SANDBOX_REST_CLIENT_ID'    => $this->check_config($this->config['ppde_sandbox_rest_client_id'], 'string'),
			'PPDE_SANDBOX_WEBHOOK_ID'        => $this->check_config($this->config['ppde_sandbox_webhook_id'], 'string'),
			'S_PPDE_SANDBOX_REST_SECRET_SET' => !empty($this->config['ppde_sandbox_rest_secret']),

			'PPDE_WEBHOOK_URL'               => generate_board_url(true) . $this->controller_helper->route('skouat_ppde_webhook'),
			'PPDE_TEST_HASH'                 => generate_link_hash('ppde_test_connection'),

			// PayPal IPN vars
			'PPDE_IPN_AG_MIN_BEFORE_GROUP'   => $this->check_config($this->config['ppde_ipn_min_before_group'], 'integer', 0),
			'S_PPDE_IPN_AG_ENABLE'           => $this->check_config($this->config['ppde_ipn_autogroup_enable']),
			'S_PPDE_IPN_AG_GROUP_AS_DEFAULT' => $this->check_config($this->config['ppde_ipn_group_as_default']),
			'S_PPDE_IPN_DL_ALLOW_GUEST'      => $this->check_config($this->config['ppde_ipn_dl_allow_guest'], 'boolean', false),
			'S_PPDE_IPN_DL_ENABLE'           => $this->check_config($this->config['ppde_ipn_donorlist_enable']),
			'S_PPDE_IPN_ENABLE'              => $this->check_config($this->config['ppde_ipn_enable']),
			'S_PPDE_IPN_GROUP_OPTIONS'       => group_select_options($this->config['ppde_ipn_group_id']),
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
		// REST API credentials (Live)
		$this->config->set('ppde_rest_client_id', $this->request->variable('ppde_rest_client_id', '', true));
		$this->config->set('ppde_webhook_id', $this->request->variable('ppde_webhook_id', '', true));
		$this->set_secret('ppde_rest_secret', 'ppde_rest_secret');

		// REST API credentials (Sandbox)
		$this->config->set('ppde_sandbox_rest_client_id', $this->request->variable('ppde_sandbox_rest_client_id', '', true));
		$this->config->set('ppde_sandbox_webhook_id', $this->request->variable('ppde_sandbox_webhook_id', '', true));
		$this->set_secret('ppde_sandbox_rest_secret', 'ppde_sandbox_rest_secret');

		// Set options for PayPal IPN
		$this->config->set('ppde_ipn_autogroup_enable', $this->request->variable('ppde_ipn_autogroup_enable', false));
		$this->config->set('ppde_ipn_dl_allow_guest', $this->request->variable('ppde_ipn_dl_allow_guest', false));
		$this->config->set('ppde_ipn_donorlist_enable', $this->request->variable('ppde_ipn_donorlist_enable', false));
		$this->config->set('ppde_ipn_enable', $this->request->variable('ppde_ipn_enable', false));
		$this->config->set('ppde_ipn_group_as_default', $this->request->variable('ppde_ipn_group_as_default', false));
		$this->config->set('ppde_ipn_group_id', $this->request->variable('ppde_ipn_group_id', 0));
		$this->config->set('ppde_ipn_min_before_group', $this->request->variable('ppde_ipn_min_before_group', 0));
		$this->config->set('ppde_ipn_notification_enable', $this->request->variable('ppde_ipn_notification_enable', false));

		// Set options for Sandbox Settings
		$this->config->set('ppde_sandbox_enable', $this->request->variable('ppde_sandbox_enable', false));
		$this->config->set('ppde_sandbox_founder_enable', $this->request->variable('ppde_sandbox_founder_enable', true));

		// Settings with dependencies are the last to be set.
		$this->config->set('ppde_sandbox_address', $this->required_settings($this->request->variable('ppde_sandbox_address', ''), (bool) $this->config['ppde_sandbox_enable']));
		$this->ppde_controller_main->ppde_actions_auth->set_guest_acl();
	}

	/**
	 * Sets a secret config value.
	 *
	 * The secret input is rendered as an empty password field. When the admin
	 * leaves it blank, the previously stored secret is preserved (i.e. not overwritten with an empty string).
	 *
	 * @param string $config_name  Name of the config key to update
	 * @param string $request_name Name of the POST field
	 *
	 * @return void
	 * @access private
	 */
	private function set_secret($config_name, $request_name): void
	{
		$value = $this->request->variable($request_name, '', true);

		if ($value !== '')
		{
			$this->config->set($config_name, $value);
		}
	}

	/**
	 * Handle the AJAX "test connection" request and send a JSON response.
	 *
	 * @return void
	 * @access private
	 */
	private function handle_connection_test(): void
	{
		if (!check_link_hash($this->request->variable('hash', ''), 'ppde_test_connection'))
		{
			$this->send_test_result(false, $this->language->lang('FORM_INVALID'));
		}

		$sandbox = $this->request->variable('env', '') === 'sandbox';
		$result = $this->ppde_client_factory->test_connection($sandbox);

		if ($result['success'])
		{
			$this->send_test_result(true, $this->language->lang('PPDE_REST_TEST_SUCCESS'));
		}

		switch ($result['reason'])
		{
			case 'missing':
				$message = $this->language->lang('PPDE_REST_CREDENTIALS_MISSING');
			break;
			case 'auth':
				$message = $this->language->lang('PPDE_REST_TEST_INVALID');
			break;
			case 'curl':
				$message = $this->language->lang('PPDE_REST_TEST_CURL_ERROR', $result['detail']);
			break;
			default:
				$message = $this->language->lang('PPDE_REST_TEST_HTTP_ERROR', $result['http_code']);
		}

		$this->send_test_result(false, $message);
	}

	/**
	 * Send a JSON response for the connection test (and stop execution).
	 *
	 * @param bool   $success
	 * @param string $message
	 *
	 * @return void
	 * @access private
	 */
	private function send_test_result($success, $message): void
	{
		$json_response = new \phpbb\json_response;
		$json_response->send([
			'success'      => (bool) $success,
			'MESSAGE_TEXT' => $message,
		]);
	}
}
