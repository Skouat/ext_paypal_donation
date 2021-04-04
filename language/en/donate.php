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

$lang = array_merge($lang, [
	// Header
	'PPDE_HEADER_LINK_TITLE'           => 'Donations',
	'PPDE_HEADER_DONORLIST_LINK_TITLE' => 'Donors',

	// Index page
	'PPDE_INDEX_STATISTICS_TITLE'      => 'Donation statistics',

	// Pages
	'PPDE_DONATION_BUTTON_TITLE'       => 'Donate',
	'PPDE_DONATION_TITLE'              => 'Make a Donation',
	'PPDE_DONATION_TITLE_HEAD'         => 'Make a Donation to %s',
	'PPDE_CANCEL_TITLE'                => 'Donation canceled',
	'PPDE_SUCCESS_TITLE'               => 'Donation successful',
	'PPDE_CONTACT_PAYPAL'              => 'Connecting to PayPal. Please wait…',
	'PPDE_SANDBOX_TITLE'               => 'Test PayPal Donation with PayPal Sandbox',

	// Donors list
	'PPDE_DONORLIST_TITLE'             => 'Donors list',
	'PPDE_DONORLIST_LAST_DONATION'     => 'Last donation',
	'PPDE_DONORLIST_LAST_DATE'         => 'Made on',
	'PPDE_DONORLIST_TOTAL_DONATION'    => 'Donation amount',

	'PPDE_NO_DONORS'            => 'No donors',

	// Statistics
	'PPDE_DONATE_GOAL_RAISE'    => 'Our goal is to raise <strong>%s</strong>.',
	'PPDE_DONATE_GOAL_REACHED'  => 'Our donation goal was reached.',
	'PPDE_DONATE_NO_GOAL'       => 'We haven’t defined a donation goal.',
	'PPDE_DONATE_NOT_RECEIVED'  => 'We haven’t received any donations.',
	'PPDE_DONATE_NOT_USED'      => 'We haven’t used any donations.',
	'PPDE_DONATE_RECEIVED'      => 'We received <strong>%s</strong> in donations.',
	'PPDE_DONATE_USED'          => 'We used <strong>%1$s</strong> out of a total of <strong>%2$s</strong> received in donations.',
	'PPDE_DONATE_USED_EXCEEDED' => 'We used <strong>%s</strong>. All donations have been used.',

	// Viewonline
	'PPDE_VIEWONLINE'           => 'Viewing Donation page',
	'PPDE_VIEWONLINE_DONORLIST' => 'Viewing the list of donors',
]);

$lang = array_merge($lang, [
	'PPDE_DONORS' => [
		1 => '%d donor',  // 1
		2 => '%d donors', // 2+
	],
]);

$lang = array_merge($lang, [
	// Error
	'CURL_ERROR'                => 'cURL error: %s',
	'INVALID_TXN'               => 'Invalid transaction:',
	'INVALID_TXN_ACCOUNT_ID'    => 'Merchant ID does not match.',
	'INVALID_TXN_ASCII'         => 'Non ASCII chars detected in “%s”.',
	'INVALID_TXN_CONTENT'       => 'Unexpected content for “%s”.',
	'INVALID_TXN_EMPTY'         => 'Empty value for “%s”.',
	'INVALID_TXN_INVALID_CHECK' => 'Unknown Postdata.',
	'INVALID_TXN_LENGTH'        => 'The expected number of chars for “%s” does not match.',
	'INVALID_RESPONSE_STATUS'   => 'Invalid response status: ',
	'NO_CONNECTION_DETECTED'    => 'cURL not detected. Please contact the administrator of your web server.',
	'REQUIREMENT_NOT_SATISFIED' => 'cURL, TLS 1.2 or HTTP1/1 not detected. Please contact the administrator of your web server.',
	'UNEXPECTED_RESPONSE'       => 'Unexpected response from PayPal.',
]);

$lang = array_merge($lang, [
	// Notification
	'NOTIFICATION_PPDE_ADMIN_DONATION_ERRORS'   => '%1$s’s last donation requires your attention.',
	'NOTIFICATION_PPDE_ADMIN_DONATION_RECEIVED' => '%1$s has donated “%2$s”.',
	'NOTIFICATION_PPDE_DONOR_DONATION_RECEIVED' => 'Your donation of “%1$s” has been received.',
]);
