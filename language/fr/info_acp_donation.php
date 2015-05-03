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


/**
* mode: main
*/
$lang = array_merge($lang, array(
	'PPDE_ACP_DONATION_MOD'			=> 'PayPal Donation',
	'PPDE_ACP_OVERVIEW'				=> 'Vue d’ensemble',
	'PPDE_ACP_SETTINGS'				=> 'Paramètres généraux',
	'PPDE_ACP_DONATION_PAGES'		=> 'Pages des dons',
));

/**
* mode: overview
*/
$lang = array_merge($lang, array(
	'PPDE_OVERVIEW'			=> 'Vue d’ensemble',

	'INFO_CURL'				=> 'cURL',
	'INFO_FSOCKOPEN'		=> 'Fsockopen',
	'INFO_DETECTED'			=> 'Détecté',
	'INFO_NOT_DETECTED'		=> 'Non détecté',

	'PPDE_INSTALL_DATE'		=> 'Date d’installation de <strong>%s</strong>',
	'PPDE_NO_VERSIONCHECK'	=> 'Cette extension ne prend pas en charge le contrôle de version.',
	'PPDE_NOT_UP_TO_DATE'	=> '%s n’est pas à jour',
	'PPDE_STATS'			=> 'Statistiques des dons',
	'PPDE_VERSION'			=> 'Version de <strong>%s</strong>',

	'STAT_RESET_DATE'			=> 'Réinitialiser la date d’installation du MOD',
	'STAT_RESET_DATE_EXPLAIN'	=> 'La réinitialisation de la date d’installation affectera le calcul du montant total des dons et quelques autres informations.',
	'STAT_RESET_DATE_CONFIRM'	=> 'Etes-vous sûr de vouloir réinitialiser la date d’installation de cette extension ?',
));

/**
* mode: settings
*/
$lang = array_merge($lang, array(
	'PPDE_SETTINGS'			=> 'Paramètres généraux',
	'PPDE_SETTINGS_EXPLAIN'	=> '',

	'MODE_CURRENCY'			=> 'devise',
	'MODE_DONATION_PAGES'	=> 'pages de dons',

	// Global Donation settings
	'PPDE_LEGEND_GENERAL_SETTINGS'	=> 'Paramètres généraux',
	'PPDE_ENABLE'					=> 'Activer PayPal Donation',
	'PPDE_ENABLE_EXPLAIN'			=> 'Active ou désactive le MOD PayPal Donation.',
	'PPDE_ACCOUNT_ID'				=> 'ID du compte PayPal',
	'PPDE_ACCOUNT_ID_EXPLAIN'		=> 'Saisir l’adresse e-mail ou l’ID de compte marchand.',
	'PPDE_DEFAULT_CURRENCY'			=> 'Devise par défaut',
	'PPDE_DEFAULT_CURRENCY_EXPLAIN'	=> 'Défini quelle devise sera sélectionnée par défaut.',
	'PPDE_DEFAULT_VALUE'			=> 'Valeur de don par défaut',
	'PPDE_DEFAULT_VALUE_EXPLAIN'	=> 'Défini quelle valeur de don sera suggérée par défaut.',
	'PPDE_DROPBOX_ENABLE'			=> 'Activer la liste déroulante',
	'PPDE_DROPBOX_ENABLE_EXPLAIN'	=> 'Si activée, elle remplacera la zonte de texte par un menu déroulant.',
	'PPDE_DROPBOX_VALUE'			=> 'Valeurs de la liste déroulante',
	'PPDE_DROPBOX_VALUE_EXPLAIN'	=> 'Définissez les nombres que vous voulez voir dans la liste déroulante.<br />Séparez chaques valeurs par une virgule (",") et sans espaces.',

	// PayPal sandbox settings
	'PPDE_LEGEND_SANDBOX_SETTINGS'			=> 'Paramètres PayPal Sandbox',
	'PPDE_SANDBOX_ENABLE'					=> 'Tester avec PayPal Sandbox',
	'PPDE_SANDBOX_ENABLE_EXPLAIN'			=> 'Activez cette option si vous voulez utiliser PayPal Sandbox au lieu des services PayPal.<br />Pratique pour les développeurs/testeurs. Toutes les transactions sont fictives.',
	'PPDE_SANDBOX_FOUNDER_ENABLE'			=> 'Sandbox pour les fondateurs',
	'PPDE_SANDBOX_FOUNDER_ENABLE_EXPLAIN'	=> 'Si activé, PayPal Sandbox ne sera visible que par les fondateurs du forum.',
	'PPDE_SANDBOX_ADDRESS'					=> 'Adresse PayPal Sandbox',
	'PPDE_SANDBOX_ADDRESS_EXPLAIN'			=> 'Inscrire votre addresse e-mail de vendeur PayPal Sandbox.',

	// Stats Donation settings
	'PPDE_LEGEND_STATS_SETTINGS'		=> 'Paramètres des statistiques',
	'PPDE_STATS_INDEX_ENABLE'			=> 'Statistiques des dons sur l’index',
	'PPDE_STATS_INDEX_ENABLE_EXPLAIN'	=> 'Activez cette option si vous voulez afficher les statistiques des dons sur l’index du forum.',
	'PPDE_RAISED_ENABLE'				=> 'Activer dons recueillis',
	'PPDE_RAISED'						=> 'Dons recueillis',
	'PPDE_RAISED_EXPLAIN'				=> 'Inscrire le montant total des dons actuellement reçus.',
	'PPDE_GOAL_ENABLE'					=> 'Activer Objectif des dons',
	'PPDE_GOAL'							=> 'Objectif des dons',
	'PPDE_GOAL_EXPLAIN'					=> 'Inscrire le montant total des dons à atteindre.',
	'PPDE_USED_ENABLE'					=> 'Activer dons utilisés',
	'PPDE_USED'							=> 'Dons Utilisés',
	'PPDE_USED_EXPLAIN'					=> 'Inscrire le montant des dons déjà utilisés.',

	'PPDE_CURRENCY_ENABLE'				=> 'Activer Devise des dons',
	'PPDE_CURRENCY_ENABLE_EXPLAIN'		=> 'Activez cette option, pour rendre visible le Code ISO 4217 de la devise défini par défaut dans les statistiques des dons.',
));

/**
* mode: donation pages
* Info: language keys are prefixed with 'PPDE_DP_' for 'PPDE_DONATION_PAGES_'
*/
$lang = array_merge($lang, array(
	// Donation Page settings
	'PPDE_DP_CONFIG'			=> 'Pages des dons',
	'PPDE_DP_CONFIG_EXPLAIN'	=> 'Permet d’améliorer le rendu des pages personalisables de l’extension.',

	'PPDE_DP_PAGE'				=> 'Type de page',
	'PPDE_DP_LANG'				=> 'Langue',
	'PPDE_DP_LANG_SELECT'		=> 'Sélectionnez une langue',

	// Donation Page Body settings
	'DONATION_BODY'				=> 'Page principale',
	'DONATION_BODY_EXPLAIN'		=> 'Saisir le texte que vous souhaitez afficher sur la page principale.',

	// Donation Success settings
	'DONATION_SUCCESS'			=> 'Page des dons validés',
	'DONATION_SUCCESS_EXPLAIN'	=> 'Saisir le texte que vous souhaitez afficher sur la page des dons validés.',

	// Donation Cancel settings
	'DONATION_CANCEL'			=> 'Page des dons annulés',
	'DONATION_CANCEL_EXPLAIN'	=> 'Saisir le texte que vous souhaitez afficher sur la page des dons annulés.',
));

/**
* logs
*/
$lang = array_merge($lang, array(
	//logs
	'LOG_PPDE_SETTINGS_UPDATED'	=> '<strong>PayPal Donation : Configuration mise à jour.</strong>',

	// Confirm box
	'PPDE_DP_LANG_ADDED'	=> 'Une page de dons pour la langue « %s » a été ajoutée.',
	'PPDE_SETTINGS_SAVED'	=> 'Les paramètres de PayPal Donation ont été sauvegardés.',

	// Errors
	'PPDE_FIELD_MISSING'	=> 'Le champ « %s » est manquant.',
	'PPDE_ITEM_EXIST'		=> 'L’élément sélectionné existe déjà.',
	'PPDE_NO_ITEM'			=> 'Aucun élément n’a été trouvé.',
	'PPDE_MUST_SELECT_ITEM'	=> 'L’élément sélectionné n’existe pas.',
	'PPDE_MUST_SELECT_LANG'	=> 'Aucune langue n’a été sélectionnée.',
));
