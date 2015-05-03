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

class admin_controller implements admin_interface
{
	protected $lang_local_name;

	protected $auth;
	protected $cache;
	protected $config;
	protected $container;
	protected $db;
	protected $extension_manager;
	protected $phpbb_log;
	protected $ppde_operator;
	protected $request;
	protected $template;
	protected $user;
	protected $phpbb_container;
	protected $phpbb_root_path;
	protected $php_ext;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth                     $auth               Authentication object
	* @param \phpbb\cache\service                 $cache              Cache object
	* @param \phpbb\config\config                 $config             Config object
	* @param ContainerInterface                   $container          Service container interface
	* @param \phpbb\db\driver\driver_interface    $db                 Database connection
	* @param \phpbb\extension\manager             $extension_manager  An instance of the phpBB extension manager
	* @param \phpbb\log\log                       $phpbb_log          The phpBB log system
	* @param \skouat\ppde\operators\donation_page $ppde_operator      Operator object
	* @param \phpbb\request\request               $request            Request object
	* @param \phpbb\template\template             $template           Template object
	* @param \phpbb\user                          $user               User object
	* @param string                               $phpbb_root_path    phpBB root path
	* @param string                               $php_ext            phpEx
	* @access public
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\config $config, ContainerInterface $container, \phpbb\db\driver\driver_interface $db, \phpbb\extension\manager $extension_manager, \phpbb\log\log $phpbb_log, \skouat\ppde\operators\donation_page $ppde_operator, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $phpbb_root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->container = $container;
		$this->db = $db;
		$this->extension_manager = $extension_manager;
		$this->phpbb_log = $phpbb_log;
		$this->ppde_operator = $ppde_operator;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* Display the overwiew page
	*
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
						if (!$this->auth->acl_get('a_board'))
						{
							trigger_error($this->user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$this->config->set('ppde_install_date', time() - 1);
						$this->phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_STAT_RESET_DATE');
					break;
				}
			}
		}

		// Retrieve the extension name based on the namespace of this file
		$this->retrieve_ext_name(__NAMESPACE__);

		// init variables
		$ext_meta = array();

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
		catch(\RuntimeException $e)
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

			curl_close($ch);

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
	* Display the general settings a user can configure for this extension
	*
	* @return null
	* @access public
	*/
	public function display_settings()
	{
		// Define the name of the form for use as a form key
		add_form_key('ppde_settings');

		// Create an array to collect errors that will be output to the user
		$errors = array();
		// Is the form being submitted to us?
		if ($this->request->is_set_post('submit'))
		{
			// Test if the submitted form is valid
			if (!check_form_key('ppde_settings'))
			{
				$errors[] = $this->user->lang('FORM_INVALID');
			}

			// If no errors, process the form data
			if (empty($errors))
			{
				// Set the options the user configured
				$this->set_settings();

				// Add option settings change action to the admin log
				$phpbb_log = $this->container->get('log');
				$phpbb_log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PPDE_SETTINGS_UPDATED');

				// Option settings have been updated and logged
				// Confirm this to the user and provide link back to previous page
				trigger_error($this->user->lang('PPDE_SETTINGS_SAVED') . adm_back_link($this->u_action));
			}
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ERROR'		=> (sizeof($errors)) ? true : false,
			'ERROR_MSG'		=> (sizeof($errors)) ? implode('<br />', $errors) : '',

			'U_ACTION'		=> $this->u_action,

			// Global Settings vars
			'PPDE_ACCOUNT_ID'				=> $this->config['ppde_account_id'] ? $this->config['ppde_account_id'] : '',
			'PPDE_DEFAULT_CURRENCY'			=> 'select',
			'PPDE_DEFAULT_VALUE'			=> $this->config['ppde_default_value'] ? $this->config['ppde_default_value'] : 0,
			'PPDE_DROPBOX_VALUE'			=> $this->config['ppde_dropbox_value'] ? $this->config['ppde_dropbox_value'] : '1,2,3,4,5,10,20,25,50,100',

			'S_PPDE_DROPBOX_ENABLE'			=> $this->config['ppde_dropbox_enable'] ? true : false,
			'S_PPDE_ENABLE'					=> $this->config['ppde_enable'] ? true : false,

			// Sandbox Settings vars
			'PPDE_SANDBOX_ADDRESS'			=> $this->config['ppde_sandbox_address'] ? $this->config['ppde_sandbox_address'] : '',

			'S_PPDE_SANDBOX_ENABLE'			=> $this->config['ppde_sandbox_enable'] ? true : false,
			'S_PPDE_SANDBOX_FOUNDER_ENABLE'	=> $this->config['ppde_sandbox_founder_enable'] ? true : false,

			// Statistics Settings vars
			'PPDE_RAISED'					=> $this->config['ppde_raised'] ? $this->config['ppde_raised'] : 0,
			'PPDE_GOAL'						=> $this->config['ppde_goal'] ? $this->config['ppde_goal'] : 0,
			'PPDE_USED'						=> $this->config['ppde_used'] ? $this->config['ppde_used'] : 0,

			'S_PPDE_STATS_INDEX_ENABLE'		=> $this->config['ppde_stats_index_enable'] ? true : false,
			'S_PPDE_RAISED_ENABLE'			=> $this->config['ppde_raised_enable'] ? true : false,
			'S_PPDE_GOAL_ENABLE'			=> $this->config['ppde_goal_enable'] ? true : false,
			'S_PPDE_USED_ENABLE'			=> $this->config['ppde_used_enable'] ? true : false,
		));
	}

	/**
	* Set the options a user can configure
	*
	* @return null
	* @access protected
	*/
	protected function set_settings()
	{
		// Set options for Global settings
		$this->config->set('ppde_enable', $this->request->variable('ppde_enable', false));
		$this->config->set('ppde_account_id', $this->request->variable('ppde_account_id', ''));
		$this->config->set('ppde_default_currency', $this->request->variable('ppde_default_currency', 'USD'));
		$this->config->set('ppde_default_value', $this->request->variable('ppde_default_value', 0));
		$this->config->set('ppde_dropbox_enable', $this->request->variable('ppde_dropbox_enable', false));
		$this->config->set('ppde_dropbox_value', $this->request->variable('ppde_dropbox_value', '1,2,3,4,5,10,20,25,50,100'));

		// Set options for Sandbox Settings
		$this->config->set('ppde_sandbox_enable', $this->request->variable('ppde_sandbox_enable', false));
		$this->config->set('ppde_sandbox_founder_enable', $this->request->variable('ppde_sandbox_founder_enable', false));
		$this->config->set('ppde_sandbox_address', $this->request->variable('ppde_sandbox_address', ''));

		// Set options for Statistics Settings
		$this->config->set('ppde_stats_index_enable', $this->request->variable('ppde_stats_index_enable', false));
		$this->config->set('ppde_raised_enable', $this->request->variable('ppde_raised_enable', false));
		$this->config->set('ppde_raised', $this->request->variable('ppde_raised', 0));
		$this->config->set('ppde_goal_enable', $this->request->variable('ppde_goal_enable', false));
		$this->config->set('ppde_goal', $this->request->variable('ppde_goal', 0));
		$this->config->set('ppde_used_enable', $this->request->variable('ppde_used_enable', false));
		$this->config->set('ppde_used', $this->request->variable('ppde_used', 0));
	}

	/**
	* Display the pages
	*
	* @return null
	* @access public
	*/
	public function display_donation_pages()
	{
		// Get list of available language packs
		$langs = $this->ppde_operator->get_languages();

		// Set output vars
		foreach ($langs as $lang => $entry)
		{
			$this->template->assign_block_vars('ppde_langs', array(
				'LANG_LOCAL_NAME' => $entry['name'],
			));

			// Grab language id
			$lang_id = $entry['id'];

			// Grab all the pages from the db
			$entities = $this->ppde_operator->get_item_data('donation_pages', $lang_id);

			foreach ($entities as $item)
			{
				// Do not treat the item whether language identifier does not match
				if ($item['item_iso_code'] != $lang_id)
				{
					continue;
				}

				$this->template->assign_block_vars('ppde_langs.dp_list', array(
					'DONATION_PAGE_TITLE'	=> $this->user->lang[strtoupper($item['item_name'])],
					'DONATION_PAGE_LANG'	=> (string) $lang,

					'U_DELETE'				=> $this->u_action . '&amp;action=delete&amp;item_id=' . $item['item_id'],
					'U_EDIT'				=> $this->u_action . '&amp;action=edit&amp;item_id=' . $item['item_id'],
				));
			}
			unset($entities, $item);
		}
		unset($entry, $langs, $lang);

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'U_ACTION'		=> $this->u_action,
		));
	}

	/**
	* Add a donation page
	*
	* @return null
	* @access public
	*/
	public function add_donation_page()
	{
		// Add form key
		add_form_key('add_edit_donation_page');

		// Initiate a page donation entity
		$entity = $this->container->get('skouat.ppde.entity');

		// Collect the form data
		$data = array(
			'item_type'		=> 'donation_pages',
			'item_name'		=> $this->request->variable('item_name', ''),
			'item_iso_code'	=> $this->request->variable('lang_id', '', true),
			'item_text'		=> $this->request->variable('item_text', '', true),
			'bbcode'		=> !$this->request->variable('disable_bbcode', false),
			'magic_url'		=> !$this->request->variable('disable_magic_url', false),
			'smilies'		=> !$this->request->variable('disable_smilies', false),
		);

		// Set template vars for language select menu
		$this->create_language_options($data['item_iso_code']);

		// Process the new page
		$this->add_edit_item_data($entity, $data);

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ADD_DONATION_PAGE'	=> true,

			'U_ADD_ACTION'			=> "{$this->u_action}&amp;action=add",
			'U_BACK'				=> "{$this->u_action}",
		));
	}

	/**
	* Process item data to be added or edited
	*
	* @param object $entity The rule entity object
	* @param array $data The form data to be processed
	* @return null
	* @access protected
	*/
	protected function add_edit_item_data($entity, $data)
	{
		// Get form's POST actions (submit or preview)
		$submit = $this->request->is_set_post('submit');
		$preview = $this->request->is_set_post('preview');

		// Load posting language file for the BBCode editor
		$this->user->add_lang('posting');

		// Create an array to collect errors that will be output to the user
		$errors = array();

		// Grab the form data's message parsing options (possible values: 1 or 0)
		$message_parse_options = array(
			'bbcode'	=> ($submit || $preview) ? $data['bbcode'] : $entity->message_bbcode_enabled(),
			'magic_url'	=> ($submit || $preview) ? $data['magic_url'] : $entity->message_magic_url_enabled(),
			'smilies'	=> ($submit || $preview) ? $data['smilies'] : $entity->message_smilies_enabled(),
		);

		// Set the message parse options in the entity
		foreach ($message_parse_options as $function => $enabled)
		{
			call_user_func(array($entity, ($enabled ? 'message_enable_' : 'message_disable_') . $function));
		}

		unset($message_parse_options);

		// Grab the form's data fields
		$item_fields = array(
			'lang_id'	=> $data['item_iso_code'],
			'name'		=> $data['item_name'],
			'type'		=> $data['item_type'],
			'message'	=> $data['item_text'],
		);

		// Set the donation page's data in the entity
		foreach ($item_fields as $entity_function => $item_data)
		{
				// Calling the set_$entity_function on the entity and passing it $rule_data
				call_user_func_array(array($entity, 'set_' . $entity_function), array($item_data));
		}
		unset($item_fields, $entity_function, $item_data);

		// Set hidden fields
		$s_hidden_fields = build_hidden_fields(array(
			'item_name'	=> $entity->get_name(),
		));

		// If the form has been submitted or previewed
		if ($submit || $preview)
		{
			// Test if the form is valid
			if (!check_form_key('add_edit_donation_page'))
			{
				$errors[] = $this->user->lang('FORM_INVALID');
			}

			// Do not allow an empty item name
			if ($entity->get_name() == '')
			{
				$errors[] = $this->user->lang('PPDE_MUST_SELECT_ITEM');
			}

			// Do not allow an unselected language name
			if ($entity->get_lang_id() == 0 && $submit)
			{
				$errors[] = $this->user->lang('PPDE_MUST_SELECT_LANG');
			}
		}

		// Preview
		if ($preview && empty($errors))
		{
			// Set output vars for display in the template
			$this->template->assign_vars(array(
				'S_PPDE_DP_PREVIEW'	=> $preview,

				'PPDE_DP_PREVIEW'	=> $entity->get_message_for_display(),
			));
		}

		// Insert or update rule
		if ($submit && empty($errors) && !$preview)
		{
			if ($entity->get_id())
			{
				// Save the edited item entity to the database
				$entity->save();

				// Show user confirmation of the saved item and provide link back to the previous page
				trigger_error($this->user->lang('ACP_RULE_EDITED') . adm_back_link("{$this->u_action}&amp;language={$entity->get_language()}&amp;parent_id={$entity->get_parent_id()}"));
			}
			else
			{
				// Add a new item entity to the database
				$this->ppde_operator->add_item_data($entity);

				// Grab the local language name
				$this->get_lang_local_name($this->ppde_operator->get_languages($entity->get_lang_id()));

				// Show user confirmation of the added item and provide link back to the previous page
				trigger_error($this->user->lang('PPDE_DP_LANG_ADDED', $this->lang_local_name) . adm_back_link("{$this->u_action}"));
			}
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ERROR'			=> (sizeof($errors)) ? true : false,
			'ERROR_MSG'			=> (sizeof($errors)) ? implode('<br />', $errors) : '',

			'L_DONATION_PAGES_TITLE'		=> $this->user->lang(strtoupper($entity->get_name())),
			'L_DONATION_PAGES_TITLE_EXPLAIN'=> $this->user->lang(strtoupper($entity->get_name()) . '_EXPLAIN'),
			'DONATION_BODY'					=> $entity->get_message_for_edit(),
			'LANG_ISO'						=> $data['item_iso_code'],

			'S_BBCODE_DISABLE_CHECKED'		=> !$entity->message_bbcode_enabled(),
			'S_SMILIES_DISABLE_CHECKED'		=> !$entity->message_smilies_enabled(),
			'S_MAGIC_URL_DISABLE_CHECKED'	=> !$entity->message_magic_url_enabled(),

			'BBCODE_STATUS'			=> $this->user->lang('BBCODE_IS_ON', '<a href="' . append_sid("{$this->phpbb_root_path}faq.{$this->php_ext}", 'mode=bbcode') . '">', '</a>'),
			'SMILIES_STATUS'		=> $this->user->lang('SMILIES_ARE_ON'),
			'IMG_STATUS'			=> $this->user->lang('IMAGES_ARE_ON'),
			'FLASH_STATUS'			=> $this->user->lang('FLASH_IS_ON'),
			'URL_STATUS'			=> $this->user->lang('URL_IS_ON'),

			'S_BBCODE_ALLOWED'		=> true,
			'S_SMILIES_ALLOWED'		=> true,
			'S_BBCODE_IMG'			=> true,
			'S_BBCODE_FLASH'		=> true,
			'S_LINKS_ALLOWED'		=> true,
			'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
		));

		// Assigning custom bbcodes
		include_once($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);

		display_custom_bbcodes();
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
	* Set template var options for language select menus
	*
	* @param string $current ID of the language assigned to the donation page
	* @return null
	* @access protected
	*/
	protected function create_language_options($current)
	{
		// Grab all available language packs
		$langs = $this->ppde_operator->get_languages();

		// Set the options list template vars
		foreach ($langs as $lang)
		{
			$this->template->assign_block_vars('ppde_langs', array(
				'LANG_LOCAL_NAME'	=> $lang['name'],
				'VALUE'				=> $lang['id'],
				'S_SELECTED'		=> ($lang['id'] == $current) ? true : false,
			));
		}
	}

	/**
	* Get Local lang name
	*
	* @param array $langs
	* @return null
	* @access protected
	*/
	protected function get_lang_local_name($langs)
	{
		foreach ($langs as $lang)
		{
			$this->lang_local_name = $lang['name'];
		}
	}

	/**
	* Retrieve the extension name
	*
	* @param string $namespace
	* @return null
	* @access protected
	*/
	protected function retrieve_ext_name($namespace)
	{
		$namespace_ary = explode('\\', $namespace);
		$this->ext_name = $namespace_ary[0] . '/' . $namespace_ary[1];
	}
}
