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
	'PPDE_ACP_DONATION'        => 'PayPal Donation',
	'PPDE_ACP_OVERVIEW'        => 'Overview',
	'PPDE_ACP_PAYPAL_FEATURES' => 'PayPal IPN Features',
	'PPDE_ACP_SETTINGS'        => 'General Settings',
	'PPDE_ACP_DONATION_PAGES'  => 'Donation Pages',
	'PPDE_ACP_CURRENCY'        => 'Currency management',
	'PPDE_ACP_TRANSACTIONS'    => 'Transactions log',
));

/**
 * mode: overview
 */
$lang = array_merge($lang, array(
	'PPDE_OVERVIEW'                     => 'Overview',

	'INFO_CURL'                         => 'cURL',
	'INFO_CURL_VERSION'                 => 'cURL version: %1$s<br />SSL version: %2$s',
	'INFO_FSOCKOPEN'                    => 'Fsockopen',
	'INFO_DETECTED'                     => 'Detected',
	'INFO_NOT_DETECTED'                 => 'Not detected',

	'PPDE_INSTALL_DATE'                 => 'Install date of <strong>%s</strong>',
	'PPDE_NO_VERSIONCHECK'              => 'No version check information given.',
	'PPDE_NOT_UP_TO_DATE'               => '%s is not up to date',
	'PPDE_STATS'                        => 'Donation statistics',
	'PPDE_STATS_SANDBOX'                => 'Sandbox statistics',
	'PPDE_VERSION'                      => '<strong>%s</strong> version',

	'STAT_RESET_DATE'                   => 'Reset Extension Installation date',
	'STAT_RESET_DATE_EXPLAIN'           => 'Resetting the installation date will affect the calculation of the total amount of donations and some other statistics.',
	'STAT_RESET_DATE_CONFIRM'           => 'Are you sure you wish to reset the installation date of this extension?',
	'STAT_RESYNC_STATS'                 => 'Resynchronise donors and transactions counts',
	'STAT_RESYNC_STATS_EXPLAIN'         => 'Resynchronise all donors and transactions counts. Only anonymous donors and active members will be taken in consideration.',
	'STAT_RESYNC_STATS_CONFIRM'         => 'Are you sure you wish to resynchronise donors and transactions counts?',
	'STAT_RESYNC_SANDBOX_STATS'         => 'Resynchronise PayPal Sandbox counts',
	'STAT_RESYNC_SANDBOX_STATS_EXPLAIN' => 'Resynchronise all donors and transactions PayPal Sandbox counts.',
	'STAT_RESYNC_SANDBOX_STATS_CONFIRM' => 'Are you sure you wish to resynchronise PayPal Sandbox counts?',
	'STAT_RETEST_CURL_FSOCK'            => 'Re-detect “cURL” and “fsockopen”',
	'STAT_RETEST_CURL_FSOCK_EXPLAIN'    => 'Allow to re-detect this features if the webserver configuration have changed.',
	'STAT_RETEST_CURL_FSOCK_CONFIRM'    => 'Are you sure you wish to re-detect “cURL” and “fsockopen”?',

	'STATS_ANONYMOUS_DONORS_COUNT'      => 'Number of anonymous donors',
	'STATS_ANONYMOUS_DONORS_PER_DAY'    => 'Anonymous donors per day',
	'STATS_KNOWN_DONORS_COUNT'          => 'Number of known donors',
	'STATS_KNOWN_DONORS_PER_DAY'        => 'Known donors per day',
	'STATS_TRANSACTIONS_COUNT'          => 'Number of transactions',
	'STATS_TRANSACTIONS_PER_DAY'        => 'Transactions per day',
));

/**
 * mode: settings
 */
$lang = array_merge($lang, array(
	'PPDE_SETTINGS'                   => 'General Settings',
	'PPDE_SETTINGS_EXPLAIN'           => 'Here you can configure the main settings for PayPal Donation.',

	// Global settings
	'PPDE_LEGEND_GENERAL_SETTINGS'    => 'General Settings',
	'PPDE_ENABLE'                     => 'Enable PayPal Donation',
	'PPDE_ENABLE_EXPLAIN'             => 'Enable or disable the PayPal Donation Extension.',
	'PPDE_HEADER_LINK'                => 'Display the link “Donations” in the header',
	'PPDE_ACCOUNT_ID'                 => 'PayPal account ID',
	'PPDE_ACCOUNT_ID_EXPLAIN'         => 'Enter your PayPal email address or Merchant account ID.',
	'PPDE_DEFAULT_CURRENCY'           => 'Default currency',
	'PPDE_DEFAULT_CURRENCY_EXPLAIN'   => 'Define which currency will be selected by default.',
	'PPDE_DEFAULT_VALUE'              => 'Default donation value',
	'PPDE_DEFAULT_VALUE_EXPLAIN'      => 'Define which donation value will be suggested by default.',
	'PPDE_DROPBOX_ENABLE'             => 'Enable drop-down list',
	'PPDE_DROPBOX_ENABLE_EXPLAIN'     => 'If enabled, it will replace the Textbox by a drop-down list.',
	'PPDE_DROPBOX_VALUE'              => 'Drop-down donation value',
	'PPDE_DROPBOX_VALUE_EXPLAIN'      => 'Define the numbers you want to see in the drop-down list.<br />Use <strong>comma</strong> (",") <strong>with no space</strong> to separate each values.',

	// Stats Donation settings
	'PPDE_LEGEND_STATS_SETTINGS'      => 'Stats donation config',
	'PPDE_STATS_INDEX_ENABLE'         => 'Display donation stats on index',
	'PPDE_STATS_INDEX_ENABLE_EXPLAIN' => 'Enable this if you want to display the donation stats on index.',
	'PPDE_RAISED'                     => 'Donation raised',
	'PPDE_RAISED_EXPLAIN'             => 'The current amount raised through donations.',
	'PPDE_GOAL'                       => 'Donation goal',
	'PPDE_GOAL_EXPLAIN'               => 'The total amount that you want to raise.',
	'PPDE_USED'                       => 'Donation used',
	'PPDE_USED_EXPLAIN'               => 'The amount of donation that you have already used.',
	'PPDE_AMOUNT'                     => 'Amount',
	// Note for translator: do not translate the decimal symbol
	'PPDE_DECIMAL_EXPLAIN'            => 'Use “.” as decimal symbol.',

	'PPDE_CURRENCY_ENABLE'            => 'Enable donation currency',
	'PPDE_CURRENCY_ENABLE_EXPLAIN'    => 'Enable this option if you want to display the ISO 4217 code of default currency in Stats.',
));

/**
 * mode: PayPal features
 */
$lang = array_merge($lang, array(
	'PPDE_PAYPAL_FEATURES'                 => 'PayPal IPN features',
	'PPDE_PAYPAL_FEATURES_EXPLAIN'         => 'Here you can configure all features that use the PayPal Instant Payment Notification (IPN).',

	// PayPal IPN settings
	'PPDE_LEGEND_IPN_AUTOGROUP'            => 'Auto-group',
	'PPDE_LEGEND_IPN_DONORLIST'            => 'Donors list',
	'PPDE_LEGEND_IPN_NOTIFICATION'         => 'Notification system',
	'PPDE_LEGEND_IPN_SETTINGS'             => 'Général settings',
	'PPDE_IPN_AG_ENABLE'                   => 'Enable Auto Group',
	'PPDE_IPN_AG_ENABLE_EXPLAIN'           => 'Allows to add donors to a predefined group.',
	'PPDE_IPN_AG_DONORS_GROUP'             => 'Donors group',
	'PPDE_IPN_AG_DONORS_GROUP_EXPLAIN'     => 'Select the group that will host the donor members.',
	'PPDE_IPN_AG_GROUP_AS_DEFAULT'         => 'Set donors group as default',
	'PPDE_IPN_AG_GROUP_AS_DEFAULT_EXPLAIN' => 'Enable to set the donors group as the default group for users having make a donation.',
	'PPDE_IPN_DL_ENABLE'                   => 'Enable Donors list',
	'PPDE_IPN_DL_ENABLE_EXPLAIN'           => 'Allows to enable the list of donors.',
	'PPDE_IPN_ENABLE'                      => 'Enable IPN',
	'PPDE_IPN_ENABLE_EXPLAIN'              => 'Enable this option if you want use Instant Payment Notification of PayPal services.<br />If enabled, all features dependent on PayPal IPN will be available below.',
	'PPDE_IPN_LOGGING'                     => 'Enable log errors',
	'PPDE_IPN_LOGGING_EXPLAIN'             => 'Log errors and data from PayPal IPN into the directory <strong>/store/ext/ppde/</strong>.',
	'PPDE_IPN_NOTIFICATION_ENABLE'         => 'Enable notification',
	'PPDE_IPN_NOTIFICATION_ENABLE_EXPLAIN' => 'Allows to notify PPDE admin and donors when a donation is received.',

	// PayPal sandbox settings
	'PPDE_LEGEND_SANDBOX_SETTINGS'         => 'Sandbox settings',
	'PPDE_SANDBOX_ENABLE'                  => 'Sandbox testing',
	'PPDE_SANDBOX_ENABLE_EXPLAIN'          => 'Enable this option if you want use PayPal Sandbox instead of PayPal services.<br />Useful for developers/testers. All the transactions are fictitious.',
	'PPDE_SANDBOX_FOUNDER_ENABLE'          => 'Sandbox only for founder',
	'PPDE_SANDBOX_FOUNDER_ENABLE_EXPLAIN'  => 'If enabled, PayPal Sandbox will be displayed only by the board founders.',
	'PPDE_SANDBOX_ADDRESS'                 => 'PayPal sandbox address',
	'PPDE_SANDBOX_ADDRESS_EXPLAIN'         => 'Enter your Sandbox email address or Sandbox Merchant account ID.',
));

/**
 * mode: donation pages
 * Info: language keys are prefixed with 'PPDE_DP_' for 'PPDE_DONATION_PAGES_'
 */
$lang = array_merge($lang, array(
	// Donation Page settings
	'PPDE_DP_CONFIG'           => 'Donation pages',
	'PPDE_DP_CONFIG_EXPLAIN'   => 'Permit to improve the rendering of customizable pages of the extension.',

	'PPDE_DP_PAGE'             => 'Page type',
	'PPDE_DP_LANG'             => 'Language',
	'PPDE_DP_LANG_SELECT'      => 'Select a language',

	// Donation Page Body settings
	'DONATION_BODY'            => 'Donation main page',
	'DONATION_BODY_EXPLAIN'    => 'Enter the text you want displayed on the main donation page.',

	// Donation Success settings
	'DONATION_SUCCESS'         => 'Donation success',
	'DONATION_SUCCESS_EXPLAIN' => 'Enter the text you want displayed on the success page.',

	// Donation Cancel settings
	'DONATION_CANCEL'          => 'Donation cancel',
	'DONATION_CANCEL_EXPLAIN'  => 'Enter the text you want displayed on the cancel page.',

	// Donation Page Template vars
	'PPDE_DP_PREDEFINED_VARS'  => 'Predefined Variables',
	'PPDE_DP_VAR_EXAMPLE'      => 'Example',
	'PPDE_DP_VAR_NAME'         => 'Name',
	'PPDE_DP_VAR_VAR'          => 'Variable',

	'PPDE_DP_BOARD_CONTACT'    => 'Board contact',
	'PPDE_DP_BOARD_EMAIL'      => 'Board e-mail',
	'PPDE_DP_BOARD_SIG'        => 'Board’s Signature',
	'PPDE_DP_SITE_DESC'        => 'Site description',
	'PPDE_DP_SITE_NAME'        => 'Sitename',
	'PPDE_DP_USER_ID'          => 'User ID',
	'PPDE_DP_USERNAME'         => 'Username',
));

/**
 * mode: currency
 * Info: language keys are prefixed with 'PPDE_DC_' for 'PPDE_DONATION_CURRENCY_'
 */
$lang = array_merge($lang, array(
	// Currency Management
	'PPDE_DC_CONFIG'           => 'Currency management',
	'PPDE_DC_CONFIG_EXPLAIN'   => 'Here you can manage currency.',
	'PPDE_DC_CREATE_CURRENCY'  => 'Add currency',
	'PPDE_DC_ENABLE'           => 'Enable currency',
	'PPDE_DC_ENABLE_EXPLAIN'   => 'If enabled, currency will be displayed in the dropbox.',
	'PPDE_DC_ISO_CODE'         => 'ISO 4217 code',
	'PPDE_DC_ISO_CODE_EXPLAIN' => 'Alpabetic code of the currency.<br />More about ISO 4217… refer to the <a href="http://www.phpbb.com/customise/db/mod/paypal_donation_mod/faq/f_746" title="PayPal Donation MOD FAQ">PayPal Donation MOD FAQ</a> (external link).',
	'PPDE_DC_NAME'             => 'Currency name',
	'PPDE_DC_NAME_EXPLAIN'     => 'Name of the currency.<br />(i.e. Euro).',
	'PPDE_DC_POSITION'         => 'Position of the currency',
	'PPDE_DC_POSITION_EXPLAIN' => 'Defined where the currency symbol will be positioned relative to the amount displayed.<br />eg: <strong>$20</strong> or <strong>15€</strong>.',
	'PPDE_DC_POSITION_LEFT'    => 'Left',
	'PPDE_DC_POSITION_RIGHT'   => 'Right',
	'PPDE_DC_SYMBOL'           => 'Currency symbol',
	'PPDE_DC_SYMBOL_EXPLAIN'   => 'Define the currency symbol.<br />eg: <strong>$</strong> for U.S. Dollar, <strong>€</strong> for Euro.',
));

/**
 * mode: transactions
 * Info: language keys are prefixed with 'PPDE_DT_' for 'PPDE_DONATION_TRANSACTION_'
 */
$lang = array_merge($lang, array(
	// Transactions log
	'PPDE_DT_CONFIG'                => 'Transactions log',
	'PPDE_DT_CONFIG_EXPLAIN'        => 'Here you can show transactions details.',
	'PPDE_DT_IPN_STATUS'            => 'IPN Status',
	'PPDE_DT_IPN_TEST'              => 'IPN test',
	'PPDE_DT_PAYMENT_STATUS'        => 'Payment Status',
	'PPDE_DT_TXN_ID'                => 'Transaction ID',
	'PPDE_DT_USERNAME'              => 'Donor name',

	// Display transactions
	'PPDE_DT_BOARD_USERNAME'        => 'Donors',
	'PPDE_DT_DETAILS'               => 'Transaction details',
	'PPDE_DT_EXCHANGE_RATE'         => 'Exchange rate',
	'PPDE_DT_EXCHANGE_RATE_EXPLAIN' => 'Based on the exchange rate in effect at %s',
	'PPDE_DT_FEE_AMOUNT'            => 'Fee amount',
	'PPDE_DT_ITEM_NAME'             => 'Item name',
	'PPDE_DT_ITEM_NUMBER'           => 'Item number',
	'PPDE_DT_NAME'                  => 'Name',
	'PPDE_DT_NET_AMOUNT'            => 'Net amount',
	'PPDE_DT_PAYER_ID'              => 'Payer ID',
	'PPDE_DT_PAYER_EMAIL'           => 'Payer e-mail',
	'PPDE_DT_PAYER_STATUS'          => 'Payer status',
	'PPDE_DT_PAYMENT_DATE'          => 'Payment Date',
	'PPDE_DT_RECEIVER_EMAIL'        => 'Payment sent to',
	'PPDE_DT_RECEIVER_ID'           => 'Receiver ID',
	'PPDE_DT_SETTLE_AMOUNT'         => 'Conversion to “%s”',
	'PPDE_DT_SORT_TXN_ID'           => 'Transaction ID',
	'PPDE_DT_SORT_DONORS'           => 'Donors',
	'PPDE_DT_SORT_IPN_STATUS'       => 'IPN Status',
	'PPDE_DT_SORT_IPN_TYPE'         => 'Transaction type',
	'PPDE_DT_SORT_PAYMENT_STATUS'   => 'Payment Status',
	'PPDE_DT_TOTAL_AMOUNT'          => 'Total amount',
	'PPDE_DT_UNVERIFIED'            => 'Not verified',
	'PPDE_DT_VERIFIED'              => 'Verified',
));

/**
 * mode: transactions
 * Info: language keys are prefixed with 'PPDE_DT_' for 'PPDE_DONATION_TRANSACTION_'
 * Info: This array is out of the previous because there is an issue with Transifex platform
 */
$lang = array_merge($lang, array(
	/**
	 * TRANSLATORS PLEASE NOTE
	 * The line below has a special note.
	 * "## For translate:" followed by one "Don't" and one "Yes"
	 * "Don't" means do not change this column, and "Yes" means you can translate this column.
	 */

	## For translate:					Don't					Yes
	'PPDE_DT_PAYMENT_STATUS_VALUES' => array(
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
	),
));

/**
 * logs
 */
$lang = array_merge($lang, array(
	'LOG_PPDE_DC_ADDED'            => '<strong>PayPal Donation: New currency added</strong><br />» %s',
	'LOG_PPDE_DC_DELETED'          => '<strong>PayPal Donation: Currency deleted</strong><br />» %s',
	'LOG_PPDE_DC_DEACTIVATED'      => '<strong>PayPal Donation: Currency disabled</strong><br />» %s',
	'LOG_PPDE_DC_ACTIVATED'        => '<strong>PayPal Donation: Currency enabled</strong><br />» %s',
	'LOG_PPDE_DC_MOVE_DOWN'        => '<strong>PayPal Donation: Move down the currency</strong> “%s”',
	'LOG_PPDE_DC_MOVE_UP'          => '<strong>PayPal Donation: Move up the currency</strong> “%s”',
	'LOG_PPDE_DC_UPDATED'          => '<strong>PayPal Donation: Currency edited</strong><br />» %s',
	'LOG_PPDE_DP_ADDED'            => '<strong>PayPal Donation: New donation page added</strong><br />» “%1$s” for the language “%2$s”', // eg: » “Donation success” for the language “British English”',
	'LOG_PPDE_DP_DELETED'          => '<strong>PayPal Donation: Donation page deleted</strong><br />» “%1$s” for the language “%2$s”',
	'LOG_PPDE_DP_UPDATED'          => '<strong>PayPal Donation: Donation page updated</strong><br />» “%1$s” for the language “%2$s”',
	'LOG_PPDE_DT_PURGED'           => '<strong>PayPal Donation: Purge transactions log</strong>',
	'LOG_PPDE_SETTINGS_UPDATED'    => '<strong>PayPal Donation: Settings updated</strong>',
	'LOG_PPDE_STAT_RESET_DATE'     => '<strong>PayPal Donation: Installation date reset</strong>',
	'LOG_PPDE_STAT_RESYNC'         => '<strong>PayPal Donation: Statistics resynchronised</strong>',
	'LOG_PPDE_STAT_RETEST_REMOTE'  => '<strong>PayPal Donation: Remote connection re-detected</strong>',
	'LOG_PPDE_STAT_SANDBOX_RESYNC' => '<strong>PayPal Donation: PayPal Sandbox Statistics resynchronised</strong>',
));

/**
 * Confirm box
 */
$lang = array_merge($lang, array(
	'PPDE_DC_CONFIRM_DELETE'     => 'Are you sure you want to delete the selected currency?',
	'PPDE_DC_GO_TO_PAGE'         => '%sEdit existing currency%s',
	'PPDE_DC_ADDED'              => 'A currency has been added.',
	'PPDE_DC_UPDATED'            => 'A currency has been updated.',
	'PPDE_DC_DELETED'            => 'A currency has been deleted.',
	'PPDE_DP_CONFIRM_DELETE'     => 'Are you sure you want to delete the selected donation page?',
	'PPDE_DP_GO_TO_PAGE'         => '%sEdit existing donation page%s',
	'PPDE_DP_ADDED'              => 'A donation page for the language “%s” has been added.',
	'PPDE_DP_DELETED'            => 'A donation page for the language “%s” has been removed.',
	'PPDE_DP_UPDATED'            => 'A donation page for the language “%s” has been updated.',
	'PPDE_SETTINGS_SAVED'        => 'Donation settings saved.',
	'PPDE_PAYPAL_FEATURES_SAVED' => 'PayPal IPN features saved.',
));

/**
 * Errors
 */
$lang = array_merge($lang, array(
	'PPDE_CANNOT_DISABLE_ALL_CURRENCIES' => 'You cannot disable all currencies.',
	'PPDE_DC_EMPTY_NAME'                 => 'Enter a currency name.',
	'PPDE_DC_EMPTY_ISO_CODE'             => 'Enter an ISO code.',
	'PPDE_DC_EMPTY_SYMBOL'               => 'Enter a symbol.',
	'PPDE_DC_EXISTS'                     => 'This currency already exists.',
	'PPDE_DC_INVALID_HASH'               => 'The link is corrupted. The hash is invalid.',
	'PPDE_DC_NO_CURRENCY'                => 'No currency found.',
	'PPDE_DP_EMPTY_LANG_ID'              => 'No language selected.',
	'PPDE_DP_EMPTY_NAME'                 => 'The selected donation page does not exist.',
	'PPDE_DP_EXISTS'                     => 'This donation page already exists.',
	'PPDE_DP_NO_DONATION_PAGES'          => 'No donation page found.',
	'PPDE_DT_NO_TRANSACTION'             => 'No transaction found.',
	'PPDE_DISABLE_BEFORE_DELETION'       => 'You must disable this currency before deleting it.',
	'PPDE_SETTINGS_MISSING'              => 'Please check “Account ID”.',
	'PPDE_PAYPAL_FEATURES_MISSING'       => 'Please check “Sandbox address”.',
));
