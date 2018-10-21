<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ « » “ ” …
//

/**
 * mode: transactions
 */
$lang = array_merge($lang, array(
	// Transactions log
	'PPDE_DT_CONFIG'                => 'Transactions Log',
	'PPDE_DT_CONFIG_EXPLAIN'        => 'Here you can see transaction details.',
	'PPDE_DT_IPN_STATUS'            => 'IPN Status',
	'PPDE_DT_IPN_TEST'              => 'IPN test',
	'PPDE_DT_PAYMENT_STATUS'        => 'Payment Status',
	'PPDE_DT_TXN_ID'                => 'Transaction ID',
	'PPDE_DT_USERNAME'              => 'Donor name',

	// Display transactions
	'PPDE_DT_BOARD_USERNAME'        => 'Donors',
	'PPDE_DT_DETAILS'               => 'Transaction details',
	'PPDE_DT_EXCHANGE_RATE'         => 'Exchange rate',
	'PPDE_DT_EXCHANGE_RATE_EXPLAIN' => 'Based on the exchange rate in effect at %s.',
	'PPDE_DT_FEE_AMOUNT'            => 'Fee amount',
	'PPDE_DT_ITEM_NAME'             => 'Item name',
	'PPDE_DT_ITEM_NUMBER'           => 'Item number',
	'PPDE_DT_MEMO'                  => 'Memo',
	'PPDE_DT_MEMO_EXPLAIN'          => 'Memo entered by the donor via PayPal Website.',
	'PPDE_DT_NAME'                  => 'Name',
	'PPDE_DT_NET_AMOUNT'            => 'Net amount',
	'PPDE_DT_PAYER_ID'              => 'Payer ID',
	'PPDE_DT_PAYER_EMAIL'           => 'Payer e-mail',
	'PPDE_DT_PAYER_STATUS'          => 'Payer status',
	'PPDE_DT_PAYMENT_DATE'          => 'Payment Date',
	'PPDE_DT_RECEIVER_EMAIL'        => 'Payment sent to',
	'PPDE_DT_RECEIVER_ID'           => 'Receiver ID',
	'PPDE_DT_SETTLE_AMOUNT'         => 'Conversion to “%s”',
	'PPDE_DT_SORT_TXN_ID'           => 'Transaction ID',
	'PPDE_DT_SORT_DONORS'           => 'Donors',
	'PPDE_DT_SORT_IPN_STATUS'       => 'IPN Status',
	'PPDE_DT_SORT_IPN_TYPE'         => 'Transaction type',
	'PPDE_DT_SORT_PAYMENT_STATUS'   => 'Payment Status',
	'PPDE_DT_TOTAL_AMOUNT'          => 'Total amount',
	'PPDE_DT_UNVERIFIED'            => 'Not verified',
	'PPDE_DT_VERIFIED'              => 'Verified',
));

/**
 * mode: transactions
 * Info: This array is out of the previous because there is an issue with Transifex platform
 */
$lang = array_merge($lang, array(
	/**
	 * TRANSLATORS PLEASE NOTE
	 * The line below has a special note.
	 * "## For translate:" followed by one "Don't" and one "Yes"
	 * "Don't" means do not change this column, and "Yes" means you can translate this column.
	 */

	## For translate:					Don't					Yes
	'PPDE_DT_PAYMENT_STATUS_VALUES' => array(
										'canceled_reversal' => 'Canceled Reversal',
										'completed'         => 'Completed',
										'created'           => 'Created',
										'denied'            => 'Denied',
										'expired'           => 'Expired',
										'failed'            => 'Failed',
										'pending'           => 'Pending',
										'refunded'          => 'Refunded',
										'reversed'          => 'Reversed',
										'processed'         => 'Processed',
										'voided'            => 'Voided',
	),
));

/**
 * Errors
 */
$lang = array_merge($lang, array(
	'PPDE_DT_IPN_ERRORS'     => 'You should reconsider this donation because the following errors are detected',
	'PPDE_DT_NO_TRANSACTION' => 'No transaction found.',
));
