<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * Translated By : Bassel Taha Alhitary <http://alhitary.net>
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

// Adding the permissions
$lang = array_merge($lang, array(
	'ACL_CAT_PPDE' => 'تبرعات PayPal',

	'ACL_A_PPDE_MANAGE'         => 'يستطيع إدارة الإضافة “تبَرُّعات PayPal”',
	'ACL_U_PPDE_USE'            => 'يستطيع أن يتبَرَّع',
	'ACL_U_PPDE_VIEW_DONORLIST' => 'يستطيع مشاهدة قائمة المُتَبَرِعين',
));
