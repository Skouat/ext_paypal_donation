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
 * mode: settings
 */
$lang = array_merge($lang, array(
	'PPDE_SETTINGS'                   => 'General Settings',
	'PPDE_SETTINGS_EXPLAIN'           => 'Here you can configure the main settings for PayPal Donation.',

	// General settings
	'PPDE_ACCOUNT_ID'                 => 'PayPal account ID',
	'PPDE_ACCOUNT_ID_EXPLAIN'         => 'Geef uw Merchant account ID of PayPal e-mailadres.',
	'PPDE_DEFAULT_CURRENCY'           => 'Default currency',
	'PPDE_DEFAULT_CURRENCY_EXPLAIN'   => 'Define which currency will be selected by default.',
	'PPDE_DEFAULT_VALUE'              => 'Default donation value',
	'PPDE_DEFAULT_VALUE_EXPLAIN'      => 'Define which donation value will be suggested by default.',
	'PPDE_DROPBOX_ENABLE'             => 'Enable drop-down list',
	'PPDE_DROPBOX_ENABLE_EXPLAIN'     => 'Display a drop-down list instead of a text box.',
	'PPDE_DROPBOX_VALUE'              => 'Drop-down donation values',
	'PPDE_DROPBOX_VALUE_EXPLAIN'      => 'Define the donation values available in the drop-down list.<br>Use <strong>comma</strong> (“,”) <strong>with no space</strong> to separate the values.',
	'PPDE_ENABLE'                     => 'Enable PayPal Donation',
	'PPDE_ENABLE_EXPLAIN'             => 'Enable or disable the PayPal Donation Extension.',
	'PPDE_HEADER_LINK'                => 'Display the “Donations” link in the header',
	'PPDE_LEGEND_GENERAL_SETTINGS'    => 'General Settings',

	// Advanced settings
	'PPDE_LEGEND_ADVANCED_SETTINGS'   => 'Advanced settings',
	'PPDE_DEFAULT_REMOTE'             => 'PayPal URL',
	'PPDE_DEFAULT_REMOTE_EXPLAIN'     => 'Do not change this setting, unless this extension encounters errors to contact the remote host.',

	// Stats Donation settings
	'PPDE_AMOUNT'                     => 'Amount',
	'PPDE_DECIMAL_EXPLAIN'            => 'Use “.” as decimal symbol.', // Note for translator: do not translate the decimal symbol
	'PPDE_GOAL'                       => 'Donation goal',
	'PPDE_GOAL_EXPLAIN'               => 'The total amount that you want to raise.',
	'PPDE_LEGEND_STATS_SETTINGS'      => 'Stats donation config',
	'PPDE_RAISED'                     => 'Donation raised',
	'PPDE_RAISED_EXPLAIN'             => 'The current amount raised through donations.',
	'PPDE_STATS_INDEX_ENABLE'         => 'Display donation stats on index',
	'PPDE_STATS_INDEX_ENABLE_EXPLAIN' => 'Enable this if you want to display the donation stats on index.',
	'PPDE_USED'                       => 'Donations used',
	'PPDE_USED_EXPLAIN'               => 'The amount of donations that you have already used.',
));

/**
 * Confirm box
 */
$lang = array_merge($lang, array(
	'PPDE_SETTINGS_SAVED' => 'Donation settings saved.',
));

/**
 * Errors
 */
$lang = array_merge($lang, array(
	'PPDE_SETTINGS_MISSING' => 'Please check “Account ID”.',
));
