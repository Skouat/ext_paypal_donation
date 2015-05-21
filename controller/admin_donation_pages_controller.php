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

class admin_donation_pages_controller implements admin_donation_pages_interface
{
	protected $lang_local_name;
	protected $u_action;

	protected $container;
	protected $ppde_operator_donation_pages;
	protected $request;
	protected $template;
	protected $user;
	protected $phpbb_root_path;
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface                    $container                    Service container interface
	 * @param \skouat\ppde\operators\donation_pages $ppde_operator_donation_pages Operator object
	 * @param \phpbb\request\request                $request                      Request object
	 * @param \phpbb\template\template              $template                     Template object
	 * @param \phpbb\user                           $user                         User object
	 * @param string                                $phpbb_root_path              phpBB root path
	 * @param string                                $php_ext                      phpEx
	 *
	 * @access public
	 */
	public function __construct(ContainerInterface $container, \skouat\ppde\operators\donation_pages $ppde_operator_donation_pages, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $phpbb_root_path, $php_ext)
	{
		$this->container = $container;
		$this->ppde_operator_donation_pages = $ppde_operator_donation_pages;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
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
		$langs = $this->ppde_operator_donation_pages->get_languages();

		// Set output vars
		foreach ($langs as $lang => $entry)
		{
			$this->template->assign_block_vars('ppde_langs', array(
				'LANG_LOCAL_NAME' => $entry['name'],
			));

			// Grab language id
			$lang_id = $entry['id'];

			// Grab all the pages from the db
			$entities = $this->ppde_operator_donation_pages->get_pages_data($lang_id);

			foreach ($entities as $page)
			{
				// Do not treat the item whether language identifier does not match
				if ($page['page_lang_id'] != $lang_id)
				{
					continue;
				}

				$this->template->assign_block_vars('ppde_langs.dp_list', array(
					'DONATION_PAGE_TITLE' => $this->user->lang[strtoupper($page['page_title'])],
					'DONATION_PAGE_LANG'  => (string) $lang,

					'U_DELETE'            => $this->u_action . '&amp;action=delete&amp;page_id=' . $page['page_id'],
					'U_EDIT'              => $this->u_action . '&amp;action=edit&amp;page_id=' . $page['page_id'],
				));
			}
			unset($entities, $page);
		}
		unset($entry, $langs, $lang);

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'U_ACTION' => $this->u_action,
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
		$entity = $this->container->get('skouat.ppde.entity.donation_pages');

		// Collect the form data
		$data = array(
			'page_title'   => $this->request->variable('page_title', ''),
			'page_lang_id' => $this->request->variable('lang_id', '', true),
			'page_content' => $this->request->variable('page_content', '', true),
			'bbcode'       => !$this->request->variable('disable_bbcode', false),
			'magic_url'    => !$this->request->variable('disable_magic_url', false),
			'smilies'      => !$this->request->variable('disable_smilies', false),
		);

		// Set template vars for language select menu
		$this->create_language_options($data['page_lang_id']);

		// Process the new page
		$this->add_edit_donation_page_data($entity, $data);

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ADD_DONATION_PAGE' => true,

			'U_ADD_ACTION'        => $this->u_action . '&amp;action=add',
			'U_BACK'              => $this->u_action,
		));
	}

	/**
	 * Set template var options for language select menus
	 *
	 * @param string $current ID of the language assigned to the donation page
	 *
	 * @return null
	 * @access protected
	 */
	protected function create_language_options($current)
	{
		// Grab all available language packs
		$langs = $this->ppde_operator_donation_pages->get_languages();

		// Set the options list template vars
		foreach ($langs as $lang)
		{
			$this->template->assign_block_vars('ppde_langs', array(
				'LANG_LOCAL_NAME' => $lang['name'],
				'VALUE'           => $lang['id'],
				'S_SELECTED'      => ($lang['id'] == $current) ? true : false,
			));
		}
	}

	/**
	 * Process donation pages data to be added or edited
	 *
	 * @param object $entity The donation pages entity object
	 * @param array  $data   The form data to be processed
	 *
	 * @return null
	 * @access protected
	 */
	protected function add_edit_donation_page_data($entity, $data)
	{
		// Get form's POST actions (submit or preview)
		$submit = $this->request->is_set_post('submit');
		$preview = $this->request->is_set_post('preview');

		// Load posting language file for the BBCode editor
		$this->user->add_lang('posting');

		// Create an array to collect errors that will be output to the user
		$errors = array();

		// Grab Template vars
		$dp_vars = $entity->get_vars();

		// Grab the form data's message parsing options (possible values: 1 or 0)
		$message_parse_options = array(
			'bbcode'    => ($submit || $preview) ? $data['bbcode'] : $entity->message_bbcode_enabled(),
			'magic_url' => ($submit || $preview) ? $data['magic_url'] : $entity->message_magic_url_enabled(),
			'smilies'   => ($submit || $preview) ? $data['smilies'] : $entity->message_smilies_enabled(),
		);

		// Set the message parse options in the entity
		foreach ($message_parse_options as $function => $enabled)
		{
			call_user_func(array($entity, ($enabled ? 'message_enable_' : 'message_disable_') . $function));
		}

		unset($message_parse_options);

		// Grab the form's data fields
		$item_fields = array(
			'lang_id' => $data['page_lang_id'],
			'title'   => $data['page_title'],
			'message' => $data['page_content'],
		);

		// Set the donation page's data in the entity
		foreach ($item_fields as $entity_function => $page_data)
		{
			// Calling the set_$entity_function on the entity and passing it $dp_data
			call_user_func_array(array($entity, 'set_' . $entity_function), array($page_data));
		}
		unset($item_fields, $entity_function, $page_data);

		// If the form has been submitted or previewed
		if ($submit || $preview)
		{
			// Test if the form is valid
			if (!check_form_key('add_edit_donation_page'))
			{
				$errors[] = $this->user->lang('FORM_INVALID');
			}

			// Do not allow an empty item name
			if ($entity->get_title() == '')
			{
				$errors[] = $this->user->lang('PPDE_MUST_SELECT_PAGE');
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
				'S_PPDE_DP_PREVIEW' => $preview,

				'PPDE_DP_PREVIEW'   => $entity->replace_template_vars($entity->get_message_for_display()),
			));
		}

		// Insert or update donation page
		if ($submit && empty($errors) && !$preview)
		{
			if ($entity->donation_page_exists() && $this->request->variable('action', '') === 'add')
			{
				// Show user warning for an already exist page and provide link back to the edit page
				$message = $this->user->lang('PPDE_PAGE_EXISTS');
				$message .= '<br /><br />';
				$message .= $this->user->lang('PPDE_DP_GO_TO_PAGE', '<a href="' . $this->u_action . '&amp;action=edit&amp;page_id=' . $entity->get_id() . '">&raquo; ', '</a>');
				trigger_error($message . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// Grab the local language name
			$this->get_lang_local_name($this->ppde_operator_donation_pages->get_languages($entity->get_lang_id()));

			if ($entity->get_id())
			{
				// Save the edited item entity to the database
				$entity->save();

				// Show user confirmation of the saved item and provide link back to the previous page
				trigger_error($this->user->lang('PPDE_DP_LANG_UPDATED', $this->lang_local_name) . adm_back_link($this->u_action));
			}
			else
			{
				// Add a new item entity to the database
				$this->ppde_operator_donation_pages->add_pages_data($entity);

				// Show user confirmation of the added item and provide link back to the previous page
				trigger_error($this->user->lang('PPDE_DP_LANG_ADDED', $this->lang_local_name) . adm_back_link($this->u_action));
			}
		}

		// Assigning predefined variables in a template block vars
		for ($i = 0, $size = sizeof($dp_vars); $i < $size; $i++)
		{
			$this->template->assign_block_vars('dp_vars', array(
					'NAME'		=> $dp_vars[$i]['name'],
					'VARIABLE'	=> $dp_vars[$i]['var'],
					'EXAMPLE'	=> $dp_vars[$i]['value'])
			);
		}

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_ERROR'                        => (sizeof($errors)) ? true : false,
			'ERROR_MSG'                      => (sizeof($errors)) ? implode('<br />', $errors) : '',

			'L_DONATION_PAGES_TITLE'         => $this->user->lang(strtoupper($entity->get_title())),
			'L_DONATION_PAGES_TITLE_EXPLAIN' => $this->user->lang(strtoupper($entity->get_title()) . '_EXPLAIN'),
			'DONATION_BODY'                  => $entity->get_message_for_edit(),

			'S_BBCODE_DISABLE_CHECKED'       => !$entity->message_bbcode_enabled(),
			'S_SMILIES_DISABLE_CHECKED'      => !$entity->message_smilies_enabled(),
			'S_MAGIC_URL_DISABLE_CHECKED'    => !$entity->message_magic_url_enabled(),

			'BBCODE_STATUS'                  => $this->user->lang('BBCODE_IS_ON', '<a href="' . append_sid("{$this->phpbb_root_path}faq.{$this->php_ext}", 'mode=bbcode') . '">', '</a>'),
			'SMILIES_STATUS'                 => $this->user->lang('SMILIES_ARE_ON'),
			'IMG_STATUS'                     => $this->user->lang('IMAGES_ARE_ON'),
			'FLASH_STATUS'                   => $this->user->lang('FLASH_IS_ON'),
			'URL_STATUS'                     => $this->user->lang('URL_IS_ON'),

			'S_BBCODE_ALLOWED'               => true,
			'S_SMILIES_ALLOWED'              => true,
			'S_BBCODE_IMG'                   => true,
			'S_BBCODE_FLASH'                 => true,
			'S_LINKS_ALLOWED'                => true,
			'S_HIDDEN_FIELDS'                => '<input type="hidden" name="page_title" value="' . $entity->get_title() . '" />',
		));

		// Assigning custom bbcodes
		include_once($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);

		display_custom_bbcodes();
	}

	/**
	 * Get Local lang name
	 *
	 * @param array $langs
	 *
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
	 * Edit a donation page
	 *
	 * @param int $page_id Donation page identifier
	 *
	 * @return null
	 * @access public
	 */
	public function edit_donation_page($page_id)
	{
		// Add form key
		add_form_key('add_edit_donation_page');

		// Initiate a page donation entity
		$entity = $this->container->get('skouat.ppde.entity.donation_pages')->load($page_id);

		// Collect the form data
		$data = array(
			'page_id'      => (int) $page_id,
			'page_title'   => $this->request->variable('page_title', $entity->get_title(), false),
			'page_lang_id' => $this->request->variable('page_lang_id', $entity->get_lang_id()),
			'page_content' => $this->request->variable('page_content', $entity->get_message_for_edit(), true),
			'bbcode'       => !$this->request->variable('disable_bbcode', false),
			'magic_url'    => !$this->request->variable('disable_magic_url', false),
			'smilies'      => !$this->request->variable('disable_smilies', false),
		);

		// Set template vars for language select menu
		$this->create_language_options($data['page_lang_id']);

		// Process the new page
		$this->add_edit_donation_page_data($entity, $data);

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'S_EDIT_DONATION_PAGE' => true,

			'U_EDIT_ACTION'        => $this->u_action . '&amp;action=edit&amp;page_id=' . $page_id,
			'U_BACK'               => $this->u_action,
		));
	}

	/**
	 * Delete a donation page
	 *
	 * @param int $page_id The donation page identifier to delete
	 *
	 * @return null
	 * @access public
	 */
	public function delete_donation_page($page_id)
	{
		// Use a confirmation box routine when deleting a donation page
		if (confirm_box(true))
		{
			// Initiate a page donation entity
			$entity = $this->container->get('skouat.ppde.entity.donation_pages');

			// Before deletion, grab the local language name
			$this->get_lang_local_name($this->ppde_operator_donation_pages->get_languages($entity->get_lang_id()));

			// Delete the donation page on confirmation
			$this->ppde_operator_donation_pages->delete_page($page_id);

			// Show user confirmation of the deleted donation page and provide link back to the previous page
			trigger_error($this->user->lang('PPDE_DP_LANG_DELETED', $this->lang_local_name) . adm_back_link($this->u_action));
		}
		else
		{
			// Request confirmation from the user to delete the rule
			confirm_box(false, $this->user->lang('PPDE_DP_CONFIRM_DELETE'), build_hidden_fields(array(
				'mode'    => 'donation_pages',
				'action'  => 'delete',
				'page_id' => $page_id,
			)));

			// Use a redirect to take the user back to the previous page
			// if the user chose not delete the donation page from the confirmation page.
			redirect($this->u_action);
		}
	}

	/**
	 * Set page url
	 *
	 * @param string $u_action Custom form action
	 *
	 * @return null
	 * @access public
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
