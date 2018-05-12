<?php
/**
 * Author: gaorenhua	
 * Date: 2014-12-14	
 * Email: 597170962@qq.com
 * 登录控制器  主要用于客户的登录验证
 */
class LoginAction extends Action {
	/**
	 * 登录入口
	 */
	public function login(){
		//判断POST提交
		if (IS_POST) {
			// 判断验证码 strtoupper 把字符串转换成大写
			if ($_SESSION['verify'] != md5(strtoupper($this->_post('verify')))) {
				$this->error('验证码错误', __SELF__, 1);
			}

			// 提取POST数据 检索数据库
			$where = array('cusname' => $this->_post('username'));
			$user = M('customer_platform')->where($where)->find();

			if (!$user || $user['password'] != $this->_post('password')) {
				$this->error('用户名或密码不正确,请重新输入!');
			}

			// 验证用户是否禁用
			if ($user['status']) {
				$this->error('您的平台已被禁用,请联系管理员!');
			}

			// 记住密码 自动登录
			if (isset($_POST['remember'])) {
				$username = $user['cusname'];
				$ip = get_client_ip(); //获取客户端IP 判断下次登录IP是不是该IP 如果是则执行自动登录 若不是 则重新登录

				//设置cookie
				@setcookie('customer_auto', encryption($username . "|" . $ip), C('AUTO_LOGIN_TIME'), '/');

			}

			//登陆成功写入session并跳转至首页
			session('customer_id', $user['customer_id']);

			$this->success('登录成功,正在跳转至首页...', U('Index/index'), 1);
		} else {
            isMobile() ? $this->display('mobileLogin') : $this->display();
		}
	}

	/**
	 * 注销登录
	 */ 
	public function logout(){
		cookie('customer_auto', null);  //清楚cookie  防止恢复账户后有效期内的自动登录
    	session('customer_id', null);   //清楚session  防止恢复账户后越过验证
		$this->success('退出成功,正为您跳转至网站首页...', C('INDEX_PATH'), 1);
	}

	/**
	 * 验证码
	 */
	public function verify(){
		// 加载验证码类库
		import('ORG.Util.Image');
		// 生成验证码
		Image::buildImageVerify('4', '5', 'png', '144', '34');
	}
}