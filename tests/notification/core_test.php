<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\tests\notification;

class core_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\notification\core */
	protected $notification_core;
	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $manager;
	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $currency;
	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $entity;

	protected function setUp(): void
	{
		parent::setUp();

		$container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
		$this->manager = $this->createMock(\phpbb\notification\manager::class);
		$this->currency = $this->createMock(\skouat\ppde\actions\currency::class);
		$this->entity = $this->createMock(\skouat\ppde\entity\transactions::class);

		$this->currency->method('get_currency_data')->willReturnCallback(function ($iso) {
			return ($iso === 'EUR')
				? [['currency_iso_code' => 'EUR', 'currency_symbol' => '€', 'currency_on_left' => false]]
				: [['currency_iso_code' => 'USD', 'currency_symbol' => '$', 'currency_on_left' => true]];
		});
		$this->currency->method('format_currency')->willReturnCallback(function ($value, $iso) {
			return $iso . ':' . $value;
		});

		$this->entity->method('get_mc_currency')->willReturn('USD');
		$this->entity->method('get_settle_currency')->willReturn('EUR');
		$this->entity->method('get_mc_gross')->willReturn(20.0);
		$this->entity->method('get_net_amount')->willReturn(19.0);
		$this->entity->method('get_payer_email')->willReturn('p@x.com');
		$this->entity->method('get_username')->willReturn('Bob');
		$this->entity->method('get_id')->willReturn(7);
		$this->entity->method('get_txn_id')->willReturn('TXN9');
		$this->entity->method('get_user_id')->willReturn(42);

		$this->notification_core = new \skouat\ppde\notification\core(
			$container,
			$this->manager,
			$this->currency,
			$this->entity
		);
	}

	public function test_notify_admin_uses_net_amount_when_no_settlement()
	{
		$this->entity->method('get_settle_amount')->willReturn(0.0);

		$this->manager->expects($this->once())
			->method('add_notifications')
			->with(
				'skouat.ppde.notification.type.admin_donation_received',
				[
					'mc_gross'       => 'USD:20',
					'net_amount'     => 'USD:19',
					'payer_email'    => 'p@x.com',
					'payer_username' => 'Bob',
					'transaction_id' => 7,
					'txn_id'         => 'TXN9',
					'user_from'      => 42,
				]
			);

		$this->notification_core->notify_admin_donation_received();
	}

	public function test_notify_donor_uses_settle_amount_when_converted()
	{
		// A non-zero settle amount switches net_amount to the settled currency.
		$this->entity->method('get_settle_amount')->willReturn(15.0);

		$this->manager->expects($this->once())
			->method('add_notifications')
			->with(
				'skouat.ppde.notification.type.donor_donation_received',
				[
					'mc_gross'       => 'USD:20',
					'net_amount'     => 'EUR:15',
					'payer_email'    => 'p@x.com',
					'payer_username' => 'Bob',
					'transaction_id' => 7,
					'txn_id'         => 'TXN9',
					'user_from'      => 42,
				]
			);

		$this->notification_core->notify_donor_donation_received();
	}
}
