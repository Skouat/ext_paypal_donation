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

$lang = array_merge($lang, [
	// Header
	'PPDE_HEADER_LINK_TITLE'           => 'Faire un don',
	'PPDE_HEADER_DONORLIST_LINK_TITLE' => 'Donateurs',

	// Index page
	'PPDE_INDEX_STATISTICS_TITLE'      => 'Statistiques des dons',

	// Pages
	'PPDE_DONATION_BUTTON_TITLE'       => 'Faire un don',
	'PPDE_DONATION_TITLE'              => 'Faire un don',
	'PPDE_DONATION_TITLE_HEAD'         => 'Faire un don pour %s',
	'PPDE_CANCEL_TITLE'                => 'Dons annulés',
	'PPDE_SUCCESS_TITLE'               => 'Dons validés',
	'PPDE_CONTACT_PAYPAL'              => 'Connexion à PayPal. Veuillez patienter…',
	'PPDE_SANDBOX_TITLE'               => 'Tester PayPal Donation avec PayPal Sandbox',

	// Donors list
	'PPDE_DONORLIST_TITLE'             => 'Liste des donateurs',
	'PPDE_DONORLIST_LAST_DONATION'     => 'Dernier don',
	'PPDE_DONORLIST_LAST_DATE'         => 'Effectué le',
	'PPDE_DONORLIST_TOTAL_DONATION'    => 'Somme des dons',

	'PPDE_NO_DONORS'            => 'Aucun donateur',

	// Statistics
	'PPDE_DONATE_GOAL_RAISE'    => 'Notre objectif est d’obtenir <strong>%s</strong>.',
	'PPDE_DONATE_GOAL_REACHED'  => 'L’objectif de don a été atteint.',
	'PPDE_DONATE_NO_GOAL'       => 'Nous n’avons pas défini d’objectif de dons à atteindre.',
	'PPDE_DONATE_NOT_RECEIVED'  => 'Nous n’avons pas encore reçu de dons.',
	'PPDE_DONATE_NOT_USED'      => 'Les dons n’ont pas été utilisés.',
	'PPDE_DONATE_RECEIVED'      => 'Nous avons reçu <strong>%s</strong> de dons.',
	'PPDE_DONATE_USED'          => 'Les dons ont été utilisés à hauteur de <strong>%1$s</strong> des <strong>%2$s</strong> déjà reçus.',
	'PPDE_DONATE_USED_EXCEEDED' => 'Nous avons utilisé <strong>%s</strong>. Tous les dons ont été utilisés.',

	// Viewonline
	'PPDE_VIEWONLINE'           => 'Consulte la page des dons',
	'PPDE_VIEWONLINE_DONORLIST' => 'Consulte la liste des donateurs',
]);

$lang = array_merge($lang, [
	'PPDE_DONORS' => [
		1 => '%d donateur',  // 1
		2 => '%d donateurs', // 2+
	],
]);

$lang = array_merge($lang, [
	// Error
	'CURL_ERROR'                => 'Erreur cURL : %s',
	'INVALID_TXN'               => 'Transaction non valide :',
	'INVALID_TXN_ACCOUNT_ID'    => 'Les ID marchand ne correspondent pas.',
	'INVALID_TXN_ASCII'         => 'Caractères non ASCII détectés dans « %s ».',
	'INVALID_TXN_CONTENT'       => 'Contenu inattendu dans « %s ».',
	'INVALID_TXN_EMPTY'         => 'La variable « %s » est vide.',
	'INVALID_TXN_INVALID_CHECK' => 'Variable POST inconnue.',
	'INVALID_TXN_LENGTH'        => 'Le nombre de caractères attendu pour « %s » ne correspond pas.',
	'INVALID_RESPONSE_STATUS'   => 'Statut de réponse non valide : ',
	'NO_CONNECTION_DETECTED'    => 'cURL non détecté. Veuillez contacter l’administrateur du serveur.',
	'REQUIREMENT_NOT_SATISFIED' => 'cURL, TLS 1.2 ou HTTP1/1 non détecté. Veuillez contacter l’administrateur du serveur.',
	'UNEXPECTED_RESPONSE'       => 'Réponse inattendue de PayPal.',
]);

$lang = array_merge($lang, [
	// Notification
	'NOTIFICATION_PPDE_ADMIN_DONATION_ERRORS'   => 'Le dernier don effectué par %1$s requiert votre attention.',
	'NOTIFICATION_PPDE_ADMIN_DONATION_RECEIVED' => '%1$s a effectué un don d’un montant de « %2$s ».',
	'NOTIFICATION_PPDE_DONOR_DONATION_RECEIVED' => 'Le don d’un montant de « %1$s » a bien été reçu.',
]);
