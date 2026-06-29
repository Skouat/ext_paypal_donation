<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\tests\operators;

class build_data_ary_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\operators\transactions */
	protected $operator;

	protected function setUp(): void
	{
		parent::setUp();

		if (!defined('ANONYMOUS'))
		{
			define('ANONYMOUS', 1);
		}

		$db = $this->createMock(\phpbb\db\driver\driver_interface::class);

		$this->operator = new \skouat\ppde\operators\transactions($db, 'phpbb_ppde_txn_log');
	}

	/**
	 * Regression guard for the build_transaction_data() bug: feeding the
	 * builder's defaults into build_data_ary() must never produce the literal
	 * string "Array" for an un-overridden field.
	 */
	public function test_build_data_ary_has_no_array_artifacts()
	{
		// Use the trait the same way the production code does.
		$builder = new class {
			use \skouat\ppde\entity\transaction_data_builder;

			public function build(array $overrides): array
			{
				return $this->build_transaction_data($overrides);
			}
		};

		$data = $this->operator->build_data_ary($builder->build(['txn_id' => 'TXN1']));

		foreach ($data as $field => $value)
		{
			$this->assertNotSame('Array', $value, "Field '$field' was cast from an array.");
			$this->assertIsNotArray($value, "Field '$field' should be scalar.");
		}

		// Spot-check a few typed defaults.
		$this->assertSame('', $data['memo']);
		$this->assertSame('', $data['payer_status']);
		$this->assertSame(0.0, $data['mc_gross']);
		$this->assertSame('TXN1', $data['txn_id']);
	}
}
