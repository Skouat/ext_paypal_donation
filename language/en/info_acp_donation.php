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


/**
* mode: main
*/
$lang = array_merge($lang, array(
	'ACP_DONATION_MOD' => 'PayPal Donation',
));

/**
* mode: overview
*/
$lang = array_merge($lang, array(
	'PPDE_OVERVIEW'			=> 'Overview',

	'INFO_CURL'				=> 'cURL',
	'INFO_FSOCKOPEN'		=> 'Fsockopen',
	'INFO_DETECTED'			=> 'Detected',
	'INFO_NOT_DETECTED'		=> 'Not detected',

	'PPDE_INSTALL_DATE'		=> 'Install date of <strong>%s</strong>',
	'PPDE_NO_VERSIONCHECK'	=> 'No version check information given.',
	'PPDE_NOT_UP_TO_DATE'	=> '%s is not up to date',
	'PPDE_STATS'			=> 'Donation statistics',
	'PPDE_VERSION'			=> '<strong>%s</strong> version',

	'STAT_RESET_DATE'			=> 'Reset MOD Installation date',
	'STAT_RESET_DATE_EXPLAIN'	=> 'Resetting the installation date will affect the calculation of the total amount of donations and some other statistics.',
	'STAT_RESET_DATE_CONFIRM'	=> 'Are you sure you wish to reset the installation date of this extension?',
));

/**
* mode: settings
*/
$lang = array_merge($lang, array(
	'PPDE_SETTINGS'			=> 'General Settings',
	'PPDE_SETTINGS_EXPLAIN'	=> 'Here you can configure the main settings for PayPal Donation.',
	'PPDE_SETTINGS_SAVED'	=> 'Donation settings saved',
	'MODE_CURRENCY'			=> 'currency',
	'MODE_DONATION_PAGES'	=> 'donation pages',

	// Global settings
	'PPDE_LEGEND_GENERAL_SETTINGS'	=> 'General Settings',
	'PPDE_ENABLE'					=> 'Enable PayPal Donation',
	'PPDE_ENABLE_EXPLAIN'			=> 'Enable or disable the PayPal Donation MOD',
	'PPDE_ACCOUNT_ID'				=> 'PayPal account ID',
	'PPDE_ACCOUNT_ID_EXPLAIN'		=> 'Enter your PayPal email address or Marchant account ID',
	'PPDE_DEFAULT_CURRENCY'			=> 'Default currency',
	'PPDE_DEFAULT_CURRENCY_EXPLAIN'	=> 'Define which currency will be selected by default',
	'PPDE_DEFAULT_VALUE'			=> 'Default donation value',
	'PPDE_DEFAULT_VALUE_EXPLAIN'	=> 'Define which donation value will be suggested by default',
	'PPDE_DROPBOX_ENABLE'			=> 'Enable drop-down list',
	'PPDE_DROPBOX_ENABLE_EXPLAIN'	=> 'If enabled, it will replace the Textbox by a drop-down list.',
	'PPDE_DROPBOX_VALUE'			=> 'Drop-down value',
	'PPDE_DROPBOX_VALUE_EXPLAIN'	=> 'Define the numbers you want to see in the drop-down list.<br />Use <strong>comma</strong> (",") <strong>with no space</strong> to separate each values.',

	// PayPal sandbox settings
	'PPDE_LEGEND_SANDBOX_SETTINGS'			=> 'PayPal sandbox settings',
	'PPDE_SANDBOX_ENABLE'					=> 'Sandbox testing',
	'PPDE_SANDBOX_ENABLE_EXPLAIN'			=> 'Enable this option if you want use PayPal Sandbox instead of PayPal services.<br />Useful for developers/testers. All the transactions are fictitious.',
	'PPDE_SANDBOX_FOUNDER_ENABLE'			=> 'Sandbox only for founder',
	'PPDE_SANDBOX_FOUNDER_ENABLE_EXPLAIN'	=> 'If enabled, PayPal Sandbox will be displayed only by the board founders.',
	'PPDE_SANDBOX_ADDRESS'					=> 'PayPal sandbox address',
	'PPDE_SANDBOX_ADDRESS_EXPLAIN'			=> 'Define here your PayPal Sandbox Sellers e-mail address',

	// Stats Donation settings
	'PPDE_LEGEND_STATS_SETTINGS'		=> 'Stats donation config',
	'PPDE_STATS_INDEX_ENABLE'			=> 'Display donation stats on index',
	'PPDE_STATS_INDEX_ENABLE_EXPLAIN'	=> 'Enable this if you want to display the donation stats on index',
	'PPDE_RAISED_ENABLE'				=> 'Enable donation raised',
	'PPDE_RAISED'						=> 'Donation raised',
	'PPDE_RAISED_EXPLAIN'				=> 'The current amount raised through donations',
	'PPDE_GOAL_ENABLE'					=> 'Enable donation goal',
	'PPDE_GOAL'							=> 'Donation goal',
	'PPDE_GOAL_EXPLAIN'					=> 'The total amount that you want to raise',
	'PPDE_USED_ENABLE'					=> 'Enable donation used',
	'PPDE_USED'							=> 'Donation used',
	'PPDE_USED_EXPLAIN'					=> 'The amount of donation that you have already used',

	'PPDE_CURRENCY_ENABLE'				=> 'Enable donation currency',
	'PPDE_CURRENCY_ENABLE_EXPLAIN'		=> 'Enable this option if you want to display the ISO 4217 code of default currency in Stats.',
));

/**
* logs
*/
$lang = array_merge($lang, array(
	//logs
	'LOG_PPDE_SETTINGS_UPDATED'	=> '<strong>PayPal Donation: Settings updated.</strong>',
));
