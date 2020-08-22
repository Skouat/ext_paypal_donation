<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
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
$lang = array_merge($lang, [
	// Transactions log
	'PPDE_DT_CONFIG'         => 'Transactions Log',
	'PPDE_DT_CONFIG_EXPLAIN' => 'Here you can see transaction details.',
	'PPDE_DT_IPN_STATUS'     => 'IPN Status',
	'PPDE_DT_IPN_TEST'       => 'IPN test',
	'PPDE_DT_PAYMENT_STATUS' => 'Payment Status',
	'PPDE_DT_TXN_ID'         => 'Transaction ID',
	'PPDE_DT_USERNAME'       => 'Donor name',

	// Display transactions
	'PPDE_DT_APPROVE'                       => 'Approve',
	'PPDE_DT_BOARD_USERNAME'                => 'Donor',
	'PPDE_DT_CHANGE_BOARD_USERNAME'         => 'Change donor',
	'PPDE_DT_CHANGE_BOARD_USERNAME_EXPLAIN' => 'This changes the user account this donation is associated with.',
	'PPDE_DT_DETAILS'                       => 'Transaction details',
	'PPDE_DT_DISAPPROVE'                    => 'Disapprove',
	'PPDE_DT_EXCHANGE_RATE'                 => 'Exchange rate',
	'PPDE_DT_EXCHANGE_RATE_EXPLAIN'         => 'Based on the exchange rate in effect at %s.',
	'PPDE_DT_FEE_AMOUNT'                    => 'Fee amount',
	'PPDE_DT_ITEM_NAME'                     => 'Item name',
	'PPDE_DT_ITEM_NUMBER'                   => 'Item number',
	'PPDE_DT_MEMO'                          => 'Memo',
	'PPDE_DT_MEMO_EXPLAIN'                  => 'Memo entered by the donor via PayPal Website.',
	'PPDE_DT_NAME'                          => 'Name',
	'PPDE_DT_NET_AMOUNT'                    => 'Net amount',
	'PPDE_DT_PAYER_ID'                      => 'Payer ID',
	'PPDE_DT_PAYER_EMAIL'                   => 'Payer e-mail',
	'PPDE_DT_PAYER_STATUS'                  => 'Payer status',
	'PPDE_DT_PAYMENT_DATE'                  => 'Payment Date',
	'PPDE_DT_RECEIVER_EMAIL'                => 'Payment sent to',
	'PPDE_DT_RECEIVER_ID'                   => 'Receiver ID',
	'PPDE_DT_SETTLE_AMOUNT'                 => 'Conversion to “%s”',
	'PPDE_DT_SORT_TXN_ID'                   => 'Transaction ID',
	'PPDE_DT_SORT_DONORS'                   => 'Donors',
	'PPDE_DT_SORT_IPN_STATUS'               => 'IPN Status',
	'PPDE_DT_SORT_IPN_TYPE'                 => 'Transaction type',
	'PPDE_DT_SORT_PAYMENT_STATUS'           => 'Payment Status',
	'PPDE_DT_TOTAL_AMOUNT'                  => 'Total amount',
	'PPDE_DT_UNVERIFIED'                    => 'Not verified',
	'PPDE_DT_VERIFIED'                      => 'Verified',
	'PPDE_DT_UPDATED'                       => 'The transaction has been updated.',

	'PPDE_MT_TITLE'                     => 'Manual Transaction',
	'PPDE_MT_TITLE_EXPLAIN'             => 'Here you can add a transaction manually, for example if you received a donation by means other than PayPal.',
	'PPDE_MT_REQUIRED_CHARACTER'        => '*',
	'PPDE_MT_REQUIRED_EXPLAIN'          => 'Required field',
	'PPDE_MT_DETAILS'                   => 'Transaction details',
	'PPDE_MT_USERNAME'                  => 'Donor',
	'PPDE_MT_USERNAME_EXPLAIN'          => 'Select the anonymous user if the donation was made by a guest.',
	'PPDE_MT_FIRST_NAME'                => 'First name',
	'PPDE_MT_LAST_NAME'                 => 'Last name',
	'PPDE_MT_PAYER_EMAIL'               => 'Email',
	'PPDE_MT_RESIDENCE_COUNTRY'         => 'Country',
	'PPDE_MT_RESIDENCE_COUNTRY_EXPLAIN' => 'ISO 3166 alpha-2 code, 2 characters, see <a href="https://www.phpbb.com/customise/db/extension/paypal_donation_extension/faq/2796" target="_blank" rel="noreferrer">FAQ</a>.',
	'PPDE_MT_TOTAL_AMOUNT'              => 'Total amount',
	'PPDE_DECIMAL_EXPLAIN'              => 'Use “.” as decimal symbol.', // Note for translator: do not translate the decimal symbol
	'PPDE_MT_FEE_AMOUNT'                => 'Fee amount',
	'PPDE_MT_NET_AMOUNT'                => 'Net amount',
	'PPDE_MT_PAYMENT_DATE'              => 'Donation date',
	'PPDE_MT_PAYMENT_DATE_PICK'         => 'Pick a date',
	'PPDE_MT_PAYMENT_TIME'              => 'Donation time',
	'PPDE_MT_PAYMENT_TIME_EXPLAIN'      => 'Examples of allowed time formats',
	'PPDE_MT_MEMO'                      => 'Memo',
	'PPDE_MT_ADDED'                     => 'The transaction has been added successfully.',

	// List of available translations: https://github.com/fengyuanchen/datepicker/tree/master/i18n
	'PPDE_MT_DATEPICKER_LANG'           => 'en-GB',
]);

/**
 * mode: transactions
 * Info: This array is out of the previous because there is an issue with Transifex platform
 */
$lang = array_merge($lang, [
	/**
	 * TRANSLATORS PLEASE NOTE
	 * The line below has a special note.
	 * "## For translate:" followed by one "Don't" and one "Yes"
	 * "Don't" means do not change this column, and "Yes" means you can translate this column.
	 */

	## For translate:					Don't					Yes
	'PPDE_DT_PAYMENT_STATUS_VALUES' => [
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
	],
]);

/**
 * Confirm box
 */
$lang = array_merge($lang, [
	'PPDE_DT_CONFIRM_OPERATION' => 'Are you sure you wish to carry out this operation?',
]);

/**
 * Errors
 */
$lang = array_merge($lang, [
	'PPDE_DT_IPN_APPROVED'         => 'Transaction manually approved',
	'PPDE_DT_IPN_APPROVED_EXPLAIN' => 'This donation was manually approved with the following errors',
	'PPDE_DT_IPN_ERRORS'           => 'You should reconsider this donation because the following errors are detected',
	'PPDE_DT_NO_TRANSACTION'       => 'No transaction found.',

	'PPDE_MT_DONOR_NOT_FOUND'      => 'The requested donor does not exist.',
	'PPDE_MT_MC_GROSS_TOO_LOW'     => 'The total amount must be greater than zero.',
	'PPDE_MT_MC_FEE_NEGATIVE'      => 'The fee cannot be negative.',
	'PPDE_MT_MC_FEE_TOO_HIGH'      => 'The fee must be lower than the total amount.',
	'PPDE_MT_PAYMENT_DATE_ERROR'   => 'The donation date “%1$s” could not be parsed.',
	'PPDE_MT_PAYMENT_TIME_ERROR'   => 'The donation time “%1$s” could not be parsed.',
	'PPDE_MT_PAYMENT_DATE_FUTURE'  => 'The donation date must be in the past, but it was “%1$s”.',
]);
