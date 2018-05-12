<?php
/**
 * Author: gaorenhua    
 * Date: 2014-11-05 
 * Email: 597170962@qq.com
 * 用户登录 注册 控制器
 */
class LoginAction extends Action{
    /**
     * 用户登录
     */
    public function login(){
        // 判断是否POST提交
        if (IS_POST) {
            // 判断验证码 strtoupper 把字符串转换成大写
            if ($_SESSION['verify'] != md5(strtoupper($this->_post('verify')))) {
                $this->error('验证码错误', __SELF__, 1);
            }

            // 提取POST数据 检索数据库
            $where = array('username' => $this->_post('username'));
            $user = M('users')->where($where)->find();

            if (!$user || $user['password'] != $this->_post('password', 'md5')) {
                $this->error('用户名或密码不正确,请重新输入!');
            }

            // 验证用户是否禁用
            if (!$user['status']) {
                $this->error('该用户已被禁用,请联系管理员!');
            }

            // 记住密码 自动登录
            if (isset($_POST['remember'])) {
                $username = $user['username'];
                $ip = get_client_ip(); //获取客户端IP 判断下次登录IP是不是该IP 如果是则执行自动登录 若不是 则重新登录

                //设置cookie
                @setcookie('auto', encryption($username . "|" . $ip), C('AUTO_LOGIN_TIME'), '/');

            }

            //登陆成功写入session值 用户ID 以及 用户职位
            session('uid', $user['id']);
            session('job', $user['job']);

            // 判断超级管理员
            if (!in_array(session('uid'), C('ADMINISTRATOR'))) {
                // 判断该用户所在的用户组是否被禁用 1-启用  0-禁用
                $gid = M('users_group')->where(array('uid' => $user['id']))->getField('group_id');
                $status = M('group')->where(array('id' => $gid))->getField('status');
                if (!$status) {
                    del_cookie_session();   //清楚cookie  session
                    $this->error('对不起,您所在的用户组被禁用, 请联系管理员', U('Login/login'), 2);
                }

                // 普通员工登录: 若公司账户余额小于0, 则不能登录
                if($user['pid'] > 0 && M('Users')->where('id='.$user['pid'])->getField('flag') === '0'){
                    $balance = M('Orders')->where('userid='.$user['pid'])->sum('ordfee');
                    $employee_num = M('Users')->where(array('pid'=>$user['pid'],'status'=>'1'))->count();
                    if($employee_num > C('MAX_USER_NUMS') && ($balance == null || $balance < 0) ){
                        del_cookie_session();   //清楚cookie  session
                        $this->error('对不起, 贵公司账户<font color="red">余额不足</font>, 请联系贵公司管理员进行充值', U('Login/login'), 5);
                    }
                }

                // 公司管理员登录: 若公司账户余额小于0, 则提示
                if($user['pid'] === '0' && $user['flag'] === '0'){
                    $balance = M('Orders')->where('userid='.$user['id'])->sum('ordfee');
                    $employee_num = M('Users')->where(array('pid'=>$user['pid'],'status'=>'1'))->count();
                    if($employee_num > C('MAX_USER_NUMS') && ($balance == null || $balance < 0) ){
                        if (isset($_SESSION['jump_url']) || !empty($_SESSION['jump_url'])) {
                            $this->success('登录成功, 贵公司账户<font color="red">余额不足</font>, 请及时充值', $_SESSION['jump_url'], 5);
                        } else {
                            $this->success('登录成功, 贵公司账户<font color="red">余额不足</font>, 请及时充值', U('Index/index'), 5);
                        }
                        die;
                    }
                }
            }

            // 判断有无跳转网址
            if (isset($_SESSION['jump_url']) || !empty($_SESSION['jump_url'])) {
                $this->success('登录成功,正为您跳转至登录前页面...', $_SESSION['jump_url'], 1);
            } else {
                $this->success('登录成功,正在跳转至首页...', U('Index/index'), 1);
            }
        }else{
            // 判断是否登录
            if (is_login()) {
                $this->success('您已登录', U('Index/index'), 1);
            } else {
                isMobile() ? $this->display('mobileLogin') : $this->display();
            }
        }
    }

    /**
     * 用户注册
     */
    public function register(){
        // 验证是否POST提交
        if (IS_POST) {
            // 判断验证码 strtoupper 把字符串转换成大写
            if ($_SESSION['verify'] != md5(strtoupper($this->_post('verify')))) {
                $this->error('验证码输入错误', __SELF__, 1);
            }

            // 创建数据对象  执行自动验证
            $data = D('Users')->create();

            // 验证成功
            if ($data) {
                // 添加用户附加信息  创建时间  客户端IP等
                $data['password']   = md5($data['password']);
                $data['cid'] = 0;
                $data['createtime'] = $_SERVER['REQUEST_TIME'];
                $data['ip']         = get_client_ip();
                $data['auth']      = 1; // 显示管理员的授权状态
                $data['status']     = '0';  // 审核为1 未审核为0

                // 关联数据
                $data['defined']    = array(
                    'display_field' => '1,2,3,4,5,6,7,8,13,14,',
                    'project_field' => '3,4,5,18,19,33,34,36,37,38,',
                    'theme' => 'default'
                );

                // 插入数据库
                $uid = D('Users')->relation(true)->add($data);
                if($uid){
                    // 插入公司信息
                    $info['logo'] = '1.jpg';
                    $info['admin_id'] = $uid;
                    $info['comname'] = I('post.comname');
                    $info['remark'] = '暂无';

                    // 获取公司ID
                    $comid = M('company')->add($info);

                    // 更新公司的创建人ID
                    M('users')->where(array('id' => $uid))->save(array('cid' => $comid));
                    M('users_group')->add(array('uid' => $uid, 'group_id' => 8)); //插入用户组 防止登录的时候出现权限判定的无限死循环

                    // 注册成功之后发送邮件给超级管理员 以便及时审核
                    // 定义邮件标题, 邮件内容
                    $title = '有新用户注册家装CRM,请及时审核';
                    $content = "尊敬的超级管理员:<br/>您好, 有新用户注册家装CRM系统, 请及时审核, 后面连接无用 ^_^ ";

                    // 发送申请邮件
                    sendMail(array('1425097451@qq.com','1529570905@qq.com'), $title, $content);

                    // 输出注册成功页面
                    $this->display('success');
                }else{
                    $this->error('注册失败', __SELF__, 1);
                }
            } else{
                //验证失败
                $this->error(D('Users')->getError());
            }
        } else{
            $this->error('对不起,您访问的页面不存在');
        }
    }

    /**
     * 邀请用户注册
     */
    public function inviteRegister(){
        // 验证是否POST提交
        if (IS_POST) {
            // 判断验证码 strtoupper 把字符串转换成大写
            if ($_SESSION['verify'] != md5(strtoupper($this->_post('verify')))) {
                $this->error('验证码输入错误', U('inviteRegister', array('code' => I('post.code'))), 1);
            }

            $validate = array(
                // 验证是否符合规则
                array('realname', '/^[\x{4e00}-\x{9fa5}]{2,4}$/u','真实姓名只能填写2-4个汉字,不支持英文,数字和标点符号'),
                array('tel', '/^0?(13[0-9]|15[012356789]|18[0236789])[0-9]{8}$/','手机号码格式不正确'),
                array('username', '/^[a-zA-Z][\w]{4,16}$/','用户名需以字母开头，5-17个字符 字母、数字、下划线_'),
                array('password', '/^[a-zA-Z][\w]{4,16}$/','密码需以字母开头，5-17个字符 字母、数字、下划线_'),
                array('rpassword', 'password','两次输入密码不一致', '0', 'confirm'),

                // 验证是否存在
                array('tel', '', '该联系方式已经注册!', 0, 'unique', 1),
                array('username', '', '用户名已经存在！', 0, 'unique', 1)
            );

            D('Users')->setProperty("_validate",$validate);

            // 创建数据对象  执行自动验证
            $data = D('Users')->create();

            // 验证成功
            if ($data) {
                // 添加用户附加信息  创建时间  客户端IP等
                $data['id'] = M('users')->where(array('email' => $data['email']))->getField('id');
                $data['password']   = md5($data['password']);
                $data['createtime'] = $_SERVER['REQUEST_TIME'];
                $data['ip']         = get_client_ip();
                $data['status']     = '1';

                // 插入数据库
                if(D('Users')->save($data)){
                    $this->success('注册成功', U('login'), 1);
                }else{
                    $this->error('注册失败, 重新填写注册信息', U('inviteRegister', array('code' => I('post.code'))), 1);
                }
            } else{
                //验证失败
                $this->error(D('Users')->getError());
            }
        } else{
            $code = I('get.code');
            $email = encryption($code, 1);

            $this->assign('code', $code);
            $this->assign('email', $email);
            $this->display('register');
        }
    }

    // 注销登录
    public function logout(){
        del_cookie_session();   //清楚cookie  session
        $this->success('退出成功,正为您跳转至网站首页...', C('INDEX_PATH'), 1);
    }

    // 验证码
    public function verify(){
        // 加载验证码类库
        import('ORG.Util.Image');
        // 生成验证码
        Image::buildImageVerify('4', '5', 'png', '144', '34');
    }

    // 异步验证联系方式是否存在
    public function checkTel(){
        // 判断是否为Ajax提交
        if (!$this->isAjax()) {
            $this->error('对不起,您访问的页面不存在', U('login'), 2);
        }

        // 获取POST数据
        $where = array('tel' => $this->_post('tel'));

        // 查询数据库
        if (M('users')->where($where)->getField('id')) {
            echo "false";
        }else{
            echo "true";
        }
    }

    // 异步验证邮箱是否存在
    public function checkEmail(){
        // 判断是否为Ajax提交
        if (!$this->isAjax()) {
            $this->error('对不起,您访问的页面不存在', U('login'), 2);
        }

        // 获取POST数据
        $where = array('email' => $this->_post('email'));

        // 查询数据库
        if (M('users')->where($where)->getField('id')) {
            echo "false";
        }else{
            echo "true";
        }
    }

    // 异步验证用户名是否存在
    public function checkUsername(){
        // 判断是否为Ajax提交
        if (!$this->isAjax()) {
            $this->error('对不起,您访问的页面不存在', U('login'), 2);
        }

        // 获取POST数据
        $where = array('username' => $this->_post('username'));

        // 查询数据库
        if (M('users')->where($where)->getField('id')) {
            echo "false";
        }else{
            echo "true";
        }
    }
}