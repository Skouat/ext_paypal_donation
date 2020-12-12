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
 * mode: PayPal features
 */
$lang = array_merge($lang, [
	'PPDE_PAYPAL_FEATURES'                 => 'PayPal IPN',
	'PPDE_PAYPAL_FEATURES_EXPLAIN'         => 'Depuis cette page vous pouvez configurer les fonctionnalités utilisant les notifications instantanées de paiement (IPN) de PayPal.',

	// PayPal IPN settings
	'PPDE_LEGEND_IPN_AUTOGROUP'            => 'Groupe automatique',
	'PPDE_LEGEND_IPN_DEBUG'                => 'Paramètres de débogage',
	'PPDE_LEGEND_IPN_DONORLIST'            => 'Liste des donateurs',
	'PPDE_LEGEND_IPN_NOTIFICATION'         => 'Système de notification',
	'PPDE_LEGEND_IPN_SETTINGS'             => 'Paramètres Généraux',
	'PPDE_IPN_AG_ENABLE'                   => 'Activer le groupe automatique',
	'PPDE_IPN_AG_ENABLE_EXPLAIN'           => 'Permet d’ajouter automatiquement les donateurs dans un groupe pré-défini.',
	'PPDE_IPN_AG_DONORS_GROUP'             => 'Groupe donateurs',
	'PPDE_IPN_AG_DONORS_GROUP_EXPLAIN'     => 'Sélectionnez le groupe qui accueillera les membres donateurs.',
	'PPDE_IPN_AG_GROUP_AS_DEFAULT'         => 'Définir comme groupe par défaut',
	'PPDE_IPN_AG_GROUP_AS_DEFAULT_EXPLAIN' => 'Activez cette option pour définir le groupe des donateurs comme groupe par défaut pour les membres ayant fait une donation.',
	'PPDE_IPN_AG_MIN_BEFORE_GROUP'         => 'Montant minimum requis avant ajout dans le groupe',
	'PPDE_IPN_AG_MIN_BEFORE_GROUP_EXPLAIN' => 'Définit le montant minimum qui doit être donné par un membre avant qu’il soit automatiquement ajouté au groupe.',
	'PPDE_IPN_DL_ALLOW_GUEST'              => 'Autoriser les invités à consulter la liste des donateurs',
	'PPDE_IPN_DL_ALLOW_GUEST_EXPLAIN'      => 'Cette option va définir les permissions du forum pour autoriser les invités à consulter la liste des donateurs.',
	'PPDE_IPN_DL_ENABLE'                   => 'Activer la liste des donateurs',
	'PPDE_IPN_DL_ENABLE_EXPLAIN'           => 'Permet d’activer la liste des donateurs.',
	'PPDE_IPN_ENABLE'                      => 'Activer IPN',
	'PPDE_IPN_ENABLE_EXPLAIN'              => 'Activez cette option pour utiliser IPN (Notification Instantanée de Paiement).<br>Si activé, toutes les fonctionnalités dépendant de PayPal IPN apparaîtront ci-dessous.',
	'PPDE_IPN_LOGGING'                     => 'Activer le journal des erreurs',
	'PPDE_IPN_LOGGING_EXPLAIN'             => 'Cette option permet d’enregistrer les erreurs et les données liées à PayPal IPN dans le répertoire <strong>/store/ext/ppde/</strong>.',
	'PPDE_IPN_NOTIFICATION_ENABLE'         => 'Activer les notifications',
	'PPDE_IPN_NOTIFICATION_ENABLE_EXPLAIN' => 'Permet de notifier les administrateurs et les donateurs dès qu’un don est reçu.',

	// PayPal sandbox settings
	'PPDE_LEGEND_SANDBOX_SETTINGS'         => 'Paramètres PayPal Sandbox',
	'PPDE_SANDBOX_ENABLE'                  => 'Tester avec PayPal Sandbox',
	'PPDE_SANDBOX_ENABLE_EXPLAIN'          => 'Activez cette option si vous voulez utiliser PayPal Sandbox au lieu des services PayPal.<br>Pratique pour les développeurs/testeurs. Toutes les transactions sont fictives.',
	'PPDE_SANDBOX_FOUNDER_ENABLE'          => 'Sandbox pour les fondateurs',
	'PPDE_SANDBOX_FOUNDER_ENABLE_EXPLAIN'  => 'Si activé, PayPal Sandbox ne sera visible que par les fondateurs du forum.',
	'PPDE_SANDBOX_ADDRESS'                 => 'Compte PayPal Sandbox',
	'PPDE_SANDBOX_ADDRESS_EXPLAIN'         => 'Inscrire l’adresse courriel ou l’ID de vendeur PayPal Sandbox.',
	'PPDE_SANDBOX_REMOTE'                  => 'URL PayPal Sandbox',
	'PPDE_SANDBOX_REMOTE_EXPLAIN'          => 'Ne changez pas ce paramètre, sauf si cette extension a des difficultés pour contacter les serveurs Sandbox de PayPal.',
]);

/**
 * Confirm box
 */
$lang = array_merge($lang, [
	'PPDE_PAYPAL_FEATURES_SAVED' => 'Les paramètres IPN PayPal ont été sauvegardés.',
]);

/**
 * Errors
 */
$lang = array_merge($lang, [
	'PPDE_PAYPAL_FEATURES_MISSING'        => 'Veuillez vérifier le paramètre « Adresse PayPal Sandbox ».',
	'PPDE_PAYPAL_FEATURES_NOT_ENABLEABLE' => 'PayPal IPN ne peut pas être activé. Vérifiez les prérequis systèmes depuis le module « Vue d’ensemble ».',
]);
