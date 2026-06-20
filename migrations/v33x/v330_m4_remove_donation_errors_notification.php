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

/**
 * Removes the obsolete "admin_donation_errors" notification type.
 *
 * Since the migration to the PayPal REST API, incoming webhooks are
 * cryptographically verified, so the legacy "donation with errors to approve"
 * workflow no longer exists and this notification type can never fire.
 */
class v330_m4_remove_donation_errors_notification extends \phpbb\db\migration\container_aware_migration
{
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v33x\v330_m3_update_data'];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'purge_notification']]],
		];
	}

	/**
	 * Purge the obsolete notification type from the database.
	 *
	 * @return void
	 */
	public function purge_notification()
	{
		/** @var \phpbb\notification\manager $phpbb_notifications */
		$phpbb_notifications = $this->container->get('notification_manager');
		$phpbb_notifications->purge_notifications('skouat.ppde.notification.type.admin_donation_errors');
	}
}
