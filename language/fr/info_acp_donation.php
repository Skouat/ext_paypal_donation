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
	'ACP_DONATION_MOD' => 'PayPal Donation',
));

/**
* mode: overview
*/
$lang = array_merge($lang, array(
	'PPDE_OVERVIEW'			=> 'Général',

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
