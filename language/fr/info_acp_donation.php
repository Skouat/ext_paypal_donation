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
 * mode: main
 */
$lang = array_merge($lang, [
	'PPDE_ACP_DONATION'        => 'PayPal Donation',
	'PPDE_ACP_OVERVIEW'        => 'Vue d’ensemble',
	'PPDE_ACP_PAYPAL_FEATURES' => 'Fonctionnalités PayPal IPN',
	'PPDE_ACP_SETTINGS'        => 'Paramètres généraux',
	'PPDE_ACP_DONATION_PAGES'  => 'Pages des dons',
	'PPDE_ACP_CURRENCY'        => 'Gestion des devises',
	'PPDE_ACP_TRANSACTIONS'    => 'Journal des transactions',
]);

/**
 * logs
 */
$lang = array_merge($lang, [
	'LOG_PPDE_DC_ACTIVATED'            => '<strong>PayPal Donation : Devise activée</strong><br>» %s',
	'LOG_PPDE_DC_ADDED'                => '<strong>PayPal Donation : Nouvelle devise ajoutée</strong><br>» %s',
	'LOG_PPDE_DC_DEACTIVATED'          => '<strong>PayPal Donation : Devise désactivée</strong><br>» %s',
	'LOG_PPDE_DC_DELETED'              => '<strong>PayPal Donation : Devise supprimée</strong><br>» %s',
	'LOG_PPDE_DC_MOVE_DOWN'            => '<strong>PayPal Donation : Déplacement vers le bas de la devise</strong> « %s »',
	'LOG_PPDE_DC_MOVE_UP'              => '<strong>PayPal Donation : Déplacement vers le haut de la devise</strong> « %s »',
	'LOG_PPDE_DC_UPDATED'              => '<strong>PayPal Donation : Devise mise à jour</strong><br>» %s',
	'LOG_PPDE_DP_ADDED'                => '<strong>PayPal Donation : Nouvelle page de dons ajoutée</strong><br>» « %1$s » pour la langue « %2$s »', // eg: » “Donation success” for the language “British English”',
	'LOG_PPDE_DP_DELETED'              => '<strong>PayPal Donation : Page des dons supprimée</strong><br>» « %1$s » pour la langue « %2$s »',
	'LOG_PPDE_DP_UPDATED'              => '<strong>PayPal Donation : Page de dons mise à jour</strong><br>» « %1$s » pour la langue « %2$s »',
	'LOG_PPDE_DT_PURGED'               => '<strong>PayPal Donation : Purge du journal des transactions</strong>',
	'LOG_PPDE_DT_UPDATED'              => '<strong>PayPal Donation : Transaction mise à jour</strong>',
	'LOG_PPDE_MT_ADDED'                => '<strong>PayPal Donation : Transaction manuelle ajoutée</strong><br>» Donateur : %s',
	'LOG_PPDE_PAYPAL_FEATURES_UPDATED' => '<strong>PayPal Donation : Configuration PayPal mise à jour</strong>',
	'LOG_PPDE_SETTINGS_UPDATED'        => '<strong>PayPal Donation : Configuration mise à jour</strong>',
	'LOG_PPDE_STAT_RESET_DATE'         => '<strong>PayPal Donation : Date d’installation réinitialisée</strong>',
	'LOG_PPDE_STAT_RESYNC'             => '<strong>PayPal Donation : Actualisation des statistiques</strong>',
	'LOG_PPDE_STAT_RETEST_ESI'         => '<strong>PayPal Donation : Vérification des prérequis</strong>',
	'LOG_PPDE_STAT_SANDBOX_RESYNC'     => '<strong>PayPal Donation : Actualisation des statistiques PayPal Sandbox</strong>',
]);
