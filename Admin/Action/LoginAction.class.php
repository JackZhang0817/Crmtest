<?php

/**
 * 超级管理员登录控制器
 */
class LoginAction extends Action {
	/**
	 * 超级管理员登录入口
	 */
	public function login() {
		// 判断提交方式
		if (IS_POST) {
			// 提取POST数据
			$username = I('post.username');
			$password = I('post.password', '', 'md5');

			// 判断不能为空
			if (empty($username) || empty($password)) {
				$this->error('用户名或密码不能为空!');
			}

			$where = array('username' => $username);
			$user = M('users')->where($where)->find();

			//p($user);die;

			if (!$user || $user['password'] != $password) {
				$this->error('用户名或密码不正确,请重新输入!');
			}

			// 判断是否是超级管理员
			if ($user['id'] == 1) {
				session('aid', $user['id']);
				$this->success('登录成功,正在跳转...',U('Index/sysinfo'), 2);
			} else {
				$this->error('该账户不存在,请核对后重新输入');
			}
		} else {
			$this->display();
		}
	}
}