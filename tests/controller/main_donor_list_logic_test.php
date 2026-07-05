<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\tests\controller;

class main_donor_list_logic_test extends \phpbb_test_case
{
	/** @var \skouat\ppde\controller\main_donor_list */
	protected $controller;

	protected function setUp(): void
	{
		parent::setUp();

		$config = new \phpbb\config\config([]);
		$container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
		$helper = $this->createMock(\phpbb\controller\helper::class);
		$language = $this->createMock(\phpbb\language\language::class);
		$auth = $this->createMock(\skouat\ppde\actions\auth::class);
		$currency = $this->createMock(\skouat\ppde\actions\currency::class);
		$request = $this->createMock(\phpbb\request\request::class);
		$template = $this->createMock(\phpbb\template\template::class);
		$user = $this->createMock(\phpbb\user::class);
		$user_loader = $this->createMock(\phpbb\user_loader::class);

		$this->controller = new \skouat\ppde\controller\main_donor_list(
			$config, $container, $helper, $language, $auth, $currency,
			$request, $template, $user, $user_loader, './', 'php'
		);
	}

	public function url_delim_data()
	{
		return [
			'empty params'     => ['https://foo.com/list', [], 'https://foo.com/list?'],
			'with parameters'  => ['https://foo.com/list', ['sk=u', 'sd=a'], 'https://foo.com/list&amp;'],
		];
	}

	/** @dataProvider url_delim_data */
	public function test_set_url_delim($url, $params, $expected)
	{
		$method = new \ReflectionMethod($this->controller, 'set_url_delim');
		$method->setAccessible(true);

		$this->assertSame($expected, $method->invoke($this->controller, $url, $params));
	}

	public function sort_key_data()
	{
		return [
			'matching ascending'  => ['u', 'u', 'a', 'd'],
			'matching descending' => ['u', 'u', 'd', 'a'],
			'mismatch keys'       => ['u', 'a', 'a', 'a'],
		];
	}

	/** @dataProvider sort_key_data */
	public function test_set_sort_key($sk, $sk_comp, $sd, $expected)
	{
		$method = new \ReflectionMethod($this->controller, 'set_sort_key');
		$method->setAccessible(true);

		$this->assertSame($expected, $method->invoke($this->controller, $sk, $sk_comp, $sd));
	}

	public function resolve_sorting_data()
	{
		return [
			'amount asc'          => ['a', 'a', 'a', 'amount ASC'],
			'amount desc'         => ['a', 'd', 'a', 'amount DESC'],
			'date desc (default)' => ['d', 'd', 'd', 'MAX(txn.payment_date) DESC'],
			'username asc'        => ['u', 'a', 'u', 'MAX(u.username_clean) ASC'],
			'invalid key falls back to date' => ['zzz', 'd', 'd', 'MAX(txn.payment_date) DESC'],
		];
	}

	/** @dataProvider resolve_sorting_data */
	public function test_resolve_sorting($sk, $sd, $expected_key, $expected_order_by)
	{
		$result = $this->controller->resolve_sorting($sk, $sd);

		$this->assertSame($expected_key, $result['key']);
		$this->assertSame($expected_order_by, $result['order_by']);
	}
}
