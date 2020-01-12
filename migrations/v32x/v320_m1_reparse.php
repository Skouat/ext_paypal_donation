<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\migrations\v32x;

class v320_m1_reparse extends \phpbb\db\migration\container_aware_migration
{
	/**
	 * @inheritDoc
	 */
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v31x\v310_m3_currency_data'];
	}

	/**
	 * @inheritDoc
	 */
	public function update_data()
	{
		return [
			['custom', [[$this, 'reparse']]],
		];
	}

	/**
	 * Run the ppde donation pages text reparser
	 *
	 * @param int $current A donation page identifier
	 *
	 * @return bool|int A donation page identifier or true if finished
	 */
	public function reparse($current = 0)
	{
		$reparser = new \skouat\ppde\textreparser\plugins\donation_pages_text(
			$this->db,
			$this->container->getParameter('core.table_prefix') . 'ppde_donation_pages'
		);

		if (empty($current))
		{
			$current = $reparser->get_max_id();
		}

		$limit = 50; // Lets keep the reparsing conservative
		$start = max(1, $current + 1 - $limit);
		$end   = max(1, $current);

		$reparser->reparse_range($start, $end);

		$current = $start - 1;

		return ($current === 0) ? true : $current;
	}
}
