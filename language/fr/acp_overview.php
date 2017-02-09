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
// ’ « » “ ” …
//

/**
 * mode: overview
 */
$lang = array_merge($lang, array(
	'PPDE_OVERVIEW' => 'Vue d’ensemble',

	'INFO_CURL'         => 'cURL',
	'INFO_CURL_VERSION' => 'Version cURL : %1$s<br>Version SSL : %2$s',
	'INFO_DETECTED'     => 'Détecté',
	'INFO_FSOCKOPEN'    => 'Fsockopen',
	'INFO_NOT_DETECTED' => 'Non détecté',

	'PPDE_INSTALL_DATE'    => 'Date d’installation de <strong>%s</strong>',
	'PPDE_NO_VERSIONCHECK' => 'Cette extension ne prend pas en charge le contrôle de version.',
	'PPDE_NOT_UP_TO_DATE'  => '%s n’est pas à jour',
	'PPDE_STATS'           => 'Statistiques des dons',
	'PPDE_STATS_SANDBOX'   => 'Statistiques Sandbox',
	'PPDE_VERSION'         => 'Version de <strong>%s</strong>',

	'STAT_RESET_DATE'                   => 'Réinitialiser la date d’installation de l’extension',
	'STAT_RESET_DATE_EXPLAIN'           => 'La réinitialisation de la date d’installation affectera le calcul du montant total des dons et quelques autres informations.',
	'STAT_RESET_DATE_CONFIRM'           => 'Êtes-vous sûr de vouloir réinitialiser la date d’installation de cette extension ?',
	'STAT_RESYNC_STATS'                 => 'Actualiser les compteurs des donateurs et des transactions',
	'STAT_RESYNC_STATS_EXPLAIN'         => 'Actualise tous les compteurs des donateurs et des transactions. Seuls donateurs actifs et anonymes seront pris en considération.',
	'STAT_RESYNC_STATS_CONFIRM'         => 'Êtes-vous sûr de vouloir actualiser les compteurs des donateurs et des transactions ?',
	'STAT_RESYNC_SANDBOX_STATS'         => 'Actualiser les compteurs de PayPal Sandbox',
	'STAT_RESYNC_SANDBOX_STATS_EXPLAIN' => 'Actualise tous les compteurs des donateurs et des transactions liés à PayPal Sandbox.',
	'STAT_RESYNC_SANDBOX_STATS_CONFIRM' => 'Êtes-vous sûr de vouloir actualiser les compteurs de PayPal Sandbox ?',
	'STAT_RETEST_CURL_FSOCK'            => 'Re-détecter « cURL » et « fsockopen »',
	'STAT_RETEST_CURL_FSOCK_EXPLAIN'    => 'Permet de re-détecter ces fonctionnalités si la configuration du serveur a été modifiée.',
	'STAT_RETEST_CURL_FSOCK_CONFIRM'    => 'Êtes-vous sûr de vouloir re-détecter « cURL » et « fsockopen » ?',

	'STATS_ANONYMOUS_DONORS_COUNT'   => 'Nombre de donateurs anonymes',
	'STATS_ANONYMOUS_DONORS_PER_DAY' => 'Moyenne journalière des donateurs anonymes',
	'STATS_KNOWN_DONORS_COUNT'       => 'Nombre de donateurs connus',
	'STATS_KNOWN_DONORS_PER_DAY'     => 'Moyenne journalière des donateurs connus',
	'STATS_TRANSACTIONS_COUNT'       => 'Nombre de transactions',
	'STATS_TRANSACTIONS_PER_DAY'     => 'Moyenne journalière des transactions',
));
