<?php
/**
*
* donate.php [English]
*
* @package PayPal Donation MOD
* @copyright (c) 2014 Skouat
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
	// Notice
	'DONATION_DISABLED'				=> 'Sorry, the Donation page is currently unavailable.',
	'DONATION_NOT_INSTALLED'		=> 'PayPal Donation MOD database entries are missing.<br />Please run the %sinstaller%s to make the database changes for the MOD.',
	'DONATION_INSTALL_MISSING'		=> 'The installation file seems to be missing. Please check your installation !',
	'DONATION_ADDRESS_MISSING'		=> 'Sorry, PayPal Donation is enabled but some settings are missing. Please notify the board founder.',
	'SANDBOX_ADDRESS_MISSING'		=> 'Sorry, PayPal Sandbox is enabled but some settings are missing. Please notify the board founder.',

	// Image alternative text
	'IMG_DONATE'			=> 'donate',
	'IMG_LOADER'			=> 'loading',

	// Default Currency
	'CURRENCY_DEFAULT'		=> 'USD', // Note : If you remove from ACP ALL currencies, this value will be defined as the default currency.

	// Stats
	//--------------------------->	%1$d = donation raised; %2$s = currency
	'DONATE_RECEIVED'			=> 'We received <strong>%1$d</strong> %2$s in donations.',
	'DONATE_NOT_RECEIVED'		=> 'We haven’t received any donations.',

	//--------------------------->	%1$d = donation goal; %2$s = currency
	'DONATE_GOAL_RAISE'			=> 'Our goal is to raise <strong>%1$d</strong> %2$s.',
	'DONATE_GOAL_REACHED'		=> 'Our goal donation was reached.',
	'DONATE_NO_GOAL'			=> 'We haven’t defined a donation goal.',

	//--------------------------->	%1$d = donation used; %2$s = currency; %3$d = donation raised;
	'DONATE_USED'				=> 'We used <strong>%1$d</strong> %2$s of your donations on <strong>%3$d</strong> %2$s already received.',
	'DONATE_USED_EXCEEDED'		=> 'We used <strong>%1$d</strong> %2$s. All your donations have been used.',
	'DONATE_NOT_USED'			=> 'We haven’t used any donations.',

	// Pages
	'DONATION_TITLE'			=> 'Make a Donation',
	'DONATION_TITLE_HEAD'		=> 'Make a Donation to',
	'DONATION_CANCEL_TITLE'		=> 'Donation Canceled',
	'DONATION_SUCCESS_TITLE'	=> 'Donation Successfull',
	'DONATION_CONTACT_PAYPAL'	=> 'Connecting to PayPal - Please Wait…',
	'SANDBOX_TITLE'				=> 'Test PayPal Donation with PayPal Sandbox',

	'DONATION_INDEX'			=> 'Donations',
));

/*
* UMIL
*/
$lang = array_merge($lang, array(
	'INSTALL_DONATION_MOD'				=> 'Install Donation Mod',
	'INSTALL_DONATION_MOD_CONFIRM'		=> 'Are you ready to install the Donation Mod?',
	'INSTALL_DONATION_MOD_WELCOME'		=> 'Major changes since version 1.0.3',
	'INSTALL_DONATION_MOD_WELCOME_NOTE'	=> 'The language keys used by “Donation pages” were migrated in the database.
											<br />If you use this feature, backup your language files before to update the MOD to this new release.
											<br /><br />A new permission has been added.
											<br />Do not forget to set up this new permission in <strong>ACP >> Permissions >> Global permissions >> User permissions</strong>
											<br />To allow guests to make a donation, check the box “Select anonymous user”',

	'DONATION_MOD'						=> 'Donation Mod',
	'DONATION_MOD_EXPLAIN'				=> 'Install Donation Mod database changes with UMIL auto method.',

	'UNINSTALL_DONATION_MOD'			=> 'Uninstall Donation Mod',
	'UNINSTALL_DONATION_MOD_CONFIRM'	=> 'Are you ready to uninstall the Donation Mod? All settings and data saved by this mod will be removed!',

	'UPDATE_DONATION_MOD'				=> 'Update Donation Mod',
	'UPDATE_DONATION_MOD_CONFIRM'		=> 'Are you ready to update the Donation Mod?',

	'UNUSED_LANG_FILES_TRUE'			=> 'Removal of unused language files.',
	'UNUSED_LANG_FILES_FALSE'			=> 'The removal of unused files is not necessary.',
));
