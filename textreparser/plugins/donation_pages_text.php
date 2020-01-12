<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\textreparser\plugins;

class donation_pages_text extends \phpbb\textreparser\row_based_plugin
{
	/**
	 * {@inheritdoc}
	 */
	public function get_columns()
	{
		return [
			'id'         => 'page_id',
			'text'       => 'page_content',
			'bbcode_uid' => 'page_content_bbcode_uid',
			'options'    => 'page_content_bbcode_options',
		];
	}
}
