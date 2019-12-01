<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\notification\type;

/**
 * PayPal Donation notifications class
 * This class handles notifications for Admin received donation
 */
class admin_donation_errors extends \phpbb\notification\type\base
{
	/**
	 * {@inheritdoc}
	 */
	public static $notification_option = [
		'lang'  => 'NOTIFICATION_TYPE_PPDE_ADMIN_DONATION_ERRORS',
		'group' => 'NOTIFICATION_GROUP_ADMINISTRATION',
	];
	/** @var \phpbb\config\config */
	protected $config;
	/** @var \phpbb\user_loader */
	protected $user_loader;

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
	public function get_type()
	{
		return 'skouat.ppde.notification.type.admin_donation_errors';
	}

	public function set_config(\phpbb\config\config $config)
	{
		$this->config = $config;
	}

	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
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
	public function find_users_for_notification($data, $options = [])
	{
		$options = array_merge([
			'ignore_users' => [],
		], $options);

		// Grab admins that have permission to administer extension.
		$admin_ary = $this->auth->acl_get_list(false, 'a_ppde_manage', false);
		$users = (!empty($admin_ary[0]['a_ppde_manage'])) ? $admin_ary[0]['a_ppde_manage'] : [];

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
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('user_from'), false, true);
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
		return [
			'PAYER_EMAIL'    => htmlspecialchars_decode($this->get_data('payer_email')),
			'PAYER_USERNAME' => $this->get_data('payer_username'),
			'TXN_ERRORS'     => $this->get_data('txn_errors'),
			'TXN_ID'         => $this->get_data('txn_id'),
		];
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
		return [$this->get_data('user_from')];
	}

	/**
	 * {@inheritdoc}
	 */
	public function create_insert_array($data, $pre_create_data = [])
	{
		$this->set_data('payer_email', $data['payer_email']);
		$this->set_data('payer_username', $data['payer_username']);
		$this->set_data('transaction_id', $data['transaction_id']);
		$this->set_data('txn_errors', $data['txn_errors']);
		$this->set_data('txn_id', $data['txn_id']);

		parent::create_insert_array($data, $pre_create_data);
	}
}
