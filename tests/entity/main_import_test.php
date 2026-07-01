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

class main_import_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\entity\currency A concrete entity to exercise main::import(). */
	protected $entity;

	protected function setUp(): void
	{
		parent::setUp();

		$db = $this->createMock(\phpbb\db\driver\driver_interface::class);
		$language = $this->createMock(\phpbb\language\language::class);
		$language->method('lang')->willReturnArgument(0);

		$this->entity = new \skouat\ppde\entity\currency($db, $language, 'phpbb_ppde_currency');
	}

	public function test_import_casts_declared_types()
	{
		$row = [
			'currency_id'       => '7',
			'currency_name'     => 'Euro',
			'currency_iso_code' => 'EUR',
			'currency_symbol'   => '&euro;',
			'currency_on_left'  => '1',
			'currency_enable'   => '0',
			'currency_order'    => '3',
		];

		$data = $this->entity->import($row);

		$this->assertSame(7, $data['currency_id']);        // integer
		$this->assertSame('Euro', $data['currency_name']); // string
		$this->assertTrue($data['currency_on_left']);      // boolean
		$this->assertFalse($data['currency_enable']);      // boolean
		$this->assertSame(3, $data['currency_order']);     // integer
	}

	/**
	 * A missing declared column makes import() trigger an E_USER_WARNING.
	 * A local error handler turns it into an ErrorException so the assertion
	 * works identically on PHPUnit 7, 8 and 9 (no Error\Warning dependency).
	 */
	public function test_import_warns_on_missing_field()
	{
		set_error_handler(static function ($errno, $errstr) {
			throw new \ErrorException($errstr, 0, $errno);
		}, E_USER_WARNING);

		try
		{
			$this->entity->import(['currency_id' => 1]); // other columns missing
			$this->fail('Expected an E_USER_WARNING for the missing declared field.');
		}
		catch (\ErrorException $e)
		{
			$this->assertStringContainsString('EXCEPTION_INVALID_FIELD', $e->getMessage());
		}
		finally
		{
			restore_error_handler();
		}
	}
}
