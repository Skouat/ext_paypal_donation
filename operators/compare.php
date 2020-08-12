<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\operators;

class compare
{
	/** @var array */
	private static $operators_table = [
		'<'   => 'compare_lt',
		'<='  => 'compare_lte',
		'=='  => 'compare_eq',
		'===' => 'compare_id',
		'>='  => 'compare_gte',
		'>'   => 'compare_gt',
		'<>'  => 'compare_diff',
		'!='  => 'compare_not_eq',
		'!==' => 'compare_not_id',
	];

	/**
	 * Compare two value
	 *
	 * @param int    $value1
	 * @param int    $value2
	 * @param string $operator
	 *
	 * @return bool
	 * @access public
	 */
	public function compare_value($value1, $value2, $operator)
	{
		if (array_key_exists($operator, self::$operators_table))
		{
			return call_user_func_array([$this, self::$operators_table[$operator]], [$value1, $value2]);
		}

		return false;
	}

	/**
	 * Method called by $this->compare_value
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return bool
	 * @access private
	 */
	private function compare_lt($a, $b)
	{
		return $a < $b;
	}

	/**
	 * Method called by $this->compare_value
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return bool
	 * @access private
	 */
	private function compare_lte($a, $b)
	{
		return $a <= $b;
	}

	/**
	 * Method called by $this->compare_value
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return bool
	 * @access private
	 */
	private function compare_eq($a, $b)
	{
		return $a == $b;
	}

	/**
	 * Method called by $this->compare_value
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return bool
	 * @access private
	 */
	private function compare_id($a, $b)
	{
		return $a === $b;
	}

	/**
	 * Method called by $this->compare_value
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return bool
	 * @access private
	 */
	private function compare_gte($a, $b)
	{
		return $a >= $b;
	}

	/**
	 * Method called by $this->compare_value
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return bool
	 * @access private
	 */
	private function compare_gt($a, $b)
	{
		return $a > $b;
	}

	/**
	 * Method called by $this->compare_value
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return bool
	 * @access private
	 */
	private function compare_diff($a, $b)
	{
		return $a <> $b;
	}

	/**
	 * Method called by $this->compare_value
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return bool
	 * @access private
	 */
	private function compare_not_eq($a, $b)
	{
		return $a != $b;
	}

	/**
	 * Method called by $this->compare_value
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return bool
	 * @access private
	 */
	private function compare_not_id($a, $b)
	{
		return $a !== $b;
	}
}
