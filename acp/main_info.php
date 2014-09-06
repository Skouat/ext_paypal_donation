<?php
/**
*
* @package PayPal Donation MOD
* @copyright (c) 2014 Skouat
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace skouat\ppde\acp;

class main_info
{
	function module()
	{
		return array(
			'filename'			=> '\skouat\ppde\acp\main_module',
			'title'				=> 'ACP_DONATION_MOD',
			'version'			=> '1.0.0',
			'modes'		=> array(
				'overview'			=> array('title' => 'DONATION_OVERVIEW',	'auth' => 'acl_a_pdm_manage', 'cat' => array('ACP_DONATION_MOD')),
				'configuration'		=> array('title' => 'DONATION_CONFIG',		'auth' => 'acl_a_pdm_manage', 'cat' => array('ACP_DONATION_MOD')),
				'donation_pages'	=> array('title' => 'DONATION_DP_CONFIG',	'auth' => 'acl_a_pdm_manage', 'cat' => array('ACP_DONATION_MOD')),
				'currency'			=> array('title' => 'DONATION_DC_CONFIG',	'auth' => 'acl_a_pdm_manage', 'cat' => array('ACP_DONATION_MOD')),
			),
		);
	}
}