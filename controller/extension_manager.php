<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\controller;

class extension_manager extends \phpbb\extension\manager
{
	/** Extension name  */
	protected const EXT_NAME = 'skouat/ppde';
	protected $ext_meta;

	/**
	 * Get extension metadata
	 *
	 * @return array
	 * @access public
	 */
	public function get_ext_meta(): array
	{
		return empty($this->ext_meta) ? $this->load_metadata() : $this->ext_meta;
	}

	/**
	 * Load metadata for this extension
	 *
	 * @return array
	 * @access private
	 */
	private function load_metadata(): array
	{
		$md_manager = $this->create_extension_metadata_manager($this::EXT_NAME);

		try
		{
			$this->ext_meta = $md_manager->get_metadata('all');
		}
		catch (\phpbb\extension\exception $e)
		{
			trigger_error($e, E_USER_WARNING);
		}

		return $this->ext_meta;
	}
}
