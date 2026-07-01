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

class vars_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\actions\vars */
	protected $vars_service;
	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $currency_action;

	protected function setUp(): void
	{
		parent::setUp();

		$config = new \phpbb\config\config([
			'sitename'              => 'My Forum',
			'site_desc'             => 'A great place',
			'board_contact'         => 'contact@example.com',
			'board_email'           => 'board@example.com',
			'board_email_sig'       => 'Best regards',
			'ppde_default_currency' => 1,
			'ppde_goal'             => 1000.0,
			'ppde_raised'           => 150.0,
			'ppde_used'             => 50.0,
		]);

		$language = $this->createMock(\phpbb\language\language::class);

		$user = $this->createMock(\phpbb\user::class);
		$user->data = ['user_id' => 42, 'username' => 'TestDonor'];

		$core_action = $this->createMock(\skouat\ppde\actions\core::class);
		$core_action->method('is_in_admin')->willReturn(false);

		$this->currency_action = $this->createMock(\skouat\ppde\actions\currency::class);
		$this->currency_action->method('get_default_currency_data')->willReturn([[
																					 'currency_iso_code' => 'EUR',
																					 'currency_symbol'   => '€',
																					 'currency_on_left'  => false,
																				 ]]);
		$this->currency_action->method('format_currency')->willReturnCallback(function($amount) {
			return $amount . ' €';
		});

		$this->vars_service = new \skouat\ppde\actions\vars(
			$core_action,
			$this->currency_action,
			$config,
			$language,
			$user
		);
	}

	public function test_get_vars_resolves_all_placeholders()
	{
		$resolved = $this->vars_service->get_vars();

		$expected = [
			'{USER_ID}'         => 42,
			'{USERNAME}'        => 'TestDonor',
			'{SITE_NAME}'       => 'My Forum',
			'{SITE_DESC}'       => 'A great place',
			'{BOARD_CONTACT}'   => 'contact@example.com',
			'{BOARD_EMAIL}'     => 'board@example.com',
			'{BOARD_SIG}'       => 'Best regards',
			'{DONATION_GOAL}'   => '1000 €',
			'{DONATION_RAISED}' => '150 €',
			'{DONATION_USED}'   => '50 €',
		];

		$flat = [];
		foreach ($resolved as $item)
		{
			$flat[$item['var']] = $item['value'];
		}

		$this->assertSame($expected, $flat);
	}

	public function test_replace_template_vars_substitutes_correctly()
	{
		$this->vars_service->get_vars();

		$message = "Hello {USERNAME} (ID: {USER_ID}), thank you for supporting {SITE_NAME}. Our goal is {DONATION_GOAL}.";
		$expected = "Hello TestDonor (ID: 42), thank you for supporting My Forum. Our goal is 1000 €.";

		$this->assertSame($expected, $this->vars_service->replace_template_vars($message));
	}
}
