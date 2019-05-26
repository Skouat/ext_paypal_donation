<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller\admin;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\user_loader;
use skouat\ppde\actions\core;
use skouat\ppde\actions\currency;
use skouat\ppde\exception\transaction_exception;
use skouat\ppde\operators\transactions;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @property config             config             Config object
 * @property ContainerInterface container          Service container interface
 * @property string             id_prefix_name     Prefix name for identifier in the URL
 * @property string             lang_key_prefix    Prefix for the messages thrown by exceptions
 * @property language           language           Language user object
 * @property log                log                The phpBB log system.
 * @property string             module_name        Name of the module currently used
 * @property request            request            Request object.
 * @property bool               submit             State of submit $_POST variable
 * @property template           template           Template object
 * @property string             u_action           Action URL
 * @property user               user               User object.
 * @property user_loader        user_loader        User loader object
 */
class transactions_controller extends admin_main
{
	public $ppde_operator;
	protected $adm_relative_path;
	protected $auth;
	protected $user_loader;
	protected $entry_count;
	protected $last_page_offset;
	protected $php_ext;
	protected $phpbb_admin_path;
	protected $phpbb_root_path;
	protected $ppde_actions;
	protected $ppde_actions_currency;
	protected $ppde_entity;
	protected $table_prefix;
	protected $table_ppde_transactions;

	/**
	 * Constructor
	 *
	 * @param auth                             $auth                       Authentication object
	 * @param config                           $config                     Config object
	 * @param ContainerInterface               $container                  Service container interface
	 * @param language                         $language                   Language user object
	 * @param log                              $log                        The phpBB log system
	 * @param core                             $ppde_actions               PPDE actions object
	 * @param currency                         $ppde_actions_currency      PPDE currency actions object
	 * @param \skouat\ppde\entity\transactions $ppde_entity_transactions   Entity object
	 * @param transactions                     $ppde_operator_transactions Operator object
	 * @param request                          $request                    Request object
	 * @param template                         $template                   Template object
	 * @param user                             $user                       User object.
	 * @param user_loader                      $user_loader                User loader object
	 * @param string                           $adm_relative_path          phpBB admin relative path
	 * @param string                           $phpbb_root_path            phpBB root path
	 * @param string                           $php_ext                    phpEx
	 * @param string                           $table_prefix               The table prefix
	 * @param string                           $table_ppde_transactions    Name of the table used to store data
	 *
	 * @access public
	 */
	public function __construct(
		auth $auth,
		config $config,
		ContainerInterface $container,
		language $language,
		log $log,
		core $ppde_actions,
		currency $ppde_actions_currency,
		\skouat\ppde\entity\transactions $ppde_entity_transactions,
		transactions $ppde_operator_transactions,
		request $request,
		template $template,
		user $user,
		user_loader $user_loader,
		$adm_relative_path,
		$phpbb_root_path,
		$php_ext,
		$table_prefix,
		$table_ppde_transactions
	)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->container = $container;
		$this->language = $language;
		$this->log = $log;
		$this->ppde_actions = $ppde_actions;
		$this->ppde_actions_currency = $ppde_actions_currency;
		$this->ppde_entity = $ppde_entity_transactions;
		$this->ppde_operator = $ppde_operator_transactions;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->user_loader = $user_loader;
		$this->adm_relative_path = $adm_relative_path;
		$this->phpbb_admin_path = $phpbb_root_path . $adm_relative_path;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->table_prefix = $table_prefix;
		$this->table_ppde_transactions = $table_ppde_transactions;
		parent::__construct(
			'transactions',
			'PPDE_DT',
			'transaction'
		);
	}

	/**
	 * Display the transactions list
	 *
	 * @param string $id     Module id
	 * @param string $mode   Module categorie
	 * @param string $action Action name
	 *
	 * @return void
	 * @access public
	 */
	public function display_transactions($id, $mode, $action)
	{
		// Set up general vars
		$args = array();
		$start = $this->request->variable('start', 0);
		$deletemark = $this->request->variable('delmarked', false, false, \phpbb\request\request_interface::POST);
		$deleteall = $this->request->variable('delall', false, false, \phpbb\request\request_interface::POST);
		$marked = $this->request->variable('mark', array(0));
		$txn_approve = $this->request->is_set('approve');
		$txn_approved = $this->request->variable('txn_errors_approved', 0);
		$txn_add = $this->request->is_set('add');
		$txn_change = $this->request->is_set_post('change');
		// Sort keys
		$sort_days = $this->request->variable('st', 0);
		$sort_key = $this->request->variable('sk', 't');
		$sort_dir = $this->request->variable('sd', 'd');

		// Prepares args for entries deletion
		if (($deletemark || $deleteall) && $this->auth->acl_get('a_ppde_manage'))
		{
			$action = 'delete';
			$args = array(
				'hidden_fields' => array(
					'start'     => $start,
					'delall'    => $deleteall,
					'delmarked' => $deletemark,
					'mark'      => $marked,
					'st'        => $sort_days,
					'sk'        => $sort_key,
					'sd'        => $sort_dir,
					'i'         => $id,
					'mode'      => $mode,
				),
			);
		}

		if ($txn_approve)
		{
			$transaction_id = $this->request->variable('id', 0);
			$action = 'approve';
			$args = array(
				'hidden_fields' => array(
					'approve'             => true,
					'id'                  => $transaction_id,
					'txn_errors_approved' => $txn_approved,
				),
			);
		}

		if ($txn_add)
		{
			$action = 'add';
		}
		else if ($txn_change)
		{
			$action = 'change';
		}

		$action = $this->do_action($action, $args);

		if (!$action)
		{
			/** @type \phpbb\pagination $pagination */
			$pagination = $this->container->get('pagination');

			// Sorting
			$limit_days = array(0 => $this->language->lang('ALL_ENTRIES'), 1 => $this->language->lang('1_DAY'), 7 => $this->language->lang('7_DAYS'), 14 => $this->language->lang('2_WEEKS'), 30 => $this->language->lang('1_MONTH'), 90 => $this->language->lang('3_MONTHS'), 180 => $this->language->lang('6_MONTHS'), 365 => $this->language->lang('1_YEAR'));
			$sort_by_text = array('txn' => $this->language->lang('PPDE_DT_SORT_TXN_ID'), 'u' => $this->language->lang('PPDE_DT_SORT_DONORS'), 'ipn' => $this->language->lang('PPDE_DT_SORT_IPN_STATUS'), 'ipn_test' => $this->language->lang('PPDE_DT_SORT_IPN_TYPE'), 'ps' => $this->language->lang('PPDE_DT_SORT_PAYMENT_STATUS'), 't' => $this->language->lang('SORT_DATE'));
			$sort_by_sql = array('txn' => 'txn.txn_id', 'u' => 'u.username_clean', 'ipn' => 'txn.confirmed', 'ipn_test' => 'txn.test_ipn', 'ps' => 'txn.payment_status', 't' => 'txn.payment_date');

			$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
			gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

			// Define where and sort sql for use in displaying transactions
			$sql_where = ($sort_days) ? (time() - ($sort_days * 86400)) : 0;
			$sql_sort = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

			$keywords = $this->request->variable('keywords', '', true);
			$keywords_param = !empty($keywords) ? '&amp;keywords=' . urlencode(htmlspecialchars_decode($keywords)) : '';

			// Grab log data
			$log_data = array();
			$log_count = 0;

			$this->view_txn_log($log_data, $log_count, (int) $this->config['topics_per_page'], $start, $sql_where, $sql_sort, $keywords);

			$base_url = $this->u_action . '&amp;' . $u_sort_param . $keywords_param;
			$pagination->generate_template_pagination($base_url, 'pagination', 'start', $log_count, (int) $this->config['topics_per_page'], $start);

			$this->template->assign_vars(array(
				'S_CLEARLOGS'  => $this->auth->acl_get('a_ppde_manage'),
				'S_KEYWORDS'   => $keywords,
				'S_LIMIT_DAYS' => $s_limit_days,
				'S_SORT_KEY'   => $s_sort_key,
				'S_SORT_DIR'   => $s_sort_dir,
				'S_TXN'        => $mode,
				'U_ACTION'     => $this->u_action . '&amp;' . $u_sort_param . $keywords_param . '&amp;start=' . $start,
			));

			array_map(array($this, 'display_log_assign_template_vars'), $log_data);
		}
	}

	/**
	 * Do action regarding the value of $action
	 *
	 * @param string $action Requested action
	 * @param array  $args   Arguments required for the action
	 *
	 * @return string
	 * @access private
	 */
	private function do_action($action, $args)
	{
		switch ($action)
		{
			case 'view':
				// Request Identifier of the transaction
				$transaction_id = $this->request->variable('id', 0);

				// add field username to the table schema needed by entity->import()
				$additional_table_schema = array(
					'item_username'    => array('name' => 'username', 'type' => 'string'),
					'item_user_colour' => array('name' => 'user_colour', 'type' => 'string'),
				);

				// Grab transaction data
				$data_ary = $this->ppde_entity->get_data($this->ppde_operator->build_sql_data($transaction_id), $additional_table_schema);

				array_map(array($this, 'action_assign_template_vars'), $data_ary);

				$this->template->assign_vars(array(
					'U_FIND_USERNAME' => append_sid($this->phpbb_root_path . 'memberlist.' . $this->php_ext, 'mode=searchuser&amp;form=view_transactions&amp;field=username&amp;select_single=true'),
					'U_ACTION'        => $this->u_action,
					'U_BACK'          => $this->u_action,
					'S_VIEW'          => true,
				));
			break;
			case 'delete':
				if (confirm_box(true))
				{
					$where_sql = '';

					if ($args['hidden_fields']['delmarked'] && count($args['hidden_fields']['mark']))
					{
						$where_sql = $this->ppde_operator->build_marked_where_sql($args['hidden_fields']['mark']);
					}

					if ($where_sql || $args['hidden_fields']['delall'])
					{
						$this->ppde_entity->delete(0, '', $where_sql, $args['hidden_fields']['delall']);
						$this->ppde_actions->set_ipn_test_properties(true);
						$this->ppde_actions->update_overview_stats();
						$this->ppde_actions->set_ipn_test_properties(false);
						$this->ppde_actions->update_overview_stats();
						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_PURGED', time());
					}
				}
				else
				{
					confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields($args['hidden_fields']));
				}
				// Clear $action status
				$action = '';
			break;
			case 'approve':
				if (confirm_box(true))
				{
					$transaction_id = (int) $args['hidden_fields']['id'];
					$txn_approved = !empty($args['hidden_fields']['txn_errors_approved']) ? false : true;

					// Update DB record
					$this->ppde_entity->load($transaction_id);
					$this->ppde_entity->set_txn_errors_approved($txn_approved);
					$this->ppde_entity->save(false);

					// Prepare transaction settings before doing actions
					$this->ppde_actions->set_transaction_data($this->ppde_entity->get_data($this->ppde_operator->build_sql_data($transaction_id)));
					$this->ppde_actions->set_ipn_test_properties($this->ppde_entity->get_test_ipn());
					$this->ppde_actions->is_donor_is_member();

					$this->do_transactions_actions(!$this->ppde_actions->get_ipn_test() && $this->ppde_actions->get_donor_is_member());

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_UPDATED', time());
				}
				else
				{
					confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields($args['hidden_fields']));
				}
				// Clear $action status
				$action = '';
			break;
			case 'add':
				$errors = array();

				$transaction_data = $this->request_transaction_vars();

				if ($this->request->is_set_post('submit'))
				{
					try
					{
						$data_ary = $this->build_data_ary($transaction_data);

						$this->ppde_actions->log_to_db($data_ary);

						// Prepare transaction settings before doing actions
						$this->ppde_actions->set_transaction_data($transaction_data);
						$this->ppde_actions->is_donor_is_member();

						$this->do_transactions_actions($this->ppde_actions->get_donor_is_member() && !$transaction_data['MT_ANONYMOUS']);

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_MT_ADDED', time(), array($transaction_data['MT_USERNAME']));
						trigger_error($this->language->lang('PPDE_MT_ADDED') . adm_back_link($this->u_action));
					}
					catch (transaction_exception $e)
					{
						$errors = $e->get_errors();
					}
				}

				$this->ppde_actions_currency->build_currency_select_menu((int) $this->config['ppde_default_currency']);

				$this->s_error_assign_template_vars($errors);

				$this->template->assign_vars($transaction_data);

				$this->template->assign_vars(array(
					'U_ACTION'             => $this->u_action,
					'U_BACK'               => $this->u_action,
					'S_ADD'                => true,
					'ANONYMOUS_USER_ID'    => ANONYMOUS,
					'U_FIND_USERNAME'      => append_sid($this->phpbb_root_path . 'memberlist.' . $this->php_ext, 'mode=searchuser&amp;form=manual_transaction&amp;field=username&amp;select_single=true'),
					'PAYMENT_TIME_FORMATS' => $this->get_payment_time_examples(),
				));
			break;
			case 'change':

				$username = $this->request->variable('username', '', true);

				if ($this->request->is_set('u') && $username === '')
				{
					$user_id = ANONYMOUS;
				}
				else
				{
					$user_id = $this->user_loader->load_user_by_username($username);

					if ($user_id == ANONYMOUS)
					{
						trigger_error($this->language->lang('NO_USER') . adm_back_link($this->u_action), E_USER_WARNING);
					}
				}

				// Request Identifier of the transaction
				$transaction_id = $this->request->variable('id', 0);

				$this->ppde_entity->load($transaction_id);

				if (!$this->ppde_entity->data_exists($this->ppde_entity->build_sql_data_exists()))
				{
					trigger_error($this->language->lang('PPDE_DT_NO_TRANSACTION') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$log_action = $this->ppde_entity
					->set_user_id($user_id)
					->add_edit_data();

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_' . strtoupper($log_action));
				trigger_error($this->language->lang($this->lang_key_prefix . '_' . strtoupper($log_action)) . adm_back_link($this->u_action));
			break;
		}

		return $action;
	}

	/**
	 * Does actions for validated transaction
	 *
	 * @param bool $is_member
	 *
	 * @return void
	 * @access private
	 */
	private function do_transactions_actions($is_member)
	{
		$this->ppde_actions->update_overview_stats();
		$this->ppde_actions->update_raised_amount();

		if ($is_member)
		{
			$this->ppde_actions->update_donor_stats();
			$this->ppde_actions->donors_group_user_add();
			$this->ppde_actions->notification->notify_donor_donation_received();
		}
	}

	/**
	 * Returns requested data from manual transaction form
	 *
	 * @return array
	 * @access private
	 */
	private function request_transaction_vars()
	{
		return array(
			'MT_ANONYMOUS'          => $this->request->is_set('u'),
			'MT_USERNAME'           => $this->request->variable('username', '', true),
			'MT_FIRST_NAME'         => $this->request->variable('first_name', '', true),
			'MT_LAST_NAME'          => $this->request->variable('last_name', '', true),
			'MT_PAYER_EMAIL'        => $this->request->variable('payer_email', '', true),
			'MT_RESIDENCE_COUNTRY'  => $this->request->variable('residence_country', ''),
			'MT_MC_GROSS'           => $this->request->variable('mc_gross', (float) 0),
			'MT_MC_CURRENCY'        => $this->request->variable('mc_currency', ''),
			'MT_MC_FEE'             => $this->request->variable('mc_fee', (float) 0),
			'MT_PAYMENT_DATE_YEAR'  => $this->request->variable('payment_date_year', (int) $this->user->format_date(time(), 'Y')),
			'MT_PAYMENT_DATE_MONTH' => $this->request->variable('payment_date_month', (int) $this->user->format_date(time(), 'n')),
			'MT_PAYMENT_DATE_DAY'   => $this->request->variable('payment_date_day', (int) $this->user->format_date(time(), 'j')),
			'MT_PAYMENT_TIME'       => $this->request->variable('payment_time', $this->user->format_date(time(), 'H:i:s')),
			'MT_MEMO'               => $this->request->variable('memo', '', true),
		);
	}

	/**
	 * Returns a list of valid times that the user can provide in the manual transaction form
	 *
	 * @return array Array of strings representing the current time, each in a different format
	 * @access private
	 */
	private function get_payment_time_examples()
	{
		$formats = array(
			'H:i:s',
			'G:i',
			'h:i:s a',
			'g:i A',
		);

		$examples = array();

		foreach ($formats as $format)
		{
			$examples[] = $this->user->format_date(time(), $format);
		}

		return $examples;
	}

	/**
	 * View log
	 *
	 * @param array  &$log         The result array with the logs
	 * @param mixed  &$log_count   If $log_count is set to false, we will skip counting all entries in the
	 *                             database. Otherwise an integer with the number of total matching entries is returned.
	 * @param int     $limit       Limit the number of entries that are returned
	 * @param int     $offset      Offset when fetching the log entries, f.e. when paginating
	 * @param int     $limit_days
	 * @param string  $sort_by     SQL order option, e.g. 'l.log_time DESC'
	 * @param string  $keywords    Will only return log entries that have the keywords in log_operation or log_data
	 *
	 * @return int Returns the offset of the last valid page, if the specified offset was invalid (too high)
	 * @access private
	 */
	private function view_txn_log(&$log, &$log_count, $limit = 0, $offset = 0, $limit_days = 0, $sort_by = 'txn.payment_date DESC', $keywords = '')
	{
		$count_logs = ($log_count !== false);

		$log = $this->get_logs($count_logs, $limit, $offset, $limit_days, $sort_by, $keywords);
		$log_count = $this->get_log_count();

		return $this->get_valid_offset();
	}

	/**
	 * @param bool   $count_logs
	 * @param int    $limit
	 * @param int    $offset
	 * @param int    $log_time
	 * @param string $sort_by
	 * @param string $keywords
	 *
	 * @return array $log
	 * @access private
	 */
	private function get_logs($count_logs = true, $limit = 0, $offset = 0, $log_time = 0, $sort_by = 'txn.payment_date DESC', $keywords = '')
	{
		$this->entry_count = 0;
		$this->last_page_offset = $offset;
		$url_ary = array();

		if ($this->ppde_entity->is_in_admin() && $this->phpbb_admin_path)
		{
			$url_ary['profile_url'] = append_sid($this->phpbb_admin_path . 'index.' . $this->php_ext, 'i=users&amp;mode=overview');
			$url_ary['txn_url'] = append_sid($this->phpbb_admin_path . 'index.' . $this->php_ext, 'i=-skouat-ppde-acp-ppde_module&amp;mode=transactions');

		}
		else
		{
			$url_ary['profile_url'] = append_sid($this->phpbb_root_path . 'memberlist.' . $this->php_ext, 'mode=viewprofile');
			$url_ary['txn_url'] = '';
		}

		$get_logs_sql_ary = $this->ppde_operator->get_logs_sql_ary($keywords, $sort_by, $log_time);

		if ($count_logs)
		{
			$this->entry_count = $this->ppde_operator->query_sql_count($get_logs_sql_ary, 'txn.transaction_id');

			if ($this->entry_count == 0)
			{
				// Save the queries, because there are no logs to display
				$this->last_page_offset = 0;

				return array();
			}

			// Return the user to the last page that is valid
			while ($this->last_page_offset >= $this->entry_count)
			{
				$this->last_page_offset = max(0, $this->last_page_offset - $limit);
			}
		}

		return $this->ppde_operator->build_log_ary($get_logs_sql_ary, $url_ary, $limit, $this->last_page_offset);
	}

	/**
	 * @return integer
	 */
	public function get_log_count()
	{
		return ($this->entry_count) ? (int) $this->entry_count : 0;
	}

	/**
	 * @return integer
	 */
	public function get_valid_offset()
	{
		return ($this->last_page_offset) ? (int) $this->last_page_offset : 0;
	}

	/**
	 * Prepare data array() before send it to $this->entity
	 *
	 * @param array $transaction_data
	 *
	 * @return array
	 * @throws transaction_exception
	 */
	private function build_data_ary($transaction_data)
	{
		$errors = array();

		if ($this->request->is_set('u') && $transaction_data['MT_USERNAME'] === '')
		{
			$user_id = ANONYMOUS;
		}
		else
		{
			$user_ary = $this->ppde_operator->query_donor_user_data('username', $transaction_data['MT_USERNAME']);

			if ($user_ary)
			{
				$user_id = $user_ary['user_id'];
			}
			else
			{
				$errors[] = $this->language->lang('PPDE_MT_DONOR_NOT_FOUND', $transaction_data['MT_USERNAME']);
			}
		}

		if ($transaction_data['MT_MC_GROSS'] <= 0)
		{
			$errors[] = $this->language->lang('PPDE_MT_MC_GROSS_TOO_LOW');
		}

		if ($transaction_data['MT_MC_FEE'] < 0)
		{
			$errors[] = $this->language->lang('PPDE_MT_MC_FEE_NEGATIVE');
		}

		if ($transaction_data['MT_MC_FEE'] >= $transaction_data['MT_MC_GROSS'])
		{
			$errors[] = $this->language->lang('PPDE_MT_MC_FEE_TOO_HIGH');
		}

		$payment_date = implode('-', [
			$transaction_data['MT_PAYMENT_DATE_YEAR'],
			$transaction_data['MT_PAYMENT_DATE_MONTH'],
			$transaction_data['MT_PAYMENT_DATE_DAY'],
		]);

		$payment_date_timestamp_at_midnight = $this->user->get_timestamp_from_format('Y-m-d H:i:s', $payment_date . ' 00:00:00');

		if ($payment_date_timestamp_at_midnight === false)
		{
			$errors[] = $this->language->lang('PPDE_MT_PAYMENT_DATE_ERROR', $payment_date);
		}

		$payment_time = $transaction_data['MT_PAYMENT_TIME'];
		$payment_time_timestamp = strtotime($payment_time);

		if ($payment_time_timestamp === false)
		{
			$errors[] = $this->language->lang('PPDE_MT_PAYMENT_TIME_ERROR', $payment_time);
		}

		// Normalize payment time to start from today at midnight
		$payment_time_timestamp_from_midnight = $payment_time_timestamp - strtotime('00:00:00');

		$payment_date_time = $payment_date_timestamp_at_midnight + $payment_time_timestamp_from_midnight;

		if ($payment_date_time > time())
		{
			$errors[] = $this->language->lang('PPDE_MT_PAYMENT_DATE_FUTURE', $this->user->format_date($payment_date_time));
		}

		if ($errors)
		{
			throw (new transaction_exception())->set_errors($errors);
		}

		return array(
			'business'          => $this->config['ppde_account_id'],
			'confirmed'         => true,
			'exchange_rate'     => '',
			'first_name'        => $transaction_data['MT_FIRST_NAME'],
			'item_name'         => '',
			'item_number'       => implode('_', ['uid', $user_id, time()]),
			'last_name'         => $transaction_data['MT_LAST_NAME'],
			'mc_currency'       => $transaction_data['MT_MC_CURRENCY'],
			'mc_gross'          => $transaction_data['MT_MC_GROSS'],
			'mc_fee'            => $transaction_data['MT_MC_FEE'],
			'net_amount'        => (float) 0, // This value is calculated in core_actions:log_to_db()
			'parent_txn_id'     => '',
			'payer_email'       => $transaction_data['MT_PAYER_EMAIL'],
			'payer_id'          => '',
			'payer_status'      => '',
			'payment_date'      => $payment_date_time,
			'payment_status'    => 'Completed',
			'payment_type'      => '',
			'memo'              => $transaction_data['MT_MEMO'],
			'receiver_id'       => '',
			'receiver_email'    => '',
			'residence_country' => strtoupper($transaction_data['MT_RESIDENCE_COUNTRY']),
			'settle_amount'     => (float) 0,
			'settle_currency'   => '',
			'test_ipn'          => false,
			'txn_errors'        => '',
			'txn_id'            => 'PPDE' . gen_rand_string(13),
			'txn_type'          => 'ppde_manual_donation',
			'user_id'           => $user_id,
		);
	}

	/**
	 * Set log output vars for display in the template
	 *
	 * @param array $row
	 *
	 * @return void
	 * @access protected
	 */
	protected function display_log_assign_template_vars($row)
	{
		$this->template->assign_block_vars('log', array(
			'CONFIRMED'        => ($row['confirmed']) ? $this->language->lang('PPDE_DT_VERIFIED') : $this->language->lang('PPDE_DT_UNVERIFIED'),
			'DATE'             => $this->user->format_date($row['payment_date']),
			'ID'               => $row['transaction_id'],
			'PAYMENT_STATUS'   => $this->language->lang(array('PPDE_DT_PAYMENT_STATUS_VALUES', strtolower($row['payment_status']))),
			'TNX_ID'           => $row['txn_id'],
			'USERNAME'         => $row['username_full'],
			'S_CONFIRMED'      => (bool) $row['confirmed'],
			'S_PAYMENT_STATUS' => (strtolower($row['payment_status']) === 'completed') ? true : false,
			'S_TXN_ERRORS'     => !empty($row['txn_errors']),
			'S_TEST_IPN'       => (bool) $row['test_ipn'],
		));
	}

	/**
	 * Set output vars for display in the template
	 *
	 * @param array $data
	 *
	 * @return void
	 * @access protected
	 */
	protected function action_assign_template_vars($data)
	{
		$s_hidden_fields = build_hidden_fields(array(
			'id'                  => $data['transaction_id'],
			'txn_errors_approved' => $data['txn_errors_approved'],
		));

		$currency_mc_data = $this->ppde_actions_currency->get_currency_data($data['mc_currency']);
		$currency_settle_data = $this->ppde_actions_currency->get_currency_data($data['settle_currency']);

		$this->template->assign_vars(array(
			'BOARD_USERNAME' => get_username_string('full', $data['user_id'], $data['username'], $data['user_colour'], $this->language->lang('GUEST'), append_sid($this->phpbb_admin_path . 'index.' . $this->php_ext, 'i=users&amp;mode=overview')),
			'EXCHANGE_RATE'  => '1 ' . $data['mc_currency'] . ' = ' . $data['exchange_rate'] . ' ' . $data['settle_currency'],
			'ITEM_NAME'      => $data['item_name'],
			'ITEM_NUMBER'    => $data['item_number'],
			'MC_GROSS'       => $this->ppde_actions_currency->format_currency($data['mc_gross'], $currency_mc_data[0]['currency_iso_code'], $currency_mc_data[0]['currency_symbol'], (bool) $currency_mc_data[0]['currency_on_left']),
			'MC_FEE'         => $this->ppde_actions_currency->format_currency($data['mc_fee'], $currency_mc_data[0]['currency_iso_code'], $currency_mc_data[0]['currency_symbol'], (bool) $currency_mc_data[0]['currency_on_left']),
			'MC_NET'         => $this->ppde_actions_currency->format_currency($data['net_amount'], $currency_mc_data[0]['currency_iso_code'], $currency_mc_data[0]['currency_symbol'], (bool) $currency_mc_data[0]['currency_on_left']),
			'MEMO'           => $data['memo'],
			'NAME'           => $data['first_name'] . ' ' . $data['last_name'],
			'PAYER_EMAIL'    => $data['payer_email'],
			'PAYER_ID'       => $data['payer_id'],
			'PAYER_STATUS'   => $data['payer_status'] ? $this->language->lang('PPDE_DT_VERIFIED') : $this->language->lang('PPDE_DT_UNVERIFIED'),
			'PAYMENT_DATE'   => $this->user->format_date($data['payment_date']),
			'PAYMENT_STATUS' => $this->language->lang(array('PPDE_DT_PAYMENT_STATUS_VALUES', strtolower($data['payment_status']))),
			'RECEIVER_EMAIL' => $data['receiver_email'],
			'RECEIVER_ID'    => $data['receiver_id'],
			'SETTLE_AMOUNT'  => $this->ppde_actions_currency->format_currency($data['settle_amount'], $currency_settle_data[0]['currency_iso_code'], $currency_settle_data[0]['currency_symbol'], (bool) $currency_settle_data[0]['currency_on_left']),
			'TXN_ID'         => $data['txn_id'],

			'L_PPDE_DT_SETTLE_AMOUNT'         => $this->language->lang('PPDE_DT_SETTLE_AMOUNT', $data['settle_currency']),
			'L_PPDE_DT_EXCHANGE_RATE_EXPLAIN' => $this->language->lang('PPDE_DT_EXCHANGE_RATE_EXPLAIN', $this->user->format_date($data['payment_date'])),
			'S_CONVERT'                       => ($data['settle_amount'] == 0 && empty($data['exchange_rate'])) ? false : true,
			'S_ERROR'                         => !empty($data['txn_errors']),
			'S_ERROR_APPROVED'                => !empty($data['txn_errors_approved']),
			'S_HIDDEN_FIELDS'                 => $s_hidden_fields,
			'ERROR_MSG'                       => (!empty($data['txn_errors'])) ? $data['txn_errors'] : '',
		));
	}
}
