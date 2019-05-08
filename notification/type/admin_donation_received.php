<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\notification\type;

use skouat\ppde\notification\donation_received;

/**
 * PayPal Donation notifications class
 * This class handles notifications for Admin received donation
 */
class admin_donation_received extends donation_received
{
	/**
	 * {@inheritdoc}
	 */
	public static $notification_option = array(
		'lang'  => 'NOTIFICATION_TYPE_PPDE_ADMIN_DONATION_RECEIVED',
		'group' => 'NOTIFICATION_GROUP_ADMINISTRATION',
	);

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
	public function find_users_for_notification($data, $options = array())
	{
		$options = array_merge(array(
			'ignore_users' => array(),
		), $options);

		// Grab admins that have permission to administer extension.
		$admin_ary = $this->auth->acl_get_list(false, 'a_ppde_manage', false);
		$users = (!empty($admin_ary[0]['a_ppde_manage'])) ? $admin_ary[0]['a_ppde_manage'] : array();

		if (empty($users))
		{
			return array();
		}

		sort($users);

		return $this->check_user_notification_options($users, $options);
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

	/**
	 * {@inheritdoc}
	 */
	public function get_url()
	{
		return '';
	}
}
