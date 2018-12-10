<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017 Skouat
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
// ’ « » “ ” …
//

/**
 * mode: transactions
 */
$lang = array_merge($lang, array(
	// Transactions log
	'PPDE_DT_CONFIG'                => 'Journal des transactions',
	'PPDE_DT_CONFIG_EXPLAIN'        => 'Depuis cette page vous pouvez consulter le détail des transactions PayPal.',
	'PPDE_DT_IPN_STATUS'            => 'État de la transaction',
	'PPDE_DT_IPN_TEST'              => 'Test IPN',
	'PPDE_DT_PAYMENT_STATUS'        => 'État du paiement',
	'PPDE_DT_TXN_ID'                => 'Numéro de transaction',
	'PPDE_DT_USERNAME'              => 'Nom du donateur',

	// Display transactions
	'PPDE_DT_APPROVE'               => 'Approuver',
	'PPDE_DT_BOARD_USERNAME'        => 'Donateur',
	'PPDE_DT_DETAILS'               => 'Détails de la transaction',
	'PPDE_DT_DISAPPROVE'            => 'Désapprouver',
	'PPDE_DT_EXCHANGE_RATE'         => 'Taux de change',
	'PPDE_DT_EXCHANGE_RATE_EXPLAIN' => 'Basé sur le taux de change effectif au %s.',
	'PPDE_DT_FEE_AMOUNT'            => 'Montant de la commission',
	'PPDE_DT_ITEM_NAME'             => 'Titre de l’objet',
	'PPDE_DT_ITEM_NUMBER'           => 'Numéro d’objet',
	'PPDE_DT_MEMO'                  => 'Message',
	'PPDE_DT_MEMO_EXPLAIN'          => 'Message laissé par le donateur via le site PayPal.',
	'PPDE_DT_NAME'                  => 'Nom',
	'PPDE_DT_NET_AMOUNT'            => 'Montant net',
	'PPDE_DT_PAYER_ID'              => 'Identifiant de l’émetteur du paiement',
	'PPDE_DT_PAYER_EMAIL'           => 'E-mail',
	'PPDE_DT_PAYER_STATUS'          => 'État de l’émetteur du paiement',
	'PPDE_DT_PAYMENT_DATE'          => 'Date du paiement',
	'PPDE_DT_RECEIVER_EMAIL'        => 'Paiement envoyé à',
	'PPDE_DT_RECEIVER_ID'           => 'Ident. compte marchand',
	'PPDE_DT_SETTLE_AMOUNT'         => 'Conversion en « %s »',
	'PPDE_DT_SORT_TXN_ID'           => 'Numéro transaction',
	'PPDE_DT_SORT_DONORS'           => 'Donateur',
	'PPDE_DT_SORT_IPN_STATUS'       => 'État de la transaction',
	'PPDE_DT_SORT_IPN_TYPE'         => 'Type de transaction',
	'PPDE_DT_SORT_PAYMENT_STATUS'   => 'État du paiement',
	'PPDE_DT_TOTAL_AMOUNT'          => 'Montant total',
	'PPDE_DT_UNVERIFIED'            => 'Non vérifié',
	'PPDE_DT_VERIFIED'              => 'Vérifié',

	'PPDE_MT_TITLE'                     => 'Manual Transaction',
	'PPDE_MT_TITLE_EXPLAIN'             => 'Here you can add a transaction manually, for example if you received a donation by means other than PayPal.',
	'PPDE_MT_REQUIRED_CHARACTER'        => '*',
	'PPDE_MT_REQUIRED_EXPLAIN'          => 'Required field',
	'PPDE_MT_DETAILS'                   => 'Transaction details',
	'PPDE_MT_USERNAME'                  => 'Donor',
	'PPDE_MT_USERNAME_EXPLAIN'		    => 'Select the anonymous user if the donation was made by a guest.',
	'PPDE_MT_FIRST_NAME'                => 'First name',
	'PPDE_MT_LAST_NAME'                 => 'Last name',
	'PPDE_MT_PAYER_EMAIL'               => 'Email',
	'PPDE_MT_RESIDENCE_COUNTRY'	        => 'Country',
	'PPDE_MT_RESIDENCE_COUNTRY_EXPLAIN' => 'ISO 3166 alpha-2 code, 2 characters, see <a href="https://www.phpbb.com/customise/db/extension/paypal_donation_extension/faq/2796" target="_blank" rel="noreferrer">FAQ.</a>',
	'PPDE_MT_TOTAL_AMOUNT'              => 'Total amount',
	'PPDE_DECIMAL_EXPLAIN'              => 'Utiliser le « . » comme symbole décimal.', // Note for translator: do not translate the decimal symbol
	'PPDE_MT_FEE_AMOUNT'                => 'Fee amount',
	'PPDE_MT_NET_AMOUNT'                => 'Net amount',
	'PPDE_MT_PAYMENT_DATE'              => 'Donation date',
	'PPDE_MT_PAYMENT_DATE_PICK'		    => 'Pick a date',
	'PPDE_MT_PAYMENT_TIME'			    => 'Donation time',
	'PPDE_MT_PAYMENT_TIME_EXPLAIN'      => 'Examples of allowed time formats',
	'PPDE_MT_MEMO'                      => 'Memo',
	'PPDE_MT_ADDED'                     => 'The transaction has been added successfully.',

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
										'canceled_reversal' => 'Annulation invalidée',
										'completed'         => 'Effectué',
										'created'           => 'Créé',
										'denied'            => 'Rejeté',
										'expired'           => 'Expiré',
										'failed'            => 'Échoué',
										'pending'           => 'En attente',
										'refunded'          => 'Remboursé',
										'reversed'          => 'Annulé',
										'processed'         => 'Accepté',
										'voided'            => 'Annulé',
	),
));

/**
 * Errors
 */
$lang = array_merge($lang, array(
	'PPDE_DT_IPN_APPROVED'         => 'Transaction approuvée manuellement',
	'PPDE_DT_IPN_APPROVED_EXPLAIN' => 'Cette donation a été approuvée manuellement avec les erreurs suivantes',
	'PPDE_DT_IPN_ERRORS'           => 'Vous devriez reconsidérer ce don car les erreurs suivantes ont été détectées',
	'PPDE_DT_NO_TRANSACTION'       => 'Aucune transaction n’a été trouvée.',

	'PPDE_MT_DONOR_NOT_FOUND'      => 'The donor user “%1$s” was not found.',
	'PPDE_MT_PAYMENT_DATE_ERROR'   => 'The donation date “%1$s” could not be parsed.',
	'PPDE_MT_PAYMENT_TIME_ERROR'   => 'The donation time “%1$s” could not be parsed.',
));
