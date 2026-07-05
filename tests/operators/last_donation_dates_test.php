<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\tests\operators;

/**
 * @group database
 */
class last_donation_dates_test extends \phpbb_database_test_case
{
	/** @var \skouat\ppde\operators\transactions */
	protected $operator;
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	static protected function setup_extensions()
	{
		return ['skouat/ppde'];
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/last_donation_dates.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $table_prefix;
		$this->db = $this->new_dbal();
		$this->operator = new \skouat\ppde\operators\transactions($this->db, $table_prefix . 'ppde_txn_log');
	}

	/**
	 * The last donation must be the most recent by payment_date, NOT the one
	 * with the highest transaction_id (regression guard for point #3).
	 */
	public function test_last_donation_is_the_most_recent_by_date()
	{
		$sql_ary = $this->operator->sql_last_donations_ary([2]);
		$sql = $this->operator->build_sql_donorlist_data($sql_ary);

		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		// Exactly one row survives the anti-join for this (user, currency) group.
		$this->assertSame(1, (int) $row['transaction_id']);          // recent, smaller id
		$this->assertEquals(1700000200, (int) $row['payment_date']); // most recent date
		$this->assertEquals(10.0, (float) $row['mc_gross']);         // NOT 99 (the backdated one)
	}
}
