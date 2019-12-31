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

use phpbb\config\config;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use skouat\ppde\actions\currency;

/**
 * @property config   config             Config object
 * @property string   id_prefix_name     Prefix name for identifier in the URL
 * @property string   lang_key_prefix    Prefix for the messages thrown by exceptions
 * @property language language           Language user object
 * @property log      log                The phpBB log system
 * @property string   module_name        Name of the module currently used
 * @property request  request            Request object
 * @property bool     submit             State of submit $_POST variable
 * @property template template           Template object
 * @property string   u_action           Action URL
 * @property user     user               User object
 */
class settings_controller extends admin_main
{
	protected $ppde_actions_currency;

	/**
	 * Constructor
	 *
	 * @param config   $config                Config object
	 * @param language $language              Language user object
	 * @param log      $log                   The phpBB log system
	 * @param currency $ppde_actions_currency Main controller object
	 * @param request  $request               Request object
	 * @param template $template              Template object
	 * @param user     $user                  User object
	 *
	 * @access public
	 */
	public function __construct(
		config $config,
		language $language,
		log $log,
		currency $ppde_actions_currency,
		request $request,
		template $template,
		user $user
	)
	{
		$this->config = $config;
		$this->language = $language;
		$this->log = $log;
		$this->ppde_actions_currency = $ppde_actions_currency;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		parent::__construct(
			'settings',
			'PPDE_SETTINGS',
			''
		);
	}

	/**
	 * Display the general settings a user can configure for this extension
	 *
	 * @return void
	 * @access public
	 */
	public function display_settings()
	{
		// Define the name of the form for use as a form key
		add_form_key('ppde_settings');

		// Create an array to collect errors that will be output to the user
		$errors = [];

		$this->submit_settings();

		// Set output vars for display in the template
		$this->s_error_assign_template_vars($errors);
		$this->u_action_assign_template_vars();
		$this->ppde_actions_currency->build_currency_select_menu((int) $this->config['ppde_default_currency']);
		$this->build_remote_uri_select_menu($this->config['ppde_default_remote'], 'live');
		$this->template->assign_vars([
			// Global Settings vars
			'PPDE_ACCOUNT_ID'           => $this->check_config($this->config['ppde_account_id'], 'string', ''),
			'PPDE_DEFAULT_VALUE'        => $this->check_config($this->config['ppde_default_value'], 'integer', 0),
			'PPDE_DROPBOX_VALUE'        => $this->check_config($this->config['ppde_dropbox_value'], 'string', '1,2,3,4,5,10,20,25,50,100'),
			'S_PPDE_DROPBOX_ENABLE'     => $this->check_config($this->config['ppde_dropbox_enable']),
			'S_PPDE_ENABLE'             => $this->check_config($this->config['ppde_enable']),
			'S_PPDE_HEADER_LINK'        => $this->check_config($this->config['ppde_header_link']),

			// Statistics Settings vars
			'PPDE_GOAL'                 => $this->check_config($this->config['ppde_goal'], 'float', 0),
			'PPDE_RAISED'               => $this->check_config($this->config['ppde_raised'], 'float', 0),
			'PPDE_USED'                 => $this->check_config($this->config['ppde_used'], 'float', 0),
			'S_PPDE_GOAL_ENABLE'        => $this->check_config($this->config['ppde_goal_enable']),
			'S_PPDE_RAISED_ENABLE'      => $this->check_config($this->config['ppde_raised_enable']),
			'S_PPDE_STATS_INDEX_ENABLE' => $this->check_config($this->config['ppde_stats_index_enable']),
			'S_PPDE_USED_ENABLE'        => $this->check_config($this->config['ppde_used_enable']),
		]);
	}

	/**
	 * Set the options a user can configure
	 *
	 * @return void
	 * @access protected
	 */
	protected function set_settings()
	{
		// Set options for Global settings
		$this->config->set('ppde_default_currency', $this->request->variable('ppde_default_currency', 0));
		$this->config->set('ppde_default_value', $this->request->variable('ppde_default_value', 0));
		$this->config->set('ppde_dropbox_enable', $this->request->variable('ppde_dropbox_enable', false));
		$this->config->set('ppde_dropbox_value', $this->rebuild_items_list($this->request->variable('ppde_dropbox_value', '1,2,3,4,5,10,20,25,50,100'), $this->config['ppde_default_value']));
		$this->config->set('ppde_enable', $this->request->variable('ppde_enable', false));
		$this->config->set('ppde_header_link', $this->request->variable('ppde_header_link', false));

		// Set options for Advanced settings
		$this->config->set('ppde_default_remote', $this->request->variable('ppde_default_remote', 0));

		// Set options for Statistics Settings
		$this->config->set('ppde_stats_index_enable', $this->request->variable('ppde_stats_index_enable', false));
		$this->config->set('ppde_raised_enable', $this->request->variable('ppde_raised_enable', false));
		$this->config->set('ppde_raised', $this->request->variable('ppde_raised', 0.0));
		$this->config->set('ppde_goal_enable', $this->request->variable('ppde_goal_enable', false));
		$this->config->set('ppde_goal', $this->request->variable('ppde_goal', 0.0));
		$this->config->set('ppde_used_enable', $this->request->variable('ppde_used_enable', false));
		$this->config->set('ppde_used', $this->request->variable('ppde_used', 0.0));

		// Settings with dependencies are the last to be set.
		$this->config->set('ppde_account_id', $this->required_settings($this->request->variable('ppde_account_id', ''), $this->depend_on('ppde_enable')));
	}

	/**
	 * Rebuild items list to conserve only numeric values
	 *
	 * @param string $config_value
	 * @param string $added_value
	 *
	 * @return string
	 * @access private
	 */
	private function rebuild_items_list($config_value, $added_value = '')
	{
		$items_list = explode(',', $config_value);
		$merge_items = [];

		$this->add_int_data_in_array($merge_items, $added_value);

		foreach ($items_list as $item)
		{
			$this->add_int_data_in_array($merge_items, $item);
		}
		unset($items_list, $item);

		natsort($merge_items);

		return $this->check_config(implode(',', array_unique($merge_items)), 'string', '');
	}

	/**
	 * Add integer data in an array()
	 *
	 * @param array  &$array
	 * @param string  $var
	 *
	 * @return void
	 * @access private
	 */
	private function add_int_data_in_array(&$array, $var)
	{
		if (settype($var, 'integer') && $var != 0)
		{
			$array[] = $var;
		}
	}
}
