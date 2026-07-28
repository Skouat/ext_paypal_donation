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
	protected $phpbb_root_path;
	protected $php_ext;
	protected $config;
	protected $user;

	/**
	 * auth constructor.
	 *
	 * @param \phpbb\auth\auth     $auth            Auth object
	 * @param \phpbb\config\config $config          Config object
	 * @param \phpbb\user          $user            User object
	 * @param string               $phpbb_root_path phpBB root path
	 * @param string               $php_ext         phpEx
	 *
	 * @access public
	 */

	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\user $user,
		$phpbb_root_path,
		$php_ext
	)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	public function set_guest_acl(): void
	{
		if (!class_exists(\auth_admin::class))
		{
			include($this->phpbb_root_path . 'includes/acp/auth.' . $this->php_ext);
		}
		$auth_admin = new \auth_admin();

		$auth['u_ppde_use'] = (int) $this->config['ppde_allow_guest'];
		$auth['u_ppde_view_donorlist'] = (int) $this->config['ppde_ipn_dl_allow_guest'];

		$auth_admin->acl_set('user', [0], [ANONYMOUS], $auth);
	}

	/**
	 * @return bool
	 * @access public
	 */
	public function can_use_ppde(): bool
	{
		return $this->auth->acl_get('u_ppde_use');
	}

	/**
	 * @return bool
	 * @access public
	 */
	public function can_view_ppde_donorlist(): bool
	{
		return $this->auth->acl_get('u_ppde_view_donorlist');
	}

	/**
	 * @return bool
	 * @access public
	 */
	public function can_manage_ppde(): bool
	{
		return $this->auth->acl_get('a_ppde_manage');
	}

	/**
	 * Check we are in the ACP
	 *
	 * @return bool
	 * @access public
	 */
	public function is_in_admin(): bool
	{
		return defined('IN_ADMIN') && isset($this->user->data['session_admin']) && (bool) $this->user->data['session_admin'];
	}
}
