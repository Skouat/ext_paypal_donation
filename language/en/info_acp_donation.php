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
