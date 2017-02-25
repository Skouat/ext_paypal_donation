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

/**
 * mode: overview
 */
$lang = array_merge($lang, array(
	'PPDE_OVERVIEW' => 'Overview',

	'PPDE_ESI'                 => 'Extension and System Infomation',
	'PPDE_ESI_DETECTED'        => 'Detected',
	'PPDE_ESI_FSOCKOPEN'       => 'Fsockopen',
	'PPDE_ESI_INSTALL_DATE'    => 'Install date of <strong>%s</strong>',
	'PPDE_ESI_NOT_DETECTED'    => 'Not detected',
	'PPDE_ESI_NO_VERSIONCHECK' => 'No version check information given.',
	'PPDE_ESI_NOT_UP_TO_DATE'  => '%s is not up to date',
	'PPDE_ESI_VERSION'         => '<strong>%s</strong> version',
	'PPDE_ESI_VERSION_CURL'    => 'cURL version',
	'PPDE_ESI_VERSION_SSL'     => 'SSL version',
	'PPDE_ESI_RESYNC_OPTIONS'  => 'Reset or recheck extension and system information',

	'PPDE_STATS'         => 'Donation statistics',
	'PPDE_STATS_SANDBOX' => 'Sandbox statistics',

	'STAT_RESYNC_OPTIONS'               => 'Resynchronise statistics',
	'STAT_RESET_DATE'                   => 'Reset Extension Installation date',
	'STAT_RESET_DATE_EXPLAIN'           => 'Resetting the installation date will affect the calculation of the total amount of donations and some other statistics.',
	'STAT_RESET_DATE_CONFIRM'           => 'Are you sure you wish to reset the installation date of this extension?',
	'STAT_RESYNC_STATS'                 => 'Resynchronise donors and transactions counts',
	'STAT_RESYNC_STATS_EXPLAIN'         => 'Resynchronise all donors and transactions counts. Only anonymous donors and active members will be taken in consideration.',
	'STAT_RESYNC_STATS_CONFIRM'         => 'Are you sure you wish to resynchronise donors and transactions counts?',
	'STAT_RESYNC_SANDBOX_STATS'         => 'Resynchronise PayPal Sandbox counts',
	'STAT_RESYNC_SANDBOX_STATS_EXPLAIN' => 'Resynchronise all donors and transactions PayPal Sandbox counts.',
	'STAT_RESYNC_SANDBOX_STATS_CONFIRM' => 'Are you sure you wish to resynchronise PayPal Sandbox counts?',
	'STAT_RETEST_ESI'                   => 'Check extension prerequisites',
	'STAT_RETEST_ESI_EXPLAIN'           => 'Allows to check the prerequisites of the extension, in case the Web server configuration has been changed.',
	'STAT_RETEST_ESI_CONFIRM'           => 'Are you sure you wish to check extension prerequisites?',

	'STATS_ANONYMOUS_DONORS_COUNT'   => 'Number of anonymous donors',
	'STATS_ANONYMOUS_DONORS_PER_DAY' => 'Anonymous donors per day',
	'STATS_KNOWN_DONORS_COUNT'       => 'Number of known donors',
	'STATS_KNOWN_DONORS_PER_DAY'     => 'Known donors per day',
	'STATS_TRANSACTIONS_COUNT'       => 'Number of transactions',
	'STATS_TRANSACTIONS_PER_DAY'     => 'Transactions per day',
));
