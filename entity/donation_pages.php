<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\entity;

/**
 * Entity for a donation page
 */
class donation_pages extends main implements donation_pages_interface
{
	/**
	 * Data for this entity
	 *
	 * @var array
	 *    page_id
	 *    page_title
	 *    page_lang_id
	 *    page_content
	 *    page_content_bbcode_bitfield
	 *    page_content_bbcode_uid
	 *    page_content_bbcode_options
	 * @access protected
	 */
	protected $data;
	protected $dp_vars;

	protected $config;
	protected $db;
	protected $user;
	protected $donation_pages_table;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config              $config     Config object
	 * @param \phpbb\db\driver\driver_interface $db         Database object
	 * @param \phpbb\user                       $user       User object
	 * @param string                            $table_name Name of the table used to store data
	 *
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, $table_name)
	{
		$this->config = $config;
		$this->db = $db;
		$this->user = $user;
		$this->donation_pages_table = $table_name;
		parent::__construct(
			$table_name,
			'DONATION_PAGES',
			array(
				'item_id' => 'page_id',
			)
		);
	}

	/**
	 * Check the page_id exist from the database for this donation page
	 *
	 * @return int $this->data['page_id'] Donation page identifier; 0 if the page doesn't exist
	 * @access public
	 */
	public function donation_page_exists()
	{
		$sql = 'SELECT page_id
			FROM ' . $this->donation_pages_table . "
			WHERE page_title = '" . $this->db->sql_escape($this->data['page_title']) . "'
			AND page_lang_id = " . (int) $this->data['page_lang_id'];
		$result = $this->db->sql_query($sql);
		$this->data['page_id'] = (int) $this->db->sql_fetchfield('page_id');
		$this->db->sql_freeresult($result);

		return $this->data['page_id'];
	}

	/**
	 * Import and validate data for donation page
	 *
	 * Used when the data is already loaded externally.
	 * Any existing data on this page is over-written.
	 * All data is validated and an exception is thrown if any data is invalid.
	 *
	 * @param array $data Data array, typically from the database
	 *
	 * @return donation_pages_interface $this->data object
	 * @access public
	 */
	public function import($data)
	{
		// Clear out any saved data
		$this->data = array();

		// All of our fields
		$fields = array(
			// column						=> data type (see settype())
			'page_id'                      => 'integer',
			'page_title'                   => 'string',
			'page_lang_id'                 => 'integer',
			'page_content'                 => 'string',
			'page_content_bbcode_bitfield' => 'string',
			'page_content_bbcode_uid'      => 'string',
			'page_content_bbcode_options'  => 'integer',
		);

		// Go through the basic fields and set them to our data array
		foreach ($fields as $field => $type)
		{
			// If the data wasn't sent to us, throw an exception
			if (!isset($data[$field]))
			{
				$this->display_error_message('PPDE_FIELD_MISSING');
			}

			// settype passes values by reference
			$value = $data[$field];

			// We're using settype to enforce data types
			settype($value, $type);

			$this->data[$field] = $value;
		}

		return $this->data;
	}

	/**
	 * Insert the item for the first time
	 *
	 * Will throw an exception if the item was already inserted (call save() instead)
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function insert()
	{
		if (!empty($this->data['page_id']))
		{
			// The page already exists
			$this->display_error_message('PPDE_PAGE_EXIST');
		}

		// Make extra sure there is no page_id set
		unset($this->data['page_id']);

		// Insert the page data to the database
		$sql = 'INSERT INTO ' . $this->donation_pages_table . ' ' . $this->db->sql_build_array('INSERT', $this->data);
		$this->db->sql_query($sql);

		// Set the page_id using the id created by the SQL insert
		$this->data['page_id'] = (int) $this->db->sql_nextid();

		return $this;
	}

	/**
	 * Save the current settings to the database
	 *
	 * This must be called before closing or any changes will not be saved!
	 * If adding a page (saving for the first time), you must call insert() or an exception will be thrown
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function save()
	{
		if (empty($this->data['page_title']) || empty($this->data['page_lang_id']))
		{
			// The page already exists
			$this->display_error_message('PPDE_NO_DONATION_PAGES');
		}

		$sql = 'UPDATE ' . $this->donation_pages_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $this->data) . '
			WHERE page_id = ' . $this->get_id();
		$this->db->sql_query($sql);

		return $this;
	}

	/**
	 * Get id
	 *
	 * @return int Page identifier
	 * @access public
	 */
	public function get_id()
	{
		return (isset($this->data['page_id'])) ? (int) $this->data['page_id'] : 0;
	}

	/**
	 * Get template vars
	 *
	 * @return array $this->dp_vars
	 * @access public
	 */
	public function get_vars()
	{
		$this->dp_vars = array(
			0 => array(
				'var'   => '{USER_ID}',
				'value' => $this->user->data['user_id'],
			),
			1 => array(
				'var'   => '{USERNAME}',
				'value' => $this->user->data['username'],
			),
			2 => array(
				'var'   => '{SITE_NAME}',
				'value' => $this->config['sitename'],
			),
			3 => array(
				'var'   => '{SITE_DESC}',
				'value' => $this->config['site_desc'],
			),
			4 => array(
				'var'   => '{BOARD_CONTACT}',
				'value' => $this->config['board_contact'],
			),
			5 => array(
				'var'   => '{BOARD_EMAIL}',
				'value' => $this->config['board_email'],
			),
			6 => array(
				'var'   => '{BOARD_SIG}',
				'value' => $this->config['board_email_sig'],
			),
		);

		//Add language entries for displaying the vars
		for ($i = 0, $size = sizeof($this->dp_vars); $i < $size; $i++)
		{
			$this->dp_vars[$i]['name'] = $this->user->lang['PPDE_DP_' . substr(substr($this->dp_vars[$i]['var'], 0, -1), 1)];
		}

		return $this->dp_vars;
	}

	/**
	 * Get language id
	 *
	 * @return int Lang identifier
	 * @access public
	 */
	public function get_lang_id()
	{
		return (isset($this->data['page_lang_id'])) ? (int) $this->data['page_lang_id'] : 0;
	}

	/**
	 * Set Lang identifier
	 *
	 * @param int $lang
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_lang_id($lang)
	{
		// Set the lang_id on our data array
		$this->data['page_lang_id'] = (int) $lang;

		return $this;
	}

	/**
	 * Get Page title
	 *
	 * @return string Title page
	 * @access public
	 */
	public function get_title()
	{
		return (isset($this->data['page_title'])) ? (string) $this->data['page_title'] : '';
	}

	/**
	 * Set Page title
	 *
	 * @param string $title
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_title($title)
	{
		// Set the item type on our data array
		$this->data['page_title'] = (string) $title;

		return $this;
	}

	/**
	 * Get message for edit
	 *
	 * @return string
	 * @access public
	 */
	public function get_message_for_edit()
	{
		// Use defaults if these haven't been set yet
		$message = (isset($this->data['page_content'])) ? $this->data['page_content'] : '';
		$uid = (isset($this->data['page_content_bbcode_uid'])) ? $this->data['page_content_bbcode_uid'] : '';
		$options = (isset($this->data['page_content_bbcode_options'])) ? (int) $this->data['page_content_bbcode_options'] : 0;

		// Generate for edit
		$message_data = generate_text_for_edit($message, $uid, $options);

		return $message_data['text'];
	}

	/**
	 * Get message for display
	 *
	 * @param bool $censor_text True to censor the text (Default: true)
	 *
	 * @return string
	 * @access public
	 */
	public function get_message_for_display($censor_text = true)
	{
		// If these haven't been set yet; use defaults
		$message = (isset($this->data['page_content'])) ? $this->data['page_content'] : '';
		$uid = (isset($this->data['page_content_bbcode_uid'])) ? $this->data['page_content_bbcode_uid'] : '';
		$bitfield = (isset($this->data['page_content_bbcode_bitfield'])) ? $this->data['page_content_bbcode_bitfield'] : '';
		$options = (isset($this->data['page_content_bbcode_options'])) ? (int) $this->data['page_content_bbcode_options'] : 0;

		// Generate for display
		return generate_text_for_display($message, $uid, $bitfield, $options, $censor_text);
	}

	/**
	 * Replace template vars in the message
	 *
	 * @param string $message
	 *
	 * @return string
	 * @access public
	 */
	public function replace_template_vars($message)
	{
		$tpl_ary = array();
		for ($i = 0, $size = sizeof($this->dp_vars); $i < $size; $i++)
		{
			$tpl_ary[$this->dp_vars[$i]['var']] = $this->dp_vars[$i]['value'];
		}

		return str_replace(array_keys($tpl_ary), array_values($tpl_ary), $message);
	}

	/**
	 * Enable bbcode on the message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_enable_bbcode()
	{
		$this->set_message_option(OPTION_FLAG_BBCODE);

		return $this;
	}

	/**
	 * Set option helper
	 *
	 * @param int  $option_value    Value of the option
	 * @param bool $negate          Negate (unset) option (Default: False)
	 * @param bool $reparse_message Reparse the message after setting option (Default: True)
	 *
	 * @return null
	 * @access protected
	 */
	protected function set_message_option($option_value, $negate = false, $reparse_message = true)
	{
		// Set item_text_bbcode_options to 0 if it does not yet exist
		$this->data['page_content_bbcode_options'] = (isset($this->data['page_content_bbcode_options'])) ? $this->data['page_content_bbcode_options'] : 0;

		// If we're setting the option and the option is not already set
		if (!$negate && !($this->data['page_content_bbcode_options'] & $option_value))
		{
			// Add the option to the options
			$this->data['page_content_bbcode_options'] += $option_value;
		}

		// If we're unsetting the option and the option is already set
		if ($negate && $this->data['page_content_bbcode_options'] & $option_value)
		{
			// Subtract the option from the options
			$this->data['page_content_bbcode_options'] -= $option_value;
		}

		// Reparse the message
		if ($reparse_message && !empty($this->data['page_content']))
		{
			$message = $this->data['page_content'];

			decode_message($message, $this->data['page_content_bbcode_uid']);

			$this->set_message($message);
		}
	}

	/**
	 * Set message
	 *
	 * @param string $message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_message($message)
	{
		// Prepare the text for storage
		$uid = $bitfield = $flags = '';
		generate_text_for_storage($message, $uid, $bitfield, $flags, $this->message_bbcode_enabled(), $this->message_magic_url_enabled(), $this->message_smilies_enabled());

		// Set the message to our data array
		$this->data['page_content'] = $message;
		$this->data['page_content_bbcode_uid'] = $uid;
		$this->data['page_content_bbcode_bitfield'] = $bitfield;

		// Flags are already set

		return $this;
	}

	/**
	 * Check if bbcode is enabled on the message
	 *
	 * @return bool
	 * @access public
	 */
	public function message_bbcode_enabled()
	{
		return ($this->data['page_content_bbcode_options'] & OPTION_FLAG_BBCODE);
	}

	/**
	 * Check if magic_url is enabled on the message
	 *
	 * @return bool
	 * @access public
	 */
	public function message_magic_url_enabled()
	{
		return ($this->data['page_content_bbcode_options'] & OPTION_FLAG_LINKS);
	}

	/**
	 * Check if smilies are enabled on the message
	 *
	 * @return bool
	 * @access public
	 */
	public function message_smilies_enabled()
	{
		return ($this->data['page_content_bbcode_options'] & OPTION_FLAG_SMILIES);
	}

	/**
	 * Disable bbcode on the message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_disable_bbcode()
	{
		$this->set_message_option(OPTION_FLAG_BBCODE, true);

		return $this;
	}

	/**
	 * Enable magic url on the message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_enable_magic_url()
	{
		$this->set_message_option(OPTION_FLAG_LINKS);

		return $this;
	}

	/**
	 * Disable magic url on the message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_disable_magic_url()
	{
		$this->set_message_option(OPTION_FLAG_LINKS, true);

		return $this;
	}

	/**
	 * Enable smilies on the message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_enable_smilies()
	{
		$this->set_message_option(OPTION_FLAG_SMILIES);

		return $this;
	}

	/**
	 * Disable smilies on the message
	 *
	 * @return donation_pages_interface $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_disable_smilies()
	{
		$this->set_message_option(OPTION_FLAG_SMILIES, true);

		return $this;
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
