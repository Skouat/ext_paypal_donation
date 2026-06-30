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

class currency_entity_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\entity\currency */
	protected $entity;

	protected function setUp(): void
	{
		parent::setUp();

		$db = $this->createMock(\phpbb\db\driver\driver_interface::class);
		$language = $this->createMock(\phpbb\language\language::class);

		$this->entity = new \skouat\ppde\entity\currency($db, $language, 'phpbb_ppde_currency');
	}

	public function test_check_required_fields()
	{
		// Set complete valid data
		$this->entity->set_name('Euro');
		$this->entity->set_iso_code('EUR');
		$this->entity->set_symbol('€');
		$this->assertFalse($this->entity->check_required_field());

		// Missing name
		$this->entity->set_name('');
		$this->assertTrue($this->entity->check_required_field());

		// Restore name but miss ISO
		$this->entity->set_name('Euro');
		$this->entity->set_iso_code('');
		$this->assertTrue($this->entity->check_required_field());

		// Restore ISO but miss symbol
		$this->entity->set_iso_code('EUR');
		$this->entity->set_symbol('');
		$this->assertTrue($this->entity->check_required_field());
	}

	public function test_symbol_html_entity_handling()
	{
		// Encodage HTML lors du set
		$this->entity->set_symbol('€');

		// L'entité stocke la version convertie en entités HTML
		$property = new \ReflectionProperty($this->entity, 'data');
		$property->setAccessible(true);
		$data = $property->getValue($this->entity);

		$this->assertSame('&euro;', $data['currency_symbol']);

		// Le getter décode automatiquement pour l'affichage
		$this->assertSame('€', $this->entity->get_symbol());
	}

	public function test_getters_and_setters()
	{
		$this->entity->set_currency_position(true);
		$this->assertTrue($this->entity->get_currency_position());

		$this->entity->set_currency_position(false);
		$this->assertFalse($this->entity->get_currency_position());

		$this->entity->set_currency_enable(true);
		$this->assertTrue($this->entity->get_currency_enable());

		$this->entity->set_iso_code('USD');
		$this->assertSame('USD', $this->entity->get_iso_code());
	}
}
