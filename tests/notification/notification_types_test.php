<?php
/**
 *
 * PayPal Donation extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015-2026 Skouat
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace skouat\ppde\tests\notification;

class notification_types_test extends \phpbb_test_case
{
	/**
	 * Build a notification type without invoking phpBB's heavy base constructor,
	 * then inject the two collaborators is_available() needs.
	 */
	private function make_type(string $class, $auth, array $config_data)
	{
		$notification = (new \ReflectionClass($class))->newInstanceWithoutConstructor();

		// config is set through the public setter added by the extension.
		$notification->set_config(new \phpbb\config\config($config_data));

		// auth is a protected property inherited from phpBB's notification base.
		try
		{
			$prop = new \ReflectionProperty($class, 'auth');
		}
		catch (\ReflectionException $e)
		{
			$this->markTestSkipped('phpBB notification base exposes no "auth" property here.');
		}
		$prop->setAccessible(true);
		$prop->setValue($notification, $auth);

		return $notification;
	}

	public function test_get_type_returns_service_id()
	{
		$admin = (new \ReflectionClass(\skouat\ppde\notification\type\admin_donation_received::class))
			->newInstanceWithoutConstructor();
		$donor = (new \ReflectionClass(\skouat\ppde\notification\type\donor_donation_received::class))
			->newInstanceWithoutConstructor();

		$this->assertSame('skouat.ppde.notification.type.admin_donation_received', $admin->get_type());
		$this->assertSame('skouat.ppde.notification.type.donor_donation_received', $donor->get_type());
	}

	public function test_get_email_template()
	{
		$admin = (new \ReflectionClass(\skouat\ppde\notification\type\admin_donation_received::class))
			->newInstanceWithoutConstructor();
		$donor = (new \ReflectionClass(\skouat\ppde\notification\type\donor_donation_received::class))
			->newInstanceWithoutConstructor();

		$this->assertSame('@skouat_ppde/admin_donation_received', $admin->get_email_template());
		$this->assertSame('@skouat_ppde/donor_donation_received', $donor->get_email_template());
	}

	public function availability_data()
	{
		return [
			'all enabled'        => [true,  1, 1, true],
			'no permission'      => [false, 1, 1, false],
			'extension disabled' => [true,  0, 1, false],
			'notifs disabled'    => [true,  1, 0, false],
		];
	}

	/** @dataProvider availability_data */
	public function test_admin_is_available($acl, $enable, $notif, $expected)
	{
		$auth = $this->createMock(\phpbb\auth\auth::class);
		$auth->method('acl_get')->with('a_ppde_manage')->willReturn($acl);

		$type = $this->make_type(
			\skouat\ppde\notification\type\admin_donation_received::class,
			$auth,
			['ppde_enable' => $enable, 'ppde_ipn_notification_enable' => $notif]
		);

		$this->assertSame($expected, (bool) $type->is_available());
	}

	/** @dataProvider availability_data */
	public function test_donor_is_available($acl, $enable, $notif, $expected)
	{
		$auth = $this->createMock(\phpbb\auth\auth::class);
		$auth->method('acl_get')->with('u_ppde_use')->willReturn($acl);

		$type = $this->make_type(
			\skouat\ppde\notification\type\donor_donation_received::class,
			$auth,
			['ppde_enable' => $enable, 'ppde_ipn_notification_enable' => $notif]
		);

		$this->assertSame($expected, (bool) $type->is_available());
	}
}
