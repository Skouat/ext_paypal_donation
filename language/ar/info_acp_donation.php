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
 * mode: main
 */
$lang = array_merge($lang, array(
	'PPDE_ACP_DONATION'        => 'تبَرُّعات PayPal',
	'PPDE_ACP_OVERVIEW'        => 'نظرة عامة',
	'PPDE_ACP_PAYPAL_FEATURES' => 'مميزات إشعار الدفع الفوري',
	'PPDE_ACP_SETTINGS'        => 'إعدادات عامة',
	'PPDE_ACP_DONATION_PAGES'  => 'صفحات التبَرُّعات',
	'PPDE_ACP_CURRENCY'        => 'إدارة العُملات',
	'PPDE_ACP_TRANSACTIONS'    => 'سِجلّ المعاملات',
));

/**
 * logs
 */
$lang = array_merge($lang, array(
	'LOG_PPDE_DC_ACTIVATED'            => '<strong>“تبَرُّعات PayPal”: تم تفعيل العملة</strong><br>» %s',
	'LOG_PPDE_DC_ADDED'                => '<strong>“تبَرُّعات PayPal”: تم إضافة عملة جديدة</strong><br>» %s',
	'LOG_PPDE_DC_DEACTIVATED'          => '<strong>“تبَرُّعات PayPal”: تم تعطيل العملة</strong><br>» %s',
	'LOG_PPDE_DC_DELETED'              => '<strong>“تبَرُّعات PayPal”: تم حذف العملة</strong><br>» %s',
	'LOG_PPDE_DC_MOVE_DOWN'            => '<strong>“تبَرُّعات PayPal”: تم نقل العملة للأسفل</strong> “%s”',
	'LOG_PPDE_DC_MOVE_UP'              => '<strong>“تبَرُّعات PayPal”: تم نقل العملة للأعلى</strong> “%s”',
	'LOG_PPDE_DC_UPDATED'              => '<strong>“تبَرُّعات PayPal”: تم تعديل العملة</strong><br>» %s',
	'LOG_PPDE_DP_ADDED'                => '<strong>“تبَرُّعات PayPal”: تم إضافة صفحة تبَرُّع جديدة</strong><br>» “%1$s” باللغة “%2$s”', // eg: » “Donation success” for the language “British English”',
	'LOG_PPDE_DP_DELETED'              => '<strong>“تبَرُّعات PayPal”: تم حذف صفحة تبَرُّع</strong><br>» “%1$s” باللغة “%2$s”',
	'LOG_PPDE_DP_UPDATED'              => '<strong>“تبَرُّعات PayPal”: تم تحديث صفحة تبَرُّع</strong><br>» “%1$s” باللغة “%2$s”',
	'LOG_PPDE_DT_PURGED'               => '<strong>“تبَرُّعات PayPal”: تم إزالة سِجلّ المعاملات</strong>',
	'LOG_PPDE_DT_UPDATED'              => '<strong>“تبَرُّعات PayPal”: تم تحديث المعاملات</strong>',
	'LOG_PPDE_MT_ADDED'                => '<strong>“تبَرُّعات PayPal”: تمت إضافة معاملة يدوية</strong><br>» المُتَبَرِع: %s',
	'LOG_PPDE_PAYPAL_FEATURES_UPDATED' => '<strong>“تبَرُّعات PayPal”: تم تحديث إعدادات PayPal</strong>',
	'LOG_PPDE_SETTINGS_UPDATED'        => '<strong>“تبَرُّعات PayPal”: تم تحديث الإعدادات</strong>',
	'LOG_PPDE_STAT_RESET_DATE'         => '<strong>“تبَرُّعات PayPal”: إعادة ضبط تاريخ التنصيب</strong>',
	'LOG_PPDE_STAT_RESYNC'             => '<strong>“تبَرُّعات PayPal”: تم إعادة مزامنة الإحصائيات</strong>',
	'LOG_PPDE_STAT_RETEST_ESI'         => '<strong>“تبَرُّعات PayPal”: تم فحص المتطلبات الأساسية</strong>',
	'LOG_PPDE_STAT_SANDBOX_RESYNC'     => '<strong>“تبَرُّعات PayPal”: تم إعادة مزامنة احصائيات تقنية صندوق الرمل في PayPal</strong>',
));
