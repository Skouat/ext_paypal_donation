<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\actions;

use Locale;
use phpbb\config\config;
use phpbb\template\template;
use phpbb\user;
use ResourceBundle;

class locale_icu
{
	protected $config;
	protected $template;
	protected $user;

	/**
	 * currency constructor.
	 *
	 * @param config   $config
	 * @param template $template Template object
	 * @param user     $user     User object
	 *
	 * @access public
	 */

	public function __construct(config $config, template $template, user $user)
	{
		$this->config = $config;
		$this->template = $template;
		$this->user = $user;
	}

	public function build_locale_select_menu($config_value = '')
	{
		// Grab the list of all available locales
		$locale_items = ResourceBundle::getLocales('');
		natsort($locale_items);

		// Process each locale item for pull-down
		foreach ($locale_items as $locale)
		{
			// Set output block vars for display in the template
			$this->template->assign_block_vars('locale_options', array(

				'LOCALE_ID'        => $locale,
				'LOCALE_NAME'      => Locale::getDisplayName($locale, $this->user->lang_name),
				'S_LOCALE_DEFAULT' => $config_value === $locale,
			));
		}
		unset ($currency_items, $currency_item);
	}

	/**
	 * Checks if the PHP PECL intl extension is fully available
	 *
	 * @return bool
	 * @access public
	 */
	public function icu_requirements()
	{
		return (bool) $this->config['ppde_intl_version_valid'] && $this->config["ppde_intl_detected"];
	}

	/**
	 * Get the default Locale
	 *
	 * @return string A string with the current Locale.
	 */
	public function locale_get_default()
	{
		return $this->icu_requirements() ? \locale_get_default() : '';
	}

	/**
	 * Get The currency symbol based on ISO code
	 *
	 * @param $currency_iso_code
	 *
	 * @return string
	 * @access public
	 */
	public function get_currency_symbol($currency_iso_code)
	{
		$fmt = new \NumberFormatter($this->config['ppde_default_locale'] . "@currency=" . $currency_iso_code, \NumberFormatter::CURRENCY);
		return $fmt->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
	}

	/**
	 * Check if the PPDE locale feature is configured
	 *
	 * @return bool.
	 * @access public
	 */
	public function is_locale_configured()
	{
		return $this->icu_requirements() && !empty($this->config['ppde_default_locale']);
	}

	/**
	 * Create a number formatter
	 *
	 * @return \NumberFormatter NumberFormatter object or FALSE on error.
	 * @access public
	 */
	public function numfmt_create()
	{
		return numfmt_create($this->config['ppde_default_locale'], \NumberFormatter::CURRENCY);
	}

	/**
	 * Format a currency value
	 *
	 * @param \NumberFormatter $fmt
	 * @param float            $value
	 * @param string           $currency_iso_code
	 *
	 * @return \phpbb\config\config
	 * @access public
	 */
	public function numfmt_format_currency($fmt, $value, $currency_iso_code)
	{
		return numfmt_format_currency($fmt, (float) $value, (string) $currency_iso_code);
	}

	/**
	 * Set config value for PHP Intl extension version
	 *
	 * @return void
	 * @throws \ReflectionException
	 * @access public
	 */
	public function set_intl_info()
	{
		$this->config->set('ppde_intl_version', $this->get_php_extension_version('intl', $this->icu_available_features()));
		$this->config->set('ppde_intl_version_valid', (int) $this->icu_version_compare());
	}

	/**
	 * @param string $name
	 * @param bool   $proceed
	 *
	 * @return string
	 * @throws \ReflectionException
	 */
	private function get_php_extension_version($name, $proceed)
	{
		$version = '';
		if ($proceed)
		{
			$ext = new \ReflectionExtension($name);
			$version = $ext->getVersion();
		}
		return $version;
	}

	/**
	 * Checks if some function/class are available.
	 *
	 * @return bool
	 * @access public
	 */
	public function icu_available_features()
	{
		return class_exists('\ResourceBundle') && function_exists('\locale_get_default');
	}

	/**
	 * @return bool
	 * @throws \ReflectionException
	 */
	public function icu_version_compare()
	{
		$icu_min_version = '1.1.0';
		$icu_version = $this->get_php_extension_version('intl', $this->icu_available_features());
		return version_compare($icu_version, $icu_min_version, '>=');
	}

	/**
	 * Set config value for cURL and fsockopen
	 *
	 * @return void
	 * @access public
	 */
	public function set_intl_detected()
	{
		$this->config->set('ppde_intl_detected', $this->icu_available_features());
	}
}
