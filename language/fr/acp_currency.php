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
 * mode: currency
 */
$lang = array_merge($lang, [
	'PPDE_DC_CONFIG'             => 'Gestion des devises',
	'PPDE_DC_CONFIG_EXPLAIN'     => 'Permet de gérer les devises pour faire un don.',
	'PPDE_DC_CREATE_CURRENCY'    => 'Ajouter une devise',
	'PPDE_DC_DEFAULT_CURRENCY'   => '(devise par défaut)',
	'PPDE_DC_ENABLE'             => 'Activer la devise',
	'PPDE_DC_ENABLE_EXPLAIN'     => 'Si activée, la devise sera disponible dans la liste de sélection.',
	'PPDE_DC_ISO_CODE'           => 'Code ISO 4217',
	'PPDE_DC_ISO_CODE_EXPLAIN'   => 'Code alphabétique de la devise.<br>En savoir plus sur la norme ISO 4217… Consultez la <a href="https://www.phpbb.com/customise/db/extension/paypal_donation_extension/faq/1841" title="FAQ PayPal Donation">FAQ</a> de l’extension PayPal Donation (lien externe en anglais).',
	'PPDE_DC_LOCALE_AVAILABLE'   => 'L’extension PHP <code>intl</code> a été détectée. Allez dans le module « Paramètres généraux » pour sélectionner vos paramètres régionnaux.',
	'PPDE_DC_LOCALE_DEPRECATED'  => 'Cette option est désactivée car votre serveur est compatible avec l’extension PHP <code>intl</code>.',
	'PPDE_DC_LOCALE_UNAVAILABLE' => 'PPDE ne détecte pas l’extension PHP <code>intl</code>. Songez à installer cette extension PHP pour profiter d’une meilleure expérience.',
	'PPDE_DC_NAME'               => 'Nom de la devise',
	'PPDE_DC_NAME_EXPLAIN'       => 'Exemple : Euro.',
	'PPDE_DC_POSITION'           => 'Position du symbole',
	'PPDE_DC_POSITION_EXPLAIN'   => 'Définit où le symbole de la devise sera positionné par rapport au montant affiché.<br>Exemple : <strong>$20</strong> ou <strong>15€</strong>.',
	'PPDE_DC_POSITION_LEFT'      => 'À gauche',
	'PPDE_DC_POSITION_RIGHT'     => 'À droite',
	'PPDE_DC_SYMBOL'             => 'Symbole de la devise',
	'PPDE_DC_SYMBOL_EXPLAIN'     => 'Inscrivez le symbole de la devise.<br>Exemple : <strong>€</strong> pour Euro.',
]);

/**
 * Confirm box
 */
$lang = array_merge($lang, [
	'PPDE_DC_ADDED'             => 'Une devise a été ajoutée.',
	'PPDE_DC_CONFIRM_OPERATION' => 'Êtes-vous sûr de vouloir supprimer cette devise ?',
	'PPDE_DC_DELETED'           => 'Une devise a été supprimée.',
	'PPDE_DC_GO_TO_PAGE'        => '%sModifier la devise existante%s',
	'PPDE_DC_UPDATED'           => 'Une devise a été mise à jour.',
]);

/**
 * Errors
 */
$lang = array_merge($lang, [
	'PPDE_CANNOT_DISABLE_DEFAULT_CURRENCY' => 'Vous ne pouvez pas désactiver la devise par défaut.',
	'PPDE_DC_EMPTY_NAME'                   => 'Saisissez un nom de devise.',
	'PPDE_DC_EMPTY_ISO_CODE'               => 'Saisissez un code ISO.',
	'PPDE_DC_EMPTY_SYMBOL'                 => 'Saisissez un symbole.',
	'PPDE_DC_EXISTS'                       => 'Cette devise existe déjà.',
	'PPDE_DC_INVALID_HASH'                 => 'Le lien est corrompu. Le hachage n’est pas valide.',
	'PPDE_DC_NO_CURRENCY'                  => 'Aucune devise n’a été trouvée.',
	'PPDE_DISABLE_BEFORE_DELETION'         => 'Vous devez désactiver la devise avant de la supprimer.',
]);
