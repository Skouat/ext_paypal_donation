<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\notification;

use phpbb\notification\manager;
use skouat\ppde\controller\main_controller;
use Symfony\Component\DependencyInjection\ContainerInterface;

class core
{
	protected $notification;
	protected $container;
	protected $ppde_controller_main;
	protected $ppde_entity_transaction;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface               $container               Service container interface
	 * @param manager                          $notification            Notification object
	 * @param main_controller                  $ppde_controller_main    Main controller object
	 * @param \skouat\ppde\entity\transactions $ppde_entity_transaction Transaction entity object
	 * @access public
	 */
	public function __construct(ContainerInterface $container, manager $notification, main_controller $ppde_controller_main, \skouat\ppde\entity\transactions $ppde_entity_transaction)
	{
		$this->container = $container;
		$this->notification = $notification;
		$this->ppde_controller_main = $ppde_controller_main;
		$this->ppde_entity_transaction = $ppde_entity_transaction;
	}

	/**
	 * Notify donors and admin when the donation is received
	 *
	 * @return void
	 * @access public
	 */
	public function notify_donation_received()
	{
		$notification_data = $this->notify_donation_core();
		// Send admin notification
		$this->notification->add_notifications('skouat.ppde.notification.type.admin_donation_received', $notification_data);
		// Send donor notification
		$this->notification->add_notifications('skouat.ppde.notification.type.donor_donation_received', $notification_data);
	}

	/**
	 * Build Notification data
	 *
	 * @return array
	 * @access private
	 */
	private function notify_donation_core()
	{
		// Set currency data properties
		$currency_mc_data = $this->ppde_controller_main->get_currency_data($this->ppde_entity_transaction->get_mc_currency());

		// Set currency settle data properties if exists
		$settle_amount = '';
		if ($this->ppde_entity_transaction->get_settle_amount())
		{
			$currency_settle_data = $this->ppde_controller_main->get_currency_data($this->ppde_entity_transaction->get_settle_currency());
			$settle_amount = $this->ppde_controller_main->currency_on_left((float) $settle_amount, $currency_settle_data[0]['currency_symbol'], (bool) $currency_settle_data[0]['currency_on_left']);
		}

		return $notification_data = array(
			'net_amount'     => $this->ppde_controller_main->currency_on_left($this->ppde_entity_transaction->get_net_amount(), $currency_mc_data[0]['currency_symbol'], (bool) $currency_mc_data[0]['currency_on_left']),
			'mc_gross'       => $this->ppde_controller_main->currency_on_left($this->ppde_entity_transaction->get_mc_gross(), $currency_mc_data[0]['currency_symbol'], (bool) $currency_mc_data[0]['currency_on_left']),
			'payer_email'    => $this->ppde_entity_transaction->get_payer_email(),
			'payer_username' => $this->ppde_entity_transaction->get_username(),
			'settle_amount'  => $settle_amount,
			'transaction_id' => $this->ppde_entity_transaction->get_id(),
			'txn_id'         => $this->ppde_entity_transaction->get_txn_id(),
			'user_from'      => $this->ppde_entity_transaction->get_user_id(),
		);
	}
}
