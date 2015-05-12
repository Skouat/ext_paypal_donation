<?php
/**
*
* PayPal Donation extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Skouat
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace skouat\ppde\controller;

class admin_currency_controller implements admin_currency_interface
{
	protected $u_action;

	protected $ppde_operator_currency;
	protected $template;

	/**
	* Constructor
	*
	* @param \skouat\ppde\operators\currency  $ppde_operator_currency    Operator object
	* @param \phpbb\template\template               $template           Template object
	* @access public
	*/
	public function __construct(\skouat\ppde\operators\currency $ppde_operator_currency, \phpbb\template\template $template)
	{
		$this->ppde_operator_currency = $ppde_operator_currency;
		$this->template = $template;
	}

	/**
	* Display the currency list
	*
	* @return null
	* @access public
	*/
	public function display_currency()
	{
		// Grab all the pages from the db
		$entities = $this->ppde_operator_currency->get_currency_data();

		foreach ($entities as $row)
		{
			// Do not treat the item whether language identifier does not match
			$this->template->assign_block_vars('currency', array(
				'CURRENCY_NAME'		=> $row['currency_name'],
				'CURRENCY_ENABLED'	=> $row['currency_enable'] ? true : false,

				'U_ENABLE'			=> $this->u_action . '&amp;action=enable&amp;currency_id=' . $row['currency_id'],
				'U_DISABLE'			=> $this->u_action . '&amp;action=disable&amp;currency_id=' . $row['currency_id'],
				'U_MOVE_UP'			=> $this->u_action . '&amp;action=move_up&amp;currency_id=' . $row['currency_id'],
				'U_MOVE_DOWN'		=> $this->u_action . '&amp;action=move_down&amp;currency_id=' . $row['currency_id'],
				'U_EDIT'			=> $this->u_action . '&amp;action=edit&amp;currency_id=' . $row['currency_id'],
				'U_DELETE'			=> $this->u_action . '&amp;action=delete&amp;currency_id=' . $row['currency_id'],
			));
		}

		unset($entities, $page);

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'U_ACTION'		=> $this->u_action,
		));
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
