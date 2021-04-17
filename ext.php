<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde;

/**
 * Extension class for custom enable/disable/purge actions
 *
 * NOTE TO EXTENSION DEVELOPERS:
 * Normally it is not necessary to define any functions inside the ext class below.
 * The ext class may contain special (un)installation commands in the methods
 * enable_step(), disable_step() and purge_step(). As it is, these methods are defined
 * in phpbb_extension_base, which this class extends, but you can overwrite them to
 * give special instructions for those cases.
 */
class ext extends \phpbb\extension\base
{
	/**
	 * Check whether or not the extension can be enabled.
	 * The current phpBB version should meet or exceed
	 * the minimum version required by this extension:
	 *
	 * Requires phpBB 3.3.0 and PHP 7.1.3
	 *
	 * @return bool
	 * @access public
	 */
	public function is_enableable()
	{
		$config = $this->container->get('config');

		return phpbb_version_compare($config['version'], '3.3.0', '>=') && PHP_VERSION_ID >= 70103;
	}

	/**
	 * Overwrite enable_step to enable extension notifications before any included migrations are installed.
	 *
	 * @param mixed $old_state State returned by previous call of this method
	 *
	 * @return bool|string Returns false after last step, otherwise temporary state
	 * @access public
	 */
	public function enable_step($old_state)
	{
		// Empty means nothing has run yet
		if ($old_state === '')
		{
			// Enable notifications
			return $this->notification_handler('enable', $this->notification_types());
		}
		// Run parent enable step method
		return parent::enable_step($old_state);
	}

	/**
	 * Overwrite disable_step to disable extension notifications before the extension is disabled.
	 *
	 * @param mixed $old_state State returned by previous call of this method
	 *
	 * @return false|string Returns false after last step, otherwise temporary state
	 * @access public
	 */
	public function disable_step($old_state)
	{
		// Empty means nothing has run yet
		if ($old_state === '')
		{
			// Disable notifications
			return $this->notification_handler('disable', $this->notification_types());
		}
		// Run parent disable step method
		return parent::disable_step($old_state);
	}

	/**
	 * Overwrite purge_step to purge extension notifications before any included and installed migrations are reverted.
	 *
	 * @param mixed $old_state State returned by previous call of this method
	 *
	 * @return bool|string Returns false after last step, otherwise temporary state
	 * @access public
	 */
	public function purge_step($old_state)
	{
		// Empty means nothing has run yet
		if ($old_state === '')
		{
			// Purge notifications
			return $this->notification_handler('purge', $this->notification_types());
		}
		// Run parent purge step method
		return parent::purge_step($old_state);
	}

	/**
	 * Notification handler to call notification enable/disable/purge steps
	 *
	 * @param string $step               The step (enable, disable, purge)
	 * @param array  $notification_types The notification type names
	 *
	 * @return string Return notifications as temporary state
	 * @access        protected
	 * @author        VSEphpbb (Matt Friedman)
	 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
	 * @license       GNU General Public License, version 2 (GPL-2.0)
	 */
	protected function notification_handler($step, $notification_types): string
	{
		/** @type \phpbb\notification\manager $phpbb_notifications */
		$phpbb_notifications = $this->container->get('notification_manager');

		foreach ($notification_types as $notification_type)
		{
			call_user_func([$phpbb_notifications, $step . '_notifications'], $notification_type);
		}

		return 'notifications';
	}

	/**
	 * Returns the list of notification types
	 *
	 * @return array
	 * @access protected
	 */
	protected function notification_types(): array
	{
		return [
			'skouat.ppde.notification.type.admin_donation_errors',
			'skouat.ppde.notification.type.admin_donation_received',
			'skouat.ppde.notification.type.donor_donation_received',
		];
	}
}
