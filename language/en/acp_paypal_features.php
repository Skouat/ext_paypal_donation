<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license       GNU General Public License, version 2 (GPL-2.0)
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

	// REST API settings
	'PPDE_LEGEND_REST_API'                 => 'REST API settings',
	'PPDE_REST_LIVE'                       => 'Live credentials',
	'PPDE_REST_SANDBOX'                    => 'Sandbox credentials',
	'PPDE_REST_CLIENT_ID'                  => 'Client ID',
	'PPDE_REST_CLIENT_ID_EXPLAIN'          => 'The REST API app Client ID from your PayPal Developer Dashboard.',
	'PPDE_REST_SECRET'                     => 'Secret',
	'PPDE_REST_SECRET_EXPLAIN'             => 'The REST API app Secret. Leave blank to keep the current value.',
	'PPDE_REST_SECRET_SET'                 => '•••••••• (a secret is already stored)',
	'PPDE_REST_SECRET_EMPTY'               => 'No secret stored yet',
	'PPDE_WEBHOOK_ID'                      => 'Webhook ID',
	'PPDE_WEBHOOK_ID_EXPLAIN'              => 'The Webhook ID created in your PayPal Developer Dashboard. Used to verify incoming webhook notifications.',

	// REST tools (webhook URL + connection test)
	'PPDE_LEGEND_REST_TOOLS'               => 'REST API tools',
	'PPDE_WEBHOOK_URL'                     => 'Webhook URL',
	'PPDE_WEBHOOK_URL_EXPLAIN'             => 'Add this URL as a webhook in your PayPal Developer Dashboard (for both your Live and Sandbox apps), subscribe to the “Payment capture” events (completed, pending, denied, refunded, reversed), then paste the resulting Webhook ID above.',
	'PPDE_REST_TEST_LIVE'                  => 'Test Live connection',
	'PPDE_REST_TEST_SANDBOX'               => 'Test Sandbox connection',
	'PPDE_REST_TEST_BUTTON'                => 'Test connection',
	'PPDE_REST_TESTING'                    => 'Testing…',
	'PPDE_REST_TEST_SUCCESS'               => 'Connection successful: credentials are valid.',
	'PPDE_REST_TEST_INVALID'               => 'Connection failed: invalid Client ID or Secret.',
	'PPDE_REST_TEST_CURL_ERROR'            => 'Connection error: %s',
	'PPDE_REST_TEST_HTTP_ERROR'            => 'Connection failed (HTTP %s).',

	// PayPal IPN settings
	'PPDE_LEGEND_IPN_AUTOGROUP'            => 'Auto group',
	'PPDE_LEGEND_IPN_DONORLIST'            => 'Donors list',
	'PPDE_LEGEND_IPN_NOTIFICATION'         => 'Notification system',
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
	'PPDE_IPN_NOTIFICATION_ENABLE'         => 'Enable notification',
	'PPDE_IPN_NOTIFICATION_ENABLE_EXPLAIN' => 'Allows to notify site admin and donors when a donation is received.',

	// PayPal sandbox settings
	'PPDE_LEGEND_SANDBOX_SETTINGS'         => 'Sandbox settings',
	'PPDE_SANDBOX_ENABLE'                  => 'Sandbox testing',
	'PPDE_SANDBOX_ENABLE_EXPLAIN'          => 'Use PayPal Sandbox instead of PayPal services.<br>Useful for developers and testers. All transactions are fictitious.',
	'PPDE_SANDBOX_FOUNDER_ENABLE'          => 'Sandbox only for founder',
	'PPDE_SANDBOX_FOUNDER_ENABLE_EXPLAIN'  => 'PayPal Sandbox will be displayed only by the board founders.',
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
	'PPDE_REST_CREDENTIALS_MISSING'       => 'PayPal REST API credentials (Client ID / Secret) are not configured. Please set them in the PayPal IPN Features module.',
]);
