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
 * mode: settings
 */
$lang = array_merge($lang, [
	'PPDE_SETTINGS'                   => 'Paramètres généraux',
	'PPDE_SETTINGS_EXPLAIN'           => 'Depuis cette page vous pouvez configurer les paramètres généraux de PayPal Donation.',

	// General settings
	'PPDE_ACCOUNT_ID'                 => 'ID du compte PayPal',
	'PPDE_ACCOUNT_ID_EXPLAIN'         => 'Saisir l’ID de compte marchand ou l’adresse courriel.',
	'PPDE_ALLOW_GUEST'                => 'Autoriser les invités à faire des dons',
	'PPDE_ALLOW_GUEST_EXPLAIN'        => 'Cette option va définir les permissions du forum pour autoriser les invités à faire des dons',
	'PPDE_DEFAULT_CURRENCY'           => 'Devise par défaut',
	'PPDE_DEFAULT_CURRENCY_EXPLAIN'   => 'Défini quelle devise sera sélectionnée par défaut.',
	'PPDE_DEFAULT_LOCALE'             => 'Paramètres régionnaux',
	'PPDE_DEFAULT_LOCALE_EXPLAIN'     => 'Définit les paramètres régionaux utilisés pour le formatage des devises.',
	'PPDE_DEFAULT_LOCALE_REQUIRED'    => 'L’extension <a href="https://www.php.net/manual/en/book.intl.php">PHP intl</a> est nécessaire et doit être en version 1.1.0 ou supérieure.',
	'PPDE_DEFAULT_LOCALE_SELECT'      => 'Sélectionnez une localisation',
	'PPDE_DEFAULT_VALUE'              => 'Valeur de don par défaut',
	'PPDE_DEFAULT_VALUE_EXPLAIN'      => 'Défini quelle valeur de don sera proposée par défaut sur la page de dons.',
	'PPDE_DROPBOX_ENABLE'             => 'Activer le menu déroulant',
	'PPDE_DROPBOX_ENABLE_EXPLAIN'     => 'Activez cette option pour remplacer la zone de texte par un menu déroulant.',
	'PPDE_DROPBOX_VALUE'              => 'Valeurs des dons du menu déroulant',
	'PPDE_DROPBOX_VALUE_EXPLAIN'      => 'Définissez les valeurs que vous voulez faire apparaître dans le menu déroulant.<br>Séparez chaque valeur par une <strong>virgule</strong> (",") et <strong>sans espace</strong>.',
	'PPDE_ENABLE'                     => 'Activer PayPal Donation',
	'PPDE_ENABLE_EXPLAIN'             => 'Active ou désactive l’extension PayPal Donation.',
	'PPDE_HEADER_LINK'                => 'Afficher le lien « Faire un don » dans l’entête du forum',
	'PPDE_LEGEND_GENERAL_SETTINGS'    => 'Paramètres généraux',

	// Advanced settings
	'PPDE_LEGEND_ADVANCED_SETTINGS'   => 'Paramètres avancés',
	'PPDE_DEFAULT_REMOTE'             => 'URL PayPal',
	'PPDE_DEFAULT_REMOTE_EXPLAIN'     => 'Ne changez pas ce paramètre, sauf si cette extension a des difficultés pour contacter les serveurs de PayPal.',

	// Stats Donation settings
	'PPDE_AMOUNT'                     => 'Montant',
	'PPDE_DECIMAL_EXPLAIN'            => 'Utiliser le « . » comme symbole décimal.', // Note for translator: do not translate the decimal symbol
	'PPDE_GOAL'                       => 'Objectif des dons',
	'PPDE_GOAL_EXPLAIN'               => 'Inscrire le montant total des dons à atteindre.',
	'PPDE_LEGEND_STATS_SETTINGS'      => 'Paramètres des statistiques',
	'PPDE_RAISED'                     => 'Dons recueillis',
	'PPDE_RAISED_EXPLAIN'             => 'Inscrire le montant total des dons actuellement reçus.',
	'PPDE_STATS_INDEX_ENABLE'         => 'Statistiques des dons sur l’index',
	'PPDE_STATS_INDEX_ENABLE_EXPLAIN' => 'Activez cette option si vous voulez afficher les statistiques des dons sur l’index du forum.',
	'PPDE_STATS_LOCATION'             => 'Position des statistiques des dons sur l’index',
	'PPDE_STATS_LOCATION_EXPLAIN'     => 'Défini où seront affichées les statistiques de dons sur l’index.',
	'PPDE_STATS_TEXT_ONLY'            => 'Statistiques des dons avec uniquement du texte',
	'PPDE_STATS_TEXT_ONLY_EXPLAIN'    => 'Activez cette option si vous souhaitez désactiver les barres de statistiques des dons. Seul le texte sera affiché.',
	'PPDE_USED'                       => 'Dons utilisés',
	'PPDE_USED_EXPLAIN'               => 'Inscrire le montant des dons déjà utilisés.',
]);

/**
 * Confirm box
 */
$lang = array_merge($lang, [
	'PPDE_SETTINGS_SAVED' => 'Les paramètres de PayPal Donation ont été sauvegardés.',
]);

/**
 * Errors
 */
$lang = array_merge($lang, [
	'PPDE_SETTINGS_MISSING' => 'Veuillez vérifier le paramètre « ID du compte PayPal ».',
]);
