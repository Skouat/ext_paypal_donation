<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\tests\entity;

class transactions_execute_update_test extends \phpbb_test_case
{
	private function make_entity($db)
	{
		$language = $this->createMock(\phpbb\language\language::class);

		return new \skouat\ppde\entity\transactions($db, $language, 'phpbb_ppde_txn_log');
	}

	private function invoke($entity, string $sql): void
	{
		$m = new \ReflectionMethod($entity, 'execute_update');
		$m->setAccessible(true);
		$m->invoke($entity, $sql);
	}

	public function test_success_is_silent_and_restores_error_mode()
	{
		$db = $this->createMock(\phpbb\db\driver\driver_interface::class);
		$db->method('sql_query')->willReturn(true);
		// return_on_error toggled on, then back off.
		$db->expects($this->exactly(2))->method('sql_return_on_error');

		$this->invoke($this->make_entity($db), 'UPDATE x');
		$this->addToAssertionCount(1); // reached here => no exception
	}

	public function test_failure_throws_runtime_not_transaction_exception()
	{
		$db = $this->createMock(\phpbb\db\driver\driver_interface::class);
		$db->method('sql_query')->willReturn(false);
		$db->method('get_sql_error_returned')->willReturn([
			'message' => 'Data too long for column exchange_rate',
			'code'    => 1406,
		]);

		// RuntimeException (not transaction_exception) => webhook returns 500 => PayPal retries.
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('PPDE UPDATE failed');

		$this->invoke($this->make_entity($db), 'UPDATE x');
	}
}
