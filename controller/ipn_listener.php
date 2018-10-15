<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * Special Thanks to the following individuals for their inspiration:
 *    David Lewis (Highway of Life) http://startrekguide.com
 *    Micah Carrick (email@micahcarrick.com) http://www.micahcarrick.com
 */

namespace skouat\ppde\controller;

use phpbb\config\config;
use phpbb\event\dispatcher_interface;
use phpbb\language\language;
use phpbb\notification\manager;
use phpbb\path_helper;
use phpbb\request\request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ipn_listener
{
	const ASCII_RANGE = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	/** Setup the PayPal variables list with default values and conditions to check.
	 * Example:
	 *      array(
	 *          'name' => 'txn_id'
	 *          'default' => ''
	 *          'condition_check' => array('ascii' => true),
	 *      ),
	 *      array(
	 *          'name' => 'business'
	 *          'default' => ''
	 *          'condition_check' => array('length' => array('value' => 127, 'operator' => '<=')),
	 *      ),
	 * The index 'name' and 'default' are mandatory.
	 * The index 'condition_check' is optional
	 *
	 */
	private static $paypal_vars_table = array(
		array('name' => 'confirmed', 'default' => false),    // used to check if the payment is confirmed
		array('name' => 'exchange_rate', 'default' => ''),   // Exchange rate used if a currency conversion occurred
		array('name' => 'mc_currency', 'default' => ''),     // Currency
		array('name' => 'mc_gross', 'default' => 0.00),      // Amt received (before fees)
		array('name' => 'mc_fee', 'default' => 0.00),        // Amt of fees
		array('name' => 'payment_status', 'default' => ''),  // Payment status. e.g.: 'Completed'
		array('name' => 'settle_amount', 'default' => 0.00), // Amt received after currency conversion (before fees)
		array('name' => 'settle_currency', 'default' => ''), // Currency of 'settle_amount'
		array('name' => 'test_ipn', 'default' => false),     // used when transaction come from Sandbox platform
		array('name' => 'txn_type', 'default' => ''),        // Transaction type - Should be: 'web_accept'
		array(  // Primary merchant e-mail address
				'name'            => 'business',
				'default'         => '',
				'condition_check' => array('length' => array('value' => 127, 'operator' => '<=')),
		),
		array(  // Sender's First name
				'name'            => 'first_name',
				'default'         => array('', true),
				'condition_check' => array('length' => array('value' => 64, 'operator' => '<=')),
		),
		array(  // Equal to: $this->config['sitename']
				'name'            => 'item_name',
				'default'         => array('', true),
				'condition_check' => array('length' => array('value' => 127, 'operator' => '<=')),
		),
		array(  // Equal to: 'uid_' . $this->user->data['user_id'] . '_' . time()
				'name'            => 'item_number',
				'default'         => '',
				'condition_check' => array('length' => array('value' => 127, 'operator' => '<=')),
		),
		array(  // Sender's Last name
				'name'            => 'last_name',
				'default'         => array('', true),
				'condition_check' => array('length' => array('value' => 64, 'operator' => '<=')),
		),
		array(  // The Parent transaction ID, in case of refund.
				'name'            => 'parent_txn_id',
				'default'         => '',
				'condition_check' => array('length' => array('value' => 19, 'operator' => '<=')),
		),
		array(  // PayPal sender email address
				'name'            => 'payer_email',
				'default'         => '',
				'condition_check' => array('length' => array('value' => 127, 'operator' => '<=')),
		),
		array(  // PayPal sender ID
				'name'            => 'payer_id',
				'default'         => '',
				'condition_check' => array('length' => array('value' => 13, 'operator' => '<=')),
		),
		array(  // PayPal sender status (verified or unverified)
				'name'            => 'payer_status',
				'default'         => 'unverified',
				'condition_check' => array('length' => array('value' => 13, 'operator' => '<=')),
		),
		array(  // Payment Date/Time in the format 'HH:MM:SS Mmm DD, YYYY PDT'
				'name'            => 'payment_date',
				'default'         => '',
				'condition_check' => array('length' => array('value' => 28, 'operator' => '<=')),
		),
		array(  // Payment type (echeck or instant)
				'name'            => 'payment_type',
				'default'         => '',
				'condition_check' => array('content' => array('echeck', 'instant')),
		),
		array(  // Secure Merchant Account ID
				'name'            => 'receiver_id',
				'default'         => '',
				'condition_check' => array('length' => array('value' => 13, 'operator' => '<=')),
		),
		array(  // Merchant e-mail address
				'name'            => 'receiver_email',
				'default'         => '',
				'condition_check' => array('length' => array('value' => 127, 'operator' => '<=')),
		),
		array(  // Merchant country code
				'name'            => 'residence_country',
				'default'         => '',
				'condition_check' => array('length' => array('value' => 2, 'operator' => '==')),
		),
		array(  // Transaction ID
				'name'            => 'txn_id',
				'default'         => '',
				'condition_check' => array('ascii' => true),
		),
	);

	/**
	 * Services properties declaration
	 */
	protected $config;
	protected $container;
	protected $dispatcher;
	protected $language;
	protected $notification;
	protected $path_helper;
	protected $php_ext;
	protected $ppde_controller_main;
	protected $ppde_controller_transactions_admin;
	protected $ppde_ipn_log;
	protected $ppde_ipn_paypal;
	protected $request;

	/**
	 * Args from PayPal notify return URL
	 *
	 * @var string
	 */
	private $args_return_uri = array();
	/**
	 * Main currency data
	 *
	 * @var array
	 */
	private $currency_mc_data;
	/**
	 * Settle currency data
	 *
	 * @var array
	 */
	private $currency_settle_data;
	/**
	 * @var boolean
	 */
	private $donor_is_member = false;
	/**
	 * @var array|boolean
	 */
	private $payer_data;
	/**
	 * phpBB root path
	 *
	 * @var string
	 */
	private $root_path;
	/**
	 * Data from PayPal transaction
	 *
	 * @var array
	 */
	private $transaction_data = array();
	/**
	 * PayPal URL
	 * Could be Sandbox URL ou normal PayPal URL.
	 *
	 * @var string
	 */
	private $u_paypal = '';
	/**
	 * Transaction status
	 *
	 * @var boolean
	 */
	private $verified = false;

	/**
	 * Constructor
	 *
	 * @param config                        $config                             Config object
	 * @param ContainerInterface            $container                          Service container interface
	 * @param language                      $language                           Language user object
	 * @param manager                       $notification                       Notification object
	 * @param path_helper                   $path_helper                        Path helper object
	 * @param main_controller               $ppde_controller_main               Main controller object
	 * @param admin_transactions_controller $ppde_controller_transactions_admin Admin transactions controller object
	 * @param ipn_log                       $ppde_ipn_log                       IPN log
	 * @param ipn_paypal                    $ppde_ipn_paypal                    IPN PayPal
	 * @param request                       $request                            Request object
	 * @param dispatcher_interface          $dispatcher                         Dispatcher object
	 * @param string                        $php_ext                            phpEx
	 *
	 * @access public
	 */
	public function __construct(config $config, ContainerInterface $container, language $language, manager $notification, path_helper $path_helper, main_controller $ppde_controller_main, admin_transactions_controller $ppde_controller_transactions_admin, ipn_log $ppde_ipn_log, ipn_paypal $ppde_ipn_paypal, request $request, dispatcher_interface $dispatcher, $php_ext)
	{
		$this->config = $config;
		$this->container = $container;
		$this->dispatcher = $dispatcher;
		$this->language = $language;
		$this->notification = $notification;
		$this->path_helper = $path_helper;
		$this->ppde_controller_main = $ppde_controller_main;
		$this->ppde_controller_transactions_admin = $ppde_controller_transactions_admin;
		$this->ppde_ipn_log = $ppde_ipn_log;
		$this->ppde_ipn_paypal = $ppde_ipn_paypal;
		$this->request = $request;
		$this->php_ext = $php_ext;

		$this->root_path = $this->path_helper->get_phpbb_root_path();
	}

	public function handle()
	{
		$this->language->add_lang('donate', 'skouat/ppde');

		// Set IPN logging
		$this->ppde_ipn_log->set_use_log_error((bool) $this->config['ppde_ipn_logging']);

		// Determine which remote connection to use to contact PayPal
		$this->ppde_ipn_paypal->is_remote_detected();

		// if no connection detected, disable IPN, log error and exit code execution
		if (!$this->ppde_controller_main->is_ipn_requirement_satisfied())
		{
			$this->config->set('ppde_ipn_enable', false);
			$this->ppde_ipn_log->log_error($this->language->lang('REQUIREMENT_NOT_SATISFIED'), true, true, E_USER_WARNING);
		}

		// Check the transaction returned by PayPal
		$this->validate_transaction();

		$this->log_to_db();

		$this->do_actions();

		// We stop the execution of the code because nothing need to be returned to phpBB.
		// And PayPal need it to terminate properly the IPN process.
		garbage_collection();
		exit_handler();
	}

	/**
	 * Post Data back to PayPal to validate the authenticity of the transaction.
	 *
	 * @return bool
	 * @access private
	 */
	private function validate_transaction()
	{
		// Request and populate $this->transaction_data
		array_map(array($this, 'get_post_data'), $this::$paypal_vars_table);

		$decode_ary = array('receiver_email', 'payer_email', 'payment_date', 'business');
		foreach ($decode_ary as $key)
		{
			$this->transaction_data[$key] = urldecode($this->transaction_data[$key]);
		}

		$this->set_args_return_uri();

		// Get PayPal or Sandbox URI
		$this->u_paypal = $this->ppde_controller_main->get_paypal_uri((bool) $this->transaction_data['test_ipn']);

		// Initiate PayPal connection
		$this->ppde_ipn_paypal->set_u_paypal($this->u_paypal);
		$this->ppde_ipn_paypal->initiate_paypal_connection($this->args_return_uri, $this->transaction_data);

		if ($this->ppde_ipn_paypal->check_response_status())
		{
			$this->ppde_ipn_log->log_error($this->language->lang('INVALID_RESPONSE_STATUS'), $this->ppde_ipn_log->is_use_log_error(), true, E_USER_NOTICE, array($this->ppde_ipn_paypal->get_response_status()));
		}

		return $this->check_response();
	}

	/**
	 * Get all args and build the return URI
	 *
	 * @return void
	 * @access private
	 */
	private function set_args_return_uri()
	{
		$values = array();
		// Add the cmd=_notify-validate for PayPal
		$this->args_return_uri = 'cmd=_notify-validate';

		// Grab the post data form and set in an array to be used in the URI to PayPal
		foreach ($this->get_postback_args() as $key => $value)
		{
			$encoded = urlencode($value);
			$values[] = $key . '=' . $encoded;
		}

		// implode the array into a string URI
		$this->args_return_uri .= '&' . implode('&', $values);
	}

	/**
	 * Get $_POST content as is. This is used to Postback args to PayPal.
	 *
	 * @return array
	 */
	private function get_postback_args()
	{
		$data_ary = array();

		foreach ($this->request->variable_names(\phpbb\request\request_interface::POST) as $key)
		{
			$data_ary[$key] = $this->request->variable($key, '', true);
		}

		return $data_ary;
	}

	/**
	 * Check response returned by PayPal et log errors if there is no valid response
	 * Set true if response is 'VERIFIED'. In other case set to false and log errors
	 *
	 * @return bool $this->verified
	 * @access private
	 */
	private function check_response()
	{
		// Prepare data to include in report
		$this->ppde_ipn_log->set_report_data($this->u_paypal, $this->ppde_ipn_paypal->get_remote_used(), $this->ppde_ipn_paypal->get_report_response(), $this->ppde_ipn_paypal->get_response_status(), $this->transaction_data);

		if ($this->txn_is_verified())
		{
			$this->verified = $this->transaction_data['confirmed'] = true;
			$this->ppde_ipn_log->log_error("DEBUG VERIFIED:\n" . $this->ppde_ipn_log->get_text_report(), $this->ppde_ipn_log->is_use_log_error());
		}
		else if ($this->txn_is_invalid())
		{
			$this->verified = $this->transaction_data['confirmed'] = false;
			$this->ppde_ipn_log->log_error("DEBUG INVALID:\n" . $this->ppde_ipn_log->get_text_report(), $this->ppde_ipn_log->is_use_log_error(), true);
		}
		else
		{
			$this->verified = $this->transaction_data['confirmed'] = false;
			$this->ppde_ipn_log->log_error("DEBUG OTHER:\n" . $this->ppde_ipn_log->get_text_report(), $this->ppde_ipn_log->is_use_log_error());
			$this->ppde_ipn_log->log_error($this->language->lang('UNEXPECTED_RESPONSE'), $this->ppde_ipn_log->is_use_log_error(), true);
		}

		return $this->verified;
	}

	/**
	 * Check if transaction is VERIFIED
	 *
	 * @return bool
	 * @access private
	 */
	private function txn_is_verified()
	{
		return $this->ppde_ipn_paypal->is_curl_strcmp('VERIFIED');
	}

	/**
	 * Check if transaction is INVALID
	 *
	 * @return bool
	 * @access private
	 */
	private function txn_is_invalid()
	{
		return $this->ppde_ipn_paypal->is_curl_strcmp('INVALID');
	}

	/**
	 * Log the transaction to the database
	 *
	 * @access private
	 */
	private function log_to_db()
	{
		// Initiate a transaction log entity
		/** @type \skouat\ppde\entity\transactions $entity */
		$entity = $this->container->get('skouat.ppde.entity.transactions');

		// the item number contains the user_id
		$this->extract_item_number_data();
		$this->validate_user_id();

		// set username in extra_data property in $entity
		$user_ary = $this->ppde_controller_transactions_admin->ppde_operator->query_donor_user_data('user', $this->transaction_data['user_id']);
		$entity->set_username($user_ary['username']);

		// list the data to be thrown into the database
		$data = $this->build_data_ary();

		$this->ppde_controller_transactions_admin->set_entity_data($entity, $data);

		$this->submit_data($entity);
	}

	/**
	 * Retrieve user_id from item_number args
	 *
	 * @return void
	 * @access private
	 */
	private function extract_item_number_data()
	{
		list($this->transaction_data['user_id']) = explode('_', substr($this->transaction_data['item_number'], 4), -1);
	}

	/**
	 * Avoid the user_id to be set to 0
	 *
	 * @return void
	 * @access private
	 */
	private function validate_user_id()
	{
		if (empty($this->transaction_data['user_id']) || !is_numeric($this->transaction_data['user_id']))
		{
			$this->transaction_data['user_id'] = ANONYMOUS;
		}
	}

	/**
	 * Prepare data array() before send it to $entity
	 *
	 * @return array
	 */
	private function build_data_ary()
	{
		return array(
			'business'          => $this->transaction_data['business'],
			'confirmed'         => (bool) $this->transaction_data['confirmed'],
			'exchange_rate'     => $this->transaction_data['exchange_rate'],
			'first_name'        => $this->transaction_data['first_name'],
			'item_name'         => $this->transaction_data['item_name'],
			'item_number'       => $this->transaction_data['item_number'],
			'last_name'         => $this->transaction_data['last_name'],
			'mc_currency'       => $this->transaction_data['mc_currency'],
			'mc_gross'          => floatval($this->transaction_data['mc_gross']),
			'mc_fee'            => floatval($this->transaction_data['mc_fee']),
			'net_amount'        => $this->net_amount($this->transaction_data['mc_gross'], $this->transaction_data['mc_fee']),
			'parent_txn_id'     => $this->transaction_data['parent_txn_id'],
			'payer_email'       => $this->transaction_data['payer_email'],
			'payer_id'          => $this->transaction_data['payer_id'],
			'payer_status'      => $this->transaction_data['payer_status'],
			'payment_date'      => strtotime($this->transaction_data['payment_date']),
			'payment_status'    => $this->transaction_data['payment_status'],
			'payment_type'      => $this->transaction_data['payment_type'],
			'receiver_id'       => $this->transaction_data['receiver_id'],
			'receiver_email'    => $this->transaction_data['receiver_email'],
			'residence_country' => $this->transaction_data['residence_country'],
			'settle_amount'     => floatval($this->transaction_data['settle_amount']),
			'settle_currency'   => $this->transaction_data['settle_currency'],
			'test_ipn'          => $this->transaction_data['test_ipn'],
			'txn_id'            => $this->transaction_data['txn_id'],
			'txn_type'          => $this->transaction_data['txn_type'],
			'user_id'           => (int) $this->transaction_data['user_id'],
		);
	}

	/**
	 * Returns the net amount of a PayPal Transaction
	 *
	 * @param float  $amount
	 * @param float  $fee
	 * @param string $dec_point
	 * @param string $thousands_sep
	 *
	 * @return string
	 * @access private
	 */
	private function net_amount($amount, $fee, $dec_point = '.', $thousands_sep = '')
	{
		return number_format((float) $amount - (float) $fee, 2, $dec_point, $thousands_sep);
	}

	/**
	 *  Submit data to the database
	 *
	 * @param \skouat\ppde\entity\transactions $entity The transactions log entity object
	 *
	 * @return void
	 * @access private
	 */
	private function submit_data(\skouat\ppde\entity\transactions $entity)
	{
		if ($this->verified)
		{
			// load the ID of the transaction in the entity
			$entity->set_id($entity->transaction_exists());
			// Add or edit transaction data
			$this->ppde_controller_transactions_admin->add_edit_data($entity);
		}
	}

	/**
	 * Do actions if the transaction is verified
	 *
	 * @return void
	 * @access private
	 */
	private function do_actions()
	{
		// If the transaction is not verified do nothing
		if (!$this->verified)
		{
			return;
		}

		if ($this->payment_status_is_completed())
		{
			$transaction_data = $this->transaction_data;

			/**
			 * Event that is triggered when a transaction has been successfully completed
			 *
			 * @event skouat.ppde.do_actions_completed_before
			 * @var array    transaction_data    Array containing transaction data
			 * @since 1.0.3
			 */
			$vars = array(
				'transaction_data',
			);
			extract($this->dispatcher->trigger_event('skouat.ppde.do_actions_completed_before', compact($vars)));

			$this->transaction_data = $transaction_data;
			unset($transaction_data);

			// Do actions whether the transaction is real or a test.
			$this->ppde_controller_transactions_admin->update_overview_stats((bool) $this->transaction_data['test_ipn']);
			$this->update_raised_amount();

			// Do additional actions if the transaction is not a test.
			if (!$this->ppde_controller_transactions_admin->get_ipn_test())
			{
				// Set donor_is_member property
				$this->donor_is_member();

				// Do actions
				$this->update_donor_stats();
				$this->donors_group_user_add();
				$this->notify_donation_received();
			}
		}
	}

	/**
	 * Checks if payment_status is completed
	 *
	 * @return bool
	 * @access private
	 */
	private function payment_status_is_completed()
	{
		return $this->transaction_data['payment_status'] === 'Completed';
	}

	/**
	 * Updates the amount of donation raised
	 *
	 * @return void
	 * @access private
	 */
	private function update_raised_amount()
	{
		$ipn_suffix = $this->ppde_controller_transactions_admin->get_suffix_ipn();
		$this->config->set('ppde_raised' . $ipn_suffix, (float) $this->config['ppde_raised' . $ipn_suffix] + (float) $this->net_amount($this->transaction_data['mc_gross'], $this->transaction_data['mc_fee']), true);
	}

	/**
	 * Returns if donor is member
	 *
	 * @return bool
	 * @access private
	 */
	private function donor_is_member()
	{
		return $this->is_donor_is_member() && !empty($this->payer_data);
	}

	/**
	 * Checks if the donor is a member then gets payer_data values
	 *
	 * @return void
	 * @access private
	 */

	private function is_donor_is_member()
	{
		$anonymous_user = false;

		// if the user_id is not anonymous
		if ($this->transaction_data['user_id'] != ANONYMOUS)
		{
			$this->donor_is_member = $this->check_donors_status('user', $this->transaction_data['user_id']);

			if (!$this->donor_is_member)
			{
				// no results, therefore the user is anonymous...
				$anonymous_user = true;
			}
		}
		else
		{
			// the user is anonymous by default
			$anonymous_user = true;
		}

		if ($anonymous_user)
		{
			// if the user is anonymous, check their PayPal email address with all known email hashes
			// to determine if the user exists in the database with that email
			$this->donor_is_member = $this->check_donors_status('email', $this->transaction_data['payer_email']);
		}
	}

	/**
	 * Gets donor informations (user id, username, amount donated) and returns if exists
	 *
	 * @param string     $type Allowed value : 'user' or 'email'
	 * @param string|int $args If $type is set to 'user', $args must be a user id.
	 *                         If $type is set to 'email', $args must be an email address
	 *
	 * @return bool
	 * @access private
	 */
	private function check_donors_status($type, $args)
	{
		$this->payer_data = $this->ppde_controller_transactions_admin->ppde_operator->query_donor_user_data($type, $args);

		return !empty($this->payer_data);
	}

	private function update_donor_stats()
	{
		if ($this->donor_is_member)
		{
			$this->ppde_controller_transactions_admin->update_user_stats((int) $this->payer_data['user_id'], (float) $this->payer_data['user_ppde_donated_amount'] + (float) $this->net_amount($this->transaction_data['mc_gross'], $this->transaction_data['mc_fee']));
		}
	}

	/**
	 * Add donor to the donors group
	 *
	 * @return void
	 * @access private
	 */
	private function donors_group_user_add()
	{
		// we add the user to the donors group
		$can_use_autogroup = $this->can_use_autogroup();
		$group_id = (int) $this->config['ppde_ipn_group_id'];
		$payer_id = (int) $this->payer_data['user_id'];
		$payer_username = $this->payer_data['username'];
		$default_group = $this->config['ppde_ipn_group_as_default'];

		/**
		 * Event to modify data before a user is added to the donors group
		 *
		 * @event skouat.ppde.donors_group_user_add_before
		 * @var bool    can_use_autogroup   Whether or not to add the user to the group
		 * @var int     group_id            The ID of the group to which the user will be added
		 * @var int     payer_id            The ID of the user who will we added to the group
		 * @var string  payer_username      The user name
		 * @var bool    default_group       Whether or not the group should be made default for the user
		 * @since 1.0.3
		 */
		$vars = array(
			'can_use_autogroup',
			'group_id',
			'payer_id',
			'payer_username',
			'default_group',
		);
		extract($this->dispatcher->trigger_event('skouat.ppde.donors_group_user_add_before', compact($vars)));

		if ($can_use_autogroup)
		{
			if (!function_exists('group_user_add'))
			{
				include($this->root_path . 'includes/functions_user.' . $this->php_ext);
			}

			// add the user to the donors group and set as default.
			group_user_add($group_id, array($payer_id), array($payer_username), get_group_name($group_id), $default_group);
		}
	}

	/**
	 * Checks if all required settings are meet for adding the donor to the group of donors
	 *
	 * @return bool
	 * @access private
	 */
	private function can_use_autogroup()
	{
		return
			$this->autogroup_is_enabled() &&
			$this->donor_is_member &&
			$this->payment_status_is_completed() &&
			$this->minimum_donation_raised();
	}

	/**
	 * Checks if Autogroup could be used
	 *
	 * @return bool
	 * @access private
	 */
	private function autogroup_is_enabled()
	{
		return $this->verified && $this->config['ppde_ipn_enable'] && $this->config['ppde_ipn_autogroup_enable'];
	}

	/**
	 * @return bool
	 */
	private function minimum_donation_raised()
	{
		return (float) $this->payer_data['user_ppde_donated_amount'] >= (float) $this->config['ppde_ipn_min_before_group'] ? true : true;
	}

	/**
	 * Notify donors and admin when the donation is received
	 *
	 * @return void
	 * @access private
	 */
	private function notify_donation_received()
	{
		// Initiate a transaction entity
		/** @type \skouat\ppde\entity\transactions $entity */
		$entity = $this->container->get('skouat.ppde.entity.transactions');

		// Initiate a currency entity
		/** @type \skouat\ppde\entity\currency $currency_entity */
		$currency_entity = $this->container->get('skouat.ppde.entity.currency');

		// Set currency data properties
		$this->currency_settle_data = $this->get_currency_data($currency_entity, $entity->get_settle_currency());
		$this->currency_mc_data = $this->get_currency_data($currency_entity, $entity->get_mc_currency());

		$notification_data = array(
			'net_amount'     => $this->ppde_controller_main->currency_on_left($entity->get_net_amount(), $this->currency_mc_data[0]['currency_symbol'], (bool) $this->currency_mc_data[0]['currency_on_left']),
			'mc_gross'       => $this->ppde_controller_main->currency_on_left($this->transaction_data['mc_gross'], $this->currency_mc_data[0]['currency_symbol'], (bool) $this->currency_mc_data[0]['currency_on_left']),
			'payer_email'    => $this->transaction_data['payer_email'],
			'payer_username' => $entity->get_username(),
			'settle_amount'  => $this->transaction_data['settle_amount'] ? $this->ppde_controller_main->currency_on_left($this->transaction_data['settle_amount'], $this->currency_settle_data[0]['currency_symbol'], (bool) $this->currency_settle_data[0]['currency_on_left']) : '',
			'transaction_id' => $entity->get_id(),
			'txn_id'         => $this->transaction_data['txn_id'],
			'user_from'      => $entity->get_user_id(),
		);

		// Send admin notification
		$this->notification->add_notifications('skouat.ppde.notification.type.admin_donation_received', $notification_data);
		// Send donor notification
		$this->notification->add_notifications('skouat.ppde.notification.type.donor_donation_received', $notification_data);
	}

	/**
	 * Get currency data based on currency ISO code
	 *
	 * @param \skouat\ppde\entity\currency $entity The currency entity object
	 * @param string                       $iso_code
	 *
	 * @return array
	 * @access private
	 */
	private function get_currency_data(\skouat\ppde\entity\currency $entity, $iso_code)
	{
		// Retrieve the currency ID for settle
		$entity->data_exists($entity->build_sql_data_exists($iso_code));

		return $this->ppde_controller_main->get_default_currency_data($entity->get_id());
	}

	/**
	 * Request predefined variables from super global, then fill in the $this->transaction_data array
	 *
	 * @param array $data_ary List of data to request
	 *
	 * @return void
	 */
	private function get_post_data($data_ary = array())
	{
		if (is_array($data_ary['default']))
		{
			$data_ary['value'] = $this->request->variable($data_ary['name'], (string) $data_ary['default'][0], (bool) $data_ary['default'][1]);
		}
		else
		{
			$data_ary['value'] = $this->request->variable($data_ary['name'], $data_ary['default']);
		}

		if ($this->validate_post_data($data_ary) === true)
		{
			$this->transaction_data[$data_ary['name']] = $data_ary['value'];
		}
		else
		{
			// The minimum required checks are not met
			// So we force to log collected data in /store/ext/ppde directory
			$this->ppde_ipn_log->log_error($this->language->lang('INVALID_TXN_ACCOUNT_ID'), true, true, E_USER_NOTICE, $this->transaction_data);
		}
	}

	/**
	 * Check if some settings are valid.
	 *
	 * @param array $data_ary
	 *
	 * @return bool
	 * @access private
	 */
	private function validate_post_data($data_ary = array())
	{
		$check = array();

		// check all conditions declared for this post_data
		if (isset($data_ary['condition_check']))
		{
			$check[] = $this->check_post_data($data_ary);
		}

		return (bool) array_product($check);
	}

	/**
	 * Check if value is true
	 * Return true on success. Otherwise, we send an exit code via log_error()
	 *
	 * @param array $checked_ary
	 *
	 * @return bool
	 * @access private
	 */
	private function check_post_data($checked_ary)
	{
		$result = $this->call_post_data_func($checked_ary);
		if (is_array($result) && !array_product($result))
		{
			$this->ppde_ipn_log->log_error($this->language->lang('INVALID_TXN_' . strtoupper(key($result)), $checked_ary['name']), $this->ppde_ipn_log->is_use_log_error(), true, E_USER_NOTICE, $this->transaction_data);
		}
		else if (!$result)
		{
			$this->ppde_ipn_log->log_error($this->language->lang('INVALID_TXN_INVALID_CHECK'), $this->ppde_ipn_log->is_use_log_error(), true, E_USER_NOTICE, $this->transaction_data);
		}

		return true;
	}

	/**
	 * Check requirements for data value.
	 *
	 * @param array $data_ary
	 *
	 * @access public
	 * @return bool|array
	 */
	public function call_post_data_func($data_ary)
	{
		foreach ($data_ary['condition_check'] as $control_point => $params)
		{
			// Calling the check_post_data_function
			if (call_user_func_array(array($this, 'check_post_data_' . $control_point), array($data_ary['value'], $params)))
			{
				return true;
			}
			else
			{
				return array($control_point => false);
			}
		}
		unset($data_ary, $control_point, $params);

		return false;
	}

	/**
	 * Check Post data length.
	 * Called by $this->check_post_data() method
	 *
	 * @param string $value
	 * @param array  $statement
	 *
	 * @return bool
	 * @access private
	 */
	private function check_post_data_length($value, $statement)
	{
		return $this->ppde_controller_main->compare(strlen($value), $statement['value'], $statement['operator']);
	}

	/**
	 * Check Post data content based on an array list.
	 * Called by $this->check_post_data() method
	 *
	 * @param string $value
	 * @param array  $content_ary
	 *
	 * @return bool
	 * @access private
	 */
	private function check_post_data_content($value,$content_ary)
	{
		return in_array($value, $content_ary) ? true : false;
	}

	/**
	 * Check if Merchant ID set on the extension match with the ID stored in the transaction.
	 *
	 * @return bool
	 * @access private
	 */
	private function check_account_id()
	{
		$account_value = $this->ipn_use_sandbox() ? $this->config['ppde_sandbox_address'] : $this->config['ppde_account_id'];

		if ($this->check_post_data_ascii($account_value))
		{
			return $account_value == $this->transaction_data['receiver_id'];
		}
		else
		{
			return $account_value == $this->transaction_data['receiver_email'];
		}
	}

	/**
	 * Check if Sandbox is enabled based on config value
	 *
	 * @return bool
	 * @access private
	 */
	private function ipn_use_sandbox()
	{
		return $this->ppde_controller_main->use_ipn() && !empty($this->config['ppde_sandbox_enable']);
	}

	/**
	 * Check if parsed value contains only ASCII chars.
	 * Return false if it contains non ASCII chars.
	 *
	 * @param $value
	 *
	 * @return bool
	 * @access private
	 */
	private function check_post_data_ascii($value)
	{
		// we ensure that the value contains only ASCII chars...
		$pos = strspn($value, self::ASCII_RANGE);
		$len = strlen($value);

		return $pos != $len ? false : true;
	}
}
