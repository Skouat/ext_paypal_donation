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

class admin_overview_controller extends admin_main implements admin_overview_interface
{
	protected $auth;
	protected $cache;
	protected $config;
	protected $log;
	protected $ppde_controller_main;
	protected $request;
	protected $template;
	protected $user;
	protected $php_ext;

	protected $ext_name;
	protected $ext_meta = array();

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth                        $auth                 Authentication object
	 * @param \phpbb\cache\service                    $cache                Cache object
	 * @param \phpbb\config\config                    $config               Config object
	 * @param \phpbb\log\log                          $log                  The phpBB log system
	 * @param \skouat\ppde\controller\main_controller $ppde_controller_main Main controller object
	 * @param \phpbb\request\request                  $request              Request object
	 * @param \phpbb\template\template                $template             Template object
	 * @param \phpbb\user                             $user                 User object
	 * @param string                                  $php_ext              phpEx
	 *
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\log\log $log, \skouat\ppde\controller\main_controller $ppde_controller_main, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $php_ext)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->log = $log;
		$this->ppde_controller_main = $ppde_controller_main;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->php_ext = $php_ext;
	}

	/**
	 * Display the overview page
	 *
	 * @param string $id     Module id
	 * @param string $mode   Module categorie
	 * @param string $action Action name
	 *
	 * @return null
	 * @access public
	 */
	public function display_overview($id, $mode, $action)
	{
		$this->do_action($id, $mode, $action);

		//Load metadata for this extension
		$this->ext_meta = $this->ppde_controller_main->load_metadata();

		// Check if a new version is available
		$this->obtain_last_version();

		// Set output block vars for display in the template
		$this->template->assign_vars(array(
			'INFO_CURL'                 => $this->config['ppde_curl_detected'] ? $this->user->lang('INFO_DETECTED') : $this->user->lang('INFO_NOT_DETECTED'),
			'INFO_FSOCKOPEN'            => $this->config['ppde_fsock_detected'] ? $this->user->lang('INFO_DETECTED') : $this->user->lang('INFO_NOT_DETECTED'),

			'L_PPDE_INSTALL_DATE'       => $this->user->lang('PPDE_INSTALL_DATE', $this->ext_meta['extra']['display-name']),
			'L_PPDE_VERSION'            => $this->user->lang('PPDE_VERSION', $this->ext_meta['extra']['display-name']),

			'PPDE_INSTALL_DATE'         => $this->user->format_date($this->config['ppde_install_date']),
			'PPDE_VERSION'              => $this->ext_meta['version'],

			'S_ACTION_OPTIONS'          => ($this->auth->acl_get('a_ppde_manage')) ? true : false,
			'S_FSOCKOPEN'               => $this->config['ppde_curl_detected'],
			'S_CURL'                    => $this->config['ppde_fsock_detected'],

			'U_PPDE_MORE_INFORMATION'   => append_sid("index.$this->php_ext", 'i=acp_extensions&amp;mode=main&amp;action=details&amp;ext_name=' . urlencode($this->ext_meta['name'])),
			'U_PPDE_VERSIONCHECK_FORCE' => $this->u_action . '&amp;versioncheck_force=1',
			'U_ACTION'                  => $this->u_action,
		));
	}

	private function do_action($id, $mode, $action)
	{
		if ($action)
		{
			if (!confirm_box(true))
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
					default:
						$confirm = true;
						$confirm_lang = 'CONFIRM_OPERATION';
				}

				if ($confirm)
				{
					confirm_box(false, $this->user->lang[$confirm_lang], build_hidden_fields(array(
						'i'      => $id,
						'mode'   => $mode,
						'action' => $action,
					)));
				}
			}
			else
			{
				switch ($action)
				{
					case 'date':
						if (!$this->auth->acl_get('a_ppde_manage'))
						{
							trigger_error($this->user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$this->config->set('ppde_install_date', time() - 1);
						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_STAT_RESET_DATE');
						break;
					case 'remote':
						$this->config->set('ppde_curl_detected', $this->ppde_controller_main->check_curl());
						$this->config->set('ppde_fsock_detected', $this->ppde_controller_main->check_fsockopen());
						break;
				}
			}
		}
	}

	/**
	 * Obtain the last version for this extension
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
}
