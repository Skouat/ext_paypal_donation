<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\notification\type;

use skouat\ppde\notification\donation;

/**
 * PayPal Donation notifications class
 * This class handles notifications for Admin received donation
 */
class admin_donation_received extends donation
{
	/**
	 * {@inheritdoc}
	 */
	public static $notification_option = [
		'lang'  => 'NOTIFICATION_TYPE_PPDE_ADMIN_DONATION_RECEIVED',
		'group' => 'NOTIFICATION_GROUP_ADMINISTRATION',
	];

	/**
	 * {@inheritdoc}
	 */
	public function get_type()
	{
		return 'skouat.ppde.notification.type.admin_donation_received';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_available()
	{
		return ($this->auth->acl_get('a_ppde_manage') && $this->config['ppde_enable'] && $this->config['ppde_ipn_enable'] && $this->config['ppde_ipn_notification_enable']);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_title()
	{
		$username = $this->user_loader->get_username($this->get_data('user_from'), 'no_profile');
		$mc_gross = $this->get_data('mc_gross');

		return $this->language->lang('NOTIFICATION_PPDE_ADMIN_DONATION_RECEIVED', $username, $mc_gross);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_email_template()
	{
		return '@skouat_ppde/admin_donation_received';
	}
}
