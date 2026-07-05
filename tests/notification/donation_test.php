<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\tests\notification;

use skouat\ppde\notification\type\admin_donation_received;

class donation_test extends \phpbb_test_case
{
	public function test_get_item_id_casts_to_int()
	{
		$this->assertSame(42, admin_donation_received::get_item_id(['transaction_id' => '42']));
	}

	public function test_get_item_parent_id_is_zero()
	{
		$this->assertSame(0, admin_donation_received::get_item_parent_id(['transaction_id' => '42']));
	}
}
