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
 * mode: overview
 */
$lang = array_merge($lang, [
	'PPDE_OVERVIEW' => 'Overview',

	'PPDE_ESI'                   => 'Extension and System Information',
	'PPDE_ESI_DETECTED'          => 'Detected',
	'PPDE_ESI_INSTALL_DATE'      => 'Install date of <strong>%s</strong>',
	'PPDE_ESI_INTL_NOT_DETECTED' => 'Consider to install the <a href="https://www.php.net/manual/en/book.intl.php">PHP intl</a> extension',
	'PPDE_ESI_MORE_INFORMATION'  => 'More information…',
	'PPDE_ESI_NOT_DETECTED'      => 'Not detected',
	'PPDE_ESI_RESYNC_OPTIONS'    => 'Reset or recheck extension and system information',
	'PPDE_ESI_TLS'               => 'TLS 1.2',
	'PPDE_ESI_VERSION'           => '<strong>%s</strong> version',
	'PPDE_ESI_VERSION_CURL'      => '<code>cURL</code> version',
	'PPDE_ESI_VERSION_INTL'      => 'PHP <code>Intl</code> version',
	'PPDE_ESI_VERSION_SSL'       => 'SSL version',

	'PPDE_STATS'         => 'Donation Statistics',
	'PPDE_STATS_SANDBOX' => 'Sandbox Statistics',

	'STAT_RESET_DATE'                   => 'Reset extension installation date',
	'STAT_RESET_DATE_CONFIRM'           => 'Are you sure you wish to reset the installation date of this extension?',
	'STAT_RESET_DATE_EXPLAIN'           => 'Resetting the installation date will affect the calculation of the total amount of donations and some other statistics.',
	'STAT_RESYNC_OPTIONS'               => 'Resynchronise statistics',
	'STAT_RESYNC_SANDBOX_STATS'         => 'Resynchronise PayPal Sandbox counts',
	'STAT_RESYNC_SANDBOX_STATS_CONFIRM' => 'Are you sure you wish to resynchronise PayPal Sandbox counts?',
	'STAT_RESYNC_SANDBOX_STATS_EXPLAIN' => 'Resynchronise all donors and transactions PayPal Sandbox counts.',
	'STAT_RESYNC_STATS'                 => 'Resynchronise donors and transactions counts',
	'STAT_RESYNC_STATS_CONFIRM'         => 'Are you sure you wish to resynchronise donors and transactions counts?',
	'STAT_RESYNC_STATS_EXPLAIN'         => 'Resynchronise all donors and transactions counts. Only anonymous donors and active members will be taken in consideration.',
	'STAT_RETEST_ESI'                   => 'Check extension prerequisites',
	'STAT_RETEST_ESI_CONFIRM'           => 'Are you sure you wish to check extension prerequisites?',
	'STAT_RETEST_ESI_EXPLAIN'           => 'Allows to check the prerequisites of the extension, in case the web server configuration has been changed.',

	'STATS_ANONYMOUS_DONORS_COUNT'   => 'Number of anonymous donors',
	'STATS_ANONYMOUS_DONORS_PER_DAY' => 'Anonymous donors per day',
	'STATS_KNOWN_DONORS_COUNT'       => 'Number of known donors',
	'STATS_KNOWN_DONORS_PER_DAY'     => 'Known donors per day',
	'STATS_TRANSACTIONS_COUNT'       => 'Number of transactions',
	'STATS_TRANSACTIONS_PER_DAY'     => 'Transactions per day',
]);
