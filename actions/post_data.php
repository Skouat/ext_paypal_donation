<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2021 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\actions;

use phpbb\language\language;
use phpbb\request\request;
use skouat\ppde\operators\compare;

class post_data
{
	private const ASCII_RANGE = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	/**
	 * Services properties declaration
	 */
	protected $language;
	protected $ppde_operator_compare;
	protected $request;

	/**
	 * Constructor
	 *
	 * @param language $language              Language object
	 * @param compare  $ppde_operator_compare Compare operator object
	 * @param request  $request               Request object
	 * @access public
	 */
	public function __construct(language $language, compare $ppde_operator_compare, request $request)
	{
		$this->language = $language;
		$this->ppde_operator_compare = $ppde_operator_compare;
		$this->request = $request;
	}

	/**
	 * Check requirements for data value.
	 *
	 * @param array $data_ary
	 *
	 * @return mixed
	 * @access public
	 */
	public function set_func($data_ary)
	{
		$value = $data_ary['value'];

		foreach ($data_ary['force_settings'] as $control_point => $params)
		{
			// Calling the set_post_data_function
			$value = call_user_func_array([$this, 'set_post_data_' . $control_point], [$data_ary['value'], $params]);
		}
		unset($control_point);

		return $value;
	}

	/**
	 * Request predefined variable from super global
	 *
	 * @param array $data_ary List of data to request
	 *
	 * @return array
	 * @access public
	 */
	public function get_post_data($data_ary = []): array
	{
		// Request variables
		if (is_array($data_ary['default']))
		{
			$data_ary['value'] = $this->request->variable($data_ary['name'], (string) $data_ary['default'][0], (bool) $data_ary['default'][1]);
		}
		else
		{
			$data_ary['value'] = $this->request->variable($data_ary['name'], $data_ary['default']);
		}

		return $data_ary;
	}

	/**
	 * Check if some settings are valid.
	 *
	 * @param array $data_ary
	 *
	 * @return array
	 * @access public
	 */
	public function check_post_data($data_ary = []): array
	{
		$data_ary['txn_errors'] = '';
		// Check all conditions declared for this post_data
		if (isset($data_ary['condition_check']))
		{
			$check = $this->call_func($data_ary);
			$data_ary['txn_errors'] .= $check['txn_errors'];
			unset($check['txn_errors']);
			$data_ary['condition_checked'] = (bool) array_product($check);
		}

		return $data_ary;
	}

	/**
	 * Check requirements for data value.
	 * If a check fails, error message are stored in $this->error_message
	 *
	 * @param array $data_ary
	 *
	 * @return array
	 * @access public
	 */

	public function call_func($data_ary): array
	{
		$check = [];
		$check['txn_errors'] = '';

		foreach ($data_ary['condition_check'] as $control_point => $params)
		{
			// Calling the check_post_data_function
			if (call_user_func_array([$this, 'check_post_data_' . $control_point], [$data_ary['value'], $params]))
			{
				$check[] = true;
				continue;
			}

			$check['txn_errors'] .= '<br>' . $this->language->lang('INVALID_TXN_' . strtoupper($control_point), $data_ary['name']);
			$check[] = false;
		}
		unset($control_point);

		return $check;
	}

	/**
	 * Check Post data length.
	 * Called by $this->check_post_data() method
	 *
	 * @param string $value
	 * @param array  $statement
	 *
	 * @return bool
	 * @access public
	 */
	public function check_post_data_length($value, $statement): bool
	{
		return $this->ppde_operator_compare->compare_value(strlen($value), $statement['value'], $statement['operator']);
	}

	/**
	 * Check if parsed value contains only ASCII chars.
	 * Return false if it contains non ASCII chars.
	 *
	 * @param string $value
	 *
	 * @return bool
	 * @access public
	 */
	public function check_post_data_ascii($value): bool
	{
		return strlen($value) === strspn($value, self::ASCII_RANGE);
	}

	/**
	 * Check Post data content based on an array list.
	 * Called by $this->check_post_data() method
	 *
	 * @param string $value
	 * @param array  $content_ary
	 *
	 * @return bool
	 * @access public
	 */
	public function check_post_data_content($value, $content_ary): bool
	{
		return in_array($value, $content_ary);
	}

	/**
	 * Check if Post data is empty.
	 * Called by $this->check_post_data() method
	 *
	 * @param string $value
	 *
	 * @return bool
	 * @access public
	 */
	public function check_post_data_empty($value): bool
	{
		return !empty($value);
	}

	/**
	 * Set Post data length.
	 * Called by $this->set_post_data() method
	 *
	 * @param string  $value
	 * @param integer $length
	 *
	 * @return string
	 * @access public
	 */
	public function set_post_data_length($value, $length): string
	{
		return substr($value, 0, (int) $length);
	}

	/**
	 * Set Post data to lowercase.
	 * Called by $this->set_post_data() method
	 *
	 * @param string $value
	 * @param bool   $force
	 *
	 * @return string
	 * @access public
	 */
	public function set_post_data_lowercase($value, $force = false): string
	{
		return $force ? strtolower($value) : $value;
	}

	/**
	 * Set Post data to date/time format.
	 * Called by $this->set_post_data() method
	 *
	 * @param string $value
	 * @param bool   $force
	 *
	 * @return string
	 * @access public
	 */
	public function set_post_data_strtotime($value, $force = false): string
	{
		return $force ? strtotime($value) : $value;
	}
}
