<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
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
$lang = array_merge($lang, [
	// Transactions log
	'PPDE_DT_CONFIG'         => 'Journal des transactions',
	'PPDE_DT_CONFIG_EXPLAIN' => 'Depuis cette page vous pouvez consulter le détail des transactions PayPal.',
	'PPDE_DT_IPN_STATUS'     => 'État de la transaction',
	'PPDE_DT_IPN_TEST'       => 'Test IPN',
	'PPDE_DT_PAYMENT_STATUS' => 'État du paiement',
	'PPDE_DT_TXN_ID'         => 'Numéro de transaction',
	'PPDE_DT_USERNAME'       => 'Nom du donateur',

	// Display transactions
	'PPDE_DT_APPROVE'                       => 'Approuver',
	'PPDE_DT_BOARD_USERNAME'                => 'Donateur',
	'PPDE_DT_CHANGE_BOARD_USERNAME'         => 'Modifier le donateur',
	'PPDE_DT_CHANGE_BOARD_USERNAME_EXPLAIN' => 'Permet de modifier le nom du donateur auquel ce don est associé.',
	'PPDE_DT_DETAILS'                       => 'Détails de la transaction',
	'PPDE_DT_DISAPPROVE'                    => 'Désapprouver',
	'PPDE_DT_EXCHANGE_RATE'                 => 'Taux de change',
	'PPDE_DT_EXCHANGE_RATE_EXPLAIN'         => 'Basé sur le taux de change effectif au %s.',
	'PPDE_DT_FEE_AMOUNT'                    => 'Montant de la commission',
	'PPDE_DT_ITEM_NAME'                     => 'Titre de l’objet',
	'PPDE_DT_ITEM_NUMBER'                   => 'Numéro de l’objet',
	'PPDE_DT_MEMO'                          => 'Message',
	'PPDE_DT_MEMO_EXPLAIN'                  => 'Message laissé par le donateur via le site PayPal.',
	'PPDE_DT_NAME'                          => 'Nom',
	'PPDE_DT_NET_AMOUNT'                    => 'Montant net',
	'PPDE_DT_PAYER_ID'                      => 'Identifiant de l’émetteur du paiement',
	'PPDE_DT_PAYER_EMAIL'                   => 'Courriel du payeur',
	'PPDE_DT_PAYER_STATUS'                  => 'État de l’émetteur du paiement',
	'PPDE_DT_PAYMENT_DATE'                  => 'Date du paiement',
	'PPDE_DT_RECEIVER_EMAIL'                => 'Paiement envoyé à',
	'PPDE_DT_RECEIVER_ID'                   => 'Ident. compte marchand',
	'PPDE_DT_SETTLE_AMOUNT'                 => 'Conversion en « %s »',
	'PPDE_DT_SORT_TXN_ID'                   => 'Numéro de transaction',
	'PPDE_DT_SORT_DONORS'                   => 'Donateur',
	'PPDE_DT_SORT_IPN_STATUS'               => 'État de la transaction',
	'PPDE_DT_SORT_IPN_TYPE'                 => 'Type de transaction',
	'PPDE_DT_SORT_PAYMENT_STATUS'           => 'État du paiement',
	'PPDE_DT_TOTAL_AMOUNT'                  => 'Montant total',
	'PPDE_DT_UNVERIFIED'                    => 'Non vérifié',
	'PPDE_DT_VERIFIED'                      => 'Vérifié',
	'PPDE_DT_UPDATED'                       => 'La transaction a été mise à jour.',

	'PPDE_MT_TITLE'                     => 'Transaction manuelle',
	'PPDE_MT_TITLE_EXPLAIN'             => 'Depuis cette page vous pouvez ajouter une transaction manuellement, par exemple si vous avez reçu un don par un moyen autre que PayPal.',
	'PPDE_MT_REQUIRED_CHARACTER'        => '*',
	'PPDE_MT_REQUIRED_EXPLAIN'          => 'Champs requis',
	'PPDE_MT_DETAILS'                   => 'Détails de la transaction',
	'PPDE_MT_USERNAME'                  => 'Donateur',
	'PPDE_MT_USERNAME_EXPLAIN'          => 'Sélectionnez le compte invité si le don a été effectué par un utilisateur non enregistré.',
	'PPDE_MT_FIRST_NAME'                => 'Prénom',
	'PPDE_MT_LAST_NAME'                 => 'Nom',
	'PPDE_MT_PAYER_EMAIL'               => 'Courriel',
	'PPDE_MT_RESIDENCE_COUNTRY'         => 'Pays',
	'PPDE_MT_RESIDENCE_COUNTRY_EXPLAIN' => 'Code ISO 3166 alpha-2, 2 caractères, consulter la <a href="https://www.phpbb.com/customise/db/extension/paypal_donation_extension/faq/2796" target="_blank" rel="noreferrer">FAQ</a>.',
	'PPDE_MT_TOTAL_AMOUNT'              => 'Montant total',
	'PPDE_DECIMAL_EXPLAIN'              => 'Utiliser le « . » comme symbole décimal.', // Note for translator: do not translate the decimal symbol
	'PPDE_MT_FEE_AMOUNT'                => 'Montant de la commission',
	'PPDE_MT_NET_AMOUNT'                => 'Montant net',
	'PPDE_MT_PAYMENT_DATE'              => 'Date du don',
	'PPDE_MT_PAYMENT_DATE_PICK'         => 'Choisir une date',
	'PPDE_MT_PAYMENT_TIME'              => 'Heure du don',
	'PPDE_MT_PAYMENT_TIME_EXPLAIN'      => 'Exemples de format horaires autorisés',
	'PPDE_MT_MEMO'                      => 'Message',
	'PPDE_MT_ADDED'                     => 'La transaction a été ajoutée.',

	// List of available translations: https://github.com/fengyuanchen/datepicker/tree/master/i18n
	'PPDE_MT_DATEPICKER_LANG'           => 'fr-FR',
]);

/**
 * mode: transactions
 * Info: This array is out of the previous because there is an issue with Transifex platform
 */
$lang = array_merge($lang, [
	/**
	 * TRANSLATORS PLEASE NOTE
	 * The line below has a special note.
	 * "## For translate:" followed by one "Don't" and one "Yes"
	 * "Don't" means do not change this column, and "Yes" means you can translate this column.
	 */

	## For translate:					Don't					Yes
	'PPDE_DT_PAYMENT_STATUS_VALUES' => [
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
	],
]);

/**
 * Confirm box
 */
$lang = array_merge($lang, [
	'PPDE_DT_CONFIRM_OPERATION' => 'Êtes-vous sûr de vouloir effectuer cette opération ?',
]);

/**
 * Errors
 */
$lang = array_merge($lang, [
	'PPDE_DT_IPN_APPROVED'         => 'Transaction approuvée manuellement',
	'PPDE_DT_IPN_APPROVED_EXPLAIN' => 'Cette donation a été approuvée manuellement avec les erreurs suivantes',
	'PPDE_DT_IPN_ERRORS'           => 'Vous devriez reconsidérer ce don car les erreurs suivantes ont été détectées',
	'PPDE_DT_NO_TRANSACTION'       => 'Aucune transaction n’a été trouvée.',

	'PPDE_MT_DONOR_NOT_FOUND'      => 'Le donateur demandé n’existe pas.',
	'PPDE_MT_MC_GROSS_TOO_LOW'     => 'Le montant total doit être supérieure à zéro.',
	'PPDE_MT_MC_FEE_NEGATIVE'      => 'Le montant de la commission ne peut être négatif.',
	'PPDE_MT_MC_FEE_TOO_HIGH'      => 'Le montant de la commission doit être inférieur au montant total.',
	'PPDE_MT_PAYMENT_DATE_ERROR'   => 'La date du don « %1$s » n’est pas valide.',
	'PPDE_MT_PAYMENT_TIME_ERROR'   => 'L’heure du don « %1$s » n’est pas valide.',
	'PPDE_MT_PAYMENT_DATE_FUTURE'  => 'La date du don doit être une date échue, mais vous avez renseigné « %1$s ».',
]);
