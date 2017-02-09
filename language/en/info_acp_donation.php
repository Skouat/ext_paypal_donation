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
 * mode: main
 */
$lang = array_merge($lang, array(
	'PPDE_ACP_DONATION'        => 'PayPal Donation',
	'PPDE_ACP_OVERVIEW'        => 'Overview',
	'PPDE_ACP_PAYPAL_FEATURES' => 'PayPal IPN Features',
	'PPDE_ACP_SETTINGS'        => 'General Settings',
	'PPDE_ACP_DONATION_PAGES'  => 'Donation Pages',
	'PPDE_ACP_CURRENCY'        => 'Currency management',
	'PPDE_ACP_TRANSACTIONS'    => 'Transactions log',
));

/**
 * logs
 */
$lang = array_merge($lang, array(
	'LOG_PPDE_DC_ACTIVATED'            => '<strong>PayPal Donation: Currency enabled</strong><br>» %s',
	'LOG_PPDE_DC_ADDED'                => '<strong>PayPal Donation: New currency added</strong><br>» %s',
	'LOG_PPDE_DC_DEACTIVATED'          => '<strong>PayPal Donation: Currency disabled</strong><br>» %s',
	'LOG_PPDE_DC_DELETED'              => '<strong>PayPal Donation: Currency deleted</strong><br>» %s',
	'LOG_PPDE_DC_MOVE_DOWN'            => '<strong>PayPal Donation: Move down the currency</strong> “%s”',
	'LOG_PPDE_DC_MOVE_UP'              => '<strong>PayPal Donation: Move up the currency</strong> “%s”',
	'LOG_PPDE_DC_UPDATED'              => '<strong>PayPal Donation: Currency edited</strong><br>» %s',
	'LOG_PPDE_DP_ADDED'                => '<strong>PayPal Donation: New donation page added</strong><br>» “%1$s” for the language “%2$s”', // eg: » “Donation success” for the language “British English”',
	'LOG_PPDE_DP_DELETED'              => '<strong>PayPal Donation: Donation page deleted</strong><br>» “%1$s” for the language “%2$s”',
	'LOG_PPDE_DP_UPDATED'              => '<strong>PayPal Donation: Donation page updated</strong><br>» “%1$s” for the language “%2$s”',
	'LOG_PPDE_DT_PURGED'               => '<strong>PayPal Donation: Purge transactions log</strong>',
	'LOG_PPDE_PAYPAL_FEATURES_UPDATED' => '<strong>PayPal Donation: PayPal settings updated</strong>',
	'LOG_PPDE_SETTINGS_UPDATED'        => '<strong>PayPal Donation: Settings updated</strong>',
	'LOG_PPDE_STAT_RESET_DATE'         => '<strong>PayPal Donation: Installation date reset</strong>',
	'LOG_PPDE_STAT_RESYNC'             => '<strong>PayPal Donation: Statistics resynchronised</strong>',
	'LOG_PPDE_STAT_RETEST_REMOTE'      => '<strong>PayPal Donation: Remote connection re-detected</strong>',
	'LOG_PPDE_STAT_SANDBOX_RESYNC'     => '<strong>PayPal Donation: PayPal Sandbox Statistics resynchronised</strong>',
));
