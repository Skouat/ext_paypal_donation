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

$lang = array_merge($lang, array(
	// Header
	'PPDE_HEADER_LINK_TITLE'           => 'التبَرُّعات',
	'PPDE_HEADER_DONORLIST_LINK_TITLE' => 'المُتَبَرِعين',

	// Index page
	'PPDE_INDEX_STATISTICS_TITLE'      => 'احصائيات التبَرُّعات',

	// Pages
	'PPDE_DONATION_BUTTON_TITLE'       => 'تبَرَّع',
	'PPDE_DONATION_TITLE'              => 'أرسل تبَرُّع',
	'PPDE_DONATION_TITLE_HEAD'         => 'أرسل تبَرُّع إلى %s',
	'PPDE_CANCEL_TITLE'                => 'تم إلغاء عملية التبَرُّع',
	'PPDE_SUCCESS_TITLE'               => 'تم التبَرُّع بنجاح',
	'PPDE_CONTACT_PAYPAL'              => 'جاري الإتصال بـ PayPal. نرجوا الإنتظار…',
	'PPDE_SANDBOX_TITLE'               => 'تجربة التبَرُّع بواسطة PayPal مع خدمة صندوق الرمل Sandbox',

	// Donors list
	'PPDE_DONORLIST_TITLE'             => 'قائمة المُتَبَرِعين',
	'PPDE_DONORLIST_LAST_DONATION'     => 'آخر تبَرُّع',
	'PPDE_DONORLIST_LAST_DATE'         => 'تاريخ التبَرُّع',
	'PPDE_DONORLIST_TOTAL_DONATION'    => 'مبلغ التبَرُّع',

	'PPDE_NO_DONORS'            => 'لا يوجد مُتَبَرِعين',

	// Statistics
	'PPDE_DONATE_GOAL_RAISE'    => 'هدفنا أن نحصل على مبلغ <strong>%s</strong>.',
	'PPDE_DONATE_GOAL_REACHED'  => 'تم الحصول إلى المبلغ المطلوب لهدف التبَرُّع.',
	'PPDE_DONATE_NO_GOAL'       => 'لم يتم تحديد المبلغ المطلوب لهدف التبَرُّع.',
	'PPDE_DONATE_NOT_RECEIVED'  => 'لم نستلم أي تبَرُّعات.',
	'PPDE_DONATE_NOT_USED'      => 'لم نستخدم مبلغ التبَرُّعات.',
	'PPDE_DONATE_RECEIVED'      => 'لقد حصلنا على <strong>%s</strong> تبَرُّعات.',
	'PPDE_DONATE_USED'          => 'لقد استخدمنا <strong>%1$s</strong> من إجمالي <strong>%2$s</strong> تبَرُّعات مُستلمة.',
	'PPDE_DONATE_USED_EXCEEDED' => 'لقد استخدمنا <strong>%s</strong>. تم استخدام مبلغ التبَرُّعات كاملاً.',

	// Viewonline
	'PPDE_VIEWONLINE'           => 'يشاهد صفحة التبَرُّع',
	'PPDE_VIEWONLINE_DONORLIST' => 'يشاهد قائمة المُتَبَرِعين',
));

/**
 * Note: This array is out of the previous because there is an issue with Transifex platform
 * Note for translators: Before pushing your translation on Transifex, please surround array indexes with ''.
 */
$lang = array_merge($lang, array(
	'PPDE_DONORS' => array(
		1 => '%d مُتَبَرِع',  // 1
		2 => '%d مُتَبَرِعين', // 2+
	),
));

$lang = array_merge($lang, array(
	// Error
	'CURL_ERROR'                => 'هناك خطأ في cURL: %s',
	'INVALID_TXN'               => 'عملية تحويل غير صالحة:',
	'INVALID_TXN_ACCOUNT_ID'    => 'رقم التعريف ID لحسابك غير متطابق.',
	'INVALID_TXN_ASCII'         => 'تم اكتشاف حروف غير ASCII في “%s”.',
	'INVALID_TXN_CONTENT'       => 'محتوى غير متوقع لـ “%s”.',
	'INVALID_TXN_EMPTY'         => 'قيمة فارغة لـ “%s”.',
	'INVALID_TXN_INVALID_CHECK' => 'بيانات نشر غير معروفة.',
	'INVALID_TXN_LENGTH'        => 'العدد المتوقع للحروف في “%s” غير متطابق.',
	'INVALID_RESPONSE_STATUS'   => 'حالة استجابة غير صالحة: ',
	'NO_CONNECTION_DETECTED'    => 'لم يتم الكشف عن cURL. نرجوا الاتصال بمسؤول خادم الشبكة الخاص بك.',
	'REQUIREMENT_NOT_SATISFIED' => 'لم يتم الكشف عن cURL, TLS 1.2 أو HTTP1/1. نرجوا الاتصال بمسؤول خادم الشبكة الخاص بك.',
	'UNEXPECTED_RESPONSE'       => 'استجابة غير متوقعة من PayPal.',
));

$lang = array_merge($lang, array(
	// Notification
	'NOTIFICATION_PPDE_ADMIN_DONATION_ERRORS'   => 'تبَرُّع %1$s يتطلب انتباهكم.',
	'NOTIFICATION_PPDE_ADMIN_DONATION_RECEIVED' => '%1$s تبَرَّع بمبلغ “%2$s”.',
	'NOTIFICATION_PPDE_DONOR_DONATION_RECEIVED' => 'لقد تم استلام مبلغ التبَرُّع “%1$s” الذي أرسلته.',
));
