<?php
/**
*
* PayPal Donation extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Skouat
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace skouat\ppde\entity;

/**
* Entity for a donation page
*/
class currency implements currency_interface
{
	/**
	* Data for this entity
	*
	* @var array
	*	currency_id
	*	currency_name
	*	currency_iso_code
	*	currency_symbol
	*	currency_enable
	*	currency_left_id
	*	currency_right_id
	* @access protected
	*/
	protected $currency_data;
	protected $u_action;

	protected $user;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface    $db                Database object
	* @param \phpbb\user                          $user              User object
	* @param string                               $currency_table    Name of the table used to store data
	* @access public
	*/
	public function __construct(\phpbb\user $user)
	{
		$this->user = $user;
	}

	/**
	* Import and validate data for currency
	*
	* Used when the data is already loaded externally.
	* Any existing data on this page is over-written.
	* All data is validated and an exception is thrown if any data is invalid.
	*
	* @param  array $data Data array, typically from the database
	* @return currency_interface $this->currency_data object
	* @access public
	*/
	public function import($data)
	{
		// Clear out any saved data
		$this->currency_data = array();

		// All of our fields
		$fields = array(
			// column			=> data type (see settype())
			'currency_id'		=> 'integer',
			'currency_name'		=> 'string',
			'currency_iso_code'	=> 'string',
			'currency_symbol'	=> 'string',
			'currency_enable'	=> 'boolean',
			'currency_left_id'	=> 'string',
			'currency_right_id'	=> 'integer',
		);

		// Go through the basic fields and set them to our data array
		foreach ($fields as $field => $type)
		{
			// If the data wasn't sent to us, throw an exception
			if (!isset($data[$field]))
			{
				$this->display_error_message('PPDE_FIELD_MISSING');
			}

			// settype passes values by reference
			$value = $data[$field];

			// We're using settype to enforce data types
			settype($value, $type);

			$this->currency_data[$field] = $value;
			$this->currency_data[$field] = $value;
		}

		return $this->currency_data;
	}

	/**
	 * Display Error message
	 *
	 * @param string $lang_key
	 * @return null
	 * @access protected
	 */
	protected function display_error_message($lang_key)
	{
		$message = call_user_func_array(array($this->user, 'lang'), array_merge(array(strtoupper($lang_key)))) . adm_back_link($this->u_action);
		trigger_error($message, E_USER_WARNING);
	}

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
