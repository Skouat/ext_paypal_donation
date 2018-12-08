<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller;

use phpbb\config\config;
use phpbb\event\dispatcher_interface;
use phpbb\language\language;
use phpbb\path_helper;

class core_actions
{
	/** @var array */
	private static $operators_table = array(
		'<'  => 'compare_lt',
		'<=' => 'compare_lte',
		'==' => 'compare_eq',
		'>=' => 'compare_gte',
		'>'  => 'compare_gt',
	);

	/**
	 * Services properties declaration
	 */
	public $notification;
	protected $config;
	protected $dispatcher;
	protected $language;
	protected $path_helper;
	protected $php_ext;
	protected $ppde_entity_transaction;
	protected $ppde_operator_transaction;
	protected $transaction_data;

	/**
	 * @var boolean
	 */
	private $donor_is_member = false;
	/**
	 * @var boolean
	 */
	private $is_ipn_test = false;
	/**
	 * @var array
	 */
	private $payer_data;
	/**
	 * phpBB root path
	 *
	 * @var string
	 */
	private $root_path;
	/**
	 * @var string
	 */
	private $suffix_ipn;

	/**
	 * Constructor
	 *
	 * @param config                              $config                    Config object
	 * @param language                            $language                  Language user object
	 * @param \skouat\ppde\notification\core      $notification              PPDE Notification object
	 * @param path_helper                         $path_helper               Path helper object
	 * @param \skouat\ppde\entity\transactions    $ppde_entity_transaction   Transaction entity object
	 * @param \skouat\ppde\operators\transactions $ppde_operator_transaction Transaction operator object
	 * @param dispatcher_interface                $dispatcher                Dispatcher object
	 * @param string                              $php_ext                   phpEx
	 *
	 * @access public
	 */
	public function __construct(config $config, language $language, \skouat\ppde\notification\core $notification, path_helper $path_helper, \skouat\ppde\entity\transactions $ppde_entity_transaction, \skouat\ppde\operators\transactions $ppde_operator_transaction, dispatcher_interface $dispatcher, $php_ext)
	{
		$this->config = $config;
		$this->dispatcher = $dispatcher;
		$this->language = $language;
		$this->notification = $notification;
		$this->path_helper = $path_helper;
		$this->ppde_entity_transaction = $ppde_entity_transaction;
		$this->ppde_operator_transaction = $ppde_operator_transaction;
		$this->php_ext = $php_ext;

		$this->root_path = $this->path_helper->get_phpbb_root_path();
	}

	/**
	 * Sets properties related to ipn tests
	 *
	 * @param bool $ipn_test
	 *
	 * @return void
	 * @access public
	 */
	public function set_ipn_test_properties($ipn_test)
	{
		$this->set_ipn_test($ipn_test);
		$this->set_suffix_ipn($this->is_ipn_test);
	}

	/**
	 * Sets the property $this->is_ipn_test
	 *
	 * @param bool $ipn_test
	 *
	 * @return void
	 * @access private
	 */
	private function set_ipn_test($ipn_test)
	{
		$this->is_ipn_test = $ipn_test ? (bool) $ipn_test : false;
	}

	/**
	 * Sets the property $this->suffix_ipn
	 *
	 * @param bool $is_ipn_test
	 *
	 * @return void
	 * @access private
	 */
	private function set_suffix_ipn($is_ipn_test)
	{
		$this->suffix_ipn = $is_ipn_test ? '_ipn' : '';
	}

	/**
	 * @return string
	 */
	public function get_suffix_ipn()
	{
		return ($this->get_ipn_test()) ? $this->suffix_ipn : '';
	}

	/**
	 * @return boolean
	 */
	public function get_ipn_test()
	{
		return ($this->is_ipn_test) ? (bool) $this->is_ipn_test : false;
	}

	/**
	 * Updates the amount of donation raised
	 *
	 * @return void
	 * @access public
	 */
	public function update_raised_amount()
	{
		$this->config->set('ppde_raised' . $this->suffix_ipn, (float) $this->config['ppde_raised' . $this->suffix_ipn] + (float) $this->net_amount($this->transaction_data['mc_gross'], $this->transaction_data['mc_fee']), true);
	}

	/**
	 * Returns the net amount of a donation
	 *
	 * @param float  $amount
	 * @param float  $fee
	 * @param string $dec_point
	 * @param string $thousands_sep
	 *
	 * @return string
	 * @access public
	 */
	public function net_amount($amount, $fee, $dec_point = '.', $thousands_sep = '')
	{
		return number_format((float) $amount - (float) $fee, 2, $dec_point, $thousands_sep);
	}

	/**
	 * Updates the Overview module statistics
	 *
	 * @return void
	 * @access public
	 */
	public function update_overview_stats()
	{
		$this->config->set('ppde_anonymous_donors_count' . $this->suffix_ipn, $this->get_count_result('ppde_anonymous_donors_count' . $this->suffix_ipn));
		$this->config->set('ppde_known_donors_count' . $this->suffix_ipn, $this->get_count_result('ppde_known_donors_count' . $this->suffix_ipn), true);
		$this->config->set('ppde_transactions_count' . $this->suffix_ipn, $this->get_count_result('ppde_transactions_count' . $this->suffix_ipn), true);
	}

	/**
	 * Returns count result for updating stats
	 *
	 * @param string $config_name
	 *
	 * @return int
	 * @access private
	 */
	private function get_count_result($config_name)
	{
		if (!$this->config->offsetExists($config_name))
		{
			trigger_error($this->language->lang('EXCEPTION_INVALID_CONFIG_NAME', $config_name), E_USER_WARNING);
		}

		return $this->ppde_operator_transaction->sql_query_count_result($config_name, $this->is_ipn_test);
	}

	/**
	 * Checks if the donor is a member then gets payer_data values
	 *
	 * @return void
	 * @access public
	 */

	public function is_donor_is_member()
	{
		$anonymous_user = false;

		// If the user_id is not anonymous
		if ($this->transaction_data['user_id'] != ANONYMOUS)
		{
			$this->donor_is_member = $this->check_donors_status('user', $this->transaction_data['user_id']);

			if (!$this->donor_is_member)
			{
				// No results, therefore the user is anonymous...
				$anonymous_user = true;
			}
		}
		else
		{
			// The user is anonymous by default
			$anonymous_user = true;
		}

		if ($anonymous_user)
		{
			// If the user is anonymous, check their PayPal email address with all known email hashes
			// to determine if the user exists in the database with that email
			$this->donor_is_member = $this->check_donors_status('email', $this->transaction_data['payer_email']);
		}
	}

	/**
	 * @return boolean
	 */
	public function get_donor_is_member()
	{
		return ($this->donor_is_member) ? (bool) $this->donor_is_member : false;
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
		$this->payer_data = $this->ppde_operator_transaction->query_donor_user_data($type, $args);

		return count($this->payer_data) == 0;
	}

	/**
	 * @return array
	 */
	public function get_payer_data()
	{
		return (count($this->payer_data) != 0) ? $this->payer_data : array();
	}

	/**
	 * Updates donor member stats
	 *
	 * @return void
	 * @access public
	 */
	public function update_donor_stats()
	{
		if ($this->donor_is_member)
		{
			$this->update_user_stats((int) $this->payer_data['user_id'], (float) $this->payer_data['user_ppde_donated_amount'] + (float) $this->net_amount($this->transaction_data['mc_gross'], $this->transaction_data['mc_fee']));
		}
	}

	/**
	 * @param int   $user_id
	 * @param float $amount
	 */
	public function update_user_stats($user_id, $amount)
	{
		if (!$user_id)
		{
			trigger_error($this->language->lang('EXCEPTION_INVALID_USER_ID', $user_id), E_USER_WARNING);
		}

		$this->ppde_operator_transaction->sql_update_user_stats($user_id, $amount);
	}

	/**
	 * Add donor to the donors group
	 *
	 * @return void
	 * @access public
	 */
	public function donors_group_user_add()
	{
		// We add the user to the donors group
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

			// Adds the user to the donors group and set as default.
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
		return $this->config['ppde_ipn_enable'] && $this->config['ppde_ipn_autogroup_enable'];
	}

	/**
	 * Checks if payment_status is completed
	 *
	 * @return bool
	 * @access public
	 */
	public function payment_status_is_completed()
	{
		return $this->transaction_data['payment_status'] === 'Completed';
	}

	/**
	 * Checks if member's donation is upper or equal to the minimum defined
	 *
	 * @return bool
	 * @access public
	 */
	public function minimum_donation_raised()
	{
		return (float) $this->payer_data['user_ppde_donated_amount'] >= (float) $this->config['ppde_ipn_min_before_group'] ? true : false;
	}

	/**
	 * Log the transaction to the database
	 *
	 * @param array $data Transaction data array
	 *
	 * @access public
	 */
	public function log_to_db($data)
	{
		// Set the property $this->transaction_data
		$this->set_transaction_data($data);

		// The item number contains the user_id
		$this->extract_item_number_data();
		$this->validate_user_id();

		// Set username in extra_data property in $entity
		$user_ary = $this->ppde_operator_transaction->query_donor_user_data('user', $this->transaction_data['user_id']);
		$this->ppde_entity_transaction->set_username($user_ary['username']);

		// Set 'net_amount' in $this->transaction_data
		$this->transaction_data['net_amount'] = $this->net_amount($this->transaction_data['mc_gross'], $this->transaction_data['mc_fee']);

		// List the data to be thrown into the database
		$data = $this->ppde_operator_transaction->build_data_ary($this->transaction_data);

		// Load data in the entity
		$this->ppde_entity_transaction->set_entity_data($data);
		$this->ppde_entity_transaction->set_id($this->ppde_entity_transaction->transaction_exists());

		// Add or edit transaction data
		$this->ppde_entity_transaction->add_edit_data();
	}

	/**
	 * Set Transaction Data array
	 *
	 * @param array $transaction_data Array of the donation transaction.
	 *
	 * @return void
	 * @access public
	 */
	public function set_transaction_data($transaction_data)
	{
		if (!empty($this->transaction_data))
		{
			array_merge($this->transaction_data, $transaction_data);
		}
		else
		{
			$this->transaction_data = $transaction_data;
		}
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
	 * Compare two value
	 *
	 * @param int    $value1
	 * @param int    $value2
	 * @param string $operator
	 *
	 * @return bool
	 * @access public
	 */
	public function compare($value1, $value2, $operator)
	{
		if (array_key_exists($operator, self::$operators_table))
		{
			return call_user_func_array(array($this, self::$operators_table[$operator]), array($value1, $value2));
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method called by $this->compare
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return bool
	 * @access private
	 */
	private function compare_lt($a, $b)
	{
		return $a < $b;
	}

	/**
	 * Method called by $this->compare
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return bool
	 * @access private
	 */
	private function compare_lte($a, $b)
	{
		return $a <= $b;
	}

	/**
	 * Method called by $this->compare
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return bool
	 * @access private
	 */
	private function compare_eq($a, $b)
	{
		return $a == $b;
	}

	/**
	 * Method called by $this->compare
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return bool
	 * @access private
	 */
	private function compare_gte($a, $b)
	{
		return $a >= $b;
	}

	/**
	 * Method called by $this->compare
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return bool
	 * @access private
	 */
	private function compare_gt($a, $b)
	{
		return $a > $b;
	}
}
