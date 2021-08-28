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

use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @property ContainerInterface container         Service container interface
 * @property string             id_prefix_name    Prefix name for identifier in the URL
 * @property string             lang_key_prefix   Prefix for the messages thrown by exceptions
 * @property language           language          Language user object
 * @property log                log               The phpBB log system.
 * @property string             module_name       Name of the module currently used
 * @property bool               preview           State of preview $_POST variable
 * @property request            request           Request object.
 * @property bool               submit            State of submit $_POST variable
 * @property template           template          Template object
 * @property user               user              User object.
 */
class donation_pages_controller extends admin_main
{
	protected $phpbb_root_path;
	protected $php_ext;
	protected $ppde_actions_vars;
	protected $ppde_entity;
	protected $ppde_operator;
	protected $lang_local_name;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface                    $container                    Service container interface
	 * @param language                              $language                     Language user object
	 * @param log                                   $log                          The phpBB log system
	 * @param \skouat\ppde\actions\vars             $ppde_actions_vars            PPDE Actions vars object
	 * @param \skouat\ppde\entity\donation_pages    $ppde_entity_donation_pages   PPDE Entity object
	 * @param \skouat\ppde\operators\donation_pages $ppde_operator_donation_pages Operator object
	 * @param request                               $request                      Request object
	 * @param template                              $template                     Template object
	 * @param user                                  $user                         User object
	 * @param string                                $phpbb_root_path              phpBB root path
	 * @param string                                $php_ext                      phpEx
	 *
	 * @access public
	 */
	public function __construct(
		ContainerInterface $container,
		language $language,
		log $log,
		\skouat\ppde\actions\vars $ppde_actions_vars,
		\skouat\ppde\entity\donation_pages $ppde_entity_donation_pages,
		\skouat\ppde\operators\donation_pages $ppde_operator_donation_pages,
		request $request,
		template $template,
		user $user,
		$phpbb_root_path,
		$php_ext
	)
	{
		$this->container = $container;
		$this->language = $language;
		$this->log = $log;
		$this->ppde_actions_vars = $ppde_actions_vars;
		$this->ppde_entity = $ppde_entity_donation_pages;
		$this->ppde_operator = $ppde_operator_donation_pages;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		parent::__construct(
			'donation_pages',
			'PPDE_DP',
			'page'
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function display(): void
	{
		// Get list of available language packs
		$langs = $this->ppde_operator->get_languages();

		// Set output vars
		foreach ($langs as $lang => $entry)
		{
			$this->assign_langs_template_vars($entry);

			// Grab all the pages from the db
			$data_ary = $this->ppde_entity->get_data($this->ppde_operator->build_sql_data($entry['id']));

			foreach ($data_ary as $data)
			{
				// Do not treat the item whether language identifier does not match
				if ((int) $data['page_lang_id'] !== (int) $entry['id'])
				{
					continue;
				}

				$this->template->assign_block_vars('ppde_langs.dp_list', [
					'DONATION_PAGE_TITLE' => $this->language->lang(strtoupper($data['page_title'])),
					'DONATION_PAGE_LANG'  => (string) $lang,
					'U_DELETE'            => $this->u_action . '&amp;action=delete&amp;' . $this->id_prefix_name . '_id=' . $data['page_id'],
					'U_EDIT'              => $this->u_action . '&amp;action=edit&amp;' . $this->id_prefix_name . '_id=' . $data['page_id'],
				]);
			}
			unset($data_ary);
		}
		unset($langs, $lang);

		$this->u_action_assign_template_vars();
	}

	/**
	 * Assign language template vars to a block vars
	 * $current is for build options select menu
	 *
	 * @param array $lang
	 * @param int   $current
	 *
	 * @return void
	 * @access private
	 */
	private function assign_langs_template_vars($lang, $current = 0): void
	{
		$this->template->assign_block_vars('ppde_langs', [
			'LANG_LOCAL_NAME' => $lang['name'],
			'VALUE'           => $lang['id'],
			'S_SELECTED'      => ((int) $lang['id'] === (int) $current),
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function add(): void
	{
		// Add form key
		add_form_key('add_edit_donation_pages');

		// Collect the form data
		$data = [
			'page_title'   => $this->request->variable('page_title', ''),
			'page_lang_id' => $this->request->variable('lang_id', '', true),
			'page_content' => $this->request->variable('page_content', '', true),
			'bbcode'       => !$this->request->variable('disable_bbcode', false),
			'magic_url'    => !$this->request->variable('disable_magic_url', false),
			'smilies'      => !$this->request->variable('disable_smilies', false),
		];

		// Set template vars for language select menu
		$this->create_language_options($data['page_lang_id']);

		// Process the new page
		$this->add_edit_donation_page_data($this->ppde_entity, $data);

		// Set output vars for display in the template
		$this->add_edit_action_assign_template_vars('add');
	}

	/**
	 * Set template var options for language select menus
	 *
	 * @param int $current ID of the language assigned to the donation page
	 *
	 * @return void
	 * @access protected
	 */
	protected function create_language_options($current): void
	{
		// Grab all available language packs
		$langs = $this->ppde_operator->get_languages();

		// Set the options list template vars
		foreach ($langs as $lang)
		{
			$this->assign_langs_template_vars($lang, $current);
		}
	}

	/**
	 * Process donation pages data to be added or edited
	 *
	 * @param \skouat\ppde\entity\donation_pages $entity The donation pages entity object
	 * @param array                              $data   The form data to be processed
	 *
	 * @return void
	 * @access private
	 */
	private function add_edit_donation_page_data(\skouat\ppde\entity\donation_pages $entity, $data): void
	{
		// Get form's POST actions (submit or preview)
		$this->submit = $this->request->is_set_post('submit');
		$this->preview = $this->request->is_set_post('preview');

		// Create an array to collect errors that will be output to the user
		$errors = [];

		// Load posting language file for the BBCode editor
		$this->language->add_lang('posting');

		$message_parse_options = [
			'bbcode'    => $data['bbcode'],
			'magic_url' => $data['magic_url'],
			'smilies'   => $data['smilies'],
		];

		// Set the message parse options in the entity
		foreach ($message_parse_options as $function => $enabled)
		{
			call_user_func([$entity, ($enabled ? 'message_enable_' : 'message_disable_') . $function]);
		}

		unset($message_parse_options);

		// Set the donation page's data in the entity
		$item_fields = [
			'lang_id' => $data['page_lang_id'],
			'name'    => $data['page_title'],
			'message' => $data['page_content'],
		];
		$entity->set_entity_data($item_fields);

		// Check some settings before loading and submitting form
		$errors = array_merge($errors,
			$this->is_invalid_form('add_edit_' . $this->module_name, $this->submit_or_preview($this->submit)),
			$this->is_empty_data($entity, 'name', '', $this->submit_or_preview($this->submit)),
			$this->is_empty_data($entity, 'lang_id', 0, $this->submit_or_preview($this->submit))
		);

		// Grab predefined template vars
		$vars = $this->ppde_actions_vars->get_vars();

		// Assign variables in a template block vars
		$this->assign_preview_template_vars($entity, $errors);
		$this->assign_predefined_block_vars($vars);

		// Submit form data
		$this->submit_data($entity, $errors);

		// Set output vars for display in the template
		$this->s_error_assign_template_vars($errors);
		$this->template->assign_vars([
			'DONATION_BODY'                  => $entity->get_message_for_edit(),
			'L_DONATION_PAGES_TITLE'         => $this->language->lang(strtoupper($entity->get_name())),
			'L_DONATION_PAGES_TITLE_EXPLAIN' => $this->language->lang(strtoupper($entity->get_name()) . '_EXPLAIN'),

			'BBCODE_STATUS'  => $this->language->lang('BBCODE_IS_ON', '<a href="' . append_sid("{$this->phpbb_root_path}faq.{$this->php_ext}", 'mode=bbcode') . '">', '</a>'),
			'FLASH_STATUS'   => $this->language->lang('FLASH_IS_ON'),
			'IMG_STATUS'     => $this->language->lang('IMAGES_ARE_ON'),
			'SMILIES_STATUS' => $this->language->lang('SMILIES_ARE_ON'),
			'URL_STATUS'     => $this->language->lang('URL_IS_ON'),

			'S_BBCODE_ALLOWED'  => true,
			'S_SMILIES_ALLOWED' => true,
			'S_HIDDEN_FIELDS'   => '<input type="hidden" name="page_title" value="' . $entity->get_name() . '">',
		]);

		// Display custom bbcodes and smilies
		$this->include_custom_bbcodes($this->user->optionget('bbcode') || $entity->message_bbcode_enabled());
		$this->include_smilies($this->user->optionget('smilies') || $entity->message_smilies_enabled());
	}

	/**
	 * Assign vars to the template if preview is true.
	 *
	 * @param \skouat\ppde\entity\donation_pages $entity The donation pages entity object
	 * @param array                              $errors
	 *
	 * @return void
	 * @access private
	 */
	private function assign_preview_template_vars(\skouat\ppde\entity\donation_pages $entity, $errors): void
	{
		if ($this->preview && empty($errors))
		{
			// Set output vars for display in the template
			$this->template->assign_vars([
				'PPDE_DP_PREVIEW'   => $this->ppde_actions_vars->replace_template_vars($entity->get_message_for_display()),
				'S_PPDE_DP_PREVIEW' => $this->preview,
			]);
		}
	}

	/**
	 * Assign Predefined variables to a template block_vars
	 *
	 * @param array $vars
	 *
	 * @return void
	 * @access private
	 */
	private function assign_predefined_block_vars($vars): void
	{
		foreach ($vars as $var)
		{
			$this->template->assign_block_vars('dp_vars', [
					'NAME'     => $var['name'],
					'VARIABLE' => $var['var'],
					'EXAMPLE'  => $var['value']]
			);
		}
	}

	/**
	 *  Submit data to the database
	 *
	 * @param \skouat\ppde\entity\donation_pages $entity The donation pages entity object
	 * @param array                              $errors
	 *
	 * @return void
	 * @access private
	 */
	private function submit_data(\skouat\ppde\entity\donation_pages $entity, array $errors): void
	{
		if ($this->can_submit_data($errors))
		{
			$this->trigger_error_data_already_exists($entity);

			// Grab the local language name
			$this->get_lang_local_name($this->ppde_operator->get_languages($entity->get_lang_id()));

			$log_action = $entity->add_edit_data();
			// Log and show user confirmation of the saved item and provide link back to the previous page
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_' . strtoupper($log_action), time(), [$this->language->lang(strtoupper($entity->get_name())), $this->lang_local_name]);
			trigger_error($this->language->lang($this->lang_key_prefix . '_' . strtoupper($log_action), $this->lang_local_name) . adm_back_link($this->u_action));
		}
	}

	/**
	 * Get Local lang name
	 *
	 * @param array $langs
	 *
	 * @return void
	 * @access private
	 */
	private function get_lang_local_name($langs): void
	{
		foreach ($langs as $lang)
		{
			$this->lang_local_name = $lang['name'];
		}
	}

	/**
	 * @param bool $bbcode_enabled
	 *
	 * @return void
	 * @access private
	 */
	private function include_custom_bbcodes($bbcode_enabled): void
	{
		if ($bbcode_enabled)
		{
			$this->include_function('display_custom_bbcodes', $this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
			display_custom_bbcodes();
		}
	}

	/**
	 * Includes the file that contains the function, if not loaded.
	 *
	 * @param string $function_name     Name of the function to test
	 * @param string $function_filepath Path of the file that containing the function
	 *
	 * @return void
	 * @access private
	 */
	private function include_function($function_name, $function_filepath): void
	{
		if (!function_exists($function_name))
		{
			include($function_filepath);
		}
	}

	/**
	 * @param bool $smilies_enabled
	 *
	 * @return void
	 * @access private
	 */
	private function include_smilies($smilies_enabled): void
	{
		if ($smilies_enabled)
		{
			$this->include_function('generate_smilies', $this->phpbb_root_path . 'includes/functions_posting.' . $this->php_ext);
			generate_smilies('inline', 0);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function edit(): void
	{
		$page_id = (int) $this->args[$this->id_prefix_name . '_id'];

		// Add form key
		add_form_key('add_edit_donation_pages');

		// Load data
		$this->ppde_entity->load($page_id);

		// Collect the form data
		$data = [
			'page_id'      => $page_id,
			'page_title'   => $this->request->variable('page_title', $this->ppde_entity->get_name(), false),
			'page_lang_id' => $this->request->variable('page_lang_id', $this->ppde_entity->get_lang_id()),
			'page_content' => $this->request->variable('page_content', $this->ppde_entity->get_message_for_edit(), true),
			'bbcode'       => !$this->request->variable('disable_bbcode', false),
			'magic_url'    => !$this->request->variable('disable_magic_url', false),
			'smilies'      => !$this->request->variable('disable_smilies', false),
		];

		// Set template vars for language select menu
		$this->create_language_options($data['page_lang_id']);

		// Process the new page
		$this->add_edit_donation_page_data($this->ppde_entity, $data);

		// Set output vars for display in the template
		$this->add_edit_action_assign_template_vars('edit', $page_id);
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(): void
	{
		$page_id = (int) $this->args[$this->id_prefix_name . '_id'];

		// Load data
		$this->ppde_entity->load($page_id);

		// Before deletion, grab the local language name
		$this->get_lang_local_name($this->ppde_operator->get_languages($this->ppde_entity->get_lang_id()));

		$this->ppde_entity->delete($page_id);

		// Log the action
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_DELETED', time(), [$this->language->lang(strtoupper($this->ppde_entity->get_name())), $this->lang_local_name]);

		// If AJAX was used, show user a result message
		$message = $this->language->lang($this->lang_key_prefix . '_DELETED', $this->lang_local_name);
		$this->ajax_delete_result_message($message);
	}
}
