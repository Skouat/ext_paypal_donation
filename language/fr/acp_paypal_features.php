<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license       GNU General Public License, version 2 (GPL-2.0)
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

	// REST API settings
	'PPDE_LEGEND_REST_API'                 => 'REST API settings',
	'PPDE_REST_LIVE'                       => 'Live credentials',
	'PPDE_REST_SANDBOX'                    => 'Sandbox credentials',
	'PPDE_REST_CLIENT_ID'                  => 'Client ID',
	'PPDE_REST_CLIENT_ID_EXPLAIN'          => 'The REST API app Client ID from your PayPal Developer Dashboard.',
	'PPDE_REST_SECRET'                     => 'Secret',
	'PPDE_REST_SECRET_EXPLAIN'             => 'The REST API app Secret. Leave blank to keep the current value.',
	'PPDE_REST_SECRET_SET'                 => '•••••••• (a secret is already stored)',
	'PPDE_REST_SECRET_EMPTY'               => 'No secret stored yet',
	'PPDE_WEBHOOK_ID'                      => 'Webhook ID',
	'PPDE_WEBHOOK_ID_EXPLAIN'              => 'The Webhook ID created in your PayPal Developer Dashboard. Used to verify incoming webhook notifications.',

	// REST tools (webhook URL + connection test)
	'PPDE_LEGEND_REST_TOOLS'               => 'Outils de l’API REST',
	'PPDE_WEBHOOK_URL'                     => 'URL du webhook',
	'PPDE_WEBHOOK_URL_EXPLAIN'             => 'Ajoutez cette URL comme webhook dans votre tableau de bord PayPal Developer (pour vos applications Live et Sandbox), abonnez-vous aux événements « Payment capture » (completed, pending, denied, refunded, reversed), puis collez le Webhook ID obtenu ci-dessus.',
	'PPDE_REST_TEST_LIVE'                  => 'Tester la connexion Live',
	'PPDE_REST_TEST_SANDBOX'               => 'Tester la connexion Sandbox',
	'PPDE_REST_TEST_BUTTON'                => 'Tester la connexion',
	'PPDE_REST_TESTING'                    => 'Test en cours…',
	'PPDE_REST_TEST_SUCCESS'               => 'Connexion réussie : les identifiants sont valides.',
	'PPDE_REST_TEST_INVALID'               => 'Échec de la connexion : Client ID ou Secret invalide.',
	'PPDE_REST_TEST_CURL_ERROR'            => 'Erreur de connexion : %s',
	'PPDE_REST_TEST_HTTP_ERROR'            => 'Échec de la connexion (HTTP %s).',

	// PayPal IPN settings
	'PPDE_LEGEND_IPN_AUTOGROUP'            => 'Groupe automatique',
	'PPDE_LEGEND_IPN_DONORLIST'            => 'Liste des donateurs',
	'PPDE_LEGEND_IPN_NOTIFICATION'         => 'Système de notification',
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
	'PPDE_IPN_NOTIFICATION_ENABLE'         => 'Activer les notifications',
	'PPDE_IPN_NOTIFICATION_ENABLE_EXPLAIN' => 'Permet de notifier les administrateurs et les donateurs dès qu’un don est reçu.',

	// PayPal sandbox settings
	'PPDE_LEGEND_SANDBOX_SETTINGS'         => 'Paramètres PayPal Sandbox',
	'PPDE_SANDBOX_ENABLE'                  => 'Tester avec PayPal Sandbox',
	'PPDE_SANDBOX_ENABLE_EXPLAIN'          => 'Activez cette option si vous voulez utiliser PayPal Sandbox au lieu des services PayPal.<br>Pratique pour les développeurs/testeurs. Toutes les transactions sont fictives.',
	'PPDE_SANDBOX_FOUNDER_ENABLE'          => 'Sandbox pour les fondateurs',
	'PPDE_SANDBOX_FOUNDER_ENABLE_EXPLAIN'  => 'Si activé, PayPal Sandbox ne sera visible que par les fondateurs du forum.',
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
	'PPDE_REST_CREDENTIALS_MISSING'       => 'Les identifiants de l’API REST PayPal (Client ID / Secret) ne sont pas configurés. Veuillez les renseigner dans le module « Fonctionnalités PayPal IPN ».',
]);
