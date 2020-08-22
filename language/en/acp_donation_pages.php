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
 * mode: donation pages
 */
$lang = array_merge($lang, [
	// Donation Page Body settings
	'DONATION_BODY'            => 'Donation main page',
	'DONATION_BODY_EXPLAIN'    => 'Enter the text you want displayed on the main donation page.',

	// Donation Cancel settings
	'DONATION_CANCEL'          => 'Donation cancel',
	'DONATION_CANCEL_EXPLAIN'  => 'Enter the text you want displayed on the cancel page.',

	// Donation Success settings
	'DONATION_SUCCESS'         => 'Donation success',
	'DONATION_SUCCESS_EXPLAIN' => 'Enter the text you want displayed on the success page.',

	// Donation Page settings
	'PPDE_DP_CONFIG'           => 'Donation Pages',
	'PPDE_DP_CONFIG_EXPLAIN'   => 'Allows you to customise main, success & error pages of the extension.',
	'PPDE_DP_LANG'             => 'Language',
	'PPDE_DP_LANG_SELECT'      => 'Select a language',
	'PPDE_DP_PAGE'             => 'Page type',

	// Donation Page Template vars
	'PPDE_DP_BOARD_CONTACT'    => 'Board contact',
	'PPDE_DP_BOARD_EMAIL'      => 'Board e-mail',
	'PPDE_DP_BOARD_SIG'        => 'Board’s signature',
	'PPDE_DP_DONATION_GOAL'    => 'Donation goal',
	'PPDE_DP_DONATION_RAISED'  => 'Donation raised',
	'PPDE_DP_PREDEFINED_VARS'  => 'Predefined variables',
	'PPDE_DP_SITE_DESC'        => 'Site description',
	'PPDE_DP_SITE_NAME'        => 'Sitename',
	'PPDE_DP_USER_ID'          => 'User ID',
	'PPDE_DP_USERNAME'         => 'Username',
	'PPDE_DP_VAR_EXAMPLE'      => 'Example',
	'PPDE_DP_VAR_NAME'         => 'Name',
	'PPDE_DP_VAR_VAR'          => 'Variable',
]);

/**
 * Confirm box
 */
$lang = array_merge($lang, [
	'PPDE_DP_ADDED'             => 'A donation page for the language “%s” has been added.',
	'PPDE_DP_CONFIRM_OPERATION' => 'Are you sure you want to delete the selected donation page?',
	'PPDE_DP_DELETED'           => 'A donation page for the language “%s” has been removed.',
	'PPDE_DP_GO_TO_PAGE'        => '%sEdit existing donation page%s',
	'PPDE_DP_UPDATED'           => 'A donation page for the language “%s” has been updated.',
]);

/**
 * Errors
 */
$lang = array_merge($lang, [
	'PPDE_DP_EMPTY_LANG_ID'     => 'No language selected.',
	'PPDE_DP_EMPTY_NAME'        => 'The selected donation page does not exist.',
	'PPDE_DP_EXISTS'            => 'This donation page already exists.',
	'PPDE_DP_NO_DONATION_PAGES' => 'No donation page found.',
]);
