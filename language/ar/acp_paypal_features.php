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
 * mode: PayPal features
 */
$lang = array_merge($lang, array(
	'PPDE_PAYPAL_FEATURES'                 => 'مميزات إشعار الدفع الفوري في PayPal',
	'PPDE_PAYPAL_FEATURES_EXPLAIN'         => 'من هنا تستطيع ضبط جميع المميزات التي تستخدم إشعار الدفع الفوري (IPN) في PayPal.',

	// PayPal IPN settings
	'PPDE_LEGEND_IPN_AUTOGROUP'            => 'المجموعة التلقائية',
	'PPDE_LEGEND_IPN_DONORLIST'            => 'قائمة المُتَبَرِعين',
	'PPDE_LEGEND_IPN_NOTIFICATION'         => 'نظام الإشعارات',
	'PPDE_LEGEND_IPN_SETTINGS'             => 'الإعدادات العامة',
	'PPDE_IPN_AG_ENABLE'                   => 'تفعيل',
	'PPDE_IPN_AG_ENABLE_EXPLAIN'           => 'السماح بإضافة المُتَبَرِعين تلقائياً إلى مجموعة مُحددة مُسبقاً.',
	'PPDE_IPN_AG_DONORS_GROUP'             => 'مجموعة المُتَبَرِعين',
	'PPDE_IPN_AG_DONORS_GROUP_EXPLAIN'     => 'تحديد المجموعة التي سيتم إضافة المُتَبَرِعين إليها.',
	'PPDE_IPN_AG_GROUP_AS_DEFAULT'         => 'ضبط مجموعة المُتَبَرِعين إلى الإفتراضية',
	'PPDE_IPN_AG_GROUP_AS_DEFAULT_EXPLAIN' => 'ستكون مجموعة المُتَبَرِعين هي المجموعة الإفتراضية للعضو عند اختيارك “نعم”.',
	'PPDE_IPN_AG_MIN_BEFORE_GROUP'         => 'المبلغ الأدنى لمجموعة المُتَبَرِعين',
	'PPDE_IPN_AG_MIN_BEFORE_GROUP_EXPLAIN' => 'إجمالي التبَرُّعات التي يجب على العضو أن يدفعها من أجل إضافته لمجموعة المُتَبَرِعين.',
	'PPDE_IPN_DL_ENABLE'                   => 'تفعيل',
	'PPDE_IPN_DL_ENABLE_EXPLAIN'           => 'السماح بتفعيل قائمة المُتَبَرِعين.',
	'PPDE_IPN_ENABLE'                      => 'تفعيل IPN',
	'PPDE_IPN_ENABLE_EXPLAIN'              => 'تفعيل هذا الخيار من أجل استخدام خدمة إشعار الدفع الفوري في PayPal.',
	'PPDE_IPN_LOGGING'                     => 'تفعيل سِجلّ الأخطاء',
	'PPDE_IPN_LOGGING_EXPLAIN'             => 'كتابة الأخطاء والبيانات من خدمة إشعار الدفع الفوري في PayPal إلى مسار الملف <strong>/store/ext/ppde/</strong>.',
	'PPDE_IPN_NOTIFICATION_ENABLE'         => 'تفعيل',
	'PPDE_IPN_NOTIFICATION_ENABLE_EXPLAIN' => 'السماح بإشعار مدير الموقع و المُتَبَرِع عند إستلام التبَرُّعات.',

	// PayPal sandbox settings
	'PPDE_LEGEND_SANDBOX_SETTINGS'         => 'تقنية صندوق الرمل Sandbox',
	'PPDE_SANDBOX_ENABLE'                  => 'تفعيل الاختبار',
	'PPDE_SANDBOX_ENABLE_EXPLAIN'          => 'استخدم تقنية صندوق الرمل في PayPal بدلاً من خدمات PayPal.<br>هذه التقنية مفيدة للمطورين ولمن يريد التجربة والاختبار. جميع عمليات التحويل وهمية - غير حقيقية.',
	'PPDE_SANDBOX_FOUNDER_ENABLE'          => 'تخصيص التقنية للمؤسسين فقط',
	'PPDE_SANDBOX_FOUNDER_ENABLE_EXPLAIN'  => 'تقنية صندوق الرمل ستظهر لمؤسسين المنتدى فقط.',
	'PPDE_SANDBOX_ADDRESS'                 => 'حساب تقنية صندوق الرمل',
	'PPDE_SANDBOX_ADDRESS_EXPLAIN'         => 'أدخل عنوان البريد الإلكتروني أو رقم التعريف ID الخاص بحساب تقنية صندوق الرمل في PayPal.',
	'PPDE_SANDBOX_REMOTE'                  => 'عنوان الرابط لتقنية صندوق الرمل في PayPal',
	'PPDE_SANDBOX_REMOTE_EXPLAIN'          => 'يجب عليك عدم تغيير هذا الإعداد, إلا إذا واجهت هذه الإضافة أخطاء في الاتصال بالخادم الخاص بتقنية صندوق الرمل Sandbox.',
));

/**
 * Confirm box
 */
$lang = array_merge($lang, array(
	'PPDE_PAYPAL_FEATURES_SAVED' => 'تم حفظ الإعدادات بنجاح.',
));

/**
 * Errors
 */
$lang = array_merge($lang, array(
	'PPDE_PAYPAL_FEATURES_MISSING'        => 'نرجوا التحقق من “حساب تقنية صندوق الرمل”.',
	'PPDE_PAYPAL_FEATURES_NOT_ENABLEABLE' => 'لا يمكن تفعيل إشعار الدفع الفوري لـ PayPal. نرجوا التحقق من متطلبات النظام في قسم “نظرة عامة”.',
));
