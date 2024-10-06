<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2024 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\notification\type;

use skouat\ppde\notification\donation;

/**
 * PayPal Donation notifications class
 * This class handles notifications for Admin received donation errors
 */
class admin_donation_errors extends donation
{
	/**
	 * {@inheritdoc}
	 */
	public static $notification_option = [
		'lang'  => 'NOTIFICATION_TYPE_PPDE_ADMIN_DONATION_ERRORS',
		'group' => 'NOTIFICATION_GROUP_ADMINISTRATION',
	];

	/**
	 * {@inheritdoc}
	 */
	public function get_type()
	{
		return 'skouat.ppde.notification.type.admin_donation_errors';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_available()
	{
		return $this->auth->acl_get('a_ppde_manage') && $this->config['ppde_enable'] && $this->config['ppde_ipn_enable'] && $this->config['ppde_ipn_notification_enable'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_title()
	{
		$username = $this->user_loader->get_username($this->get_data('user_from'), 'no_profile');

		return $this->language->lang('NOTIFICATION_PPDE_ADMIN_DONATION_ERRORS', $username);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_email_template()
	{
		return '@skouat_ppde/admin_donation_errors';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_email_template_variables()
	{
		$variables = parent::get_email_template_variables();
		$variables['TXN_ERRORS'] = $this->get_data('txn_errors');
		return $variables;
	}

	/**
	 * {@inheritdoc}
	 */
	public function create_insert_array($data, $pre_create_data = [])
	{
		parent::create_insert_array($data, $pre_create_data);
		$this->set_data('txn_errors', $data['txn_errors']);
	}
}
