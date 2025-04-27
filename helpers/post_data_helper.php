<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2025 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\helpers;

use phpbb\language\language;
use phpbb\request\request;

/**
 * Helper for handling post data
 */
class post_data_helper
{
	/** @var language */
	protected $language;

	/** @var request */
	protected $request;

	/**
	 * Constructor
	 *
	 * @param language $language Language object
	 * @param request  $request  Request object
	 */
	public function __construct(
		language $language,
		request $request
	)
	{
		$this->language = $language;
		$this->request = $request;
	}

	/**
	 * Get post data from request
	 *
	 * @param array $data_ary Data array
	 * @return array Post data
	 */
	public function get_post_data(array $data_ary): array
	{
		$name = $data_ary['name'];
		$default = $data_ary['default'];

		// Handle default value
		if (is_array($default))
		{
			$default = $default[0];
			$multibyte = $default[1] ?? false;
		}
		else
		{
			$multibyte = false;
		}

		// Get value from request
		$value = $this->request->variable($name, $default, $multibyte);

		return [
			'name'            => $name,
			'value'           => $value,
			'condition_check' => $data_ary['condition_check'] ?? [],
			'force_settings'  => $data_ary['force_settings'] ?? [],
			'txn_errors'      => '',
		];
	}

	/**
	 * Check post data against conditions
	 *
	 * @param array $post_data Post data
	 * @return array Checked post data
	 */
	public function check_post_data(array $post_data): array
	{
		if (empty($post_data['condition_check']))
		{
			return $post_data;
		}

		foreach ($post_data['condition_check'] as $check_type => $check_value)
		{
			$post_data = $this->apply_check($post_data, $check_type, $check_value);
		}

		return $post_data;
	}

	/**
	 * Apply check to post data
	 *
	 * @param array  $post_data   Post data
	 * @param string $check_type  Check type
	 * @param mixed  $check_value Check value
	 * @return array Updated post data
	 */
	private function apply_check(array $post_data, string $check_type, $check_value): array
	{
		switch ($check_type)
		{
			case 'ascii':
				if ($check_value && !$this->is_ascii($post_data['value']))
				{
					$post_data['txn_errors'] .= '<br>' . $this->language->lang('INVALID_TXN_ASCII', $post_data['name']);
				}
			break;

			case 'content':
				if (!in_array($post_data['value'], $check_value, true))
				{
					$post_data['txn_errors'] .= '<br>' . $this->language->lang('INVALID_TXN_CONTENT', $post_data['name']);
				}
			break;

			case 'empty':
				if ($check_value === false && empty($post_data['value']))
				{
					$post_data['txn_errors'] .= '<br>' . $this->language->lang('INVALID_TXN_EMPTY', $post_data['name']);
				}
			break;

			case 'length':
				$post_data = $this->check_length($post_data, $check_value);
			break;
		}

		return $post_data;
	}

	/**
	 * Check if string contains only ASCII characters
	 *
	 * @param string $string String to check
	 * @return bool Whether string contains only ASCII characters
	 */
	private function is_ascii(string $string): bool
	{
		return (bool) preg_match('/^[\x00-\x7F]*$/', $string);
	}

	/**
	 * Check length of value
	 *
	 * @param array $post_data   Post data
	 * @param array $check_value Check value
	 * @return array Updated post data
	 */
	private function check_length(array $post_data, array $check_value): array
	{
		$value_length = utf8_strlen($post_data['value']);
		$expected_length = $check_value['value'];
		$operator = $check_value['operator'] ?? '==';

		$valid = false;
		switch ($operator)
		{
			case '==':
				$valid = $value_length == $expected_length;
			break;
			case '<=':
				$valid = $value_length <= $expected_length;
			break;
			case '>=':
				$valid = $value_length >= $expected_length;
			break;
		}

		if (!$valid)
		{
			$post_data['txn_errors'] .= '<br>' . $this->language->lang('INVALID_TXN_LENGTH', $post_data['name']);
		}

		return $post_data;
	}

	/**
	 * Set function to apply to post data
	 *
	 * @param array $post_data Post data
	 * @return mixed Modified value
	 */
	public function set_func(array $post_data)
	{
		$value = $post_data['value'];

		foreach ($post_data['force_settings'] as $func => $func_value)
		{
			switch ($func)
			{
				case 'length':
					$value = utf8_substr($value, 0, (int) $func_value);
				break;

				case 'lowercase':
					$value = utf8_strtolower($value);
				break;

				case 'strtotime':
					$value = strtotime($value);
				break;
			}
		}

		return $value;
	}
}
