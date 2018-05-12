<?php
/**
 * Author: gaorenhua
 * Date: 2014-11-05
 * Email: 597170962@qq.com
 * 公共控制器  主要用于处理自动登录  AND 权限验证
 */
class CommonAction extends Action{
    /**
     * 自动登录  AND Auth权限认证
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
        if (!isset($_SESSION['uid'])) {
            $this->error('请先登录再进行浏览', U('Login/login'), 1);
        }

        // 判断超级管理员
        if (!in_array(session('uid'), C('ADMINISTRATOR'))) {
            // 判断该用户所在的用户组是否被禁用 1-启用  0-禁用
            $gid = M('users_group')->where(array('uid' => $_SESSION['uid']))->getField('group_id');
            $status = M('group')->where(array('id' => $gid))->getField('status');
            if (!$status) {
                del_cookie_session();   //清楚cookie  session
                $this->error('对不起,您所在的用户组被禁用, 请联系管理员', U('Login/login'), 2);
            }
        }

        // 判断用户是否被禁用
        if (!M('users')->where(array('id' => $_SESSION['uid']))->getField('status')) {
            del_cookie_session();   //清楚cookie  session
            $this->error('对不起,您的账户被禁用, 请联系管理员', U('Login/login'), 2);
        }

        // Auth权限认证
        import("ORG.Util.Auth");
        $auth = new Auth();

        // 判断超级管理员  首页越过判断
        if (in_array(session('uid'), C('ADMINISTRATOR')) || in_array(ACTION_NAME, C('APPLYACTION'))) {
            return true;
        } else {
            if (!$auth->check(MODULE_NAME . '/' . ACTION_NAME, $_SESSION['uid'])) {
                $this->error('对不起,您没有该操作的权限!');
            }
        }
    }

    /**
     * 公共头部|侧边栏
     */
    public function header(){
        $this->display();
    }


    /**
     * 公共分页类
     * @param $model        实例化后的模型
     * @param $condition    查询条件
     * @param null $order   排序
     * @param int $num      每页记录数
     */
    public function page($model, $condition, $order = null, $num = 20)
    {
        // 导入分页类
        import('ORG.Util.Page');
        $count      = $model->where($condition)->count();// 查询满足要求的总记录数
        $Page       = new Page($count,$num);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $model->where($condition)->order($order)->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
    }
}
