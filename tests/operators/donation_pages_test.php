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

class donation_pages_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\operators\donation_pages */
	protected $operator;

	protected function setUp(): void
	{
		parent::setUp();

		$db = $this->createMock(\phpbb\db\driver\driver_interface::class);

		$this->operator = new \skouat\ppde\operators\donation_pages($db, 'phpbb_ppde_donation_pages');
	}

	public function test_build_sql_data_all_pages_has_no_title_filter()
	{
		$sql = $this->operator->build_sql_data(2, 'all_pages');

		$this->assertStringContainsString('WHERE page_lang_id = 2', $sql);
		$this->assertStringContainsString('ORDER BY page_title', $sql);
		$this->assertStringNotContainsString('page_title =', $sql);
	}

	public function page_mode_data()
	{
		return [
			'body'    => ['body',    "AND page_title = 'donation_body'"],
			'cancel'  => ['cancel',  "AND page_title = 'donation_cancel'"],
			'success' => ['success', "AND page_title = 'donation_success'"],
		];
	}

	/** @dataProvider page_mode_data */
	public function test_build_sql_data_filters_by_mode($mode, $expected_clause)
	{
		$sql = $this->operator->build_sql_data(1, $mode);

		$this->assertStringContainsString('WHERE page_lang_id = 1', $sql);
		$this->assertStringContainsString($expected_clause, $sql);
	}

	public function test_build_sql_data_casts_lang_id()
	{
		$sql = $this->operator->build_sql_data('7abc', 'all_pages');

		$this->assertStringContainsString('WHERE page_lang_id = 7', $sql);
	}
}
