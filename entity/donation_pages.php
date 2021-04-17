<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\entity;

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\user;

/**
 * @property driver_interface db                 phpBB Database object
 * @property language         language           phpBB Language object
 * @property string           lang_key_prefix    Prefix for the messages thrown by exceptions
 * @property string           lang_key_suffix    Suffix for the messages thrown by exceptions
 */
class donation_pages extends main
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
	protected $config;
	protected $donation_pages_table;
	protected $user;

	/**
	 * Constructor
	 *
	 * @param config           $config     Config object
	 * @param driver_interface $db         Database object
	 * @param language         $language   Language user object
	 * @param user             $user       User object
	 * @param string           $table_name Name of the table used to store data
	 *
	 * @access public
	 */
	public function __construct(
		config $config,
		driver_interface $db,
		language $language,
		user $user,
		$table_name
	)
	{
		$this->config = $config;
		$this->donation_pages_table = $table_name;
		$this->user = $user;
		parent::__construct(
			$db,
			$language,
			'PPDE_DP',
			'DONATION_PAGES',
			$table_name,
			[
				'item_id'                      => ['name' => 'page_id', 'type' => 'integer'],
				'item_name'                    => ['name' => 'page_title', 'type' => 'string'],
				'item_lang_id'                 => ['name' => 'page_lang_id', 'type' => 'integer'],
				'item_content'                 => ['name' => 'page_content', 'type' => 'string'],
				'item_content_bbcode_bitfield' => ['name' => 'page_content_bbcode_bitfield', 'type' => 'string'],
				'item_content_bbcode_uid'      => ['name' => 'page_content_bbcode_uid', 'type' => 'string'],
				'item_content_bbcode_options'  => ['name' => 'page_content_bbcode_options', 'type' => 'integer'],
			]
		);
	}

	/**
	 * Get language id
	 *
	 * @return int Lang identifier
	 * @access public
	 */
	public function get_lang_id(): int
	{
		return (int) ($this->data['page_lang_id'] ?? 0);
	}

	/**
	 * Set Lang identifier
	 *
	 * @param int $lang
	 *
	 * @return donation_pages $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function set_lang_id($lang)
	{
		// Set the lang_id on our data array
		$this->data['page_lang_id'] = (int) $lang;

		return $this;
	}

	/**
	 * Get message for edit
	 *
	 * @return string
	 * @access public
	 */
	public function get_message_for_edit(): string
	{
		// Use defaults if these haven't been set yet
		$message = $this->data['page_content'] ?? '';
		$uid = $this->data['page_content_bbcode_uid'] ?? '';
		$options = (int) ($this->data['page_content_bbcode_options'] ?? 0);

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
	public function get_message_for_display($censor_text = true): string
	{
		// If these haven't been set yet; use defaults
		$message = $this->data['page_content'] ?? '';
		$uid = $this->data['page_content_bbcode_uid'] ?? '';
		$bitfield = $this->data['page_content_bbcode_bitfield'] ?? '';
		$options = (int) ($this->data['page_content_bbcode_options'] ?? 0);

		// Generate for display
		return generate_text_for_display($message, $uid, $bitfield, $options, $censor_text);
	}

	/**
	 * Enable bbcode on the message
	 *
	 * @return donation_pages $this object for chaining calls; load()->set()->save()
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
	 * @return void
	 * @access protected
	 */
	protected function set_message_option($option_value, $negate = false, $reparse_message = true): void
	{
		// Set item_text_bbcode_options to 0 if it does not yet exist
		$this->data['page_content_bbcode_options'] = (int) ($this->data['page_content_bbcode_options'] ?? 0);

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
	 * @return donation_pages $this object for chaining calls; load()->set()->save()
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
	public function message_bbcode_enabled(): bool
	{
		return (bool) ($this->data['page_content_bbcode_options'] & OPTION_FLAG_BBCODE);
	}

	/**
	 * Check if magic_url is enabled on the message
	 *
	 * @return bool
	 * @access public
	 */
	public function message_magic_url_enabled(): bool
	{
		return (bool) ($this->data['page_content_bbcode_options'] & OPTION_FLAG_LINKS);
	}

	/**
	 * Check if smilies are enabled on the message
	 *
	 * @return bool
	 * @access public
	 */
	public function message_smilies_enabled(): bool
	{
		return (bool) ($this->data['page_content_bbcode_options'] & OPTION_FLAG_SMILIES);
	}

	/**
	 * Disable bbcode on the message
	 *
	 * @return donation_pages $this object for chaining calls; load()->set()->save()
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
	 * @return donation_pages $this object for chaining calls; load()->set()->save()
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
	 * @return donation_pages $this object for chaining calls; load()->set()->save()
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
	 * @return donation_pages $this object for chaining calls; load()->set()->save()
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
	 * @return donation_pages $this object for chaining calls; load()->set()->save()
	 * @access public
	 */
	public function message_disable_smilies()
	{
		$this->set_message_option(OPTION_FLAG_SMILIES, true);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function build_sql_data_exists(): string
	{
		return 'SELECT page_id
			FROM ' . $this->donation_pages_table . "
			WHERE page_title = '" . $this->db->sql_escape($this->data['page_title']) . "'
			AND page_lang_id = " . (int) $this->data['page_lang_id'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function check_required_field(): bool
	{
		return empty($this->data['page_title']) || empty($this->data['page_lang_id']);
	}
}
