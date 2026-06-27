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
	 * @return array
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
		$fmt = new \NumberFormatter($this->get_effective_locale() . '@currency=' . $currency_iso_code, \NumberFormatter::CURRENCY);
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
		return $this->icu_requirements();
	}

	/**
	 * Creates a number formatter
	 *
	 * @return \NumberFormatter NumberFormatter object or FALSE on error.
	 * @access public
	 */
	public function numfmt_create()
	{
		return numfmt_create($this->get_effective_locale(), \NumberFormatter::CURRENCY);
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
	 * @access public
	 */
	public function set_intl_info(): void
	{
		$this->config->set('ppde_intl_version', $this->get_intl_version($this->is_intl_loaded()));
		$this->config->set('ppde_intl_version_valid', (int) $this->icu_version_compare());
	}

	/**
	 * Gets intl extension version
	 *
	 * @param bool $is_loaded
	 *
	 * @return string
	 * @access private
	 */
	private function get_intl_version($is_loaded): string
	{
		$version = '';
		if ($is_loaded)
		{
			$version = defined('INTL_ICU_VERSION') ? INTL_ICU_VERSION : '';
		}
		return $version;
	}

	public function is_intl_loaded(): bool
	{
		return extension_loaded('intl');
	}

	/**
	 * Checks if ICU version matches with requirement
	 *
	 * @return bool
	 * @access private
	 */
	private function icu_version_compare(): bool
	{
		$icu_min_version = '1.1.0';
		$icu_version = $this->get_intl_version($this->is_intl_loaded());
		return version_compare($icu_version, $icu_min_version, '>=');
	}

	/**
	 * Sets config value for PHP Intl extension detection
	 *
	 * @return void
	 * @access public
	 */
	public function set_intl_detected(): void
	{
		$this->config->set('ppde_intl_detected', $this->is_intl_loaded());
	}

	/**
	 * Gets the number of fraction digits for a currency (ISO 4217).
	 *
	 * Decimals are intrinsic to the currency (USD = 2, JPY = 0, KWD = 3),
	 * independent of the locale; used to format amounts for the PayPal REST API.
	 *
	 * @param string $currency_iso_code
	 *
	 * @return int
	 * @access public
	 */
	public function get_currency_fraction_digits(string $currency_iso_code): int
	{
		$fmt = new \NumberFormatter(
			'en_US@currency=' . $currency_iso_code,
			\NumberFormatter::CURRENCY
		);

		$digits = $fmt->getAttribute(\NumberFormatter::FRACTION_DIGITS);

		// getAttribute() returns false on ICU error → safe fallback to 2
		return ($digits === false) ? 2 : (int) $digits;
	}

	/**
	 * Resolve the effective ICU locale used to format currencies.
	 *
	 * Cascade: admin override (General Settings) → current user's forum
	 * language → board default language → English. Each phpBB ISO code is
	 * canonicalised to a valid ICU locale; any unresolved level is skipped.
	 *
	 * @return string
	 * @access public
	 */
	public function get_effective_locale(): string
	{
		// 1. Optional admin override (empty = auto).
		$override = (string) ($this->config['ppde_default_locale'] ?? '');
		if ($override !== '' && $this->is_valid_locale($override))
		{
			return $override;
		}

		// 2. Current user's forum language.
		$user_locale = $this->iso_to_locale((string) ($this->user->data['user_lang'] ?? ''));
		if ($user_locale !== '')
		{
			return $user_locale;
		}

		// 3. Board default language.
		$board_locale = $this->iso_to_locale((string) ($this->config['default_lang'] ?? ''));
		if ($board_locale !== '')
		{
			return $board_locale;
		}

		// 4. Final fallback.
		return 'en_GB';
	}

	/**
	 * Convert a phpBB language ISO code to a valid ICU locale.
	 *
	 * phpBB ISO codes use dashes (e.g. 'pt-br') whereas ICU expects
	 * underscores and a normalised case (e.g. 'pt_BR').
	 *
	 * @param string $iso
	 *
	 * @return string Empty string if it cannot be resolved.
	 * @access private
	 */
	private function iso_to_locale(string $iso): string
	{
		if ($iso === '')
		{
			return '';
		}

		// phpBB "en" is British English, whereas ICU "en" defaults to US conventions.
		$overrides = ['en' => 'en_GB'];
		$iso = $overrides[$iso] ?? $iso;

		$locale = (string) \Locale::canonicalize(str_replace('-', '_', $iso));

		return $this->is_valid_locale($locale) ? $locale : '';
	}

	/**
	 * Check that a locale is actually known to ICU.
	 *
	 * @param string $locale
	 *
	 * @return bool
	 * @access public
	 */
	public function is_valid_locale(string $locale): bool
	{
		if ($locale === '')
		{
			return false;
		}

		return \Locale::lookup(\ResourceBundle::getLocales(''), $locale, false, '') !== '';
	}
}
