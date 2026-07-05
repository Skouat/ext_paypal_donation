<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde;

final class ppde_constants
{
	public const EVENT_CAPTURE_COMPLETED = 'PAYMENT.CAPTURE.COMPLETED';
	public const EVENT_CAPTURE_DENIED    = 'PAYMENT.CAPTURE.DENIED';
	public const EVENT_CAPTURE_PENDING   = 'PAYMENT.CAPTURE.PENDING';
	public const EVENT_CAPTURE_REFUNDED  = 'PAYMENT.CAPTURE.REFUNDED';
	public const EVENT_CAPTURE_REVERSED  = 'PAYMENT.CAPTURE.REVERSED';

	public const PAYPAL_ORDER_COMPLETED = 'COMPLETED';

	public const STATUS_COMPLETED = 'Completed';
	public const STATUS_DENIED    = 'Denied';
	public const STATUS_PENDING   = 'Pending';
	public const STATUS_REFUNDED  = 'Refunded';
	public const STATUS_REVERSED  = 'Reversed';

	public const TXN_TYPE_MANUAL_DONATION = 'ppde_manual_donation';
	public const TXN_TYPE_REST_DONATION   = 'ppde_rest_donation';
	public const TXN_TYPE_REST_REFUND     = 'ppde_rest_refund';
}
