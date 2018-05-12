<?php
/**
 * Author: gaorenhua	
 * Date: 2014-12-14	
 * Email: 597170962@qq.com
 * 公共控制器  主要用于验证客户是否登录
 */
class CommonAction extends Action {
	/**
	 * 验证是否登录, 未登录不能访问
	 */
	public function _initialize(){
		//处理自动登录
		if (isset($_COOKIE['customer_auto']) && !isset($_SESSION['customer_id'])) {
			//解密cookie 获取上次含有用户名 和 IP 的 登录信息
			$value = explode('|', encryption($_COOKIE['customer_auto'], 1));
			//获取本次登录的客户端IP地址
			$ip = get_client_ip();

			//对比本次登录IP和上次登录IP是否一致
			if ($ip == $value[1]) {
				$username = $value[0];
				$where = array('cusname' => $username);

				//数据库查找是否存在该帐号
				$user = M('customer_platform')->where($where)->field(array('id', 'status'))->find();

				//如果存在该账户 且 用户没有被锁定 则登录成功
				if ($user && !$user['status']) {
					session('customer_id', $user['customer_id']);
				}
			}
		}

		//判断客户是否已经登录
		if (!isset($_SESSION['customer_id'])) {
			$this->error('请先登录再进行浏览', U('Login/login'), 1);
		}
	}
}