<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\actions;

use phpbb\event\dispatcher_interface;
use skouat\ppde\exception\transaction_exception;
use skouat\ppde\operators\transactions as transactions_operator;

/**
 * Records a completed PayPal donation and runs its post-actions (statistics,
 * raised amount, auto-group and notifications).
 *
 * Shared by the asynchronous webhook listener and the synchronous capture
 * endpoint; the idempotency guard prevents double processing when both run for
 * the same transaction.
 */
class donation_recorder
{
	/** @var core */
	protected $ppde_actions;
	/** @var transactions_operator */
	protected $ppde_operator_transaction;
	/** @var dispatcher_interface */
	protected $dispatcher;

	/**
	 * Constructor
	 *
	 * @param core                  $ppde_actions
	 * @param transactions_operator $ppde_operator_transaction
	 * @param dispatcher_interface  $dispatcher
	 *
	 * @access public
	 */
	public function __construct(
		core $ppde_actions,
		transactions_operator $ppde_operator_transaction,
		dispatcher_interface $dispatcher
	)
	{
		$this->ppde_actions = $ppde_actions;
		$this->ppde_operator_transaction = $ppde_operator_transaction;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Record a completed donation (insert + post-actions), idempotently.
	 *
	 * @param array $data       PPDE transaction data array (payment_status = 'Completed')
	 * @param bool  $is_sandbox
	 *
	 * @return bool True if recorded; false if already completed or concurrently recorded by the other path.
	 * @access public
	 */
	public function record_completed(array $data, bool $is_sandbox): bool
	{
		$txn_id = $data['txn_id'] ?? '';

		// A transaction already marked Completed is never reprocessed.
		if ($txn_id === '' || $this->already_completed($txn_id))
		{
			return false;
		}

		$transaction_data = $data;

		/**
		 * Event that is triggered when a transaction has been successfully completed
		 *
		 * @event skouat.ppde.do_actions_completed_before
		 * @var	array	transaction_data	Array containing transaction data
		 * @since 1.0.3
		 * @changed 4.0.0 Moved to the donation_recorder service
		 */
		$vars = ['transaction_data'];
		extract($this->dispatcher->trigger_event('skouat.ppde.do_actions_completed_before', compact($vars)));

		$data = $transaction_data;

		try
		{
			$this->ppde_actions->log_to_db($data);
		}
		catch (transaction_exception $e)
		{
			// Concurrently recorded by the other path: let the winner run the post-actions.
			return false;
		}

		$this->do_actions($is_sandbox);

		return true;
	}

	/**
	 * Check whether a transaction has already been fully processed (Completed).
	 *
	 * @param string $txn_id
	 *
	 * @return bool
	 * @access private
	 */
	private function already_completed(string $txn_id): bool
	{
		return $this->ppde_operator_transaction->is_txn_completed($txn_id);
	}

	/**
	 * Run statistics, auto-group and notification actions after logging a completed donation.
	 *
	 * @param bool $is_sandbox
	 *
	 * @return void
	 * @access private
	 */
	private function do_actions(bool $is_sandbox): void
	{
		$this->recompute_totals($is_sandbox);

		if (!$is_sandbox)
		{
			$this->ppde_actions->notification->notify_admin_donation_received();

			if ($this->ppde_actions->get_donor_is_member())
			{
				$this->ppde_actions->update_donor_stats();
				$this->ppde_actions->donors_group_user_add();
				$this->ppde_actions->notification->notify_donor_donation_received();
			}
		}
	}

	/**
	 * Refresh sandbox context and donor, then update overview stats and raised
	 * amount. Shared by the donation and refund/reversal flows.
	 *
	 * @param bool $is_sandbox
	 *
	 * @return void
	 * @access private
	 */
	private function recompute_totals(bool $is_sandbox): void
	{
		$this->ppde_actions->set_ipn_test_properties($is_sandbox);
		$this->ppde_actions->is_donor_is_member();

		$this->ppde_actions->update_overview_stats();
		$this->ppde_actions->update_raised_amount();
	}

	/**
	 * Adjust running totals after a refund/reversal has been logged.
	 *
	 * A refund only decreases the totals: it never adds the donor to the group,
	 * and removes them only if their cumulative amount dropped below the minimum.
	 *
	 * @param bool $is_sandbox
	 *
	 * @return void
	 * @access public
	 */
	public function run_refund_actions(bool $is_sandbox): void
	{
		$this->recompute_totals($is_sandbox);

		if (!$is_sandbox && $this->ppde_actions->get_donor_is_member())
		{
			$this->ppde_actions->update_donor_stats();
			$this->ppde_actions->donors_group_user_remove();
		}
	}
}
