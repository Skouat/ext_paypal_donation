<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\notification;

use phpbb\notification\manager;
use skouat\ppde\actions\currency;
use skouat\ppde\entity\transactions;
use Symfony\Component\DependencyInjection\ContainerInterface;

class core
{
	protected $notification;
	protected $container;
	protected $ppde_actions_currency;
	protected $ppde_entity_transaction;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container               Service container interface
	 * @param manager            $notification            Notification object
	 * @param currency           $ppde_actions_currency   Currency actions object
	 * @param transactions       $ppde_entity_transaction Transaction entity object
	 * @access public
	 */
	public function __construct(
		ContainerInterface $container,
		manager $notification,
		currency $ppde_actions_currency,
		transactions $ppde_entity_transaction
	)
	{
		$this->container = $container;
		$this->notification = $notification;
		$this->ppde_actions_currency = $ppde_actions_currency;
		$this->ppde_entity_transaction = $ppde_entity_transaction;
	}

	/**
	 * Notify admin when the donation contains errors
	 *
	 * @return void
	 * @access public
	 */
	public function notify_donation_errors(): void
	{
		$notification_data = $this->notify_donation_core('donation_errors');
		$this->notification->add_notifications('skouat.ppde.notification.type.admin_donation_errors', $notification_data);
	}

	/**
	 * Notify admin when the donation is received
	 *
	 * @return void
	 * @access public
	 */
	public function notify_admin_donation_received(): void
	{
		$notification_data = $this->notify_donation_core();
		$this->notification->add_notifications('skouat.ppde.notification.type.admin_donation_received', $notification_data);
	}

	/**
	 * Notify donor when the donation is received
	 *
	 * @return void
	 * @access public
	 */
	public function notify_donor_donation_received(): void
	{
		$notification_data = $this->notify_donation_core();
		$this->notification->add_notifications('skouat.ppde.notification.type.donor_donation_received', $notification_data);
	}

	/**
	 * Build Notification data
	 *
	 * @param string $donation_type
	 *
	 * @return array
	 * @access private
	 */
	private function notify_donation_core($donation_type = ''): array
	{
		switch ($donation_type)
		{
			case 'donation_errors':
				$notification_data['txn_errors'] = $this->ppde_entity_transaction->get_txn_errors();
			// No break
			default:
				// Set currency data properties
				$currency_mc_data = $this->ppde_actions_currency->get_currency_data($this->ppde_entity_transaction->get_mc_currency());

				// Format net amount data properties
				if ($settle_amount = (float) $this->ppde_entity_transaction->get_settle_amount())
				{
					$currency_settle_data = $this->ppde_actions_currency->get_currency_data($this->ppde_entity_transaction->get_settle_currency());
					$net_amount = $this->ppde_actions_currency->format_currency($settle_amount, $currency_settle_data[0]['currency_iso_code'], $currency_settle_data[0]['currency_symbol'], (bool) $currency_settle_data[0]['currency_on_left']);
				}
				else
				{
					$net_amount = $this->ppde_actions_currency->format_currency($this->ppde_entity_transaction->get_net_amount(), $currency_mc_data[0]['currency_iso_code'], $currency_mc_data[0]['currency_symbol'], (bool) $currency_mc_data[0]['currency_on_left']);
				}

				$notification_data = [
					'mc_gross'       => $this->ppde_actions_currency->format_currency($this->ppde_entity_transaction->get_mc_gross(), $currency_mc_data[0]['currency_iso_code'], $currency_mc_data[0]['currency_symbol'], (bool) $currency_mc_data[0]['currency_on_left']),
					'net_amount'     => $net_amount,
					'payer_email'    => $this->ppde_entity_transaction->get_payer_email(),
					'payer_username' => $this->ppde_entity_transaction->get_username(),
					'transaction_id' => $this->ppde_entity_transaction->get_id(),
					'txn_id'         => $this->ppde_entity_transaction->get_txn_id(),
					'user_from'      => $this->ppde_entity_transaction->get_user_id(),
				];
		}

		return $notification_data;
	}
}
