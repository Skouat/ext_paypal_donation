<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\migrations\v40x;

/**
 * Removes duplicate/empty txn_id values so the UNIQUE index can be added.
 *
 * Such duplicates can only come from legacy IPN data (PayPal may resend the
 * same notification); the 4.0.0 webhook/capture flow has never run in production.
 */
class v400_m3_clean_duplicate_txn extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\skouat\ppde\migrations\v40x\v400_m2_remove_donation_errors_notification'];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'clean_duplicate_txn']]],
		];
	}

	/**
	 * Portable de-duplication (works on every DBMS supported by phpBB).
	 *
	 * @return void
	 */
	public function clean_duplicate_txn()
	{
		$table = $this->table_prefix . 'ppde_txn_log';

		// Make empty txn_id values unique (a UNIQUE index rejects several empty strings).
		$sql = 'SELECT transaction_id
				FROM ' . $table . "
				WHERE txn_id = ''";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$placeholder = 'EMPTY_' . (int) $row['transaction_id'];
			$this->db->sql_query('UPDATE ' . $table . "
				SET txn_id = '" . $this->db->sql_escape($placeholder) . "'
				WHERE transaction_id = " . (int) $row['transaction_id']);
		}
		$this->db->sql_freeresult($result);

		// Collapse genuine duplicates, keeping the oldest row per txn_id.
		$sql = 'SELECT txn_id, MIN(transaction_id) AS keep_id
				FROM ' . $table . '
				GROUP BY txn_id
				HAVING COUNT(transaction_id) > 1';
		$result = $this->db->sql_query($sql);
		$groups = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$groups[] = ['txn_id' => (string) $row['txn_id'], 'keep_id' => (int) $row['keep_id']];
		}
		$this->db->sql_freeresult($result);

		foreach ($groups as $group)
		{
			$this->db->sql_query('DELETE FROM ' . $table . "
				WHERE txn_id = '" . $this->db->sql_escape($group['txn_id']) . "'
					AND transaction_id <> " . $group['keep_id']);
		}
	}
}
