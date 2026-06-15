<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\migrations\v33x;

class v330_m3_update_data extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v33x\v330_m2_update_data'];
	}

	public function update_data()
	{
		return [
			// REST API - Live credentials
			['config.add', ['ppde_rest_client_id', '']],
			['config.add', ['ppde_rest_secret', '']],
			['config.add', ['ppde_webhook_id', '']],

			// REST API - Sandbox credentials
			['config.add', ['ppde_sandbox_rest_client_id', '']],
			['config.add', ['ppde_sandbox_rest_secret', '']],
			['config.add', ['ppde_sandbox_webhook_id', '']],

			['config.remove', ['ppde_ipn_logging']],
			['config.remove', ['ppde_tls_detected']],
			['config.remove', ['ppde_default_remote']],
			['config.remove', ['ppde_sandbox_remote']],
			['config.remove', ['ppde_curl_detected']],
			['config.remove', ['ppde_curl_version']],
			['config.remove', ['ppde_curl_ssl_version']],

			['config.update', ['ppde_first_start', true]],
		];
	}
}
