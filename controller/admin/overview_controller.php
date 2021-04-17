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

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use skouat\ppde\actions\core;
use skouat\ppde\actions\locale_icu;
use skouat\ppde\controller\extension_manager;
use skouat\ppde\controller\ipn_paypal;
use skouat\ppde\controller\main_controller;

/**
 * @property config     config              Config object
 * @property string     id_prefix_name      Prefix name for identifier in the URL
 * @property string     lang_key_prefix     Prefix for the messages thrown by exceptions
 * @property language   language            Language user object
 * @property log        log                 The phpBB log system
 * @property string     module_name         Name of the module currently used
 * @property locale_icu ppde_actions_locale PPDE Locale actions object
 * @property ipn_paypal ppde_ipn_paypal     IPN PayPal object
 * @property request    request             Request object
 * @property template   template            Template object
 * @property string     u_action            Action URL
 * @property user       user                User object
 */
class overview_controller extends admin_main
{
	protected $adm_relative_path;
	protected $auth;
	protected $ppde_actions;
	protected $ppde_controller_main;
	protected $ppde_controller_transactions;
	protected $ppde_ext_manager;
	protected $php_ext;
	protected $phpbb_admin_path;
	protected $phpbb_root_path;

	/**
	 * Constructor
	 *
	 * @param auth                    $auth                         Authentication object
	 * @param config                  $config                       Config object
	 * @param language                $language                     Language user object
	 * @param log                     $log                          The phpBB log system
	 * @param core                    $ppde_actions                 PPDE core actions object
	 * @param locale_icu              $ppde_actions_locale          PPDE Locale actions object
	 * @param main_controller         $ppde_controller_main         Main controller object
	 * @param transactions_controller $ppde_controller_transactions Admin transactions controller object
	 * @param extension_manager       $ppde_ext_manager             Extension manager object
	 * @param ipn_paypal              $ppde_ipn_paypal              IPN PayPal object
	 * @param request                 $request                      Request object
	 * @param template                $template                     Template object
	 * @param user                    $user                         User object
	 * @param string                  $adm_relative_path            phpBB admin relative path
	 * @param string                  $phpbb_root_path              phpBB root path
	 * @param string                  $php_ext                      phpEx
	 *
	 * @access public
	 */
	public function __construct(
		auth $auth,
		config $config,
		language $language,
		log $log,
		core $ppde_actions,
		locale_icu $ppde_actions_locale,
		main_controller $ppde_controller_main,
		transactions_controller $ppde_controller_transactions,
		extension_manager $ppde_ext_manager,
		ipn_paypal $ppde_ipn_paypal,
		request $request,
		template $template,
		user $user,
		$adm_relative_path,
		$phpbb_root_path,
		$php_ext
	)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->language = $language;
		$this->log = $log;
		$this->ppde_actions = $ppde_actions;
		$this->ppde_actions_locale = $ppde_actions_locale;
		$this->ppde_controller_main = $ppde_controller_main;
		$this->ppde_controller_transactions = $ppde_controller_transactions;
		$this->ppde_ext_manager = $ppde_ext_manager;
		$this->ppde_ipn_paypal = $ppde_ipn_paypal;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->php_ext = $php_ext;
		$this->adm_relative_path = $adm_relative_path;
		$this->phpbb_admin_path = $phpbb_root_path . $adm_relative_path;
		$this->phpbb_root_path = $phpbb_root_path;
		parent::__construct(
			'overview',
			'PPDE_',
			''
		);
	}

	/**
	 * Display the overview page
	 *
	 * @param string $action Action name
	 *
	 * @return void
	 * @throws \ReflectionException
	 * @access public
	 */
	public function display_overview($action): void
	{
		$this->ppde_first_start();

		$this->do_action($action);

		//Load metadata for this extension
		$ext_meta = $this->ppde_ext_manager->get_ext_meta();

		// Set output block vars for display in the template
		$this->template->assign_vars([
			'L_PPDE_ESI_INSTALL_DATE'        => $this->language->lang('PPDE_ESI_INSTALL_DATE', $ext_meta['extra']['display-name']),
			'L_PPDE_ESI_VERSION'             => $this->language->lang('PPDE_ESI_VERSION', $ext_meta['extra']['display-name']),
			'PPDE_ESI_INSTALL_DATE'          => $this->user->format_date($this->config['ppde_install_date']),
			'PPDE_ESI_TLS'                   => $this->config['ppde_tls_detected'] ? $this->language->lang('PPDE_ESI_DETECTED') : $this->language->lang('PPDE_ESI_NOT_DETECTED'),
			'PPDE_ESI_VERSION'               => $ext_meta['version'],
			'PPDE_ESI_VERSION_CURL'          => !empty($this->config['ppde_curl_version']) ? $this->config['ppde_curl_version'] : $this->language->lang('PPDE_ESI_NOT_DETECTED'),
			'PPDE_ESI_VERSION_INTL'          => $this->config['ppde_intl_detected'] ? $this->config['ppde_intl_version'] : $this->language->lang('PPDE_ESI_INTL_NOT_DETECTED'),
			'PPDE_ESI_VERSION_SSL'           => !empty($this->config['ppde_curl_ssl_version']) ? $this->config['ppde_curl_ssl_version'] : $this->language->lang('PPDE_ESI_NOT_DETECTED'),
			'S_ACTION_OPTIONS'               => $this->auth->acl_get('a_ppde_manage'),
			'S_CURL'                         => $this->config['ppde_curl_detected'],
			'S_INTL'                         => $this->config['ppde_intl_detected'] && $this->config['ppde_intl_version_valid'],
			'S_SSL'                          => $this->config['ppde_curl_detected'],
			'S_TLS'                          => $this->config['ppde_tls_detected'],
			'STATS_ANONYMOUS_DONORS_COUNT'   => $this->config['ppde_anonymous_donors_count'],
			'STATS_ANONYMOUS_DONORS_PER_DAY' => $this->per_day_stats('ppde_anonymous_donors_count'),
			'STATS_KNOWN_DONORS_COUNT'       => $this->config['ppde_known_donors_count'],
			'STATS_KNOWN_DONORS_PER_DAY'     => $this->per_day_stats('ppde_known_donors_count'),
			'STATS_TRANSACTIONS_COUNT'       => $this->config['ppde_transactions_count'],
			'STATS_TRANSACTIONS_PER_DAY'     => $this->per_day_stats('ppde_transactions_count'),
			'U_PPDE_MORE_INFORMATION'        => append_sid($this->phpbb_admin_path . 'index.' . $this->php_ext, 'i=acp_extensions&amp;mode=main&amp;action=details&amp;ext_name=' . urlencode($ext_meta['name'])),
			'U_ACTION'                       => $this->u_action,
		]);

		if ($this->ppde_controller_main->use_sandbox())
		{
			// Set output block vars for display in the template
			$this->template->assign_vars([
				'S_IPN_TEST'                       => true,
				'SANDBOX_ANONYMOUS_DONORS_COUNT'   => $this->config['ppde_anonymous_donors_count_ipn'],
				'SANDBOX_ANONYMOUS_DONORS_PER_DAY' => $this->per_day_stats('ppde_anonymous_donors_count_ipn'),
				'SANDBOX_KNOWN_DONORS_COUNT'       => $this->config['ppde_known_donors_count_ipn'],
				'SANDBOX_KNOWN_DONORS_PER_DAY'     => $this->per_day_stats('ppde_known_donors_count_ipn'),
				'SANDBOX_TRANSACTIONS_COUNT'       => $this->config['ppde_transactions_count_ipn'],
				'SANDBOX_TRANSACTIONS_PER_DAY'     => $this->per_day_stats('ppde_transactions_count_ipn'),
			]);
		}
	}

	/**
	 * Do action regarding the value of $action
	 *
	 * @param string $action Requested action
	 *
	 * @return void
	 * @throws \ReflectionException
	 * @access private
	 */
	private function do_action($action): void
	{
		if ($action)
		{
			if (!confirm_box(true))
			{
				$this->display_confirm($action);
				return;
			}

			$this->exec_action($action);
		}
	}

	/**
	 * Display confirm box
	 *
	 * @param string $action Requested action
	 *
	 * @return void
	 * @access private
	 */
	private function display_confirm($action): void
	{
		switch ($action)
		{
			case 'date':
				$confirm_lang = 'STAT_RESET_DATE_CONFIRM';
			break;
			case 'esi':
				$confirm_lang = 'STAT_RETEST_ESI_CONFIRM';
			break;
			case 'sandbox':
				$confirm_lang = 'STAT_RESYNC_SANDBOX_STATS_CONFIRM';
			break;
			case 'stats':
				$confirm_lang = 'STAT_RESYNC_STATS_CONFIRM';
			break;
			default:
				$confirm_lang = 'CONFIRM_OPERATION';
		}

		confirm_box(false, $this->language->lang($confirm_lang), build_hidden_fields(['action' => $action]));
	}

	/**
	 * @param string $action Requested action
	 *
	 * @return void
	 * @throws \ReflectionException
	 * @access private
	 */
	private function exec_action($action): void
	{
		if (!$this->auth->acl_get('a_ppde_manage'))
		{
			trigger_error($this->language->lang('NO_AUTH_OPERATION') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		switch ($action)
		{
			case 'date':
				$this->config->set('ppde_install_date', time() - 1);
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_STAT_RESET_DATE');
			break;
			case 'esi':
				$this->config->set('ppde_first_start', 1);
				$this->ppde_first_start();
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_STAT_RETEST_ESI');
			break;
			case 'sandbox':
				$this->ppde_actions->set_ipn_test_properties(true);
				$this->ppde_actions->update_overview_stats();
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_STAT_SANDBOX_RESYNC');
			break;
			case 'stats':
				$this->ppde_actions->set_ipn_test_properties(false);
				$this->ppde_actions->update_overview_stats();
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_STAT_RESYNC');
			break;
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
	private function per_day_stats($config_name): string
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
