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

/**
 * PayPal Donation notifications class
 * This class handles notifications for Admin received donation
 */

class donor_donation_received extends \phpbb\notification\type\base
{
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
	public static $notification_option = array(
		'lang'  => 'NOTIFICATION_TYPE_PPDE_DONATION_RECEIVED',
		'group' => 'NOTIFICATION_GROUP_MISCELLANEOUS',
	);

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
	public static function get_item_id($data)
	{
		return (int) $data['transaction_id'];
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_item_parent_id($data)
	{
		// No parent
		return 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function find_users_for_notification($data, $options = array())
	{
		$options = array_merge(array(
			'ignore_users' => array(),
		), $options);

		// Grab members that have permission to use extension.
		$donor_ary = $this->auth->acl_get_list($data['user_from'], 'u_ppde_use', false);
		$users = (!empty($donor_ary[0]['u_ppde_use'])) ? $donor_ary[0]['u_ppde_use'] : array();

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
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('user_from'), false, true);
	}

	/**
	 * {@inheritdoc}
	 */

	public function get_title()
	{
		$mc_gross = $this->get_data('mc_gross');

		return $this->user->lang('NOTIFICATION_PPDE_DONOR_DONATION_RECEIVED', $mc_gross);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_email_template()
	{
		return '@skouat_ppde/donor_donation_received';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_email_template_variables()
	{
		return array(
			'MC_GROSS'       => html_entity_decode($this->get_data('mc_gross'), ENT_COMPAT | ENT_HTML5, 'UTF-8'),
			'NET_AMOUNT'     => html_entity_decode($this->get_data('net_amount'), ENT_COMPAT | ENT_HTML5, 'UTF-8'),
			'PAYER_USERNAME' => $this->get_data('payer_username'),
			'PAYER_EMAIL'    => htmlspecialchars_decode($this->get_data('payer_email')),
			'SETTLE_AMOUNT'  => html_entity_decode($this->get_data('settle_amount'), ENT_COMPAT | ENT_HTML5, 'UTF-8'),
			'TXN_ID'         => $this->get_data('txn_id'),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_url()
	{
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function users_to_query()
	{
		return array($this->get_data('user_from'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function create_insert_array($data, $pre_create_data = array())
	{
		$this->set_data('mc_gross', $data['mc_gross']);
		$this->set_data('net_amount', $data['net_amount']);
		$this->set_data('payer_username', $data['payer_username']);
		$this->set_data('payer_email', $data['payer_email']);
		$this->set_data('settle_amount', $data['settle_amount']);
		$this->set_data('transaction_id', $data['transaction_id']);
		$this->set_data('txn_id', $data['txn_id']);
		$this->set_data('user_from', $data['user_from']);

		return parent::create_insert_array($data, $pre_create_data);
	}
}
