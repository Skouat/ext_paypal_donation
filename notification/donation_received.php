<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\notification;

/**
 * PayPal Donation notifications class
 * This class handles notifications for Admin received donation
 */

abstract class donation_received extends \phpbb\notification\type\base
{
	/**
	 * {@inheritdoc}
	 */
	public static $notification_option = [];
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
		return '';
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
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('user_from'), false, true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_email_template_variables()
	{
		return [
			'MC_GROSS'       => html_entity_decode($this->get_data('mc_gross'), ENT_COMPAT | ENT_HTML5, 'UTF-8'),
			'NET_AMOUNT'     => html_entity_decode($this->get_data('net_amount'), ENT_COMPAT | ENT_HTML5, 'UTF-8'),
			'PAYER_EMAIL'    => htmlspecialchars_decode($this->get_data('payer_email')),
			'PAYER_USERNAME' => $this->get_data('payer_username'),
			'SETTLE_AMOUNT'  => html_entity_decode($this->get_data('settle_amount'), ENT_COMPAT | ENT_HTML5, 'UTF-8'),
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
		$this->set_data('mc_gross', $data['mc_gross']);
		$this->set_data('net_amount', $data['net_amount']);
		$this->set_data('payer_email', $data['payer_email']);
		$this->set_data('payer_username', $data['payer_username']);
		$this->set_data('settle_amount', $data['settle_amount']);
		$this->set_data('transaction_id', $data['transaction_id']);
		$this->set_data('txn_id', $data['txn_id']);
		$this->set_data('user_from', $data['user_from']);

		parent::create_insert_array($data, $pre_create_data);
	}
}
