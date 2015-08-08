<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */


namespace skouat\ppde\exception;

/**
 * OutOfBounds exception
 */
class out_of_bounds extends base
{
	/**
	 * Translate this exception
	 *
	 * @param \phpbb\user $user
	 *
	 * @return string
	 * @access public
	 */
	public function get_message(\phpbb\user $user)
	{
		return $this->translate_portions($user, $this->message_full, 'EXCEPTION_OUT_OF_BOUNDS');
	}
}
