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

/**
 * @property \phpbb\log\log           log          The phpBB log system
 * @property \phpbb\request\request   request      Request object
 * @property \phpbb\template\template template     Template object
 * @property string                   u_action     Action URL
 * @property \phpbb\user              user         User object
 */

class admin_overview_controller extends admin_main
{
	protected $auth;
	protected $db;
	protected $cache;
	protected $config;
	protected $ppde_controller_main;
	protected $php_ext;
	protected $table_ppde_transactions;

	protected $ext_name;
	protected $ext_meta = array();

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth                        $auth                    Authentication object
	 * @param \phpbb\db\driver\driver_interface       $db                      Database object
	 * @param \phpbb\cache\service                    $cache                   Cache object
	 * @param \phpbb\config\config                    $config                  Config object
	 * @param \phpbb\log\log                          $log                     The phpBB log system
	 * @param \skouat\ppde\controller\main_controller $ppde_controller_main    Main controller object
	 * @param \phpbb\request\request                  $request                 Request object
	 * @param \phpbb\template\template                $template                Template object
	 * @param \phpbb\user                             $user                    User object
	 * @param string                                  $php_ext                 phpEx
	 * @param string                                  $table_ppde_transactions Name of the table used to store data
	 *
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\log\log $log, \skouat\ppde\controller\main_controller $ppde_controller_main, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $php_ext, $table_ppde_transactions)
	{
		$this->auth = $auth;
		$this->db = $db;
		$this->cache = $cache;
		$this->config = $config;
		$this->log = $log;
		$this->ppde_controller_main = $ppde_controller_main;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->php_ext = $php_ext;
		$this->table_ppde_transactions = $table_ppde_transactions;
	}

	/**
	 * Display the overview page
	 *
	 * @param string $action Action name
	 *
	 * @return null
	 * @access public
	 */
	public function display_overview($action)
	{
		$this->ppde_controller_main->first_start();

		$this->do_action($action);

		//Load metadata for this extension
		$this->ext_meta = $this->ppde_controller_main->load_metadata();

		// Check if a new version is available
		$this->obtain_last_version();

		// Set output block vars for display in the template
		$this->template->assign_vars(array(
			'ANONYMOUS_DONORS_COUNT'    => $this->config['ppde_anonymous_donors_count'],
			'ANONYMOUS_DONORS_PER_DAY'  => $this->per_day_stats('ppde_anonymous_donors_count'),
			'INFO_CURL'                 => $this->config['ppde_curl_detected'] ? $this->user->lang('INFO_CURL_VERSION', $this->config['ppde_curl_version'], $this->config['ppde_curl_ssl_version']) : $this->user->lang['INFO_NOT_DETECTED'],
			'INFO_FSOCKOPEN'            => $this->config['ppde_fsock_detected'] ? $this->user->lang['INFO_DETECTED'] : $this->user->lang['INFO_NOT_DETECTED'],
			'KNOWN_DONORS_COUNT'        => $this->config['ppde_known_donors_count'],
			'KNOWN_DONORS_PER_DAY'      => $this->per_day_stats('ppde_known_donors_count'),
			'PPDE_INSTALL_DATE'         => $this->user->format_date($this->config['ppde_install_date']),
			'PPDE_VERSION'              => $this->ext_meta['version'],
			'TRANSACTIONS_COUNT'        => $this->config['ppde_transactions_count'],
			'TRANSACTIONS_PER_DAY'      => $this->per_day_stats('ppde_transactions_count'),

			'L_PPDE_INSTALL_DATE'       => $this->user->lang('PPDE_INSTALL_DATE', $this->ext_meta['extra']['display-name']),
			'L_PPDE_VERSION'            => $this->user->lang('PPDE_VERSION', $this->ext_meta['extra']['display-name']),

			'S_ACTION_OPTIONS'          => ($this->auth->acl_get('a_ppde_manage')) ? true : false,
			'S_CURL'                    => $this->config['ppde_curl_detected'],
			'S_FSOCKOPEN'               => $this->config['ppde_fsock_detected'],

			'U_PPDE_MORE_INFORMATION'   => append_sid("index.$this->php_ext", 'i=acp_extensions&amp;mode=main&amp;action=details&amp;ext_name=' . urlencode($this->ext_meta['name'])),
			'U_PPDE_VERSIONCHECK_FORCE' => $this->u_action . '&amp;versioncheck_force=1',
			'U_ACTION'                  => $this->u_action,
		));
	}

	/**
	 * Do action regarding the value of $action
	 *
	 * @param string $action Requested action
	 *
	 * @return null
	 * @access private
	 */
	private function do_action($action)
	{
		if ($action)
		{
			if (!confirm_box(true))
			{
				$this->display_confirm($action);
			}
			else
			{
				$this->exec_action($action);
			}
		}
	}

	/**
	 * Display confirm box
	 *
	 * @param string $action Requested action
	 *
	 * @return null
	 * @access private
	 */
	private function display_confirm($action)
	{
		switch ($action)
		{
			case 'date':
				$confirm = true;
				$confirm_lang = 'STAT_RESET_DATE_CONFIRM';
				break;
			case 'remote':
				$confirm = true;
				$confirm_lang = 'STAT_RETEST_CURL_FSOCK_CONFIRM';
				break;
			case 'donors':
			case 'transactions':
				$confirm = true;
				$confirm_lang = 'STAT_RESYNC_' . strtoupper($action) . 'COUNTS_CONFIRM';
				break;
			default:
				$confirm = true;
				$confirm_lang = 'CONFIRM_OPERATION';
		}

		if ($confirm)
		{
			confirm_box(false, $this->user->lang[$confirm_lang], build_hidden_fields(array(
				'action' => $action,
			)));
		}
	}

	/**
	 * @param string $action Requested action
	 *
	 * @return null
	 * @access private
	 */
	private function exec_action($action)
	{
		if (!$this->auth->acl_get('a_ppde_manage'))
		{
			trigger_error($this->user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		switch ($action)
		{
			case 'date':
				$this->config->set('ppde_install_date', time() - 1);
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_STAT_RESET_DATE');
				break;
			case 'donors':
				$this->config->set('ppde_known_donors_count', $this->sql_query_update_stats('ppde_known_donors_count'), true);
				$this->config->set('ppde_anonymous_donors_count', $this->sql_query_update_stats('ppde_anonymous_donors_count'));
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_STAT_RESYNC_DONORSCOUNTS');
				break;
			case 'remote':
				$this->ppde_controller_main->set_curl_info();
				$this->ppde_controller_main->set_remote_detected();
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_STAT_RETEST_REMOTE');
				break;
			case 'transactions':
				$this->config->set('ppde_transactions_count', $this->sql_query_update_stats('ppde_transactions_count'), true);
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_STAT_RESYNC_TRANSACTIONSCOUNTS');
				break;
		}
	}

	/**
	 * Returns count result for updating stats
	 *
	 * @param string $config_name
	 *
	 * @return int
	 * @access private
	 */
	private function sql_query_update_stats($config_name)
	{
		if (!$this->config->offsetExists($config_name))
		{
			trigger_error($this->user->lang('EXCEPTION_INVALID_CONFIG_NAME', $config_name), E_USER_WARNING);
		}

		$this->db->sql_query($this->make_stats_sql_update($config_name));

		return (int) $this->db->sql_fetchfield('count_result');
	}

	/**
	 * Build SQL query for updating stats
	 *
	 * @param string $type
	 *
	 * @return string
	 * @access private
	 */
	private function make_stats_sql_update($type)
	{
		switch ($type)
		{
			case 'ppde_transactions_count':
				$sql = $this->make_stats_sql_select('txn_id');
				$sql .= " WHERE confirmed = 1 AND payment_status = 'Completed'";

				return $sql;
			case 'ppde_known_donors_count':
				$sql = $this->make_stats_sql_select('payer_id');
				$sql .= ' LEFT JOIN ' . USERS_TABLE . ' u
				 					ON txn.user_id = u.user_id
								WHERE u.user_type = ' . USER_NORMAL . ' OR u.user_type = ' . USER_FOUNDER;

				return $sql;
			case 'ppde_anonymous_donors_count':
				$sql = $this->make_stats_sql_select('payer_id');
				$sql .= ' WHERE txn.user_id = ' . ANONYMOUS;

				return $sql;
			default:
				return $this->make_stats_sql_select('txn_id');
		}
	}

	/**
	 * Make body of SQL query for stats calculation.
	 *
	 * @param string $field_name Name of the field
	 *
	 * @return string
	 * @access private
	 */
	private function make_stats_sql_select($field_name)
	{
		return 'SELECT COUNT(DISTINCT txn.' . $field_name . ') AS count_result
				FROM ' . $this->table_ppde_transactions . ' txn';
	}

	/**
	 * Obtains the last version for this extension
	 *
	 * @return null
	 * @access private
	 */
	private function obtain_last_version()
	{
		try
		{
			if (!isset($this->ext_meta['extra']['version-check']))
			{
				throw new \RuntimeException($this->user->lang('PPDE_NO_VERSIONCHECK'), 1);
			}

			$version_check = $this->ext_meta['extra']['version-check'];

			$version_helper = new \phpbb\version_helper($this->cache, $this->config, new \phpbb\file_downloader(), $this->user);
			$version_helper->set_current_version($this->ext_meta['version']);
			$version_helper->set_file_location($version_check['host'], $version_check['directory'], $version_check['filename']);
			$version_helper->force_stability($this->config['extension_force_unstable'] ? 'unstable' : null);

			$recheck = $this->request->variable('versioncheck_force', false);
			$s_up_to_date = $version_helper->get_suggested_updates($recheck);

			$this->template->assign_vars(array(
				'S_UP_TO_DATE'   => empty($s_up_to_date),
				'S_VERSIONCHECK' => true,
				'UP_TO_DATE_MSG' => $this->user->lang('PPDE_NOT_UP_TO_DATE', $this->ext_meta['extra']['display-name']),
			));
		}
		catch (\RuntimeException $e)
		{
			$this->template->assign_vars(array(
				'S_VERSIONCHECK_STATUS'    => $e->getCode(),
				'VERSIONCHECK_FAIL_REASON' => ($e->getMessage() !== $this->user->lang('VERSIONCHECK_FAIL')) ? $e->getMessage() : '',
			));
		}
	}

	/**
	 * Returns the percent of items (transactions, donors) per day
	 *
	 * @param string $config_name
	 *
	 * @return string
	 * @access private
	 */
	private function per_day_stats($config_name)
	{
		return sprintf('%.2f', (float) $this->config[$config_name] / $this->get_install_days());
	}

	/**
	 * Returns the number of days from the date of installation of the extension.
	 *
	 * @return float
	 * @access private
	 */
	private function get_install_days()
	{
		return (float) (time() - $this->config['ppde_install_date']) / 86400;
	}
}
