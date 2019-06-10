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
 * mode: transactions
 */
$lang = array_merge($lang, array(
	// Transactions log
	'PPDE_DT_CONFIG'                => 'سِجلّ العمليات',
	'PPDE_DT_CONFIG_EXPLAIN'        => 'من هنا تستطيع مشاهدة تفاصيل العمليات.',
	'PPDE_DT_IPN_STATUS'            => 'حالة إشعار الدفع الفوري',
	'PPDE_DT_IPN_TEST'              => 'اختبار إشعار الدفع الفوري',
	'PPDE_DT_PAYMENT_STATUS'        => 'حالة الدقع',
	'PPDE_DT_TXN_ID'                => 'رقم العملية',
	'PPDE_DT_USERNAME'              => 'إسم المُتَبَرِع',

	// Display transactions
	'PPDE_DT_APPROVE'                       => 'موافقة',
	'PPDE_DT_BOARD_USERNAME'        		=> 'المُتَبَرِع',
	'PPDE_DT_CHANGE_BOARD_USERNAME'         => 'تغيير المُتَبَرِع',
	'PPDE_DT_CHANGE_BOARD_USERNAME_EXPLAIN' => 'يعمل هذا على تغيير حساب المستخدم الذي يرتبط به هذا التبَرُّع.',
	'PPDE_DT_DETAILS'               		=> 'تفاصيل العملية',
	'PPDE_DT_DISAPPROVE'                    => 'رفض',
	'PPDE_DT_EXCHANGE_RATE'         		=> 'سعر الصرف',
	'PPDE_DT_EXCHANGE_RATE_EXPLAIN' 		=> 'يعتمد على سعر الصرف الساري في %s.',
	'PPDE_DT_FEE_AMOUNT'            		=> 'مبلغ الرسوم',
	'PPDE_DT_ITEM_NAME'             		=> 'إسم العنصر',
	'PPDE_DT_ITEM_NUMBER'           		=> 'رقم العنصر',
	'PPDE_DT_MEMO'                  		=> 'مذكرة',
	'PPDE_DT_MEMO_EXPLAIN'          		=> 'يتم ادخال المذكرة بواسطة المُتَبَرِع من خلال موقع PayPal.',
	'PPDE_DT_NAME'                  		=> 'الإسم',
	'PPDE_DT_NET_AMOUNT'            		=> 'المبلغ الصافي',
	'PPDE_DT_PAYER_ID'              		=> 'رقم المُتَبَرِع',
	'PPDE_DT_PAYER_EMAIL'           		=> 'البريد الإلكتروني للمُتَبَرِع',
	'PPDE_DT_PAYER_STATUS'          		=> 'حالة المُتَبَرِع',
	'PPDE_DT_PAYMENT_DATE'          		=> 'تاريخ التبَرُّع',
	'PPDE_DT_RECEIVER_EMAIL'        		=> 'تم ارسال قيمة التبَرُّع إلى',
	'PPDE_DT_RECEIVER_ID'           		=> 'رقم المستلم',
	'PPDE_DT_SETTLE_AMOUNT'         		=> 'تحويل إلى “%s”',
	'PPDE_DT_SORT_TXN_ID'           		=> 'رقم العملية',
	'PPDE_DT_SORT_DONORS'           		=> 'المُتَبَرِعين',
	'PPDE_DT_SORT_IPN_STATUS'       		=> 'حالة إشعار الدفع الفوري',
	'PPDE_DT_SORT_IPN_TYPE'         		=> 'نوع العملية',
	'PPDE_DT_SORT_PAYMENT_STATUS'   		=> 'حالة الدفع',
	'PPDE_DT_TOTAL_AMOUNT'          		=> 'إجمالي المبلغ',
	'PPDE_DT_UNVERIFIED'            		=> 'لم يتم التحقق',
	'PPDE_DT_VERIFIED'              		=> 'تم التحقق',
	'PPDE_DT_UPDATED'                       => 'تم تحديث عملية.',

	'PPDE_MT_TITLE'                     => 'عملية يدوية',
	'PPDE_MT_TITLE_EXPLAIN'             => 'من هنا تستطيع إضافة العملية يدوياً, على سبيل المثال إذا تلقيت تبَرُّع عبر وسيلة أخرى غير الـ PayPal.',
	'PPDE_MT_REQUIRED_CHARACTER'        => '*',
	'PPDE_MT_REQUIRED_EXPLAIN'          => 'حقل مطلوب',
	'PPDE_MT_DETAILS'                   => 'تفاصيل العملية',
	'PPDE_MT_USERNAME'                  => 'المُتَبَرِع',
	'PPDE_MT_USERNAME_EXPLAIN'          => 'اختار مستخدم مجهول إذا تم التبَرُّع بواسطة مجهول.',
	'PPDE_MT_FIRST_NAME'                => 'الإسم الأول',
	'PPDE_MT_LAST_NAME'                 => 'الإسم الأخير',
	'PPDE_MT_PAYER_EMAIL'               => 'البريد الإلكتروني',
	'PPDE_MT_RESIDENCE_COUNTRY'         => 'البلد',
	'PPDE_MT_RESIDENCE_COUNTRY_EXPLAIN' => 'الرمز ISO 3166 alpha-2, 2 حروف, شاهد <a href="https://www.phpbb.com/customise/db/extension/paypal_donation_extension/faq/2796" target="_blank" rel="noreferrer">الأسئلة المُتكررة</a>.',
	'PPDE_MT_TOTAL_AMOUNT'              => 'المبلغ الإجمالي',
	'PPDE_DECIMAL_EXPLAIN'              => 'استخدم “.” كرمز عشري.', // Note for translator: do not translate the decimal symbol
	'PPDE_MT_FEE_AMOUNT'                => 'مبلغ الرسوم',
	'PPDE_MT_NET_AMOUNT'                => 'المبلغ الصافي',
	'PPDE_MT_PAYMENT_DATE'              => 'تاريخ التبَرُّع',
	'PPDE_MT_PAYMENT_DATE_PICK'         => 'اختار تاريخ',
	'PPDE_MT_PAYMENT_TIME'              => 'وقت التبَرُّع',
	'PPDE_MT_PAYMENT_TIME_EXPLAIN'      => 'أمثلة على تنسيقات الوقت المسموح بها',
	'PPDE_MT_MEMO'                      => 'ملاحظة',
	'PPDE_MT_ADDED'                     => 'تمت إضافة العملية بنجاح.',

	// List of available translations: https://github.com/fengyuanchen/datepicker/tree/master/i18n
	'PPDE_MT_DATEPICKER_LANG'           => 'en-GB',
));

/**
 * mode: transactions
 * Info: This array is out of the previous because there is an issue with Transifex platform
 */
$lang = array_merge($lang, array(
	/**
	 * TRANSLATORS PLEASE NOTE
	 * The line below has a special note.
	 * "## For translate:" followed by one "Don't" and one "Yes"
	 * "Don't" means do not change this column, and "Yes" means you can translate this column.
	 */

	## For translate:					Don't					Yes
	'PPDE_DT_PAYMENT_STATUS_VALUES' => array(
										'canceled_reversal' => 'تم إلغاء عكس المبلغ',
										'completed'         => 'مكتمل',
										'created'           => 'تم الإنشاء',
										'denied'            => 'مرفوض',
										'expired'           => 'منتهي الصلاحية',
										'failed'            => 'فشل',
										'pending'           => 'مُعَلَّق',
										'refunded'          => 'تم إعادة المبلغ',
										'reversed'          => 'تم عكس المبلغ',
										'processed'         => 'تم المعالجة',
										'voided'            => 'ملغي',
	),
));

/**
 * Errors
 */
$lang = array_merge($lang, array(
	'PPDE_DT_IPN_APPROVED'         => 'تمت الموافقة على العملية يدوياً',
	'PPDE_DT_IPN_APPROVED_EXPLAIN' => 'تمت الموافقة على هذا التبَرُّع يدوياً مع وجود الأخطاء التالية',
	'PPDE_DT_IPN_ERRORS'     => 'يجب مراجعة عملية التبَرُّع هذه بسبب اكتشاف الأخطاء التالية',
	'PPDE_DT_NO_TRANSACTION' => 'لم يتم العثور على أي عملية.',

	'PPDE_MT_DONOR_NOT_FOUND'      => 'لم يتم العثور على المستخدم المُتَبَرِع “%1$s”.',
	'PPDE_MT_MC_GROSS_TOO_LOW'     => 'المبلغ الإجمالي يجب أن يكون أكثر من القيمة صفر.',
	'PPDE_MT_MC_FEE_NEGATIVE'      => 'مبلغ الرسوم يجب الا تكون بالسالب.',
	'PPDE_MT_MC_FEE_TOO_HIGH'      => 'مبلغ الرسوم يجب أن يكون اقل من المبلغ الإجمالي.',
	'PPDE_MT_PAYMENT_DATE_ERROR'   => 'لا يمكن تحليل تاريخ التبَرُّع “%1$s”.',
	'PPDE_MT_PAYMENT_TIME_ERROR'   => 'لا يمكن تحليل وقت التبَرُّع “%1$s”.',
	'PPDE_MT_PAYMENT_DATE_FUTURE'  => 'تاريخ التبَرُّع يجب أن يكون في الماضي, لكنه كان في “%1$s”.',
));
