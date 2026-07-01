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

class transaction_data_builder_test extends \phpbb_test_case
{
	/** @var object Anonymous class using the trait under test */
	protected $builder;

	protected function setUp(): void
	{
		parent::setUp();

		if (!defined('ANONYMOUS'))
		{
			define('ANONYMOUS', 1);
		}

		// Expose the protected trait methods publicly for testing.
		$this->builder = new class {
			use \skouat\ppde\entity\transaction_data_builder;

			public function build(array $overrides): array
			{
				return $this->build_transaction_data($overrides);
			}

			public function template(): array
			{
				return $this->transaction_data_template();
			}
		};
	}

	public function test_defaults_applied_when_not_overridden()
	{
		$data = $this->builder->build([]);

		// Regression guard: scalar defaults, not [default, type] pairs.
		$this->assertSame('', $data['business']);
		$this->assertSame(0.0, $data['mc_gross']);
		$this->assertFalse($data['confirmed']);
		$this->assertSame(ANONYMOUS, $data['user_id']);
	}

	public function test_overrides_take_precedence()
	{
		$data = $this->builder->build([
			'mc_gross'  => 50.0,
			'txn_id'    => 'ABC123',
			'confirmed' => true,
		]);

		$this->assertSame(50.0, $data['mc_gross']);
		$this->assertSame('ABC123', $data['txn_id']);
		$this->assertTrue($data['confirmed']);
	}

	public function test_every_template_key_is_present()
	{
		$data = $this->builder->build(['txn_id' => 'X']);

		foreach (array_keys($this->builder->template()) as $key)
		{
			$this->assertArrayHasKey($key, $data, "Missing key '$key' in built data.");
		}
	}
}
