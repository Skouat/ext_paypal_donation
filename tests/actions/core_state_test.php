<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\tests\actions;

class core_state_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\actions\core */
	protected $core;
	/** @var \phpbb\config\config */
	protected $config;
	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $operator;

	protected function setUp(): void
	{
		parent::setUp();

		$this->config = new \phpbb\config\config([
			'ppde_raised'               => 100.0,
			'ppde_raised_ipn'           => 5.0,
			'ppde_ipn_min_before_group' => 20.0,
		]);

		$language       = $this->createMock(\phpbb\language\language::class);
		$notification   = $this->createMock(\skouat\ppde\notification\core::class);
		$path_helper    = $this->createMock(\phpbb\path_helper::class);
		$entity         = $this->createMock(\skouat\ppde\entity\transactions::class);
		$this->operator = $this->createMock(\skouat\ppde\operators\transactions::class);
		$dispatcher     = $this->createMock(\phpbb\event\dispatcher_interface::class);
		$user           = $this->createMock(\phpbb\user::class);

		$this->core = new \skouat\ppde\actions\core(
			$this->config, $language, $notification, $path_helper,
			$entity, $this->operator, $dispatcher, $user, 'php'
		);
	}

	public function test_sandbox_properties_live()
	{
		$this->core->set_sandbox_properties(false);

		$this->assertFalse($this->core->get_sandbox());
		$this->assertSame('', $this->core->get_config_suffix());
	}

	public function test_sandbox_properties_sandbox()
	{
		$this->core->set_sandbox_properties(true);

		$this->assertTrue($this->core->get_sandbox());
		$this->assertSame('_ipn', $this->core->get_config_suffix());
	}

	public function test_deprecated_ipn_aliases_delegate()
	{
		$this->core->set_ipn_test_properties(true);

		$this->assertTrue($this->core->get_ipn_test());
		$this->assertSame('_ipn', $this->core->get_ipn_suffix());
	}

	public function test_set_transaction_data_merges()
	{
		$this->core->set_transaction_data(['payment_status' => 'Completed']);
		$this->core->set_transaction_data(['foo' => 'bar']);

		// payment_status survives the second call => the arrays were merged.
		$this->assertTrue($this->core->payment_status_is_completed());
	}

	public function payment_status_data()
	{
		return [
			'completed' => ['Completed', true],
			'pending'   => ['Pending',   false],
			'refunded'  => ['Refunded',  false],
		];
	}

	/** @dataProvider payment_status_data */
	public function test_payment_status_is_completed($status, $expected)
	{
		$this->core->set_transaction_data(['payment_status' => $status]);

		$this->assertSame($expected, $this->core->payment_status_is_completed());
	}

	public function test_update_raised_amount_live_uses_net_amount()
	{
		$this->core->set_sandbox_properties(false);
		$this->core->set_transaction_data(['net_amount' => 10.0]);

		$this->core->update_raised_amount();

		$this->assertSame(110.0, (float) $this->config['ppde_raised']);
	}

	public function test_update_raised_amount_settle_takes_precedence()
	{
		$this->core->set_sandbox_properties(false);
		$this->core->set_transaction_data(['net_amount' => 10.0, 'settle_amount' => 7.0]);

		$this->core->update_raised_amount();

		// settle_amount (7) wins over net_amount (10).
		$this->assertSame(107.0, (float) $this->config['ppde_raised']);
	}

	public function test_update_raised_amount_sandbox_targets_ipn_key()
	{
		$this->core->set_sandbox_properties(true);
		$this->core->set_transaction_data(['net_amount' => 3.0]);

		$this->core->update_raised_amount();

		$this->assertSame(8.0, (float) $this->config['ppde_raised_ipn']);
		// Live total untouched.
		$this->assertSame(100.0, (float) $this->config['ppde_raised']);
	}

	private function set_payer_data(array $data): void
	{
		$p = new \ReflectionProperty($this->core, 'payer_data');
		$p->setAccessible(true);
		$p->setValue($this->core, $data);
	}

	public function test_minimum_donation_raised_is_a_pure_query()
	{
		// The amount check must never hit the database anymore.
		$this->operator->expects($this->never())->method('query_donor_user_data');

		$this->set_payer_data(['user_id' => 5, 'user_ppde_donated_amount' => 50.0]);
		$this->assertTrue($this->core->minimum_donation_raised());

		$this->set_payer_data(['user_id' => 5, 'user_ppde_donated_amount' => 10.0]);
		$this->assertFalse($this->core->minimum_donation_raised());
	}
}
