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
// ’ « » “ ” …
//

/**
 * mode: settings
 */
$lang = array_merge($lang, array(
	'PPDE_SETTINGS'                   => 'الإعدادات العامة',
	'PPDE_SETTINGS_EXPLAIN'           => 'من هنا تستطيع ضبط الإعدادات الرئيسية لتبَرُّعات PayPal.',

	// General settings
	'PPDE_ACCOUNT_ID'                 => 'حساب PayPal',
	'PPDE_ACCOUNT_ID_EXPLAIN'         => 'أدخل رقم التعريف ID الخاص بحسابك أو عنوان البريد الإلكتروني في PayPal.',
	'PPDE_DEFAULT_CURRENCY'           => 'العُملة الإفتراضية',
	'PPDE_DEFAULT_CURRENCY_EXPLAIN'   => 'حدد العُملة التي ستظهر في القائمة أولاً.',
	'PPDE_DEFAULT_VALUE'              => 'القيمة الإفتراضية',
	'PPDE_DEFAULT_VALUE_EXPLAIN'      => 'حدد قيمة التبَرُّع التي سيتم اقتراحها افتراضياً للمُتَبَرِعين.',
	'PPDE_DROPBOX_ENABLE'             => 'تفعيل القائمة المنسدلة',
	'PPDE_DROPBOX_ENABLE_EXPLAIN'     => 'سيتم إظهار قائمة منسدلة تحتوي على المبالغ التي يمكن التبَرُّع بها بدلاً من حقل النص.',
	'PPDE_DROPBOX_VALUE'              => 'مبالغ القائمة المنسدلة',
	'PPDE_DROPBOX_VALUE_EXPLAIN'      => 'حدد  المبالغ التي يمكن التبَرُّع بها في القائمة المنسدلة.<br>استخدم <strong>علامة الفاصِلَة</strong> (“,”) <strong>بدون مسافة</strong> بين أرقام المبالغ.',
	'PPDE_ENABLE'                     => 'تفعيل',
	'PPDE_ENABLE_EXPLAIN'             => 'تفعيل أو تعطيل الإضافة “تبَرُّعات PayPal”.',
	'PPDE_HEADER_LINK'                => 'إظهار رابط “التبَرُّعات” في الشريط العلوي',
	'PPDE_LEGEND_GENERAL_SETTINGS'    => 'إعدادات عامة',

	// Advanced settings
	'PPDE_LEGEND_ADVANCED_SETTINGS'   => 'إعدادات متقدمة',
	'PPDE_DEFAULT_REMOTE'             => 'الرابط في PayPal',
	'PPDE_DEFAULT_REMOTE_EXPLAIN'     => 'يجب عليك عدم تغيير هذا الإعداد, إلا إذا واجهت هذه الإضافة أخطاء في الاتصال بالخادم البعيد.',

	// Stats Donation settings
	'PPDE_AMOUNT'                     => 'المبلغ',
	'PPDE_DECIMAL_EXPLAIN'            => 'استخدم “.” كرمز عشري.', // Note for translator: do not translate the decimal symbol
	'PPDE_GOAL'                       => 'الهدف',
	'PPDE_GOAL_EXPLAIN'               => 'إجمالي المبلغ الذي تريد تحقيقه أو تجميعه.',
	'PPDE_LEGEND_STATS_SETTINGS'      => 'إعدادات الإحصائيات',
	'PPDE_RAISED'                     => 'المحقق',
	'PPDE_RAISED_EXPLAIN'             => 'المبلغ الحالي الذي تم جمعه من خلال التبَرُّعات.',
	'PPDE_STATS_INDEX_ENABLE'         => 'عرض الإحصائيات في الصفحة الرئيسية',
	'PPDE_STATS_INDEX_ENABLE_EXPLAIN' => 'اختار “نعم” إذا تريد إظهار احصائيات التبَرُّعات في الصفحة الرئيسية.',
	'PPDE_USED'                       => 'المستخدم',
	'PPDE_USED_EXPLAIN'               => 'المبلغ الذي استخدمته بالفعل من قيمة التبَرُّعات.',
));

/**
 * Confirm box
 */
$lang = array_merge($lang, array(
	'PPDE_SETTINGS_SAVED' => 'تم حفظ الإعدادات بنجاح.',
));

/**
 * Errors
 */
$lang = array_merge($lang, array(
	'PPDE_SETTINGS_MISSING' => 'نرجوا تعبئة الخيار “حساب PayPal”.',
));
