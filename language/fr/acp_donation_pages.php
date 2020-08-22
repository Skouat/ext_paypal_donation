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
 * mode: donation pages
 */
$lang = array_merge($lang, [
	// Donation Page Body settings
	'DONATION_BODY'            => 'Page principale',
	'DONATION_BODY_EXPLAIN'    => 'Saisir le texte que vous souhaitez afficher sur la page principale.',

	// Donation Cancel settings
	'DONATION_CANCEL'          => 'Page des dons annulés',
	'DONATION_CANCEL_EXPLAIN'  => 'Saisir le texte que vous souhaitez afficher sur la page des dons annulés.',

	// Donation Success settings
	'DONATION_SUCCESS'         => 'Page des dons validés',
	'DONATION_SUCCESS_EXPLAIN' => 'Saisir le texte que vous souhaitez afficher sur la page des dons validés.',

	// Donation Page settings
	'PPDE_DP_CONFIG'           => 'Pages des dons',
	'PPDE_DP_CONFIG_EXPLAIN'   => 'Permet d’améliorer le rendu des pages personnalisables de l’extension.',
	'PPDE_DP_LANG'             => 'Langue',
	'PPDE_DP_LANG_SELECT'      => 'Sélectionnez une langue',
	'PPDE_DP_PAGE'             => 'Type de page',

	// Donation Page Template vars
	'PPDE_DP_BOARD_CONTACT'    => 'Adresse courriel de contact',
	'PPDE_DP_BOARD_EMAIL'      => 'Adresse courriel du forum',
	'PPDE_DP_BOARD_SIG'        => 'Signature du forum',
	'PPDE_DP_DONATION_GOAL'    => 'Objectif des dons',
	'PPDE_DP_DONATION_RAISED'  => 'Dons recueillis',
	'PPDE_DP_PREDEFINED_VARS'  => 'Variables prédéfinies',
	'PPDE_DP_SITE_DESC'        => 'Description du site',
	'PPDE_DP_SITE_NAME'        => 'Nom du site',
	'PPDE_DP_USER_ID'          => 'ID de l’utilisateur',
	'PPDE_DP_USERNAME'         => 'Nom de l’utilisateur',
	'PPDE_DP_VAR_EXAMPLE'      => 'Exemple',
	'PPDE_DP_VAR_NAME'         => 'Nom',
	'PPDE_DP_VAR_VAR'          => 'Variable',
]);

/**
 * Confirm box
 */
$lang = array_merge($lang, [
	'PPDE_DP_ADDED'             => 'Une page de dons pour la langue « %s » a été ajoutée.',
	'PPDE_DP_CONFIRM_OPERATION' => 'Êtes-vous sûr de vouloir supprimer cette page de dons ?',
	'PPDE_DP_DELETED'           => 'Une page de dons pour la langue « %s » a été supprimée.',
	'PPDE_DP_GO_TO_PAGE'        => '%sModifier la page de dons existante%s',
	'PPDE_DP_UPDATED'           => 'Une page de dons pour la langue « %s » a été mise à jour.',
]);

/**
 * Errors
 */
$lang = array_merge($lang, [
	'PPDE_DP_EMPTY_LANG_ID'     => 'Aucune langue n’a été sélectionnée.',
	'PPDE_DP_EMPTY_NAME'        => 'La page de dons sélectionnée n’existe pas.',
	'PPDE_DP_EXISTS'            => 'Cette page de dons existe déjà.',
	'PPDE_DP_NO_DONATION_PAGES' => 'Aucune page de dons n’a été trouvée.',
]);
