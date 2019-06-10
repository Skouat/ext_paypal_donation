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
 * mode: overview
 */
$lang = array_merge($lang, array(
	'PPDE_OVERVIEW' => 'نظرة عامة',

	'PPDE_ESI'                  => 'معلومات الإضافة والنظام',
	'PPDE_ESI_DETECTED'         => 'موجود',
	'PPDE_ESI_INSTALL_DATE'     => 'تاريخ تنصيب الإضافة <strong>تبَرُّعات PayPal</strong>',
	'PPDE_ESI_MORE_INFORMATION' => 'مزيد من المعلومات…',
	'PPDE_ESI_NOT_DETECTED'     => 'غير موجود',
	'PPDE_ESI_RESYNC_OPTIONS'   => 'إعادة ضبط أو إعادة التحقق من معلومات الإضافة والنظام',
	'PPDE_ESI_TLS'              => 'TLS 1.2 و HTTP/1.1',
	'PPDE_ESI_VERSION'          => 'نسخة الإضافة <strong>تبَرُّعات PayPal</strong>',
	'PPDE_ESI_VERSION_CURL'     => 'نسخة cURL',
	'PPDE_ESI_VERSION_SSL'      => 'نسخة SSL',

	'PPDE_STATS'         => 'إحصائيات التبَرُّعات',
	'PPDE_STATS_SANDBOX' => 'إحصائيات تقنية صندوق الرمل',

	'STAT_RESET_DATE'                   => 'إعادة ضبط تاريخ تنصيب الإضافة',
	'STAT_RESET_DATE_CONFIRM'           => 'متأكد أنك تريد إعادة ضبط تاريخ تنصيب هذه الإضافة؟',
	'STAT_RESET_DATE_EXPLAIN'           => 'إعادة ضبط تاريخ التنصيب ستؤثر في حساب إجمالي مبلغ التبَرَّعات وبعض الإحصائيات الأخرى.',
	'STAT_RESYNC_OPTIONS'               => 'إعادة مزامنة الإحصائيات',
	'STAT_RESYNC_SANDBOX_STATS'         => 'إعادة مزامنة إحصائيات تقنية صندوق الرمل',
	'STAT_RESYNC_SANDBOX_STATS_CONFIRM' => 'متأكد أنك تريد إعادة مزامنة إحصائيات تقنية صندوق الرمل Sandbox؟',
	'STAT_RESYNC_SANDBOX_STATS_EXPLAIN' => 'إعادة المزامنة لعدد جميع المُتَبَرِعين والعمليات المالية لتقنية صندوق الرمل في PayPal.',
	'STAT_RESYNC_STATS'                 => 'إعادة المزامنة لعدد المُتَبَرِعين والعمليات المالية',
	'STAT_RESYNC_STATS_CONFIRM'         => 'متأكد أنك تريد إعادة المزامنة لعدد المُتَبَرِعين والعمليات المالية؟',
	'STAT_RESYNC_STATS_EXPLAIN'         => 'إعادة المزامنة لعدد جميع المُتَبَرِعين والعمليات المالية. المُتَبَرِعين المجهولين والأعضاء النشطين فقط سيتم أخذهم في الاعتبار.',
	'STAT_RETEST_ESI'                   => 'التحقق من المتطلبات الأساسية للإضافة',
	'STAT_RETEST_ESI_CONFIRM'           => 'متأكد أنك تريد التحقق من المتطلبات الأساسية للإضافة؟',
	'STAT_RETEST_ESI_EXPLAIN'           => 'السماح بالتحقق من المتطلبات الأساسية للإضافة, في حالة تغيير الإعدادات في خادم الويب لديك.',

	'STATS_ANONYMOUS_DONORS_COUNT'   => 'عدد المُتَبَرِعين المجهولين',
	'STATS_ANONYMOUS_DONORS_PER_DAY' => 'المُتَبَرِعين المجهولين كل يوم',
	'STATS_KNOWN_DONORS_COUNT'       => 'عدد المُتَبَرِعين المعروفين',
	'STATS_KNOWN_DONORS_PER_DAY'     => 'المُتَبَرِعين المعروفين كل يوم',
	'STATS_TRANSACTIONS_COUNT'       => 'عدد العمليات',
	'STATS_TRANSACTIONS_PER_DAY'     => 'العمليات كل يوم',
));
