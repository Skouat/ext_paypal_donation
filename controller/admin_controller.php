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

use Symfony\Component\DependencyInjection\ContainerInterface;

class admin_controller
{
	protected $auth;
	protected $cache;
	protected $config;
	protected $container;
	protected $db;
	protected $extension_manager;
	protected $request;
	protected $template;
	protected $user;
	protected $phpbb_container;
	protected $phpbb_root_path;
	protected $php_ext;
	protected $ppde_data_table;
	protected $ppde_item_table;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth                     $auth               Authentication object
	* @param \phpbb\cache\service                 $cache              Cache object
	* @param \phpbb\config\config                 $config             Config object
	* @param ContainerInterface                   $container          Service container interface
	* @param \phpbb\db\driver\driver_interface    $db                 Database connection
	* @param \phpbb\extension\manager             $extension_manager  An instance of the phpBB extension manager
	* @param \phpbb\request\request               $request            Request object
	* @param \phpbb\template\template             $template           Template object
	* @param \phpbb\user                          $user               User object
	* @param string                               $phpbb_root_path    phpBB root path
	* @param string                               $php_ext            phpEx
	* @param string                               $ppde_data_table    Table name
	* @param string                               $ppde_item_table    Table name
	* @access public
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\config $config, ContainerInterface $container, \phpbb\db\driver\driver_interface $db, \phpbb\extension\manager $extension_manager, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $phpbb_root_path, $php_ext, $ppde_data_table, $ppde_item_table)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->container = $container;
		$this->db = $db;
		$this->extension_manager = $extension_manager;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->ppde_data_table = $ppde_data_table;
		$this->ppde_item_table = $ppde_item_table;
	}

	/**
	* Display the pages
	* @param string $id        Module id
	* @param string $mode      Module categorie
	* @param string $action    Action name
	* @return null
	* @access public
	*/
	public function display_overview($id, $mode, $action)
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

					default:
						$confirm = true;
						$confirm_lang = 'CONFIRM_OPERATION';
				}

				if ($confirm)
				{
					confirm_box(false, $this->user->lang[$confirm_lang], build_hidden_fields(array(
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> $action,
					)));
				}
			}
			else
			{
				switch ($action)
				{
					case 'date':
						if (!$auth->acl_get('a_board'))
						{
							trigger_error($this->user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						set_config('ppde_install_date', time() - 1);
						add_log('admin', 'LOG_STAT_RESET_DATE');
					break;
				}
			}
		}

		// Retrieve the extension name based on the namespace of this file
		$this->retrieve_ext_name(__NAMESPACE__);

		// If they've specified an extension, let's load the metadata manager and validate it.
		if ($this->ext_name)
		{
			$md_manager = new \phpbb\extension\metadata_manager($this->ext_name, $this->config, $this->extension_manager, $this->template, $this->user, $this->phpbb_root_path);

			try
			{
				$ext_meta = $md_manager->get_metadata('all');
			}
			catch(\phpbb\extension\exception $e)
			{
				trigger_error($e, E_USER_WARNING);
			}
		}

		// Check if a new version is available
		try
		{
			if (!isset($ext_meta['extra']['version-check']))
			{
				throw new \RuntimeException($this->user->lang('PPDE_NO_VERSIONCHECK'), 1);
			}

			$version_check = $ext_meta['extra']['version-check'];

			$version_helper = new \phpbb\version_helper($this->cache, $this->config, new \phpbb\file_downloader(), $this->user);
			$version_helper->set_current_version($ext_meta['version']);
			$version_helper->set_file_location($version_check['host'], $version_check['directory'], $version_check['filename']);
			$version_helper->force_stability($this->config['extension_force_unstable'] ? 'unstable' : null);

			$recheck = $this->request->variable('versioncheck_force', false);
			$s_up_to_date = $version_helper->get_suggested_updates($recheck);

			$this->template->assign_vars(array(
				'S_UP_TO_DATE'		=> empty($s_up_to_date),
				'S_VERSIONCHECK'	=> true,
				'UP_TO_DATE_MSG'	=> $this->user->lang('PPDE_NOT_UP_TO_DATE', $ext_meta['extra']['display-name']),
			));
		}
		catch (\RuntimeException $e)
		{
			$this->template->assign_vars(array(
				'S_VERSIONCHECK_STATUS'			=> $e->getCode(),
				'VERSIONCHECK_FAIL_REASON'		=> ($e->getMessage() !== $this->user->lang('VERSIONCHECK_FAIL')) ? $e->getMessage() : '',
			));
		}

		// Check if fsockopen and cURL are available and display it in overview
		$info_curl = $info_fsockopen = $this->user->lang['INFO_NOT_DETECTED'];
		$s_curl = $s_fsockopen = false;

		if (function_exists('fsockopen'))
		{
			$url = parse_url($ext_meta['extra']['version-check']['host']);

			$fp = @fsockopen($url['path'], 80);

			if ($fp !== false)
			{
				$info_fsockopen = $this->user->lang['INFO_DETECTED'];
				$s_fsockopen = true;
			}

			unset($fp);
		}

		if (function_exists('curl_init') && function_exists('curl_exec'))
		{

			$ch = curl_init($ext_meta['extra']['version-check']['host']);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$response = curl_exec($ch);
			$response_status = strval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

			curl_close ($ch);

			if ($response !== false || $response_status !== '0')
			{
				$info_curl = $this->user->lang['INFO_DETECTED'];
				$s_curl = true;
			}

			unset($ch, $response, $response_status);
		}

		$ppde_install_date = $this->user->format_date($this->config['ppde_install_date']);

		// Set output block vars for display in the template
		$this->template->assign_vars(array(
			'INFO_CURL'				=> $info_curl,
			'INFO_FSOCKOPEN'		=> $info_fsockopen,

			'L_PPDE_INSTALL_DATE'		=> $this->user->lang('PPDE_INSTALL_DATE', $ext_meta['extra']['display-name']),
			'L_PPDE_VERSION'			=> $this->user->lang('PPDE_VERSION', $ext_meta['extra']['display-name']),

			'PPDE_INSTALL_DATE'		=> $ppde_install_date,
			'PPDE_VERSION'			=> $ext_meta['version'],

			'S_ACTION_OPTIONS'		=> ($this->auth->acl_get('a_board')) ? true : false,
			'S_FSOCKOPEN'			=> $s_fsockopen,
			'S_CURL'				=> $s_curl,
			'S_OVERVIEW'			=> $mode,

			'U_PPDE_MORE_INFORMATION'	=> append_sid("index.$this->php_ext", 'i=acp_extensions&amp;mode=main&amp;action=details&amp;ext_name=' . urlencode($ext_meta['name'])),
			'U_PPDE_VERSIONCHECK_FORCE'	=> $this->u_action . '&amp;versioncheck_force=1',
			'U_ACTION'					=> $this->u_action,
		));
	}

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}

	/**
	* Retrieve the extension name
	*
	* @param string $module_basename
	* @return null
	* @access public
	*/
	public function retrieve_ext_name($namespace)
	{
		$namespace_ary = explode('\\', $namespace);
		$this->ext_name =  $namespace_ary[0] . '/' . $namespace_ary[1];
	}
}
