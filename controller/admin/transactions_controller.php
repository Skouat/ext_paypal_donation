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

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use skouat\ppde\actions\core;
use skouat\ppde\actions\currency;
use skouat\ppde\exception\transaction_exception;
use skouat\ppde\includes\transaction_template_helper;
use skouat\ppde\includes\transaction_validator;
use skouat\ppde\operators\transactions;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @property array              args               Array of args for hidden fields
 * @property config             config             Config object
 * @property ContainerInterface container          Service container interface
 * @property string             id_prefix_name     Prefix name for identifier in the URL
 * @property string             lang_key_prefix    Prefix for the messages thrown by exceptions
 * @property language           language           Language user object
 * @property log                log                The phpBB log system
 * @property request            request            Request object
 * @property template           template           Template object
 * @property string             u_action           Action URL
 * @property user               user               User object
 */
class transactions_controller extends admin_main
{
	protected $transactions_operator;
	protected $auth;
	protected $entry_count;
	protected $last_page_offset;
	protected $php_ext;
	protected $phpbb_admin_path;
	protected $phpbb_root_path;
	protected $ppde_actions;
	protected $ppde_actions_currency;
	protected $transactions_entity;
	protected $template_helper;
	protected $transaction_validator;

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
	 * @param user                             $user                       User object
	 * @param string                           $adm_relative_path          phpBB admin relative path
	 * @param string                           $phpbb_root_path            phpBB root path
	 * @param string                           $php_ext                    phpEx
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
		transaction_template_helper $template_helper,
		transaction_validator $transaction_validator,
		string $adm_relative_path,
		string $phpbb_root_path,
		string $php_ext
	)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->container = $container;
		$this->language = $language;
		$this->log = $log;
		$this->ppde_actions = $ppde_actions;
		$this->ppde_actions_currency = $ppde_actions_currency;
		$this->request = $request;
		$this->template = $template;
		$this->transactions_entity = $ppde_entity_transactions;
		$this->transactions_operator = $ppde_operator_transactions;
		$this->user = $user;
		$this->template_helper = $template_helper;
		$this->transaction_validator = $transaction_validator;
		$this->phpbb_admin_path = $phpbb_root_path . $adm_relative_path;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function display(): void
	{
		// Sorting and pagination setup
		$sort_by_text = $this->get_sort_by_text_options();
		$sort_by_sql = $this->transactions_operator->get_sort_options();
		$sort_key = $this->request->variable('sk', 't');
		$sort_dir = $this->request->variable('sd', 'd');
		$start = $this->request->variable('start', 0);
		$limit = (int) $this->config['topics_per_page'];

		// Filtering setup
		$limit_days = $this->get_limit_day_options();
		$selected_days = $this->request->variable('st', 0);
		$keywords = $this->request->variable('keywords', '', true);

		// Generate sorting and filtering selects
		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $selected_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		// Prepare SQL conditions
		$sql_sort = $sort_by_sql[$sort_key] . ' ' . (($sort_dir === 'd') ? 'DESC' : 'ASC');

		// Fetch log data
		$log_data = [];
		$log_count = 0;
		$log_time = $this->calculate_timestamp($selected_days);
		$this->view_txn_log($log_data, $log_count, $limit, $start, $log_time, $sql_sort, $keywords);

		// Generate pagination
		$this->generate_pagination($log_count, $limit, $start, $u_sort_param, $keywords);

		// Assign template variables
		$this->assign_template_vars($s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param, $keywords, $start);

		// Assign log entries to template
		$this->assign_log_entries_to_template($log_data);
	}

	/**
	 * Get sort by text options for transactions
	 *
	 * @return array An associative array of sort options and their corresponding language strings
	 */
	private function get_sort_by_text_options(): array
	{
		return [
			'txn'      => $this->language->lang('PPDE_DT_SORT_TXN_ID'),
			'u'        => $this->language->lang('PPDE_DT_SORT_DONORS'),
			'ipn'      => $this->language->lang('PPDE_DT_SORT_IPN_STATUS'),
			'ipn_test' => $this->language->lang('PPDE_DT_SORT_IPN_TYPE'),
			'ps'       => $this->language->lang('PPDE_DT_SORT_PAYMENT_STATUS'),
			't'        => $this->language->lang('SORT_DATE'),
		];
	}

	/**
	 * Get limit day options for filtering
	 *
	 * @return array An associative array of day limits and their corresponding language strings
	 */
	private function get_limit_day_options(): array
	{
		return [
			0   => $this->language->lang('ALL_ENTRIES'),
			1   => $this->language->lang('1_DAY'),
			7   => $this->language->lang('7_DAYS'),
			14  => $this->language->lang('2_WEEKS'),
			30  => $this->language->lang('1_MONTH'),
			90  => $this->language->lang('3_MONTHS'),
			180 => $this->language->lang('6_MONTHS'),
			365 => $this->language->lang('1_YEAR'),
		];
	}

	/**
	 * Calculate the timestamp for filtering transactions based on the selected number of days
	 *
	 * @param int $selected_days Number of days to look back for transactions
	 * @return int The calculated timestamp, or 0 if no day limit is set
	 */
	private function calculate_timestamp(int $selected_days): int
	{
		return $selected_days > 0 ? time() - ($selected_days * self::SECONDS_IN_A_DAY) : 0;
	}

	/**
	 * View transaction log
	 *
	 * @param array  &$log       The result array with the logs
	 * @param int    &$log_count If $log_count is set to false, we will skip counting all entries in the database
	 *                           Otherwise an integer with the number of total matching entries is returned
	 * @param int     $limit     Limit the number of entries that are returned
	 * @param int     $offset    Offset when fetching the log entries, e.g. when paginating
	 * @param int     $log_time  Timestamp to filter logs
	 * @param string  $sort_by   SQL order option, e.g. 'l.log_time DESC'
	 * @param string  $keywords  Will only return log entries that have the keywords in log_operation or log_data
	 * @return void
	 */
	private function view_txn_log(array &$log, &$log_count, int $limit = 0, int $offset = 0, int $log_time = 0, string $sort_by = 'txn.payment_date DESC', string $keywords = ''): void
	{
		$count_logs = ($log_count !== false);

		$log = $this->get_logs($count_logs, $limit, $offset, $log_time, $sort_by, $keywords);
		$log_count = $this->get_log_count();
	}

	/**
	 * Get logs based on specified parameters
	 *
	 * @param bool   $count_logs Whether to count the total number of logs
	 * @param int    $limit      Maximum number of logs to retrieve
	 * @param int    $offset     Starting point for retrieving logs
	 * @param int    $log_time   Timestamp to filter logs
	 * @param string $sort_by    SQL ORDER BY clause
	 * @param string $keywords   Keywords to filter logs
	 * @return array Array of log entries
	 */
	private function get_logs(bool $count_logs = true, int $limit = 0, int $offset = 0, int $log_time = 0, string $sort_by = 'txn.payment_date DESC', string $keywords = ''): array
	{
		$this->entry_count = 0;
		$this->last_page_offset = $offset;
		$url_ary = [];

		if ($this->phpbb_admin_path && $this->ppde_actions->is_in_admin())
		{
			$url_ary['profile_url'] = append_sid($this->phpbb_admin_path . 'index.' . $this->php_ext, 'i=users&amp;mode=overview');
			$url_ary['txn_url'] = append_sid($this->phpbb_admin_path . 'index.' . $this->php_ext, 'i=-skouat-ppde-acp-ppde_module&amp;mode=transactions');
		}
		else
		{
			$url_ary['profile_url'] = append_sid($this->phpbb_root_path . 'memberlist.' . $this->php_ext, 'mode=viewprofile');
			$url_ary['txn_url'] = '';
		}

		$get_logs_sql_ary = $this->transactions_operator->get_logs_sql_ary($keywords, $sort_by, $log_time);

		if ($count_logs)
		{
			$this->entry_count = $this->transactions_operator->query_sql_count($get_logs_sql_ary, 'txn.transaction_id');

			if ($this->entry_count === 0)
			{
				// Save the queries, because there are no logs to display
				$this->last_page_offset = 0;

				return [];
			}

			// Return the user to the last page that is valid
			while ($this->last_page_offset >= $this->entry_count)
			{
				$this->last_page_offset = max(0, $this->last_page_offset - $limit);
			}
		}

		return $this->transactions_operator->build_log_entries($get_logs_sql_ary, $url_ary, $limit, $this->last_page_offset);
	}

	/**
	 * Get the total count of log entries
	 *
	 * @return int The total number of log entries
	 */
	public function get_log_count(): int
	{
		return (int) $this->entry_count ?: 0;
	}

	/**
	 * Generate pagination for transaction list
	 *
	 * @param int    $log_count    Total number of log entries
	 * @param int    $limit        Number of entries per page
	 * @param int    $start        Starting offset for the current page
	 * @param string $u_sort_param URL parameters for sorting
	 * @param string $keywords     Search keywords
	 */
	private function generate_pagination(int $log_count, int $limit, int $start, string $u_sort_param, string $keywords): void
	{
		$pagination = $this->container->get('pagination');
		$base_url = $this->u_action . '&amp;' . $u_sort_param . $this->get_keywords_param($keywords);
		$pagination->generate_template_pagination($base_url, 'pagination', 'start', $log_count, $limit, $start);
	}

	/**
	 * Get keywords parameter for URL
	 *
	 * @param string $keywords Search keywords
	 * @return string URL-encoded keywords parameter
	 */
	private function get_keywords_param(string $keywords): string
	{
		return !empty($keywords) ? '&amp;keywords=' . urlencode(htmlspecialchars_decode($keywords)) : '';
	}

	/**
	 * Assign common template variables
	 *
	 * @param string $s_limit_days
	 * @param string $s_sort_key
	 * @param string $s_sort_dir
	 * @param string $u_sort_param
	 * @param string $keywords
	 * @param int    $start
	 */
	private function assign_template_vars(string $s_limit_days, string $s_sort_key, string $s_sort_dir, string $u_sort_param, string $keywords, int $start): void
	{
		$this->template->assign_vars([
			'S_CLEARLOGS'  => $this->auth->acl_get('a_ppde_manage'),
			'S_KEYWORDS'   => $keywords,
			'S_LIMIT_DAYS' => $s_limit_days,
			'S_SORT_KEY'   => $s_sort_key,
			'S_SORT_DIR'   => $s_sort_dir,
			'U_ACTION'     => $this->u_action . '&amp;' . $u_sort_param . $this->get_keywords_param($keywords) . '&amp;start=' . $start,
		]);
	}

	/**
	 * Assign log entries to template
	 *
	 * @param array $log_data Array of log entries
	 */
	private function assign_log_entries_to_template(array $log_data): void
	{
		foreach ($log_data as $row)
		{
			$this->template->assign_block_vars('log', [
				'CONFIRMED'        => ($row['confirmed']) ? $this->language->lang('PPDE_DT_VERIFIED') : $this->language->lang('PPDE_DT_UNVERIFIED'),
				'DATE'             => $this->user->format_date($row['payment_date']),
				'ID'               => $row['transaction_id'],
				'PAYMENT_STATUS'   => $this->language->lang(['PPDE_DT_PAYMENT_STATUS_VALUES', strtolower($row['payment_status'])]),
				'TXN_ID'           => $row['txn_id'],
				'USERNAME'         => $row['username_full'],
				'S_CONFIRMED'      => (bool) $row['confirmed'],
				'S_PAYMENT_STATUS' => strtolower($row['payment_status']) === 'completed',
				'S_TXN_ERRORS'     => !empty($row['txn_errors']),
				'S_TEST_IPN'       => (bool) $row['test_ipn'],
			]);
		}
	}

	/**
	 * Set hidden fields for the transaction form
	 *
	 * @param string $id     Module id
	 * @param string $mode   Module category
	 * @param string $action Action name
	 */
	public function set_hidden_fields($id, $mode, $action): void
	{
		$this->args['action'] = $action;
		$this->args['hidden_fields'] = [
			'start'     => $this->request->variable('start', 0),
			'delall'    => $this->request->variable('delall', false, false, \phpbb\request\request_interface::POST),
			'delmarked' => $this->request->variable('delmarked', false, false, \phpbb\request\request_interface::POST),
			'i'         => $id,
			'mark'      => $this->request->variable('mark', [0]),
			'mode'      => $mode,
			'st'        => $this->request->variable('st', 0),
			'sk'        => $this->request->variable('sk', 't'),
			'sd'        => $this->request->variable('sd', 'd'),
		];

		// Prepare args depending on actions
		if (($this->args['hidden_fields']['delall'] || ($this->args['hidden_fields']['delmarked'] && count($this->args['hidden_fields']['mark']))) && $this->auth->acl_get('a_ppde_manage'))
		{
			$this->args['action'] = 'delete';
		}
		else if ($this->request->is_set('approve'))
		{
			$this->args['action'] = 'approve';
			$this->args['hidden_fields'] = array_merge($this->args['hidden_fields'], [
				'approve'             => true,
				'id'                  => $this->request->variable('id', 0),
				'txn_errors_approved' => $this->request->variable('txn_errors_approved', 0),
			]);
		}
		else if ($this->request->is_set('add'))
		{
			$this->args['action'] = 'add';
		}
		else if ($this->request->is_set_post('change'))
		{
			$this->args['action'] = 'change';
		}
	}

	/**
	 * Get hidden fields for the transaction form
	 *
	 * @return array
	 */
	protected function get_hidden_fields(): array
	{
		return array_merge(
			['i'                           => $this->args['hidden_fields']['i'],
			 'mode'                        => $this->args['hidden_fields']['mode'],
			 'action'                      => $this->args['action'],
			 $this->id_prefix_name . '_id' => $this->args[$this->id_prefix_name . '_id']],
			$this->args['hidden_fields']);
	}

	/**
	 * {@inheritdoc}
	 */
	public function change(): void
	{
		$username = $this->request->variable('username', '', true);
		$donor_id = $this->request->variable('donor_id', 0);
		$transaction_id = $this->request->variable('id', 0);

		try
		{
			$user_id = $this->transaction_validator->validate_user_id($username, $donor_id);
			$this->update_transaction($transaction_id, $user_id);
			$this->log_action('DT_UPDATED');
		}
		catch (transaction_exception $e)
		{
			$this->output_errors($e->get_errors());
		}
	}

	/**
	 * Update transaction with new user ID
	 *
	 * @param int $transaction_id
	 * @param int $user_id
	 * @throws transaction_exception
	 */
	private function update_transaction(int $transaction_id, int $user_id): void
	{
		$this->transactions_entity->load($transaction_id);

		if (!$this->transactions_entity->data_exists($this->transactions_entity->build_sql_data_exists()))
		{
			throw new transaction_exception([$this->language->lang('PPDE_DT_NO_TRANSACTION')]);
		}

		$this->transactions_entity->set_user_id($user_id)->add_edit_data();
	}

	/**
	 * Log the action in the admin log
	 *
	 * @param string $action_type
	 */
	private function log_action(string $action_type): void
	{
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_' . $action_type);
		trigger_error($this->language->lang('PPDE_' . $action_type) . adm_back_link($this->u_action));
	}

	/**
	 * {@inheritdoc}
	 */
	protected function add(): void
	{
		$transaction_data = $this->request_transaction_vars();

		if ($this->is_form_submitted())
		{
			try
			{
				$this->process_transaction($transaction_data);
				$this->log_action('MT_ADDED');
			}
			catch (transaction_exception $e)
			{
				$this->prepare_add_template($e->get_errors(), $transaction_data);
				return;
			}
		}

		$this->prepare_add_template([], $transaction_data);
	}

	/**
	 * Request transaction variables from the form
	 *
	 * @return array
	 */
	private function request_transaction_vars(): array
	{
		return [
			'MT_ANONYMOUS'          => $this->request->is_set('u'),
			'MT_USERNAME'           => $this->request->variable('username', '', true),
			'MT_FIRST_NAME'         => $this->request->variable('first_name', '', true),
			'MT_LAST_NAME'          => $this->request->variable('last_name', '', true),
			'MT_PAYER_EMAIL'        => $this->request->variable('payer_email', '', true),
			'MT_RESIDENCE_COUNTRY'  => $this->request->variable('residence_country', ''),
			'MT_MC_GROSS'           => $this->request->variable('mc_gross', 0.0),
			'MT_MC_CURRENCY'        => $this->request->variable('mc_currency', ''),
			'MT_MC_FEE'             => $this->request->variable('mc_fee', 0.0),
			'MT_PAYMENT_DATE_YEAR'  => $this->request->variable('payment_date_year', (int) $this->user->format_date(time(), 'Y')),
			'MT_PAYMENT_DATE_MONTH' => $this->request->variable('payment_date_month', (int) $this->user->format_date(time(), 'n')),
			'MT_PAYMENT_DATE_DAY'   => $this->request->variable('payment_date_day', (int) $this->user->format_date(time(), 'j')),
			'MT_PAYMENT_TIME'       => $this->request->variable('payment_time', $this->user->format_date(time(), 'H:i:s')),
			'MT_MEMO'               => $this->request->variable('memo', '', true),
		];
	}

	/**
	 * Process a transaction with the given transaction data and handle any errors that occur
	 *
	 * @param array $transaction_data The data for the transaction
	 * @throws transaction_exception
	 */
	private function process_transaction(array $transaction_data): void
	{
		$data_ary = $this->build_data_ary($transaction_data);
		$this->ppde_actions->log_to_db($data_ary);

		$this->ppde_actions->set_transaction_data($transaction_data);
		$this->ppde_actions->is_donor_is_member();

		$this->ppde_actions->do_transactions_actions(
			$this->ppde_actions->get_donor_is_member() && !$transaction_data['MT_ANONYMOUS']
		);
	}

	/**
	 * Prepare data array before sending it to $this->entity
	 *
	 * @param array $transaction_data
	 * @return array
	 * @throws transaction_exception
	 */
	private function build_data_ary(array $transaction_data): array
	{
		$errors = [];

		try
		{
			$user_id = $this->transaction_validator->validate_user_id($transaction_data['MT_USERNAME']);
		}
		catch (transaction_exception $e)
		{
			$errors = array_merge($errors, $e->get_errors());
		}

		try
		{
			$payment_date_time = $this->transaction_validator->validate_payment_date_time($transaction_data);
		}
		catch (transaction_exception $e)
		{
			$errors = array_merge($errors, $e->get_errors());
		}

		try
		{
			$this->transaction_validator->validate_transaction_amounts($transaction_data);
		}
		catch (transaction_exception $e)
		{
			$errors = array_merge($errors, $e->get_errors());
		}

		if (!empty($errors))
		{
			throw new transaction_exception($errors);
		}

		return [
			'business'          => $this->config['ppde_account_id'],
			'confirmed'         => true,
			'custom'            => implode('_', ['uid', $user_id, time()]),
			'exchange_rate'     => '',
			'first_name'        => $transaction_data['MT_FIRST_NAME'],
			'item_name'         => '',
			'item_number'       => implode('_', ['uid', $user_id, time()]),
			'last_name'         => $transaction_data['MT_LAST_NAME'],
			'mc_currency'       => $transaction_data['MT_MC_CURRENCY'],
			'mc_gross'          => $transaction_data['MT_MC_GROSS'],
			'mc_fee'            => $transaction_data['MT_MC_FEE'],
			'net_amount'        => 0.0, // This value is calculated in core_actions:log_to_db()
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
			'settle_amount'     => 0.0,
			'settle_currency'   => '',
			'test_ipn'          => false,
			'txn_errors'        => '',
			'txn_id'            => 'PPDE' . gen_rand_string(13),
			'txn_type'          => 'ppde_manual_donation',
			'user_id'           => $user_id,
		];
	}

	/**
	 * Prepare and assign template variables for adding a new transaction
	 *
	 * @param array $errors           Array of error messages
	 * @param array $transaction_data Transaction data to be displayed in the form
	 */
	private function prepare_add_template(array $errors, array $transaction_data): void
	{
		$this->ppde_actions_currency->build_currency_select_menu((int) $this->config['ppde_default_currency']);
		$this->s_error_assign_template_vars($errors);
		$this->template->assign_vars($transaction_data);
		$this->template->assign_vars([
			'U_ACTION'             => $this->u_action,
			'U_BACK'               => $this->u_action,
			'S_ADD'                => true,
			'ANONYMOUS_USER_ID'    => ANONYMOUS,
			'U_FIND_USERNAME'      => append_sid($this->phpbb_root_path . 'memberlist.' . $this->php_ext, 'mode=searchuser&amp;form=manual_transaction&amp;field=username&amp;select_single=true'),
			'PAYMENT_TIME_FORMATS' => $this->get_payment_time_examples(),
		]);
	}

	/**
	 * Returns a list of valid times that the user can provide in the manual transaction form
	 *
	 * @return array Array of strings representing the current time, each in a different format
	 */
	private function get_payment_time_examples(): array
	{
		$formats = [
			'H:i:s',
			'G:i',
			'h:i:s a',
			'g:i A',
		];

		$examples = [];

		foreach ($formats as $format)
		{
			$examples[] = $this->user->format_date(time(), $format);
		}

		return $examples;
	}

	/**
	 * Output errors
	 *
	 * @param array $errors
	 */
	private function output_errors(array $errors): void
	{
		trigger_error(implode('<br>', $errors) . adm_back_link($this->u_action), E_USER_WARNING);
	}

	/**
	 * Approve a transaction
	 */
	protected function approve(): void
	{
		$transaction_id = (int) $this->args['hidden_fields']['id'];
		$txn_approved = empty($this->args['hidden_fields']['txn_errors_approved']);

		// Update DB record
		$this->transactions_entity->load($transaction_id);
		$this->transactions_entity->set_txn_errors_approved($txn_approved);
		$this->transactions_entity->save(false);

		// Prepare transaction settings before doing actions
		$transaction_data = $this->transactions_entity->get_data($this->transactions_operator->build_sql_data($transaction_id));
		$this->ppde_actions->set_transaction_data($transaction_data[0]);
		$this->ppde_actions->set_ipn_test_properties($this->transactions_entity->get_test_ipn());
		$this->ppde_actions->is_donor_is_member();

		if ($txn_approved)
		{
			$this->ppde_actions->do_transactions_actions(!$this->ppde_actions->get_ipn_test() && $this->ppde_actions->get_donor_is_member());
		}

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_UPDATED', time());
	}

	/**
	 * View transaction details
	 */
	protected function view(): void
	{
		// Request Identifier of the transaction
		$transaction_id = (int) $this->request->variable('id', 0);

		// Add additional fields to the table schema needed by entity->import()
		$additional_table_schema = [
			'item_username'    => ['name' => 'username', 'type' => 'string'],
			'item_user_colour' => ['name' => 'user_colour', 'type' => 'string'],
		];

		// Grab transaction data
		$data_ary = $this->transactions_entity->get_data($this->transactions_operator->build_sql_data($transaction_id), $additional_table_schema);

		array_map([$this, 'action_assign_template_vars'], $data_ary);

		$this->template->assign_vars([
			'U_FIND_USERNAME' => append_sid($this->phpbb_root_path . 'memberlist.' . $this->php_ext, 'mode=searchuser&amp;form=view_transactions&amp;field=username&amp;select_single=true'),
			'U_ACTION'        => $this->u_action,
			'U_BACK'          => $this->u_action,
			'S_VIEW'          => true,
		]);
	}

	/**
	 * Delete transaction(s)
	 */
	protected function delete(): void
	{
		$where_sql = '';

		if ($this->args['hidden_fields']['delmarked'] && count($this->args['hidden_fields']['mark']))
		{
			$where_sql = $this->transactions_operator->build_marked_where_sql($this->args['hidden_fields']['mark']);
		}

		if ($where_sql || $this->args['hidden_fields']['delall'])
		{
			$this->transactions_entity->delete(0, '', $where_sql, $this->args['hidden_fields']['delall']);
			$this->ppde_actions->set_ipn_test_properties(true);
			$this->ppde_actions->update_overview_stats();
			$this->ppde_actions->set_ipn_test_properties(false);
			$this->ppde_actions->update_overview_stats();
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_PURGED', time());
		}
	}

	/**
	 * Assign action template variables
	 *
	 * @param array $data Transaction data
	 */
	protected function action_assign_template_vars(array $data): void
	{
		$this->template_helper->assign_hidden_fields($data);
		$this->template_helper->assign_currency_data($data);
		$this->template_helper->assign_user_data($data);
		$this->template_helper->assign_transaction_details($data);
		$this->template_helper->assign_payment_details($data);
		$this->template_helper->assign_error_data($data);
	}
}
