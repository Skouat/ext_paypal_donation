<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\api\paypal;

trait curl_transport
{
	/**
	 * Initialise a cURL handle with the shared security and timeout policy.
	 *
	 * @param string $url
	 * @param int    $timeout
	 *
	 * @return resource|\CurlHandle
	 * @access protected
	 */
	protected function curl_init_secure(string $url, int $timeout = 30)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

		return $ch;
	}
}
