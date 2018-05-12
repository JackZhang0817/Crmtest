<?php
/**
 * 自动登录, 判断是否登录
 * User: GRH
 * Date: 15-1-30
 * Time: 上午11:05
 */
class CommonAction extends Action {
    /**
     * 自动登录 判断是否登录
     */
    public function _initialize(){
        //处理自动登录
        if (isset($_COOKIE['auto']) && !isset($_SESSION['uid'])) {
            //解密cookie 获取上次含有用户名 和 IP 的 登录信息
            $value = explode('|', encryption($_COOKIE['auto'], 1));
            //获取本次登录的客户端IP地址
            $ip = get_client_ip();

            //对比本次登录IP和上次登录IP是否一致
            if ($ip == $value[1]) {
                $username = $value[0];
                $where = array('username' => $username);

                //数据库查找是否存在该帐号
                $user = M('users')->where($where)->field(array('id', 'status'))->find();

                //如果存在该账户 且 用户没有被锁定 则登录成功
                if ($user && $user['status']) {
                    session('uid', $user['id']);
                }
            }
        }

        //判断用户是否已经登录
        if (!isset($_SESSION['aid'])) {
            $this->error('请先登录再进行浏览', U('Login/login'), 1);
        }
    }
}