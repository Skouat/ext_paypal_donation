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
	protected $donation_pages_entity;
	protected $donation_pages_operator;
	protected $lang_local_name;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface                    $container               Service container interface
	 * @param language                              $language                Language user object
	 * @param log                                   $log                     The phpBB log system
	 * @param \skouat\ppde\actions\vars             $ppde_actions_vars       PPDE actions vars object
	 * @param \skouat\ppde\entity\donation_pages    $donation_pages_entity   PPDE entity object
	 * @param \skouat\ppde\operators\donation_pages $donation_pages_operator PPDE operator object
	 * @param request                               $request                 Request object
	 * @param template                              $template                Template object
	 * @param user                                  $user                    User object
	 * @param string                                $phpbb_root_path         phpBB root path
	 * @param string                                $php_ext                 phpEx
	 */
	public function __construct(
		ContainerInterface $container,
		language $language,
		log $log,
		\skouat\ppde\actions\vars $ppde_actions_vars,
		\skouat\ppde\entity\donation_pages $donation_pages_entity,
		\skouat\ppde\operators\donation_pages $donation_pages_operator,
		request $request,
		template $template,
		user $user,
		string $phpbb_root_path,
		string $php_ext
	)
	{
		$this->container = $container;
		$this->language = $language;
		$this->log = $log;
		$this->ppde_actions_vars = $ppde_actions_vars;
		$this->donation_pages_entity = $donation_pages_entity;
		$this->donation_pages_operator = $donation_pages_operator;
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
	 * Display donation pages.
	 */
	public function display(): void
	{
		// Get list of available language packs
		$langs = $this->donation_pages_operator->get_languages();

		// Set output vars
		foreach ($langs as $lang => $entry)
		{
			$this->assign_langs_template_vars($entry);

			// Grab all the pages from the db
			$this->display_donation_pages_for_language($entry, $lang);
		}
		unset($langs, $lang);

		$this->u_action_assign_template_vars();
	}

	/**
	 * Assign language variables to template block_vars.
	 *
	 * @param array $lang    Language data
	 * @param int   $current Currently selected language ID (default: 0)
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
	 * Display donation pages for a specific language.
	 *
	 * @param array  $entry Language entry data.
	 * @param string $lang  Language code.
	 */
	private function display_donation_pages_for_language(array $entry, string $lang): void
	{
		// Grab all the pages from the db
		$data_ary = $this->donation_pages_entity->get_data($this->donation_pages_operator->build_sql_data($entry['id']));

		foreach ($data_ary as $data)
		{
			// Do not treat the item whether language identifier does not match
			if ((int) $data['page_lang_id'] !== (int) $entry['id'])
			{
				continue;
			}

			$this->assign_donation_pages_template_vars($data, $lang);
		}

		unset($data_ary);
	}

	/**
	 * Assign donation page variables to template block_vars.
	 *
	 * @param array  $data Some data about the donation page.
	 * @param string $lang The language of the donation page.
	 */
	private function assign_donation_pages_template_vars(array $data, string $lang): void
	{
		$this->template->assign_block_vars('ppde_langs.dp_list', [
			'DONATION_PAGE_TITLE' => $this->language->lang(strtoupper($data['page_title'])),
			'DONATION_PAGE_LANG'  => $lang,
			'U_DELETE'            => $this->u_action . '&amp;action=delete&amp;' . $this->id_prefix_name . '_id=' . $data['page_id'],
			'U_EDIT'              => $this->u_action . '&amp;action=edit&amp;' . $this->id_prefix_name . '_id=' . $data['page_id'],
		]);
	}

	/**
	 * Add a new donation page.
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
		$this->add_edit_donation_page_data($data);

		// Set output vars for display in the template
		$this->add_edit_action_assign_template_vars('add');
	}

	/**
	 * Create language options for select menu.
	 *
	 * @param int $current ID of the language assigned to the donation page
	 */
	protected function create_language_options($current): void
	{
		// Grab all available language packs
		$langs = $this->donation_pages_operator->get_languages();

		// Set the options list template vars
		foreach ($langs as $lang)
		{
			$this->assign_langs_template_vars($lang, $current);
		}
	}

	/**
	 * Process donation page data for adding or editing.
	 *
	 * @param array $data The form data to be processed.
	 */
	private function add_edit_donation_page_data($data): void
	{
		// Get form's POST actions (submit or preview)
		$this->submit = $this->is_form_submitted();
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
			$this->donation_pages_entity->{($enabled ? 'message_enable_' : 'message_disable_') . $function}();
		}

		unset($message_parse_options);

		// Set the donation page's data in the entity
		$item_fields = [
			'lang_id' => $data['page_lang_id'],
			'name'    => $data['page_title'],
			'message' => $data['page_content'],
		];
		$this->donation_pages_entity->set_entity_data($item_fields);

		// Check some settings before loading and submitting form
		$errors = array_merge($errors,
			$this->is_invalid_form('add_edit_' . $this->module_name, $this->submit_or_preview($this->submit)),
			$this->is_empty_data($this->donation_pages_entity, 'name', '', $this->submit_or_preview($this->submit)),
			$this->is_empty_data($this->donation_pages_entity, 'lang_id', 0, $this->submit_or_preview($this->submit))
		);

		// Grab predefined template vars
		$vars = $this->ppde_actions_vars->get_vars();

		// Assign variables in a template block vars
		$this->assign_preview_template_vars($errors);
		$this->assign_predefined_block_vars($vars);

		// Submit form data
		$this->submit_data($errors);

		// Set output vars for display in the template
		$this->s_error_assign_template_vars($errors);
		$this->template->assign_vars([
			'DONATION_BODY'                  => $this->donation_pages_entity->get_message_for_edit(),
			'L_DONATION_PAGES_TITLE'         => $this->language->lang(strtoupper($this->donation_pages_entity->get_name())),
			'L_DONATION_PAGES_TITLE_EXPLAIN' => $this->language->lang(strtoupper($this->donation_pages_entity->get_name()) . '_EXPLAIN'),

			'BBCODE_STATUS'  => $this->language->lang('BBCODE_IS_ON', '<a href="' . append_sid("{$this->phpbb_root_path}faq.{$this->php_ext}", 'mode=bbcode') . '">', '</a>'),
			'FLASH_STATUS'   => $this->language->lang('FLASH_IS_ON'),
			'IMG_STATUS'     => $this->language->lang('IMAGES_ARE_ON'),
			'SMILIES_STATUS' => $this->language->lang('SMILIES_ARE_ON'),
			'URL_STATUS'     => $this->language->lang('URL_IS_ON'),

			'S_BBCODE_ALLOWED'  => true,
			'S_SMILIES_ALLOWED' => true,
			'S_HIDDEN_FIELDS'   => '<input type="hidden" name="page_title" value="' . $this->donation_pages_entity->get_name() . '">',
		]);

		// Display custom bbcodes and smilies
		$this->include_custom_bbcodes($this->user->optionget('bbcode') || $this->donation_pages_entity->message_bbcode_enabled());
		$this->include_smilies($this->user->optionget('smilies') || $this->donation_pages_entity->message_smilies_enabled());
	}

	/**
	 * Assign template variables for previewing donation page content.
	 *
	 * @param array $errors An array of error messages.
	 */
	private function assign_preview_template_vars($errors): void
	{
		if ($this->preview && empty($errors))
		{
			// Set output vars for display in the template
			$this->template->assign_vars([
				'PPDE_DP_PREVIEW'   => $this->ppde_actions_vars->replace_template_vars($this->donation_pages_entity->get_message_for_display()),
				'S_PPDE_DP_PREVIEW' => $this->preview,
			]);
		}
	}

	/**
	 * Assign predefined variables to template block_vars.
	 *
	 * @param array $vars Variables to be assigned.
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
	 * Submit donation page data to the database.
	 *
	 * @param array $errors Errors encountered during data processing.
	 */
	private function submit_data(array $errors): void
	{
		if (!$this->can_submit_data($errors))
		{
			return;
		}

		$this->trigger_error_data_already_exists($this->donation_pages_entity);

		// Grab the local language name
		$this->set_lang_local_name($this->donation_pages_operator->get_languages($this->donation_pages_entity->get_lang_id()));

		$log_action = $this->donation_pages_entity->add_edit_data();
		// Log and show user confirmation of the saved item and provide link back to the previous page
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_' . strtoupper($log_action), time(), [$this->language->lang(strtoupper($this->donation_pages_entity->get_name())), $this->lang_local_name]);

		trigger_error($this->language->lang($this->lang_key_prefix . '_' . strtoupper($log_action), $this->lang_local_name) . adm_back_link($this->u_action));
	}

	/**
	 * Set local language name.
	 *
	 * Extract and store the local name of the language from the provided language data.
	 *
	 * @param array $langs Array containing the details of the languages.
	 *                     Each language array should have a 'name' key.
	 *                     $langs is expected to contain only one language array.
	 */
	private function set_lang_local_name($langs): void
	{
		foreach ($langs as $lang)
		{
			$this->lang_local_name = $lang['name'];
		}
	}

	/**
	 * Include custom bbcodes if enabled.
	 *
	 * @param bool $bbcode_enabled Whether BBCode is enabled.
	 */
	private function include_custom_bbcodes($bbcode_enabled): void
	{
		if ($bbcode_enabled)
		{
			$this->include_function('functions_display', 'display_custom_bbcodes');
			display_custom_bbcodes();
		}
	}

	/**
	 * Include the specified function if it is not loaded.
	 *
	 * @param string $file          The file containing the function.
	 * @param string $function_name The name of the function.
	 */
	private function include_function($file, $function_name): void
	{
		if (!function_exists($function_name))
		{
			include($this->phpbb_root_path . 'includes/' . $file . '.' . $this->php_ext);
		}
	}

	/**
	 * Include smilies if enabled.
	 *
	 * @param bool $smilies_enabled Whether smilies are enabled.
	 */
	private function include_smilies($smilies_enabled): void
	{
		if ($smilies_enabled)
		{
			$this->include_function('functions_posting', 'generate_smilies');
			generate_smilies('inline', 0);
		}
	}

	/**
	 * Edit an existing donation page.
	 */
	public function edit(): void
	{
		$page_id = (int) $this->args[$this->id_prefix_name . '_id'];

		// Add form key
		add_form_key('add_edit_donation_pages');

		// Load data
		$this->donation_pages_entity->load($page_id);

		// Collect the form data
		$data = [
			'page_id'      => $page_id,
			'page_title'   => $this->request->variable('page_title', $this->donation_pages_entity->get_name()),
			'page_lang_id' => $this->request->variable('page_lang_id', $this->donation_pages_entity->get_lang_id()),
			'page_content' => $this->request->variable('page_content', $this->donation_pages_entity->get_message_for_edit(), true),
			'bbcode'       => !$this->request->variable('disable_bbcode', false),
			'magic_url'    => !$this->request->variable('disable_magic_url', false),
			'smilies'      => !$this->request->variable('disable_smilies', false),
		];

		// Set template vars for language select menu
		$this->create_language_options($data['page_lang_id']);

		// Process the new page
		$this->add_edit_donation_page_data($data);

		// Set output vars for display in the template
		$this->add_edit_action_assign_template_vars('edit', $page_id);
	}

	/**
	 * Delete a donation page.
	 */
	public function delete(): void
	{
		$page_id = (int) $this->args[$this->id_prefix_name . '_id'];

		// Load data
		$this->donation_pages_entity->load($page_id);

		// Before deletion, grab the local language name
		$this->set_lang_local_name($this->donation_pages_operator->get_languages($this->donation_pages_entity->get_lang_id()));

		$this->donation_pages_entity->delete($page_id);

		// Log the action
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $this->lang_key_prefix . '_DELETED', time(), [$this->language->lang(strtoupper($this->donation_pages_entity->get_name())), $this->lang_local_name]);

		// If AJAX was used, show user a result message
		$message = $this->language->lang($this->lang_key_prefix . '_DELETED', $this->lang_local_name);
		$this->ajax_delete_result_message($message);
	}
}
