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

/**
 * mode: donation pages
 */
$lang = array_merge($lang, array(
	// Donation Page Body settings
	'DONATION_BODY'            => 'الصفحة الرئيسية للتبَرُّعات',
	'DONATION_BODY_EXPLAIN'    => 'من هنا تستطيع إضافة النص الذي تريد أن يظهر في الصفحة الرئيسية للتبَرُّعات.',

	// Donation Cancel settings
	'DONATION_CANCEL'          => 'إلغاء التبَرُّعات',
	'DONATION_CANCEL_EXPLAIN'  => 'من هنا تستطيع إضافة النص الذي تريد أن يظهر في صفحة إلغاء عملية التبَرُّع.',

	// Donation Success settings
	'DONATION_SUCCESS'         => 'نجاح التبَرُّعات',
	'DONATION_SUCCESS_EXPLAIN' => 'من هنا تستطيع إضافة النص الذي تريد أن يظهر في صفحة نجاح عملية التبَرُّع.',

	// Donation Page settings
	'PPDE_DP_CONFIG'           => 'صفحات التبَرُّعات',
	'PPDE_DP_CONFIG_EXPLAIN'   => 'من هنا تستطيع تخصيص الصفحة الرئيسية, صفحة النجاح & صفحة الخطأ لعمليات التبَرُّع.',
	'PPDE_DP_LANG'             => 'اللغة',
	'PPDE_DP_LANG_SELECT'      => 'تحديد اللغة',
	'PPDE_DP_PAGE'             => 'نوع الصفحة',

	// Donation Page Template vars
	'PPDE_DP_BOARD_CONTACT'    => 'عنوان الإتصال',
	'PPDE_DP_BOARD_EMAIL'      => 'البريد الإلكتروني',
	'PPDE_DP_BOARD_SIG'        => 'توقيع المنتدى',
	'PPDE_DP_PREDEFINED_VARS'  => 'متغيِّرات مُعَرّفة مُسبقاً',
	'PPDE_DP_SITE_DESC'        => 'وصف الموقع',
	'PPDE_DP_SITE_NAME'        => 'إسم الموقع',
	'PPDE_DP_USER_ID'          => 'رقم العضو',
	'PPDE_DP_USERNAME'         => 'إسم العضو',
	'PPDE_DP_VAR_EXAMPLE'      => 'مثال',
	'PPDE_DP_VAR_NAME'         => 'الإسم',
	'PPDE_DP_VAR_VAR'          => 'المتغيِّر ',
));

/**
 * Confirm box
 */
$lang = array_merge($lang, array(
	'PPDE_DP_ADDED'          => 'تم إضافة صفحة التبَرُّعات للغة “%s” بنجاح.',
	'PPDE_DP_CONFIRM_DELETE' => 'متأكد أنك تريد حذف صفحة التبَرُّعات التي حددتها؟',
	'PPDE_DP_DELETED'        => 'تم إزالة صفحة التبَرُّعات للغة “%s” بنجاح.',
	'PPDE_DP_GO_TO_PAGE'     => '%sتعديل صفحة التبَرُّعات الحالية%s',
	'PPDE_DP_UPDATED'        => 'تم تحديث صفحة التبَرُّعات للغة “%s” بنجاح.',
));

/**
 * Errors
 */
$lang = array_merge($lang, array(
	'PPDE_DP_EMPTY_LANG_ID'     => 'يجب عليك تحديد اللغة.',
	'PPDE_DP_EMPTY_NAME'        => 'صفحة التبَرُّعات التي حددتها غير موجودة.',
	'PPDE_DP_EXISTS'            => 'صفحة التبَرُّعات هذه موجودة مُسبقاً.',
	'PPDE_DP_NO_DONATION_PAGES' => 'لم يتم العثور على صفحة تبَرُّعات.',
));
