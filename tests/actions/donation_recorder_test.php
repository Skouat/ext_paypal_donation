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

		// Disable original constructors for notification mock inside core if needed
		$this->core->notification = $this->createMock(\skouat\ppde\notification\core::class);

		$this->recorder = new \skouat\ppde\actions\donation_recorder(
			$this->core,
			$this->operator,
			$this->dispatcher
		);
	}

	public function test_record_completed_aborts_if_empty_txn_id()
	{
		// Should return false immediately if txn_id is missing
		$this->assertFalse($this->recorder->record_completed([], false));
	}

	public function test_record_completed_aborts_if_already_completed()
	{
		$this->operator->expects($this->once())
			->method('is_txn_completed')
			->with('TXN_ALREADY_DONE')
			->willReturn(true);

		// Crucial idempotency check: must not log or trigger events
		$this->core->expects($this->never())->method('log_to_db');
		$this->dispatcher->expects($this->never())->method('trigger_event');

		$this->assertFalse($this->recorder->record_completed(['txn_id' => 'TXN_ALREADY_DONE'], false));
	}

	public function test_record_completed_success_flow()
	{
		$data = ['txn_id' => 'TXN_OK', 'mc_gross' => 10.0];

		$this->operator->method('is_txn_completed')->willReturn(false);

		// The dispatcher must return an array mimicking phpBB trigger_event behavior
		$this->dispatcher->expects($this->once())
			->method('trigger_event')
			->with('skouat.ppde.do_actions_completed_before', ['transaction_data' => $data])
			->willReturn(['transaction_data' => $data]);

		// The raw transaction data must be written to DB
		$this->core->expects($this->once())
			->method('log_to_db')
			->with($data);

		// Post-actions verification (live mode, non-sandbox)
		$this->core->expects($this->once())->method('set_sandbox_properties')->with(false);
		$this->core->expects($this->once())->method('is_donor_is_member');
		$this->core->expects($this->once())->method('update_overview_stats');
		$this->core->expects($this->once())->method('update_raised_amount');

		// Since we mock, let's assume donor is a forum member
		$this->core->method('get_donor_is_member')->willReturn(true);

		// Members must get auto-grouped, stats updated, and notified
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

		// The dispatcher must return an array mimicking phpBB trigger_event behavior
		$this->dispatcher->method('trigger_event')
			->willReturn(['transaction_data' => $data]);

		// Simulate another thread/process writing the transaction first (UNIQUE constraint error)
		$this->core->method('log_to_db')->willThrowException(new transaction_exception());

		// Post-actions should NOT run since this thread lost the race condition
		$this->core->expects($this->never())->method('set_sandbox_properties');
		$this->core->expects($this->never())->method('update_donor_stats');

		$this->assertFalse($this->recorder->record_completed($data, false));
	}

	public function test_run_refund_actions()
	{
		// Sandbox setting context
		$this->core->expects($this->once())->method('set_sandbox_properties')->with(false);
		$this->core->expects($this->once())->method('is_donor_is_member');

		// Totals must be decreased
		$this->core->expects($this->once())->method('update_overview_stats');
		$this->core->expects($this->once())->method('update_raised_amount');

		// If donor is a member, adjust their stats and possibly remove from autogroup
		$this->core->method('get_donor_is_member')->willReturn(true);
		$this->core->expects($this->once())->method('update_donor_stats');
		$this->core->expects($this->once())->method('donors_group_user_remove');

		$this->recorder->run_refund_actions(false);
	}
}
