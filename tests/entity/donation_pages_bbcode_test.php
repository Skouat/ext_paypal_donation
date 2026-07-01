<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\tests\entity;

class donation_pages_bbcode_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\entity\donation_pages */
	protected $entity;

	protected function setUp(): void
	{
		parent::setUp();

		if (!defined('OPTION_FLAG_BBCODE'))
		{
			define('OPTION_FLAG_BBCODE', 1);
			define('OPTION_FLAG_MAGIC_URL', 2);
			define('OPTION_FLAG_SMILIES', 4);
			define('OPTION_FLAG_LINKS', 2);
		}

		$config = $this->createMock(\phpbb\config\config::class);
		$db = $this->createMock(\phpbb\db\driver\driver_interface::class);
		$language = $this->createMock(\phpbb\language\language::class);
		$user = $this->createMock(\phpbb\user::class);

		$this->entity = new \skouat\ppde\entity\donation_pages(
			$config, $db, $language, $user, 'phpbb_ppde_donation_pages'
		);

		$data = new \ReflectionProperty($this->entity, 'data');
		$data->setAccessible(true);
		$data->setValue($this->entity, ['page_content_bbcode_options' => 0]);
	}

	public function test_all_flags_off_by_default()
	{
		$this->assertFalse($this->entity->message_bbcode_enabled());
		$this->assertFalse($this->entity->message_magic_url_enabled());
		$this->assertFalse($this->entity->message_smilies_enabled());
	}

	public function test_enable_each_flag_independently()
	{
		$this->entity->message_enable_bbcode();
		$this->assertTrue($this->entity->message_bbcode_enabled());
		$this->assertFalse($this->entity->message_magic_url_enabled());
		$this->assertFalse($this->entity->message_smilies_enabled());

		$this->entity->message_enable_smilies();
		$this->assertTrue($this->entity->message_bbcode_enabled());
		$this->assertTrue($this->entity->message_smilies_enabled());
		$this->assertFalse($this->entity->message_magic_url_enabled());

		$this->entity->message_enable_magic_url();
		$this->assertTrue($this->entity->message_magic_url_enabled());
	}

	public function test_disable_leaves_other_flags_untouched()
	{
		$this->entity->message_enable_bbcode();
		$this->entity->message_enable_smilies();

		$this->entity->message_disable_bbcode();

		$this->assertFalse($this->entity->message_bbcode_enabled());
		$this->assertTrue($this->entity->message_smilies_enabled());
	}

	public function test_enable_is_idempotent()
	{
		$this->entity->message_enable_bbcode();
		$this->entity->message_enable_bbcode();

		$this->assertTrue($this->entity->message_bbcode_enabled());

		$this->entity->message_disable_bbcode();
		$this->assertFalse($this->entity->message_bbcode_enabled());
	}

	public function test_disable_when_already_off_is_noop()
	{
		$this->entity->message_disable_smilies();

		$this->assertFalse($this->entity->message_smilies_enabled());
	}

	public function test_lang_id_getter_setter()
	{
		$this->assertSame(0, $this->entity->get_lang_id());

		$this->entity->set_lang_id(3);
		$this->assertSame(3, $this->entity->get_lang_id());
	}

	public function test_check_required_field()
	{
		$this->assertTrue($this->entity->check_required_field());

		$this->entity->set_name('donation_body');
		$this->entity->set_lang_id(1);
		$this->assertFalse($this->entity->check_required_field());
	}
}
