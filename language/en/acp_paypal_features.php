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
 * mode: PayPal features
 */
$lang = array_merge($lang, [
	'PPDE_PAYPAL_FEATURES'                 => 'PayPal IPN Features',
	'PPDE_PAYPAL_FEATURES_EXPLAIN'         => 'Here you can configure all features that use the PayPal Instant Payment Notification (IPN).',

	// PayPal IPN settings
	'PPDE_LEGEND_IPN_AUTOGROUP'            => 'Auto group',
	'PPDE_LEGEND_IPN_DEBUG'                => 'Debug settings',
	'PPDE_LEGEND_IPN_DONORLIST'            => 'Donors list',
	'PPDE_LEGEND_IPN_NOTIFICATION'         => 'Notification system',
	'PPDE_LEGEND_IPN_SETTINGS'             => 'General settings',
	'PPDE_IPN_AG_ENABLE'                   => 'Enable auto group',
	'PPDE_IPN_AG_ENABLE_EXPLAIN'           => 'Allows to add donors to a predefined group.',
	'PPDE_IPN_AG_DONORS_GROUP'             => 'Donors group',
	'PPDE_IPN_AG_DONORS_GROUP_EXPLAIN'     => 'Select the group that donors will be added to.',
	'PPDE_IPN_AG_GROUP_AS_DEFAULT'         => 'Set donors group as default',
	'PPDE_IPN_AG_GROUP_AS_DEFAULT_EXPLAIN' => 'Enable to set the donors group as the user’s default group.',
	'PPDE_IPN_AG_MIN_BEFORE_GROUP'         => 'Minimum amount for donors group',
	'PPDE_IPN_AG_MIN_BEFORE_GROUP_EXPLAIN' => 'Total amount of donations a user must make to be added to the donors group.',
	'PPDE_IPN_DL_ALLOW_GUEST'              => 'Allow guests to view donors list',
	'PPDE_IPN_DL_ALLOW_GUEST_EXPLAIN'      => 'This will set the board permissions to allow guests to view the list of donors.',
	'PPDE_IPN_DL_ENABLE'                   => 'Enable donors list',
	'PPDE_IPN_DL_ENABLE_EXPLAIN'           => 'Allows to enable the list of donors.',
	'PPDE_IPN_ENABLE'                      => 'Enable IPN',
	'PPDE_IPN_ENABLE_EXPLAIN'              => 'Enable this option if you want to use PayPal’s Instant Payment Notification service.<br>If enabled, more features will be available below.',
	'PPDE_IPN_LOGGING'                     => 'Enable errors logs',
	'PPDE_IPN_LOGGING_EXPLAIN'             => 'Write errors and data from PayPal IPN to a file in <strong>/store/ext/ppde/</strong>.',
	'PPDE_IPN_NOTIFICATION_ENABLE'         => 'Enable notification',
	'PPDE_IPN_NOTIFICATION_ENABLE_EXPLAIN' => 'Allows to notify site admin and donors when a donation is received.',

	// PayPal sandbox settings
	'PPDE_LEGEND_SANDBOX_SETTINGS'         => 'Sandbox settings',
	'PPDE_SANDBOX_ENABLE'                  => 'Sandbox testing',
	'PPDE_SANDBOX_ENABLE_EXPLAIN'          => 'Use PayPal Sandbox instead of PayPal services.<br>Useful for developers and testers. All transactions are fictitious.',
	'PPDE_SANDBOX_FOUNDER_ENABLE'          => 'Sandbox only for founder',
	'PPDE_SANDBOX_FOUNDER_ENABLE_EXPLAIN'  => 'PayPal Sandbox will be displayed only by the board founders.',
	'PPDE_SANDBOX_ADDRESS'                 => 'PayPal Sandbox account',
	'PPDE_SANDBOX_ADDRESS_EXPLAIN'         => 'Enter the PayPal Sandbox email address or Merchant ID.',
	'PPDE_SANDBOX_REMOTE'                  => 'PayPal sandbox URL',
	'PPDE_SANDBOX_REMOTE_EXPLAIN'          => 'Do not change this setting, unless this extension encounters errors to contact the sandbox remote host.',
]);

/**
 * Confirm box
 */
$lang = array_merge($lang, [
	'PPDE_PAYPAL_FEATURES_SAVED' => 'PayPal IPN features saved.',
]);

/**
 * Errors
 */
$lang = array_merge($lang, [
	'PPDE_PAYPAL_FEATURES_MISSING'        => 'Please check “Sandbox address”.',
	'PPDE_PAYPAL_FEATURES_NOT_ENABLEABLE' => 'IPN PayPal cannot be enabled. Check the system requirements from the “Overview” module.',
]);
