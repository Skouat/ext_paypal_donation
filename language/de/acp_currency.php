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
 * mode: currency
 */
$lang = array_merge($lang, [
	'PPDE_DC_CONFIG'             => 'Currency Management',
	'PPDE_DC_CONFIG_EXPLAIN'     => 'Here you can manage currencies.',
	'PPDE_DC_CREATE_CURRENCY'    => 'Add currency',
	'PPDE_DC_DEFAULT_CURRENCY'   => '(default currency)',
	'PPDE_DC_ENABLE'             => 'Enable currency',
	'PPDE_DC_ENABLE_EXPLAIN'     => 'If enabled, the currency will be displayed in the dropbox.',
	'PPDE_DC_ISO_CODE'           => 'ISO 4217 code',
	'PPDE_DC_ISO_CODE_EXPLAIN'   => 'Alphabetic code of the currency.<br>For more information about ISO 4217, please refer to the PayPal Donation Extension <a href="https://www.phpbb.com/customise/db/extension/paypal_donation_extension/faq/1841" title="PayPal Donation Extension FAQ">FAQ</a> (external link).',
	'PPDE_DC_LOCALE_AVAILABLE'   => 'PHP <code>intl</code> extension detected. Go to the PPDE “General settings” module to select your locale settings.',
	'PPDE_DC_LOCALE_DEPRECATED'  => 'This option is disabled because your server is compatible with PHP <code>intl</code> extension.',
	'PPDE_DC_LOCALE_UNAVAILABLE' => 'PPDE doesn’t detect PHP <code>intl</code> extension. Consider installing this PHP extension for a better experience.',
	'PPDE_DC_NAME'               => 'Currency name',
	'PPDE_DC_NAME_EXPLAIN'       => 'Name of the currency.<br>(i.e. Euro).',
	'PPDE_DC_POSITION'           => 'Position of the currency',
	'PPDE_DC_POSITION_EXPLAIN'   => 'Define where the currency symbol will be positioned relative to the amount displayed.<br>eg: <strong>$20</strong> or <strong>15€</strong>.',
	'PPDE_DC_POSITION_LEFT'      => 'Left',
	'PPDE_DC_POSITION_RIGHT'     => 'Right',
	'PPDE_DC_SYMBOL'             => 'Currency symbol',
	'PPDE_DC_SYMBOL_EXPLAIN'     => 'Define the currency symbol.<br>eg: <strong>$</strong> for U.S. Dollar, <strong>€</strong> for Euro.',
]);

/**
 * Confirm box
 */
$lang = array_merge($lang, [
	'PPDE_DC_ADDED'             => 'A currency has been added.',
	'PPDE_DC_CONFIRM_OPERATION' => 'Are you sure you want to delete the selected currency?',
	'PPDE_DC_DELETED'           => 'A currency has been deleted.',
	'PPDE_DC_GO_TO_PAGE'        => '%sEdit existing currency%s',
	'PPDE_DC_UPDATED'           => 'A currency has been updated.',
]);

/**
 * Errors
 */
$lang = array_merge($lang, [
	'PPDE_CANNOT_DISABLE_DEFAULT_CURRENCY' => 'You cannot disable the default currency.',
	'PPDE_DC_EMPTY_NAME'                   => 'Enter a currency name.',
	'PPDE_DC_EMPTY_ISO_CODE'               => 'Enter an ISO code.',
	'PPDE_DC_EMPTY_SYMBOL'                 => 'Enter a symbol.',
	'PPDE_DC_EXISTS'                       => 'This currency already exists.',
	'PPDE_DC_INVALID_HASH'                 => 'The link is corrupted. The hash is invalid.',
	'PPDE_DC_NO_CURRENCY'                  => 'No currency found.',
	'PPDE_DISABLE_BEFORE_DELETION'         => 'You must disable this currency before deleting it.',
]);
