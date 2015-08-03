<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
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
// ’ « » “ ” …
//

$lang = array_merge($lang, array(
	// Header
	'PPDE_HEADER_LINK_TITLE'      => 'Faire un don',

	// Index page
	'PPDE_INDEX_STATISTICS_TITLE' => 'Statistiques des dons',

	// Image alternative text
	'IMG_LOADER'                  => 'chargement',

	// Pages
	'PPDE_DONATION_BUTTON_TITLE'  => 'Faire un don',
	'PPDE_DONATION_TITLE'         => 'Faire un don',
	'PPDE_DONATION_TITLE_HEAD'    => 'Faire un don pour',
	'PPDE_CANCEL_TITLE'           => 'Dons annulés',
	'PPDE_SUCCESS_TITLE'          => 'Dons validés',
	'PPDE_CONTACT_PAYPAL'         => 'Connexion à PayPal - Veuillez patienter…',
	'PPDE_SANDBOX_TITLE'          => 'Tester PayPal Donation avec PayPal Sandbox',

	// Statistics
	// Note for translators----->    %1$d = donation raised; %2$s = currency
	'DONATE_RECEIVED'             => 'Nous avons reçu <strong>%1$d</strong> %2$s de dons.',
	'DONATE_NOT_RECEIVED'         => 'Nous n’avons pas encore reçu de dons.',

	// Note for translators----->    %1$d = donation goal; %2$s = currency
	'DONATE_GOAL_RAISE'           => 'Notre objectif est d’obtenir <strong>%1$d</strong> %2$s.',
	'DONATE_GOAL_REACHED'         => 'L’objectif de don a été atteint.',
	'DONATE_NO_GOAL'              => 'Nous n’avons pas défini d’objectif de dons à atteindre.',

	// Note for translators----->    %1$d = donation used; %2$s = currency; %3$d = donation raised;
	'DONATE_USED'                 => 'Les dons ont été utilisé à hauteur de <strong>%1$d</strong> %2$s des <strong>%3$d</strong> %2$s déjà reçus.',
	'DONATE_USED_EXCEEDED'        => 'Nous avons utilisé <strong>%1$d</strong> %2$s. Tous les dons ont été utilisés.',
	'DONATE_NOT_USED'             => 'Les dons n’ont pas été utilisés.',

	// Viewonline
	'PPDE_VIEWONLINE'             => 'Consulte la page des dons',
));
