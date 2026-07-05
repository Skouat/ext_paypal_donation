<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\tests\api;

// Fake SDK objects declare real methods so safe_call()'s method_exists() works.

class ppde_fake_name
{
	private $given;
	private $surname;
	public function __construct($given = null, $surname = null) { $this->given = $given; $this->surname = $surname; }
	public function getGivenName() { return $this->given; }
	public function getSurname() { return $this->surname; }
}

class ppde_fake_address
{
	private $country;
	public function __construct($country = null) { $this->country = $country; }
	public function getCountryCode() { return $this->country; }
}

class ppde_fake_modern_payer
{
	private $name;
	private $address;
	private $email;
	private $account_id;
	public function __construct($name, $address, $email, $account_id)
	{
		$this->name = $name;
		$this->address = $address;
		$this->email = $email;
		$this->account_id = $account_id;
	}
	public function getName() { return $this->name; }
	public function getAddress() { return $this->address; }
	public function getEmailAddress() { return $this->email; }
	public function getAccountId() { return $this->account_id; }
}

class ppde_fake_legacy_payer
{
	private $name;
	private $address;
	private $email;
	private $payer_id;
	public function __construct($name, $address, $email, $payer_id)
	{
		$this->name = $name;
		$this->address = $address;
		$this->email = $email;
		$this->payer_id = $payer_id;
	}
	public function getName() { return $this->name; }
	public function getAddress() { return $this->address; }
	public function getEmailAddress() { return $this->email; }
	public function getPayerId() { return $this->payer_id; }
}

class ppde_fake_payment_source
{
	private $paypal;
	public function __construct($paypal) { $this->paypal = $paypal; }
	public function getPaypal() { return $this->paypal; }
}

class ppde_fake_payee
{
	private $email;
	private $merchant_id;
	public function __construct($email, $merchant_id) { $this->email = $email; $this->merchant_id = $merchant_id; }
	public function getEmailAddress() { return $this->email; }
	public function getMerchantId() { return $this->merchant_id; }
}

class ppde_fake_purchase_unit
{
	private $payee;
	public function __construct($payee) { $this->payee = $payee; }
	public function getPayee() { return $this->payee; }
}

class ppde_fake_order
{
	private $payment_source;
	private $payer;
	private $units;
	public function __construct($payment_source, $payer, $units)
	{
		$this->payment_source = $payment_source;
		$this->payer = $payer;
		$this->units = $units;
	}
	public function getPaymentSource() { return $this->payment_source; }
	public function getPayer() { return $this->payer; }
	public function getPurchaseUnits() { return $this->units; }
}

class order_party_extractor_test extends \phpbb_test_case
{
	/** @var object Anonymous class exposing the trait's protected methods */
	protected $ext;

	protected function setUp(): void
	{
		parent::setUp();

		$this->ext = new class {
			use \skouat\ppde\api\paypal\order_party_extractor;

			public function payer($source): array { return $this->extract_payer_fields($source); }
			public function payee($order): array { return $this->extract_payee_fields($order); }
			public function resolve($order) { return $this->resolve_payer_source($order); }
			public function call($object, string $method, $default = null) { return $this->safe_call($object, $method, $default); }
		};
	}

	public function test_safe_call_returns_default_on_null()
	{
		$this->assertSame('DEF', $this->ext->call(null, 'whatever', 'DEF'));
	}

	public function test_safe_call_returns_default_when_method_missing()
	{
		$obj = new ppde_fake_address('US');
		$this->assertNull($this->ext->call($obj, 'getNonExistent'));
	}

	public function test_safe_call_invokes_existing_method()
	{
		$obj = new ppde_fake_address('FR');
		$this->assertSame('FR', $this->ext->call($obj, 'getCountryCode'));
	}

	public function test_extract_payer_fields_null_source()
	{
		$this->assertSame(
			['first_name' => '', 'last_name' => '', 'email' => '', 'payer_id' => '', 'country' => ''],
			$this->ext->payer(null)
		);
	}

	public function test_extract_payer_fields_modern_uses_account_id()
	{
		$source = new ppde_fake_modern_payer(
			new ppde_fake_name('John', 'Doe'),
			new ppde_fake_address('US'),
			'john@example.com',
			'ACC123'
		);

		$this->assertSame([
			'first_name' => 'John',
			'last_name'  => 'Doe',
			'email'      => 'john@example.com',
			'payer_id'   => 'ACC123',
			'country'    => 'US',
		], $this->ext->payer($source));
	}

	public function test_extract_payer_fields_legacy_uses_payer_id()
	{
		// No getAccountId() => falls back to getPayerId().
		$source = new ppde_fake_legacy_payer(
			new ppde_fake_name('Jane', 'Roe'),
			new ppde_fake_address('CA'),
			'jane@example.com',
			'PAYER99'
		);

		$data = $this->ext->payer($source);
		$this->assertSame('PAYER99', $data['payer_id']);
		$this->assertSame('jane@example.com', $data['email']);
		$this->assertSame('CA', $data['country']);
	}

	public function test_extract_payer_fields_missing_name_and_address()
	{
		// Null name/address must yield empty strings, not errors.
		$source = new ppde_fake_modern_payer(null, null, 'only@email.com', 'ACC1');

		$this->assertSame([
			'first_name' => '',
			'last_name'  => '',
			'email'      => 'only@email.com',
			'payer_id'   => 'ACC1',
			'country'    => '',
		], $this->ext->payer($source));
	}

	public function test_resolve_prefers_paypal_source()
	{
		$modern = new ppde_fake_modern_payer(null, null, 'm@x.com', 'ACC');
		$legacy = new ppde_fake_legacy_payer(null, null, 'l@x.com', 'PID');
		$order  = new ppde_fake_order(new ppde_fake_payment_source($modern), $legacy, []);

		$this->assertSame($modern, $this->ext->resolve($order));
	}

	public function test_resolve_falls_back_when_paypal_null()
	{
		$legacy = new ppde_fake_legacy_payer(null, null, 'l@x.com', 'PID');
		$order  = new ppde_fake_order(new ppde_fake_payment_source(null), $legacy, []);

		$this->assertSame($legacy, $this->ext->resolve($order));
	}

	public function test_resolve_falls_back_when_no_payment_source()
	{
		$legacy = new ppde_fake_legacy_payer(null, null, 'l@x.com', 'PID');
		$order  = new ppde_fake_order(null, $legacy, []);

		$this->assertSame($legacy, $this->ext->resolve($order));
	}

	public function test_extract_payee_fields()
	{
		$payee = new ppde_fake_payee('merchant@x.com', 'MERCH1');
		$order = new ppde_fake_order(null, null, [new ppde_fake_purchase_unit($payee)]);

		$this->assertSame(
			['email' => 'merchant@x.com', 'merchant_id' => 'MERCH1'],
			$this->ext->payee($order)
		);
	}

	public function test_extract_payee_fields_no_units()
	{
		$order = new ppde_fake_order(null, null, []);

		$this->assertSame(
			['email' => '', 'merchant_id' => ''],
			$this->ext->payee($order)
		);
	}
}
