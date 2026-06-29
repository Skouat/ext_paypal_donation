<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\tests\actions;

class locale_icu_test extends \phpbb_test_case
{
	protected function setUp(): void
	{
		parent::setUp();

		if (!extension_loaded('intl'))
		{
			$this->markTestSkipped('The intl extension is required for locale tests.');
		}
	}

	private function make_locale(array $config_overrides = [], string $user_lang = '')
	{
		$config = new \phpbb\config\config(array_merge([
			'ppde_default_locale' => '',
			'default_lang'        => '',
		], $config_overrides));

		$template = $this->createMock(\phpbb\template\template::class);

		$user = $this->createMock(\phpbb\user::class);
		$user->data = ['user_lang' => $user_lang];

		return new \skouat\ppde\actions\locale_icu($config, $template, $user);
	}

	public function fraction_digits_data()
	{
		return [
			'USD' => ['USD', 2],
			'EUR' => ['EUR', 2],
			'JPY' => ['JPY', 0],
			'KWD' => ['KWD', 3],
			'BHD' => ['BHD', 3],
		];
	}

	/** @dataProvider fraction_digits_data */
	public function test_get_currency_fraction_digits($iso, $expected)
	{
		$this->assertSame($expected, $this->make_locale()->get_currency_fraction_digits($iso));
	}

	public function valid_locale_data()
	{
		return [
			'fr_FR'   => ['fr_FR', true],
			'en_GB'   => ['en_GB', true],
			'empty'   => ['', false],
			'unknown' => ['zz_ZZ', false],
		];
	}

	/** @dataProvider valid_locale_data */
	public function test_is_valid_locale($locale, $expected)
	{
		$this->assertSame($expected, $this->make_locale()->is_valid_locale($locale));
	}

	public function test_effective_locale_uses_admin_override()
	{
		$locale = $this->make_locale(['ppde_default_locale' => 'de_DE'], 'fr');
		$this->assertSame('de_DE', $locale->get_effective_locale());
	}

	public function test_effective_locale_falls_back_to_user_lang()
	{
		$locale = $this->make_locale([], 'fr');
		$this->assertSame('fr', $locale->get_effective_locale());
	}

	public function test_effective_locale_maps_phpbb_en_to_en_gb()
	{
		$locale = $this->make_locale([], 'en');
		$this->assertSame('en_GB', $locale->get_effective_locale());
	}

	public function test_effective_locale_falls_back_to_board_default()
	{
		$locale = $this->make_locale(['default_lang' => 'es'], '');
		$this->assertSame('es', $locale->get_effective_locale());
	}

	public function test_effective_locale_final_fallback()
	{
		$this->assertSame('en_GB', $this->make_locale()->get_effective_locale());
	}
}
