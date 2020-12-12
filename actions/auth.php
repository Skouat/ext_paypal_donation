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

class auth
{
	protected $auth;
	protected $auth_admin;
	protected $phpbb_root_path;
	protected $php_ext;
	protected $config;

	/**
	 * currency constructor.
	 *
	 * @param \phpbb\auth\auth     $auth            Auth Auth object
	 * @param \phpbb\config\config $config          Config object
	 * @param string               $phpbb_root_path phpBB root path
	 * @param string               $php_ext         phpEx
	 *
	 * @access public
	 */

	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		$phpbb_root_path,
		$php_ext
	)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		if (!class_exists('auth_admin'))
		{
			include($this->phpbb_root_path . 'includes/acp/auth.' . $this->php_ext);
		}
		$this->auth_admin = new \auth_admin();
	}

	public function set_guest_acl()
	{
		$auth['u_ppde_use'] = (int) $this->config['ppde_allow_guest'];
		$auth['u_ppde_view_donorlist'] = (int) $this->config['ppde_ipn_dl_allow_guest'];

		$this->auth_admin->acl_set('user', [0], [ANONYMOUS], $auth);
	}

	/**
	 * @return bool
	 * @access public
	 */
	public function can_use_ppde()
	{
		return $this->auth->acl_get('u_ppde_use');
	}

	/**
	 * @return bool
	 * @access public
	 */
	public function can_view_ppde_donorlist()
	{
		return $this->auth->acl_get('u_ppde_view_donorlist');
	}
}
