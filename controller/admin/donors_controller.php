<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller\admin;

use phpbb\template\template;
use phpbb\user;
use phpbb\user_loader;

/**
 * @property template template           Template object
 * @property user     user               User object.
 */
class donors_controller extends admin_main
{
	protected $ppde_actions_currency;
	protected $ppde_entity_transactions;
	protected $ppde_operator_transactions;
	protected $user_loader;

	/**
	 * Constructor
	 *
	 * @param \skouat\ppde\actions\currency       $ppde_actions_currency      Currency actions object
	 * @param \skouat\ppde\entity\transactions    $ppde_entity_transactions   Transactions entity object
	 * @param \skouat\ppde\operators\transactions $ppde_operator_transactions Transactions operators object
	 * @param template                            $template                   Template object
	 * @param user                                $user                       User object.
	 * @param \phpbb\user_loader                  $user_loader                User loader object
	 * @access public
	 */
	public function __construct(
		\skouat\ppde\actions\currency $ppde_actions_currency,
		\skouat\ppde\entity\transactions $ppde_entity_transactions,
		\skouat\ppde\operators\transactions $ppde_operator_transactions,
		template $template,
		user $user,
		user_loader $user_loader
	)
	{
		$this->ppde_actions_currency = $ppde_actions_currency;
		$this->ppde_entity_transactions = $ppde_entity_transactions;
		$this->ppde_operator_transactions = $ppde_operator_transactions;
		$this->template = $template;
		$this->user = $user;
		$this->user_loader = $user_loader;
		parent::__construct(
			'donors',
			'PPDE_DD',
			'donor'
		);
	}

	/**
	 * Display the transactions list
	 *
	 * @param string $id     Module id
	 * @param string $mode   Module categorie
	 * @param string $action Action name
	 *
	 * @return void
	 * @access public
	 */
	public function display_donors($id, $mode, $action)
	{
		// Adds fields to the table schema needed by entity->import()
		$donorlist_table_schema = [
			'item_amount'      => ['name' => 'amount', 'type' => 'float'],
			'item_max_txn_id'  => ['name' => 'max_txn_id', 'type' => 'integer'],
			'item_user_id'     => ['name' => 'user_id', 'type' => 'integer'],
			'item_mc_currency' => ['name' => 'mc_currency', 'type' => 'string'],
		];

		$sql_donorlist_ary = $this->ppde_operator_transactions->sql_donorlist_ary(true);
		$data_ary = $this->ppde_entity_transactions->get_data($this->ppde_operator_transactions->build_sql_donorlist_data($sql_donorlist_ary), $donorlist_table_schema, 0, 0, true);

		// Adds fields to the table schema needed by entity->import()
		$last_donation_table_schema = [
			'item_payment_date' => ['name' => 'payment_date', 'type' => 'integer'],
			'item_mc_gross'     => ['name' => 'mc_gross', 'type' => 'float'],
			'item_mc_currency'  => ['name' => 'mc_currency', 'type' => 'string'],
		];

		foreach ($data_ary as $data)
		{
			$get_last_transaction_sql_ary = $this->ppde_operator_transactions->sql_last_donation_ary($data['max_txn_id']);
			$last_donation_data = $this->ppde_entity_transactions->get_data($this->ppde_operator_transactions->build_sql_donorlist_data($get_last_transaction_sql_ary), $last_donation_table_schema, 0, 0, true);
			$currency_mc_data = $this->ppde_actions_currency->get_currency_data($last_donation_data[0]['mc_currency']);
			$this->template->assign_block_vars('donors', [
				'PPDE_DONOR_USERNAME'      => $this->user_loader->get_username($data['user_id'], 'full', false, false, true),
				'PPDE_LAST_DONATED_AMOUNT' => $this->ppde_actions_currency->format_currency((float) $last_donation_data[0]['mc_gross'], $currency_mc_data[0]['currency_iso_code'], $currency_mc_data[0]['currency_symbol'], (bool) $currency_mc_data[0]['currency_on_left']),
				'PPDE_LAST_PAYMENT_DATE'   => $this->user->format_date($last_donation_data[0]['payment_date']),
			]);
		}
	}
}
