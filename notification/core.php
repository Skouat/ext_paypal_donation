<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2024 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\notification;

use phpbb\notification\manager;
use skouat\ppde\actions\currency;
use skouat\ppde\entity\transactions;

class core
{
	protected $notification_manager;
	protected $actions_currency;
	protected $entity_transaction;

	/**
	 * Constructor
	 *
	 * @param manager      $notification_manager Notification object
	 * @param currency     $actions_currency     Currency actions object
	 * @param transactions $entity_transaction   Transaction entity object
	 */
	public function __construct(
		manager $notification_manager,
		currency $actions_currency,
		transactions $entity_transaction
	)
	{
		$this->notification_manager = $notification_manager;
		$this->actions_currency = $actions_currency;
		$this->entity_transaction = $entity_transaction;
	}

	/**
	 * Notify admin when the donation contains errors
	 */
	public function notify_donation_errors(): void
	{
		$this->send_notification('skouat.ppde.notification.type.admin_donation_errors', 'donation_errors');
	}

	/**
	 * Notify admin when the donation is received
	 */
	public function notify_admin_donation_received(): void
	{
		$this->send_notification('skouat.ppde.notification.type.admin_donation_received');
	}

	/**
	 * Notify donor when the donation is received
	 */
	public function notify_donor_donation_received(): void
	{
		$this->send_notification('skouat.ppde.notification.type.donor_donation_received');
	}

	/**
	 * Send notification
	 *
	 * @param string $notification_type
	 * @param string $donation_type
	 */
	private function send_notification(string $notification_type, string $donation_type = ''): void
	{
		$notification_data = $this->get_notification_data($donation_type);
		$this->notification_manager->add_notifications($notification_type, $notification_data);
	}

	/**
	 * Get notification data
	 *
	 * @param string $donation_type
	 * @return array
	 */
	private function get_notification_data(string $donation_type = ''): array
	{
		$notification_data = [
			'transaction_id' => $this->entity_transaction->get_id(),
			'txn_id'         => $this->entity_transaction->get_txn_id(),
			'user_from'      => $this->entity_transaction->get_user_id(),
			'payer_email'    => $this->entity_transaction->get_payer_email(),
			'payer_username' => $this->entity_transaction->get_username(),
		];

		if ($donation_type === 'donation_errors')
		{
			$notification_data['txn_errors'] = $this->entity_transaction->get_txn_errors();
		}
		else
		{
			$this->actions_currency->set_currency_data_from_iso_code($this->entity_transaction->get_mc_currency());
			$notification_data['mc_gross'] = $this->actions_currency->format_currency($this->entity_transaction->get_mc_gross());
			$notification_data['net_amount'] = $this->get_formatted_net_amount();
		}

		return $notification_data;
	}

	/**
	 * Get formatted net amount
	 *
	 * @return string
	 */
	private function get_formatted_net_amount(): string
	{
		$settle_amount = $this->entity_transaction->get_settle_amount();
		if ($settle_amount > 0)
		{
			$this->actions_currency->set_currency_data_from_iso_code($this->entity_transaction->get_settle_currency());
		}
		else
		{
			$settle_amount = $this->entity_transaction->get_net_amount();
			$this->actions_currency->set_currency_data_from_iso_code($this->entity_transaction->get_mc_currency());
		}
		return $this->actions_currency->format_currency($settle_amount);
	}
}
