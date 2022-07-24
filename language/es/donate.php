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
// ’ « » “ ” …
//

$lang = array_merge($lang, array(
	// Header
	'PPDE_HEADER_LINK_TITLE'           => 'Donaciones',
	'PPDE_HEADER_DONORLIST_LINK_TITLE' => 'Donantes',

	// Index page
	'PPDE_INDEX_STATISTICS_TITLE'      => 'Estadísticas de Donación',

	// Pages
	'PPDE_DONATION_BUTTON_TITLE'       => 'Donar',
	'PPDE_DONATION_TITLE'              => 'Hacer una Donación',
	'PPDE_DONATION_TITLE_HEAD'         => 'Hacer una Donación para %s',
	'PPDE_CANCEL_TITLE'                => 'Donación cancelada',
	'PPDE_SUCCESS_TITLE'               => 'Donación correcta',
	'PPDE_CONTACT_PAYPAL'              => 'Conectando con PayPal. Por favor, espere…',
	'PPDE_SANDBOX_TITLE'               => 'Probar Donación PayPal con PayPal Sandbox',

	// Donors list
	'PPDE_DONORLIST_TITLE'             => 'Lista de Donantes',
	'PPDE_DONORLIST_LAST_DONATION'     => 'Última donación',
	'PPDE_DONORLIST_LAST_DATE'         => 'Hecha en',
	'PPDE_DONORLIST_TOTAL_DONATION'    => 'Cantidad de la donación',

	'PPDE_NO_DONORS'            => 'No hay donantes',

	// Statistics
	'PPDE_DONATE_GOAL_RAISE'    => 'Nuestro objetivo es alcanzar <strong>%s</strong>.',
	'PPDE_DONATE_GOAL_REACHED'  => 'Nuestro objetivo de donación fue alcanzado.',
	'PPDE_DONATE_NO_GOAL'       => 'No hemos definido un objetivo de donación.',
	'PPDE_DONATE_NOT_RECEIVED'  => 'No hemos recibido ninguna donación.',
	'PPDE_DONATE_NOT_USED'      => 'No hemos utilizado ninguna donación.',
	'PPDE_DONATE_RECEIVED'      => 'Recibimos <strong>%s</strong> en donaciones.',
	'PPDE_DONATE_USED'          => 'Usamos <strong>%1$s</strong> de un total de <strong>%2$s</strong> recibido en donaciones.',
	'PPDE_DONATE_USED_EXCEEDED' => 'Usamos <strong>%s</strong>. Todas las donaciones han sido utilizadas.',

	// Viewonline
	'PPDE_VIEWONLINE'           => 'Viendo página de Donación',
	'PPDE_VIEWONLINE_DONORLIST' => 'Viendo la lista de donantes',
));

/**
 * Note: This array is out of the previous because there is an issue with Transifex platform
 * Note for translators: Before pushing your translation on Transifex, please surround array indexes with ''.
 */
$lang = array_merge($lang, array(
	'PPDE_DONORS' => array(
		1 => '%d donante',  // 1
		2 => '%d donantes', // 2+
	),
));

$lang = array_merge($lang, array(
	// Error
	'CURL_ERROR'                => 'Error cURL: %s',
	'INVALID_TXN'               => 'Transacción inválida:',
	'INVALID_TXN_ACCOUNT_ID'    => 'ID de comerciante no coincide.',
	'INVALID_TXN_ASCII'         => 'No detectados caracteres ASCII en “%s”.',
	'INVALID_TXN_CONTENT'       => 'Contenido inesperado para “%s”.',
	'INVALID_TXN_EMPTY'         => 'Valor vacío para “%s”.',
	'INVALID_TXN_INVALID_CHECK' => 'Postdata desconocido.',
	'INVALID_TXN_LENGTH'        => 'El número esperado de caracteres para “%s” no coincide.',
	'INVALID_RESPONSE_STATUS'   => 'Estado de respuesta no válido: ',
	'NO_CONNECTION_DETECTED'    => 'cURL no se ha detectado. Por favor, póngase en contacto con el administrador de su servidor web',
	'REQUIREMENT_NOT_SATISFIED' => 'cURL, TLS 1.2 o HTTP1/1 no se han detectado. Por favor, póngase en contacto con el administrador de su servidor web.',
	'UNEXPECTED_RESPONSE'       => 'Respuesta inesperada de PayPal.',
));

$lang = array_merge($lang, array(
	// Notification
	'NOTIFICATION_PPDE_ADMIN_DONATION_ERRORS'   => 'La donación de “%1$s” requiere su atención.',
	'NOTIFICATION_PPDE_ADMIN_DONATION_RECEIVED' => '%1$s ha donado “%2$s”.',
	'NOTIFICATION_PPDE_DONOR_DONATION_RECEIVED' => 'Su donación de “%1$s” ha sido recibida.',
));
