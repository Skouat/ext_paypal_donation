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
	'PPDE_HEADER_LINK_TITLE'      => 'Donations',

	// Index page
	'PPDE_INDEX_STATISTICS_TITLE' => 'Donation statistics',

	// Image alternative text
	'IMG_LOADER'                  => 'loading',

	// Pages
	'PPDE_DONATION_BUTTON_TITLE'  => 'Donate',
	'PPDE_DONATION_TITLE'         => 'Make a Donation',
	'PPDE_DONATION_TITLE_HEAD'    => 'Make a Donation to',
	'PPDE_CANCEL_TITLE'           => 'Donation Canceled',
	'PPDE_SUCCESS_TITLE'          => 'Donation Successful',
	'PPDE_CONTACT_PAYPAL'         => 'Connecting to PayPal - Please Wait…',
	'PPDE_SANDBOX_TITLE'          => 'Test PayPal Donation with PayPal Sandbox',

	// Statistics
	// Note for translators----->    %1$d = donation raised; %2$s = currency
	'DONATE_RECEIVED'             => 'We received <strong>%s</strong> in donations.',
	'DONATE_NOT_RECEIVED'         => 'We haven’t received any donations.',

	// Note for translators----->    %1$d = donation goal; %2$s = currency
	'DONATE_GOAL_RAISE'           => 'Our goal is to raise <strong>%s</strong>.',
	'DONATE_GOAL_REACHED'         => 'Our goal donation was reached.',
	'DONATE_NO_GOAL'              => 'We haven’t defined a donation goal.',

	// Note for translators----->    %1$d = donation used; %2$s = currency; %3$d = donation raised;
	'DONATE_USED'                 => 'We used <strong>%1$s</strong> of your donations on <strong>%2$s</strong>%2$s already received.',
	'DONATE_USED_EXCEEDED'        => 'We used <strong>%s</strong>. All your donations have been used.',
	'DONATE_NOT_USED'             => 'We haven’t used any donations.',

	// Viewonline
	'PPDE_VIEWONLINE'             => 'Viewing Donation page',
));
