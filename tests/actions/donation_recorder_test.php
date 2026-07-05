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

use skouat\ppde\exception\transaction_exception;

class donation_recorder_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\actions\donation_recorder */
	protected $recorder;
	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $core;
	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $operator;
	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $dispatcher;

	protected function setUp(): void
	{
		parent::setUp();

		$this->core = $this->createMock(\skouat\ppde\actions\core::class);
		$this->operator = $this->createMock(\skouat\ppde\operators\transactions::class);
		$this->dispatcher = $this->createMock(\phpbb\event\dispatcher_interface::class);

		$this->core->notification = $this->createMock(\skouat\ppde\notification\core::class);

		$this->recorder = new \skouat\ppde\actions\donation_recorder(
			$this->core,
			$this->operator,
			$this->dispatcher
		);
	}

	public function test_record_completed_aborts_if_empty_txn_id()
	{
		$this->assertFalse($this->recorder->record_completed([], false));
	}

	public function test_record_completed_aborts_if_already_completed()
	{
		$this->operator->expects($this->once())
			->method('is_txn_completed')
			->with('TXN_ALREADY_DONE')
			->willReturn(true);

		$this->core->expects($this->never())->method('log_to_db');
		$this->dispatcher->expects($this->never())->method('trigger_event');

		$this->assertFalse($this->recorder->record_completed(['txn_id' => 'TXN_ALREADY_DONE'], false));
	}

	public function test_record_completed_success_flow()
	{
		$data = ['txn_id' => 'TXN_OK', 'mc_gross' => 10.0];

		$this->operator->method('is_txn_completed')->willReturn(false);

		$this->dispatcher->expects($this->once())
			->method('trigger_event')
			->with('skouat.ppde.do_actions_completed_before', ['transaction_data' => $data])
			->willReturn(['transaction_data' => $data]);

		$this->core->expects($this->once())->method('log_to_db')->with($data);

		$this->core->expects($this->once())->method('set_sandbox_properties')->with(false);
		$this->core->expects($this->once())->method('is_donor_is_member');
		$this->core->expects($this->once())->method('update_overview_stats');
		$this->core->expects($this->once())->method('update_raised_amount');

		$this->core->method('get_donor_is_member')->willReturn(true);

		$this->core->expects($this->once())->method('update_donor_stats');
		$this->core->expects($this->once())->method('donors_group_user_add');
		$this->core->notification->expects($this->once())->method('notify_admin_donation_received');
		$this->core->notification->expects($this->once())->method('notify_donor_donation_received');

		$this->assertTrue($this->recorder->record_completed($data, false));
	}

	public function test_record_completed_handles_concurrent_thread_collision()
	{
		$data = ['txn_id' => 'TXN_RACING'];
		$this->operator->method('is_txn_completed')->willReturn(false);
		$this->dispatcher->method('trigger_event')->willReturn(['transaction_data' => $data]);

		// Another path won the race: the UNIQUE index rejects this insert.
		$this->core->method('log_to_db')->willThrowException(new transaction_exception());

		$this->core->expects($this->never())->method('set_sandbox_properties');
		$this->core->expects($this->never())->method('update_donor_stats');

		$this->assertFalse($this->recorder->record_completed($data, false));
	}

	public function test_run_refund_actions()
	{
		$this->core->expects($this->once())->method('set_sandbox_properties')->with(false);
		$this->core->expects($this->once())->method('is_donor_is_member');
		$this->core->expects($this->once())->method('update_overview_stats');
		$this->core->expects($this->once())->method('update_raised_amount');

		$this->core->method('get_donor_is_member')->willReturn(true);
		$this->core->expects($this->once())->method('update_donor_stats');
		$this->core->expects($this->once())->method('donors_group_user_remove');

		$this->recorder->run_refund_actions(false);
	}
}
