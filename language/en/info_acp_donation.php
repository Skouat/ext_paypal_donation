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
	'PPDE_ACP_DONATION'       => 'PayPal Donation',
	'PPDE_ACP_OVERVIEW'       => 'Overview',
	'PPDE_ACP_SETTINGS'       => 'General Settings',
	'PPDE_ACP_DONATION_PAGES' => 'Donation Pages',
	'PPDE_ACP_CURRENCY'       => 'Currency management',
));

/**
 * mode: overview
 */
$lang = array_merge($lang, array(
	'PPDE_OVERVIEW'           => 'Overview',

	'INFO_CURL'               => 'cURL',
	'INFO_FSOCKOPEN'          => 'Fsockopen',
	'INFO_DETECTED'           => 'Detected',
	'INFO_NOT_DETECTED'       => 'Not detected',

	'PPDE_INSTALL_DATE'       => 'Install date of <strong>%s</strong>',
	'PPDE_NO_VERSIONCHECK'    => 'No version check information given.',
	'PPDE_NOT_UP_TO_DATE'     => '%s is not up to date',
	'PPDE_STATS'              => 'Donation statistics',
	'PPDE_VERSION'            => '<strong>%s</strong> version',

	'STAT_RESET_DATE'         => 'Reset MOD Installation date',
	'STAT_RESET_DATE_EXPLAIN' => 'Resetting the installation date will affect the calculation of the total amount of donations and some other statistics.',
	'STAT_RESET_DATE_CONFIRM' => 'Are you sure you wish to reset the installation date of this extension?',
));

/**
 * mode: settings
 */
$lang = array_merge($lang, array(
	'PPDE_SETTINGS'                       => 'General Settings',
	'PPDE_SETTINGS_EXPLAIN'               => 'Here you can configure the main settings for PayPal Donation.',

	'MODE_CURRENCY'                       => 'currency',
	'MODE_DONATION_PAGES'                 => 'donation pages',

	// Global settings
	'PPDE_LEGEND_GENERAL_SETTINGS'        => 'General Settings',
	'PPDE_ENABLE'                         => 'Enable PayPal Donation',
	'PPDE_ENABLE_EXPLAIN'                 => 'Enable or disable the PayPal Donation MOD.',
	'PPDE_ACCOUNT_ID'                     => 'PayPal account ID',
	'PPDE_ACCOUNT_ID_EXPLAIN'             => 'Enter your PayPal email address or Marchant account ID.',
	'PPDE_DEFAULT_CURRENCY'               => 'Default currency',
	'PPDE_DEFAULT_CURRENCY_EXPLAIN'       => 'Define which currency will be selected by default.',
	'PPDE_DEFAULT_VALUE'                  => 'Default donation value',
	'PPDE_DEFAULT_VALUE_EXPLAIN'          => 'Define which donation value will be suggested by default.',
	'PPDE_DROPBOX_ENABLE'                 => 'Enable drop-down list',
	'PPDE_DROPBOX_ENABLE_EXPLAIN'         => 'If enabled, it will replace the Textbox by a drop-down list.',
	'PPDE_DROPBOX_VALUE'                  => 'Drop-down donation value',
	'PPDE_DROPBOX_VALUE_EXPLAIN'          => 'Define the numbers you want to see in the drop-down list.<br />Use <strong>comma</strong> (",") <strong>with no space</strong> to separate each values.',

	// PayPal sandbox settings
	'PPDE_LEGEND_SANDBOX_SETTINGS'        => 'PayPal sandbox settings',
	'PPDE_SANDBOX_ENABLE'                 => 'Sandbox testing',
	'PPDE_SANDBOX_ENABLE_EXPLAIN'         => 'Enable this option if you want use PayPal Sandbox instead of PayPal services.<br />Useful for developers/testers. All the transactions are fictitious.',
	'PPDE_SANDBOX_FOUNDER_ENABLE'         => 'Sandbox only for founder',
	'PPDE_SANDBOX_FOUNDER_ENABLE_EXPLAIN' => 'If enabled, PayPal Sandbox will be displayed only by the board founders.',
	'PPDE_SANDBOX_ADDRESS'                => 'PayPal sandbox address',
	'PPDE_SANDBOX_ADDRESS_EXPLAIN'        => 'Define here your PayPal Sandbox Sellers e-mail address.',

	// Stats Donation settings
	'PPDE_LEGEND_STATS_SETTINGS'          => 'Stats donation config',
	'PPDE_STATS_INDEX_ENABLE'             => 'Display donation stats on index',
	'PPDE_STATS_INDEX_ENABLE_EXPLAIN'     => 'Enable this if you want to display the donation stats on index.',
	'PPDE_RAISED_ENABLE'                  => 'Enable donation raised',
	'PPDE_RAISED'                         => 'Donation raised',
	'PPDE_RAISED_EXPLAIN'                 => 'The current amount raised through donations.',
	'PPDE_GOAL_ENABLE'                    => 'Enable donation goal',
	'PPDE_GOAL'                           => 'Donation goal',
	'PPDE_GOAL_EXPLAIN'                   => 'The total amount that you want to raise.',
	'PPDE_USED_ENABLE'                    => 'Enable donation used',
	'PPDE_USED'                           => 'Donation used',
	'PPDE_USED_EXPLAIN'                   => 'The amount of donation that you have already used.',
	// Note for translator: do not translate the decimal symbol
	'PPDE_DECIMAL_EXPLAIN'                => 'Use “.” as decimal symbol.',

	'PPDE_CURRENCY_ENABLE'                => 'Enable donation currency',
	'PPDE_CURRENCY_ENABLE_EXPLAIN'        => 'Enable this option if you want to display the ISO 4217 code of default currency in Stats.',
));

/**
 * mode: donation pages
 * Info: language keys are prefixed with 'PPDE_DP_' for 'PPDE_DONATION_PAGES_'
 */
$lang = array_merge($lang, array(
	// Donation Page: Title
	'PPDE_ACP_CREATE_DONATION_PAGE' => 'Create Donation Page',
	'PPDE_ACP_EDIT_DONATION_PAGE'   => 'Edit Donation Page',

	// Donation Page settings
	'PPDE_DP_CONFIG'                => 'Donation pages',
	'PPDE_DP_CONFIG_EXPLAIN'        => 'Permit to improve the rendering of customizable pages of the extension.',

	'PPDE_DP_PAGE'                  => 'Page type',
	'PPDE_DP_LANG'                  => 'Language',
	'PPDE_DP_LANG_SELECT'           => 'Select a language',

	// Donation Page Body settings
	'DONATION_BODY'                 => 'Donation main page',
	'DONATION_BODY_EXPLAIN'         => 'Enter the text you want displayed on the main donation page.',

	// Donation Success settings
	'DONATION_SUCCESS'              => 'Donation success',
	'DONATION_SUCCESS_EXPLAIN'      => 'Enter the text you want displayed on the success page.',

	// Donation Cancel settings
	'DONATION_CANCEL'               => 'Donation cancel',
	'DONATION_CANCEL_EXPLAIN'       => 'Enter the text you want displayed on the cancel page.',

	// Donation Page Template vars
	'PPDE_DP_PREDEFINED_VARS'       => 'Predefined Variables',
	'PPDE_DP_VAR_EXAMPLE'           => 'Example',
	'PPDE_DP_VAR_NAME'              => 'Name',
	'PPDE_DP_VAR_VAR'               => 'Variable',

	'PPDE_DP_BOARD_CONTACT'         => 'Board contact',
	'PPDE_DP_BOARD_EMAIL'           => 'Board e-mail',
	'PPDE_DP_BOARD_SIG'             => 'Board’s Signature',
	'PPDE_DP_SITE_DESC'             => 'Site description',
	'PPDE_DP_SITE_NAME'             => 'Sitename',
	'PPDE_DP_USER_ID'               => 'User ID',
	'PPDE_DP_USERNAME'              => 'Username',
));

/**
 * mode: currency
 * Info: language keys are prefixed with 'PPDE_DC_' for 'PPDE_DONATION_CURRENCY_'
 */
$lang = array_merge($lang, array(
	// Currency Management
	'PPDE_DC_CONFIG'           => 'Currency management',
	'PPDE_DC_CONFIG_EXPLAIN'   => 'Here you can manage currency.',
	'PPDE_DC_NAME'             => 'Currency name',
	'PPDE_DC_NAME_EXPLAIN'     => 'Name of the currency.<br />(i.e. Euro).',
	'PPDE_DC_ISO_CODE'         => 'ISO 4217 code',
	'PPDE_DC_ISO_CODE_EXPLAIN' => 'Alpabetic code of the currency.<br />More about ISO 4217… refer to the <a href="http://www.phpbb.com/customise/db/mod/paypal_donation_mod/faq/f_746" title="PayPal Donation MOD FAQ">PayPal Donation MOD FAQ</a> (external link).',
	'PPDE_DC_SYMBOL'           => 'Currency symbol',
	'PPDE_DC_SYMBOL_EXPLAIN'   => 'Define the currency symbol.<br />(i.e. € for Euro, $ for U.S. Dollar).',
	'PPDE_DC_ENABLE'           => 'Enable currency',
	'PPDE_DC_ENABLE_EXPLAIN'   => 'If enabled, currency will be displayed in the dropbox.',
	'PPDE_DC_CREATE_CURRENCY'  => 'Add currency',
));

/**
 * logs
 */
$lang = array_merge($lang, array(
	//logs
	'LOG_PPDE_SETTINGS_UPDATED' => '<strong>PayPal Donation: Settings updated.</strong>',
	'LOG_PPDE_PAGES_UPDATED'    => '<strong>PayPal Donation: Donation Pages updated.</strong>',
	'LOG_PPDE_ITEM_ADDED'       => '<strong>PayPal Donation: %1$s added</strong><br />» %2$s',
	'LOG_PPDE_ITEM_UPDATED'     => '<strong>PayPal Donation: %1$s updated</strong><br />» %2$s',
	'LOG_PPDE_ITEM_REMOVED'     => '<strong>PayPal Donation: %1$s deleted</strong>',
	'LOG_PPDE_ITEM_MOVE_DOWN'   => '<strong>PayPal Donation: Moved a %1$s. </strong> %2$s <strong>below</strong> %3$s',
	'LOG_PPDE_ITEM_MOVE_UP'     => '<strong>PayPal Donation: Moved a %1$s. </strong> %2$s <strong>above</strong> %3$s',
	'LOG_PPDE_ITEM_ENABLED'     => '<strong>PayPal Donation: %1$s enabled</strong><br />» %2$s',
	'LOG_PPDE_ITEM_DISABLED'    => '<strong>PayPal Donation: %1$s disabled</strong><br />» %2$s',
	'LOG_PPDE_STAT_RESET_DATE'  => '<strong>PayPal Donation: Installation date reset</strong>',

	// Confirm box
	'PPDE_DC_CONFIRM_DELETE'    => 'Are you sure you want to delete the selected currency?',
	'PPDE_DC_GO_TO_PAGE'        => '%sEdit existing currency%s',
	'PPDE_DC_ADDED'             => 'A currency has been added.',
	'PPDE_DC_UPDATED'           => 'A currency has been updated.',
	'PPDE_DC_DELETED'           => 'A currency has been deleted.',
	'PPDE_DP_CONFIRM_DELETE'    => 'Are you sure you want to delete the selected donation page?',
	'PPDE_DP_GO_TO_PAGE'        => '%sEdit existing donation page%s',
	'PPDE_DP_LANG_ADDED'        => 'A donation page for the language “%s” has been added.',
	'PPDE_DP_LANG_DELETED'      => 'A donation page for the language “%s” has been removed.',
	'PPDE_DP_LANG_UPDATED'      => 'A donation page for the language “%s” has been updated.',
	'PPDE_SETTINGS_SAVED'       => 'Donation settings saved.',

	// Errors
	'PPDE_DC_EMPTY_NAME'        => 'Enter a currency name.',
	'PPDE_DC_EMPTY_ISO_CODE'    => 'Enter an ISO code.',
	'PPDE_DC_EMPTY_SYMBOL'      => 'Enter a symbol.',
	'PPDE_FIELD_MISSING'        => 'Required field “%s” is missing.',
	'PPDE_NO_PAGE'              => 'No donation page found.',
	'PPDE_MUST_SELECT_LANG'     => 'No language selected.',
	'PPDE_MUST_SELECT_PAGE'     => 'The selected donation page does not exist.',
	'PPDE_PAGE_EXISTS'          => 'This donation page already exists.',
));
