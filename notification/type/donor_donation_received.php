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
class donor_donation_received extends donation
{
	/**
	 * {@inheritdoc}
	 */
	public static $notification_option = [
		'lang'  => 'NOTIFICATION_TYPE_PPDE_DONATION_RECEIVED',
		'group' => 'NOTIFICATION_GROUP_MISCELLANEOUS',
	];

	/**
	 * {@inheritdoc}
	 */
	public function get_type()
	{
		return 'skouat.ppde.notification.type.donor_donation_received';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_available()
	{
		return ($this->auth->acl_get('u_ppde_use') && $this->config['ppde_enable'] && $this->config['ppde_ipn_enable'] && $this->config['ppde_ipn_notification_enable']);
	}

	/**
	 * {@inheritdoc}
	 */
	public function find_users_for_notification($data, $options = [])
	{
		$options = array_merge([
			'ignore_users' => [],
		], $options);

		// Grab members that have permission to use extension.
		$donor_ary = $this->auth->acl_get_list($data['user_from'], 'u_ppde_use', false);
		$users = (!empty($donor_ary[0]['u_ppde_use'])) ? $donor_ary[0]['u_ppde_use'] : [];

		if (empty($users))
		{
			return [];
		}

		sort($users);

		return $this->check_user_notification_options($users, $options);
	}

	/**
	 * {@inheritdoc}
	 */

	public function get_title()
	{
		$mc_gross = $this->get_data('mc_gross');

		return $this->language->lang('NOTIFICATION_PPDE_DONOR_DONATION_RECEIVED', $mc_gross);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_email_template()
	{
		return '@skouat_ppde/donor_donation_received';
	}
}
