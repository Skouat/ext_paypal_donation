<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
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
// ’ « » “ ” …
//

$lang = array_merge($lang, array(
	// Header
	'PPDE_HEADER_LINK_TITLE'           => 'Donations',
	'PPDE_HEADER_DONORLIST_LINK_TITLE' => 'Donors',

	// Index page
	'PPDE_INDEX_STATISTICS_TITLE'      => 'Donation statistics',

	// Pages
	'PPDE_DONATION_BUTTON_TITLE'       => 'Donate',
	'PPDE_DONATION_TITLE'              => 'Make a Donation',
	'PPDE_DONATION_TITLE_HEAD'         => 'Make a Donation to',
	'PPDE_CANCEL_TITLE'                => 'Donation Canceled',
	'PPDE_SUCCESS_TITLE'               => 'Donation Successful',
	'PPDE_CONTACT_PAYPAL'              => 'Connecting to PayPal - Please Wait…',
	'PPDE_SANDBOX_TITLE'               => 'Test PayPal Donation with PayPal Sandbox',

	// Donors list
	'PPDE_DONORLIST_TITLE'             => 'Donors list',
	'PPDE_DONORLIST_LAST_DONATION'     => 'Last donation',
	'PPDE_DONORLIST_LAST_DATE'         => 'Made on',
	'PPDE_DONORLIST_TOTAL_DONATION'    => 'Donation amount',

	'PPDE_NO_DONORS'                   => 'No donors',

	// Statistics
	'PPDE_DONATE_GOAL_RAISE'           => 'Our goal is to raise <strong>%s</strong>.',
	'PPDE_DONATE_GOAL_REACHED'         => 'Our goal donation was reached.',
	'PPDE_DONATE_NO_GOAL'              => 'We haven’t defined a donation goal.',
	'PPDE_DONATE_NOT_RECEIVED'         => 'We haven’t received any donations.',
	'PPDE_DONATE_NOT_USED'             => 'We haven’t used any donations.',
	'PPDE_DONATE_RECEIVED'             => 'We received <strong>%s</strong> in donations.',
	'PPDE_DONATE_USED'                 => 'We used <strong>%1$s</strong> of your donations on <strong>%2$s</strong> already received.',
	'PPDE_DONATE_USED_EXCEEDED'        => 'We used <strong>%s</strong>. All your donations have been used.',

	// Viewonline
	'PPDE_VIEWONLINE'                  => 'Viewing Donation page',
	'PPDE_VIEWONLINE_DONORLIST'        => 'Viewing the list of donors',
));

/**
 * Info: This array is out of the previous because there is an issue with Transifex platform
 */
$lang = array_merge($lang, array(
	'PPDE_DONORS' => array(
		1 => '%d donor',  // 1
		2 => '%d donors', // 2+
	),
));

$lang = array_merge($lang, array(
	// Error
	'CURL_ERROR'                 => 'cURL error: ',
	'FSOCK_ERROR'                => 'fsockopen error: ',
	'NO_CONNECTION_DETECTED'     => 'cURL and fsockopen() have not been detected. Please contact the administrator of your Web server.',
	'INVALID_TRANSACTION_RECORD' => 'Invalid Transaction Record: No Transaction ID found',
	'INVALID_RESPONSE_STATUS'    => 'Invalid response status: ',
	'UNEXPECTED_RESPONSE'        => 'Unexpected response from PayPal.',
));

$lang = array_merge($lang, array(
	// Notification
	'NOTIFICATION_PPDE_ADMIN_DONATION_RECEIVED' => '%1$s has donate “%2$s”.',
	'NOTIFICATION_PPDE_DONOR_DONATION_RECEIVED' => 'The donation for an amount of “%1$s” has been received.',
));
