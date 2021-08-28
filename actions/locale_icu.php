<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2020 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\actions;

use phpbb\config\config;
use phpbb\template\template;
use phpbb\user;

class locale_icu
{
	protected $config;
	protected $template;
	protected $user;

	/**
	 * locale_icu constructor.
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

	/**
	 * Build pull down menu options of available locales
	 *
	 * @param string $config_value Locale identifier; default: ''
	 *
	 * @return void
	 * @access public
	 */
	public function build_locale_select_menu($config_value = ''): void
	{
		if (!$this->icu_requirements())
		{
			return;
		}

		// Grab the list of all available locales
		$locale_list = $this->get_locale_list();

		// Process each locale item for pull-down
		foreach ($locale_list as $locale => $locale_name)
		{
			// Set output block vars for display in the template
			$this->template->assign_block_vars('locale_options', [
				'LOCALE_ID'        => $locale,
				'LOCALE_NAME'      => $locale_name,
				'S_LOCALE_DEFAULT' => $config_value === $locale,
			]);
		}
		unset ($locale, $locale_list);
	}

	/**
	 * Checks if the PHP PECL intl extension is fully available
	 *
	 * @return bool
	 * @access public
	 */
	public function icu_requirements(): bool
	{
		return $this->config['ppde_intl_version_valid'] && $this->config['ppde_intl_detected'];
	}

	/**
	 * Build an array of all locales
	 *
	 * @return mixed
	 * @access private
	 */
	private function get_locale_list()
	{
		$locale_items = \ResourceBundle::getLocales('');
		foreach ($locale_items as $locale)
		{
			$locale_ary[$locale] = \Locale::getDisplayName($locale, $this->user->lang_name);
		}
		unset ($locale_items);

		natsort($locale_ary);

		return $locale_ary;
	}

	/**
	 * Gets the default Locale
	 *
	 * @return string A string with the current Locale.
	 */
	public function locale_get_default(): string
	{
		return $this->icu_requirements() ? \locale_get_default() : '';
	}

	/**
	 * Gets the currency symbol based on ISO code
	 *
	 * @param $currency_iso_code
	 *
	 * @return string
	 * @access public
	 */
	public function get_currency_symbol($currency_iso_code): string
	{
		$fmt = new \NumberFormatter($this->config['ppde_default_locale'] . '@currency=' . $currency_iso_code, \NumberFormatter::CURRENCY);
		return $fmt->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
	}

	/**
	 * Checks if the PPDE locale feature is configured
	 *
	 * @return bool
	 * @access public
	 */
	public function is_locale_configured(): bool
	{
		return $this->icu_requirements() && !empty($this->config['ppde_default_locale']);
	}

	/**
	 * Creates a number formatter
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
	 * @return string
	 * @access public
	 */
	public function numfmt_format_currency($fmt, $value, $currency_iso_code): string
	{
		return numfmt_format_currency($fmt, (float) $value, (string) $currency_iso_code);
	}

	/**
	 * Sets config value for PHP Intl extension version
	 *
	 * @return void
	 * @throws \ReflectionException
	 * @access public
	 */
	public function set_intl_info(): void
	{
		$this->config->set('ppde_intl_version', $this->get_php_extension_version('intl', $this->icu_available_features()));
		$this->config->set('ppde_intl_version_valid', (int) $this->icu_version_compare());
	}

	/**
	 * Gets extension version
	 *
	 * @param string $name
	 * @param bool   $proceed
	 *
	 * @return string
	 * @throws \ReflectionException
	 * @access private
	 */
	private function get_php_extension_version($name, $proceed): string
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
	 * Checks if the required function/class are available.
	 *
	 * @return bool
	 * @access private
	 */
	private function icu_available_features(): bool
	{
		return class_exists(\ResourceBundle::class) && function_exists('\locale_get_default');
	}

	/**
	 * Checks if ICU version matches with requirement
	 *
	 * @return bool
	 * @throws \ReflectionException
	 * @access private
	 */
	private function icu_version_compare(): bool
	{
		$icu_min_version = '1.1.0';
		$icu_version = $this->get_php_extension_version('intl', $this->icu_available_features());
		return version_compare($icu_version, $icu_min_version, '>=');
	}

	/**
	 * Sets config value for cURL and fsockopen
	 *
	 * @return void
	 * @access public
	 */
	public function set_intl_detected(): void
	{
		$this->config->set('ppde_intl_detected', $this->icu_available_features());
	}
}
