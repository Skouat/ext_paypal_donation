<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * Translated By : Bassel Taha Alhitary - http://www.alhitary.net
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
 * mode: currency
 */
$lang = array_merge($lang, array(
	'PPDE_DC_CONFIG'           => 'إدارة العُملات',
	'PPDE_DC_CONFIG_EXPLAIN'   => 'من هنا تستطيع إدارة العُملات.',
	'PPDE_DC_CREATE_CURRENCY'  => 'إضافة عُملة',
	'PPDE_DC_DEFAULT_CURRENCY' => '(العُملة الإفتراضية)',
	'PPDE_DC_ENABLE'           => 'تفعيل',
	'PPDE_DC_ENABLE_EXPLAIN'   => 'عند اختيار “نعم”, سيتم عرض العُملة في القائمة المنسدلة للعُملات.',
	'PPDE_DC_ISO_CODE'         => 'شفرة العُملة ISO 4217',
	'PPDE_DC_ISO_CODE_EXPLAIN' => 'الشفرة الأبجدية للعُملة.<br>لمزيد من المعلومات عن ISO 4217, نرجوا الذهاب إلى <a href="https://www.phpbb.com/customise/db/mod/paypal_donation_mod/faq/f_746" title="الأسئلة المُتكررة عن هاك تبرعات PayPal">الأسئلة المُتكررة عن هاك تبرعات PayPal</a> (رابط خارجي).',
	'PPDE_DC_NAME'             => 'إسم العُملة',
	'PPDE_DC_NAME_EXPLAIN'     => 'إسم العُملة.<br>(مثال : يورو).',
	'PPDE_DC_POSITION'         => 'مكان العُملة',
	'PPDE_DC_POSITION_EXPLAIN' => 'تحديد مكان ظهور رمز العُملة بجانب المبلغ.<br>مثال: <strong>$20</strong> أو <strong>15€</strong>.',
	'PPDE_DC_POSITION_LEFT'    => 'يسار',
	'PPDE_DC_POSITION_RIGHT'   => 'يمين',
	'PPDE_DC_SYMBOL'           => 'رمز العُملة',
	'PPDE_DC_SYMBOL_EXPLAIN'   => 'تحديد الرمز الخاص بالعُملة.<br>مثال: <strong>$</strong> للدولار الأمريكي, <strong>€</strong> لليورو الأوروربي.',
));

/**
 * Confirm box
 */
$lang = array_merge($lang, array(
	'PPDE_DC_ADDED'          => 'تم إضافة العُملة بنجاح.',
	'PPDE_DC_CONFIRM_DELETE' => 'متأكد أنك تريد حذف العُملة التي حددتها؟',
	'PPDE_DC_DELETED'        => 'تم حذف العُملة بنجاح.',
	'PPDE_DC_GO_TO_PAGE'     => '%sتعديل العُملة الحالية%s',
	'PPDE_DC_UPDATED'        => 'تم تحديث العُملة بنجاح.',
));

/**
 * Errors
 */
$lang = array_merge($lang, array(
	'PPDE_CANNOT_DISABLE_DEFAULT_CURRENCY' => 'لا تستطيع تعطيل العُملة الإفتراضية.',
	'PPDE_DC_EMPTY_NAME'                   => 'يجب إدخال إسم العُملة.',
	'PPDE_DC_EMPTY_ISO_CODE'               => 'يجب إدخال شفرة العُملة ISO code.',
	'PPDE_DC_EMPTY_SYMBOL'                 => 'يجب إدخال رمز العُملة.',
	'PPDE_DC_EXISTS'                       => 'هذه العُملة موجودة مُسبقاً.',
	'PPDE_DC_INVALID_HASH'                 => 'هناك مشكلة في الرابط. الـ hash غير صالحة.',
	'PPDE_DC_NO_CURRENCY'                  => 'لم يتم العثور على أي عُملة.',
	'PPDE_DISABLE_BEFORE_DELETION'         => 'يجب تعطيل هذه العُملة قبل حذفها.',
));
