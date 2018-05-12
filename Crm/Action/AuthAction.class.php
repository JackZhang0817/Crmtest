<?php
/**
 * Author: gaorenhua    
 * Date: 2014-11-08 
 * Email: 597170962@qq.com
 * Auth权限认证控制器
 */
class AuthAction extends CommonAction {
    /**
     * 子用户列表
     */
    public function userList(){
        // 判断是否是超级管理员
        if (in_array(session('uid'), C('ADMINISTRATOR'))) {
            // 获取通用管理员组的所有成员ID (所有注册公司的管理员)
            $uid = M('users_group')->field('uid')->where(array('group_id' => 8))->select();
            $uid = array_column($uid, 'uid');

            // 查询所有通用管理组成员的基本信息
            $list = M('users')->where(array('id' => array('IN', $uid)))->order('id desc')->select();
        } else {
            // 获取该用户的职位 经理以下级别不能添加用户
            $position = M('users')->where(array('id' => session('uid')))->getField('job');
            if (session('uid')==fid()) {
                // 获取当前管理员下的子用户
                $where = array(
                    // 'id' => session('uid'),去掉显示当前登录的账户
                    'pid'  => fid()
                    // '_logic' => 'OR'
                );
                // 并入查询
                $map = array(
                    '_complex' => $where,
                    'username' => array('NEQ', '')
                );
                // 获取该管理员账户下的 所有 用户组列表
                $group = M('group')->where(array('admin_id' => fid()))->order('sort asc')->select();
                $list = M('users')->where($map)->select();
            } elseif ($position == 3) {
                // 部门经理所在部门的ID
                $gid = M('users_group')->where(array('uid' => session('uid')))->getField('group_id');
                $group = M('group')->where(array('id' => $gid))->getField('title'); // 获取当前经理的所在部门
                $where['id'] = array('IN', departusers());
                $list = M('users')->where($where)->order('job desc')->select();
            } else {
                $this->error('您无权查看员工列表');
            }
        }

        // 查询未授权的子用户  1-授权  0-未授权
        $map['auth'] = '0';
        $map['pid'] = fid(); //当前账户的用户ID 用以查询子用户
        $map['username'] = array('NEQ', '');
        $users = M('users')->where($map)->select();

        // 输出
        $this->assign('list', $list);
        $this->assign('group', $group);
        $this->assign('users', $users);
        $this->assign('pos', $position);
        $this->display();
    }

    /**
     * 部门员工列表
     */
    public function groupUsers(){
        if (!IS_GET) {
            $this->error('请求的页面不存在');
        }

        // 获取GET传值的 部门ID
        $gid = I('get.gid', 0, 'intval');

        // 获取该部门所有员工ID
        $uid = M('users_group')->where(array('group_id' => $gid))->getField('uid', true);

        // 获取本部门所有员工
        $list = M('users')->where(array('id' => array('IN', $uid)))->select();

        // 获取该管理员账户下的 所有 用户组列表
        $group = M('group')->where(array('admin_id' => fid()))->order('sort asc')->select();

        // 查询未授权的子用户  1-授权  0-未授权
        $map['auth'] = '0';
        $map['pid'] = fid(); //当前账户的用户ID 用以查询子用户
        $map['username'] = array('NEQ', '');
        $users = M('users')->where($map)->select();

        // 输出
        $this->assign('list', $list);
        $this->assign('group', $group);
        $this->assign('users', $users);
        $this->display('userList');
    }

    /**
     * 用户组列表
     */
    public function groupList(){
        // 获取该管理员账户下的 所有 用户组列表
        $list = M('group')->where(array('admin_id' => session('uid')))->order('sort asc')->select();

        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 添加子用户
     */
    public function addUser(){
        // 判断是否POST提交
        if (IS_POST) {
            // 创建数据对象  执行自动验证
            $data = D('Users')->create();

            // 验证成功
            if ($data) {
                // 添加用户附加信息  创建时间  客户端IP等
                $data['pid']        = fid();
                $data['password']   = md5($data['password']);
                $data['createtime'] = $_SERVER['REQUEST_TIME'];
                $data['ip']         = get_client_ip();
                $data['job']        = I('post.job', 0, 'intval');
                $data['auth'] = '1';
                $data['status']     = '1';
                // 关联数据
                $data['defined']    = array(
                    'display_field' => '1,2,3,4,5,6,7,8,13,14,',
                    'project_field' => '3,4,5,18,19,33,34,36,37,38,',
                    'theme' => 'default'
                );

                // 插入用户数据 返回用户的id
                $uid = D('Users')->relation(true)->add($data);  //

                // 分配部门
                $info['uid'] = $uid;
                $info['group_id'] = I('post.group_id', 0, 'intval');

                // 插入数据库
                if($uid){
                    // 判断是否对该员工分配了部门
                    if ($info['group_id']) {
                        if (!M('users_group')->add($info)) {
                            M('users')->save(array('id' => $uid, 'auth' => 0));
                            $this->error('分配部门失败, 请到部门列表栏目进行该员工授权');
                        }
                    } else {
                        M('users')->save(array('id' => $uid, 'auth' => 0));
                    }

                    $this->success('添加员工成功');
                }else{
                    $this->error('添加员工失败');
                }
            } else {
                $this->error(D('Users')->getError(), __SELF__, 1);
            }
        } else {
            // 获取该用户的职位 经理以下级别不能添加用户
            $position = M('users')->where(array('id' => session('uid')))->getField('job');
            if (session('uid')==fid() || $position == 3) {
                // 获取当前管理账户的所在公司
                $com = M('company')->field('id,comname')->where(array('admin_id' => fid()))->find();

                // 获取当前公司的所有部门
                $group = M('group')->field('id,title')->where(array('admin_id' => fid()))->order('sort asc')->select();
            } else {
                $this->error('您没有添加员工的权限!');
            }

            if ($position == 3) {
                $gid = M('users_group')->where(array('uid' => session('uid')))->getField('group_id');
            }

            $this->assign('com', $com);
            $this->assign('group', $group);
            $this->assign('pos', $position);
            $this->assign('gid', $gid);
            $this->display();
        }
    }

    /**
     * 邀请注册
     */
    public function inviteRegister(){
        // 判断是否POST提交
        if (!IS_POST) {
            $this->error('对不起,您请求的页面不存在');
        }

        // 提取POST数据
        $post = I('post.emails');
        $data = explode(',', $post);    //多个邮箱进行切割

        // 不允许提交空数据
        if (empty($post)) {
            $this->error('对不起,您未填写任何邮箱', U('addUser'), 1);
        }

        // 获取该管理员账户下的所有子用户邮箱, 防止已注册的子用户重复邀请
        $where = array(
            'id' => session('uid'), 
            'pid' => session('uid'), 
            '_logic' => 'or'
        );

        // 把多维数组中email键单独列出变成一维数组
        $emails = array_column(M('users')->where($where)->field('email')->select(), 'email');

        // 取出重复邮箱
        $result = array_intersect($data, $emails);

        if ($result) {
            $this->error('对不起,您填写的邮箱含有已注册用户', U('addUser'), 1);
        }

        // 判断用户是否付费 未付费用户有数量限制  MAX_USER_NUMS
        if (!M('users')->where(array('id' => session('uid')))->getField('ifpay')) {
            $num = M('users')->where(array('pid' => session('uid')))->count('id');
            if ( count($data) > (C('MAX_USER_NUMS') - $num)) {
                $this->error('对不起, 您最多还能邀请'.(C('MAX_USER_NUMS') - $num).'子用户', U('addUser'), 2);
            }
        }

        // 定义邮件标题, 邮件内容
        $title = '青岛奇古CRM客户管理系统邀请注册';
        $content = '尊敬的先生/女士:<br/>非常抱歉, 您接受到这封邮件是因您的好友邀请您加入我们的CRM系统体验当中, 请点击以下链接注册账户, 如您没有相关意向,请忽略<br/>';

        // 发送邀请
        if (sendMail($data, $title, $content)) {
            // 获取公司名称
            $comname = M('users')->where(array('id' => session('uid')))->getField('comname');

            // 插入子用户固定数据 匹配子用户注册验证
            foreach ($data as $key => $value) {
                $map[$key] = array(
                    'pid' => session('uid'),
                    'email' => $value,
                    'comname' => $comname,
                    'status'  => '1'
                );
            }

            if (M('users')->addAll($map)) {
                $this->success('邀请邮件发送成功!');
            } else {
                $this->error('对不起,邀请失败,请重新操作!');
            }   
        } else {
            $this->error('对不起,您的邀请发送失败.请检查邮箱地址是否填写正确');
        }
    }

    /**
     * 添加用户组
     */
    public function addGroup(){
        // 判断是否POST提交
        if (IS_POST) {
            // 创建数据对象 执行自动验证
            $data = D('Group')->create();

            // 验证成功
            if ($data) {
                // 插入当前账户的管理员ID 和 默认权限
                $data['admin_id'] = session('uid');
                $data['rules'] = '1,186,185,184,183,182,181,180,132,160,150,149,148,147,146,177,176,145,144,143,142,175,151,153,152,2,156';
                // 插入数据库
                if (D('Group')->add($data)) {
                    $this->success('添加部门成功');
                } else {
                    $this->error('添加部门失败', U('addGroup'), 1);
                }
            } else {
                $this->error(D('Group')->getError());
            }
        } else {
            $this->display();
        }
    }

    /**
     * 添加认证规则
     * name: 控制/方法
     */
    public function addRules(){
        // 是否POST提交
        if (IS_POST) {
            // 提取POST数据
            $data = array(
                'pid' => $this->_post('pid'),
                'name' => $this->_post('name'), 
                'title' => $this->_post('title'), 
                'status' => $this->_post('status'), 
                'condition' => $this->_post('condition'), 
            );

            // 插入规则
            if (M('rule')->add($data)) {
                $this->success('添加认证规则成功!');
            } else {
                $this->error('添加认证规则失败', U('addRules'), 1);
            }
        } else {
            // 获取顶级认证规则
            $list = M('rule')->where(array('pid' => 0))->field(array('id', 'title'))->select();

            $this->assign('list', $list);
            $this->display();
        }
    }

    /**
     * 认证规则列表
     */
    public function ruleList(){
        // 判断是否是超级管理员
        if (in_array(session('uid'), C('ADMINISTRATOR'))) {
            // 获取认证规则列表
            $list = M('rule')->order("sort asc , `order`  asc")->select();
        } else {
            // 非超级管理员直接获取所在组的所有权限
            $rule = M('group')->where(array('id' => 8))->getField('rules');
            $arr = explode(',', $rule);
            $list = M('rule')->where(array('id' => array('IN', $arr)))->order("sort asc, `order`  asc")->select();
        }

        // 递归重组规则信息为多维数组
        $list = node_merge($list);

        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 规则排序
     */
    public function sortRule(){
        // 判断提交方式
        if(!IS_POST){
            $this->error('您请求的页面不存在');
        }

        // 提取POST数据
        $data = array(
            'id' => I('post.id', 0, 'intval'),
            'order' => I('post.order', 0, 'intval')
        );

        // 合并数组 前者为key 后者为value
        $data = array_combine($data['id'], $data['order']);

        // 更新排序 遍历更新
        foreach ($data as $key => $value) {
            M('rule')->where(array('id' => $key))->setField('order', $value);
        }

        $this->success('排序成功');
    }

    /**
     * 分配用户组认证规则
     */
    public function giveAuth(){
        // 判断是否POST提交
        if (IS_POST) {
            // 获取post,get传值数据
            $data = array(
                'id' => I('get.id', 0, 'intval'), //获取用户组ID
                'rules' => implode(',', I('post.rule')) // 拆分POST数组为字符串
            );

            // 更新用户组认证规则
            if (M('group')->save($data)) {
                $this->success('授权成功', U('groupList'), 1);
            } else {
                $this->error('授权失败, 请联系系统管理员', U('groupList'), 1);
            }
        } else {
            // 获取GET传值的用户组ID
            $data = array('id' => I('get.id', 0, 'intval'));

            // 查询该用户组认证规则
            $rules = M('group')->field('rules')->where($data)->find();

            $this->assign('rule_arr', $rules['rules']);
            $this->ruleList();
        }
    }

    /**
     * 用户组成员授权
     * 此功能不完美 不适合一个用户属于多用户组时的授权 需后期修改 修改指数 ★★★★★★
     */
    public function addUserRules(){
        // 检查是否POST提交
        if (IS_POST) {
            // 获取POST数据  GET数据
            $post = I('post.uid', 0, 'intval');
            $data['group_id'] = I('post.group_id', 0, 'intval');

            if(empty($post)){
                $this->error('您还没有选择需要授权的用户', U('addUserRules', array('id' => $data['group_id'])), 1);
            }

            // 批量插入当前组
            foreach ($post as $uid) {
                $data['uid'] = $uid;
                $id = M('users_group')->add($data);

                // 插入成功后 用户权限设为1   循环插入, 这个地方有点牵强 后期需要修改 如何批量插入
                if ($id) {
                    $result = M('users')->where(array('id' => $uid))->save(array('auth' => 1));
                    if (!$result) {
                        $this->error('授权失败,请联系系统管理员', __SELF__, 1);
                    }
                } else {
                    $this->error('授权失败', __SELF__, 1);
                }
            }

            $this->success('授权成功');
        } else {
            // 查询当前组ID下的 所有授权成员ID集合
            $group_id = I('get.id', 0, 'intval');
            $arr = M('users_group')->field(array('uid'))->where(array('group_id' => $group_id))->select();

            // 定义查询授权子用户的ID值范围    从多维数组中返回单列数组  array_column函数在common.php文件中定义
            $data['id'] = array('in', array_column($arr, 'uid'));
            $data['pid'] = session('uid'); //当前账户的用户ID 用以查询子用户

            // 查询已授权的子用户
            $auth_users = M('users')->where($data)->select();

            // 查询未授权的子用户  1-授权  0-未授权
            $map['auth'] = '0';
            $map['pid'] = fid(); //当前账户的用户ID 用以查询子用户
            $map['username'] = array('NEQ', '');
            $users = M('users')->where($map)->select();

            $this->assign('authlist', $auth_users);
            $this->assign('list', $users);
            $this->assign('group_id', $group_id);
            $this->display();
        }
    }

    /**
     * 解除授权
     */
    public function removeAuth(){
        // 获取需解除授权的子用户ID
        $where['id'] = I('get.id', 0, 'intval');

        // 取消授权
        $re = M('users')->where($where)->save(array('auth' => '0'));
        $id = M('users_group')->where(array('uid' => $where['id']))->delete();
        if ($re && $id) {
            $this->success('解除授权成功');
        } else {
            $this->error('解除授权失败');
        }
    }

    /**
     * 更新账户信息
     */
    public function updateUser(){
        // 判断是否POST提交
        if (IS_POST) {
            // 验证必填字段是否符合规则
            $validate = array(
                array('password', '/^[a-zA-Z][\w]{4,16}$/','密码需以字母开头，5-17个字符 字母、数字、下划线_', 2),
            );
            D('users')->setProperty("_validate",$validate);

            // 创建数据集
            $data = D('users')->create();
            if (!$data) {
                $this->error(D('users')->getError());
            }

            // 获取POST数据
            $data['password'] = !empty($_POST['password']) ? I('post.password', 0, 'md5') : NULL;
            $data['realname'] = I('post.realname');
            $data['tel'] = I('post.tel');
            $data['email'] = I('post.email');
            $data['job'] = I('post.job', 0, 'intval');
            $data['updatetime'] = $_SERVER['REQUEST_TIME'];
            $data['ip']         = get_client_ip();

            // 去除空元素  主要是用来去除不需要修改密码时 提交上来的空值 防止密码错乱
            $data = array_filter($data);

            // 更新部门
            $data['group'] = array(
                'uid' => $data['id'],
                'group_id' => I('post.group_id', 0, 'intval')
            );

            // 更新
            if (D('Users')->relation(true)->save($data)) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        } else {
            // 获取该用户的职位 经理以下级别不能添加用户
            $position = M('users')->where(array('id' => session('uid')))->getField('job');
            if (session('uid') == fid()) {
                // 获取当前管理账户的所在公司
                $com = M('company')->field('id,comname')->where(array('admin_id' => fid()))->find();

                // 获取当前公司的所有部门
                $group = M('group')->field('id,title')->where(array('admin_id' => fid()))->order('sort asc')->select();
            } elseif ($position == 3) {
                $gid = M('users_group')->where(array('uid' => session('uid')))->getField('group_id');
            } else {
                $this->error('您无权修改员工信息');
            }
            // 获取要修改的子用户ID
            $where['id'] = I('get.id', 0, 'intval');

            // 查询该用户信息
            $info = D('Users')->where($where)->relation(true)->find();

            $this->assign('info', $info);
            $this->assign('com', $com);
            $this->assign('group', $group);
            $this->assign('gid', $gid);
            $this->assign('pos', $position);
            $this->display();
        }
    }

    /**
     * 账户禁用
     */
    public function disableUser(){
        // 获取要禁用的子用户ID
        $where['id'] = I('get.id', 0, 'intval');
        $where['status'] = '0';

        // 禁用该ID账户
        if (M('users')->save($where)) {
            $this->success('您已成功禁用该子用户');
        } else {
            $this->error('禁用该子用户失败');
        }
    }

    /**
     * 启用账户
     */
    public function enableUser(){
        // 获取要启用的子用户ID
        $where['id'] = I('get.id', 0, 'intval');
        $where['status'] = '1';

        $email = M('users')->where(array('id' => $where['id']))->getField('email');

        // 定义邮件标题, 邮件内容
        $title = '您申请的管理员账号已审核通过-家装CRM';
        $content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<title>家装CRM客户管理系统</title>
</head>
<body>
<table width="800" border="0" align="center" cellpadding="0" cellspacing="0" style="border:#CCCCCC solid 1px;">
  <tr>
    <td><table width="800" border="0" cellpadding="0" cellspacing="0" bgcolor="#333333">
      <tr>
        <td width="20" height="48"></td>
        <td width="120"><img src="http://www.zxicrm.com/E-mail/common/logo-crm.png" alt="家装CRM系统" width="120" height="23" border="0" /></td>
        <td width="230"></td>
        <td width="430" style="font-family:Microsoft Yahei; font-size:14px; color:#FFFFFF;"><a href="http://www.zxicrm.com/" style="color:#FFFFFF; text-decoration:none;" target="_blank">首页</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.zxicrm.com/index.php/Index/tour.html" style="color:#FFFFFF; text-decoration:none;" target="_blank">功能介绍</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.zxicrm.com/index.php/Index/plan.html" style="color:#FFFFFF; text-decoration:none;" target="_blank">付费方案</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.zxicrm.com/index.php/Index/help.html" style="color:#FFFFFF; text-decoration:none;" target="_blank">帮助中心</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.zxicrm.com/index.php/Club/clubIndex.html" style="color:#FFFFFF; text-decoration:none;" target="_blank">用户社区</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.zxicrm.com/index.php/Index/contact.html" style="color:#FFFFFF; text-decoration:none;" target="_blank">联系我们</a></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td height="20" align="center"></td>
  </tr>
  <tr>
    <td align="center" style="font-family:Microsoft Yahei; color:#666666;"><table width="760" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td height="60" colspan="2" style="font-family:Microsoft Yahei; font-size:16px; font-weight:bold;">尊敬的<span style="color:#00A2CA; text-decoration:underline;">'.$email.'</span></td>
        </tr>
        <tr style="font-family:Microsoft Yahei;font-size:14px; color:#666666; line-height:30px;">
            <td width="35" height="50" valign="top"><img src="http://www.zxicrm.com/E-mail/common/icon-i.png" width="29" height="29" border="0" style="margin-top:8px;"></td>
            <td width="725" valign="top">您申请的家装CRM系统管理员账号已审核通过，赶紧来和您的小伙伴们来一起见证一下家装CRM的强大、灵活、简单、实用与时尚吧！。<br><div>
                了解如何使用家装CRM系统，请点击<a href="http://www.zxicrm.com/index.php/Index/help.html" target="_blank"><span style="color:#00A2CA; text-decoration:underline;">帮助中心</span></a>。</div><div>您也可以使用 admin &nbsp; admin &nbsp;进行“员工登录”（网站右上角）公共测试账号，快速了解家装CRM系统。</div><div>联系QQ：<span style="border-bottom-width: 1px; border-bottom-style: dashed; border-bottom-color: rgb(204, 204, 204); z-index: 1; position: static;" t="6" onclick="return false;" data="1425097451">1425097451</span> &nbsp; &nbsp; 家装CRM官方群：<span style="border-bottom:1px dashed #ccc;z-index:1" t="6" onclick="return false;" data="383152535">383152535</span></div></td>
        </tr>
      <tr>
        <td height="100">&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td height="30" colspan="2" style="border-bottom:#CCCCCC dashed 1px; font-size:12px; color:#333333;">家装CRM-奇古兄弟网络科技</td>
        </tr>
      <tr>
        <td height="30" colspan="2" style="font-family:Microsoft Yahei; font-size:12px; color:#999999;">此为系统邮件请勿回复</td>
        </tr>
    </table></td>
  </tr>
  <tr>
    <td height="40" align="center"></td>
  </tr>
  <tr>
    <td height="100" align="center" bgcolor="#EFEFEF"><table width="800" border="0" cellspacing="0" cellpadding="0" style="font-family:Microsoft Yahei; font-size:12px; color:#666666; line-height:22px;">
      <tr>
        <td width="25"></td>
        <td width="565">          联系电话：185-0532-5137<br />
          官方网站：<a href="http://www.zxicrm.com/" target="_blank" style="color:#666666;">www.zxicrm.com</a><br />
          联系Q Q：1425097451&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;家装CRM官方群：383152535</td>
        <td width="210" align="center"><img src="http://www.zxicrm.com/E-mail/common/logo-qigu.png" alt="奇古兄弟网络" width="143" height="47" border="0" /></td>
        </tr>
    </table></td>
  </tr>
</table>
</body>
</html>';

        // 禁用该ID账户
        if (M('users')->save($where)) {
            if (in_array(session('uid'), C('ADMINISTRATOR'))) {
                $this->sendMail(array($email), $title, $content);
            }
            $this->success('您已成功启用该子用户');
        } else {
            $this->error('启用该子用户失败');
        }
    }

    /**
     * 账户删除之后 会删除连带信息 比如客户信息 需慎重操作
     */
    public function deleteUser(){
        // 获取要启用的子用户ID
        $id = I('get.id', 0, 'intval');

        // 判断是否是公司管理员 公司管理员删除之后公司内部员工全部删除.
        if (in_array(session('uid'), C('ADMINISTRATOR'))){
            // 删除该ID用户
            if (D('Users')->relation(true)->delete($id)) {
                if(M('company')->where(array('admin_id' => $id))->delete()){
                    $this->success('删除管理员成功');
                } else {
                    $this->error('删除管理员成功,但公司信息删除失败');
                }
            } else {
                $this->error('删除管理员失败');
            }
        } else {
            // 判断该ID用户下是否存在客户信息
            if(M('customer')->where(array('Userid' => $id))->find()){
                $this->error('删除失败,该员工存在相关联的客户信息, 不能删除');
            }

            // 删除该ID用户
            if (D('Users')->relation(true)->delete($id)) {
                $this->success('删除用户成功');
            } else {
                $this->error('删除用户失败');
            }
        }
    }

    /**
     * 禁用用户组
     */
    public function disableGroup(){
        // 获取要禁用的子用户ID
        $where['id'] = I('get.id', 0, 'intval');
        $where['status'] = '0';

        // 禁用该ID账户
        if (M('group')->save($where)) {
            $this->success('您已成功禁用该部门');
        } else {
            $this->error('禁用该部门失败');
        }
    }

    /**
     * 启用用户组
     */
    public function enableGroup(){
        // 获取要禁用的子用户ID
        $where['id'] = I('get.id', 0, 'intval');
        $where['status'] = '1';

        // 禁用该ID账户
        if (M('group')->save($where)) {
            $this->success('您已成功启用该部门');
        } else {
            $this->error('启用该部门失败');
        }
    }

    /**
     * 修改用户组
     */
    public function updateGroup(){
        // 判断是否POST提交
        if (IS_POST) {
            // 获取POST数据
            $data = I('post.');

            // 更新
            if (M('group')->save($data)) {
                $this->success('修改成功', U('groupList'), 1);
            } else {
                $this->error('修改失败', __SELF__, 1);
            }
        } else {
            // 获取要修改的子用户ID
            $where['id'] = I('get.id', 0, 'intval');

            // 查询该用户信息
            $info = M('group')->where($where)->find();

            $this->assign('info', $info);
            $this->display();
        }
    }

    /**
     * 删除用户组
     */
    public function deleteGroup(){
        // 获取要禁用的子用户ID
        $where['id'] = I('get.id', 0, 'intval');

        // 查询当前组ID下的 所有授权成员ID集合
        $group_id = I('get.id', 0, 'intval');
        $arr = M('users_group')->field(array('uid'))->where(array('group_id' => $group_id))->select();

        // 定义查询授权子用户的ID值范围    从多维数组中返回单列数组  array_column函数在common.php文件中定义
        $data['id'] = array('in', array_column($arr, 'uid')); 

        //删除该ID用户组 清除该组内的所用子用户ID
        if (D('Group')->where($where)->relation(true)->delete()) {
            // 该范围ID的用户 全部清空为  未授权 auth = 0
            M('users')->where($data)->save(array('auth' => 0));
            $this->success('删除部门成功');
        } else {
            $this->error('删除部门失败', U('groupList'), 1);
        }
    }

    /**
     * 个人中心  包括信息修改 头像上传
     */
    public function userCenter(){
        // 判断POST提交
        if (IS_POST) {
            // 导入image类库
            import('ORG.Net.UploadFile');
            $upload = new UploadFile();// 实例化上传类
            $upload->maxSize  = 3145728 ;// 设置附件上传大小
            $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->thumb = true;
            $upload->thumbRemoveOrigin = true;
            $upload->thumbMaxWidth = '120';
            $upload->thumbMaxHeight = '120';
            $upload->savePath =  './Uploads/Headimg/';// 设置附件上传目录
            if(!$upload->upload()) {// 上传错误提示错误信息
                $this->error($upload->getErrorMsg());
            }else{// 上传成功 获取上传文件信息
                $info =  $upload->getUploadFileInfo();
            }

            // 保存表单数据 包括附件数据
            $data['id'] = session('uid');   //获取当前用户的ID
            $data['photo'] = $info[0]['savename'];  // 保存上传的照片根据需要自行组装
            
            //插入头像
            if(M('users')->save($data)){
                $this->success('上传头像成功');
            }else{
                $this->error('上传头像失败，请联系系统管理员。');
            }
        } else {
            // 查询当前用户的信息
            $info = M('users')->where(array('id' => session('uid')))->getField('photo');

            $this->assign('info', $info);
            $this->display();
        }
    }

    /**
     * 修改密码
     */
    public function updatePass(){
        // 判断提交方式
        if (IS_POST) {
            // 提取POST数据
            $oldpass = I('post.oldpassword');
            $newpass = I('post.password');
            $respass = I('post.repassword');

            // 获取当前用户的密码
            $pass = M('users')->where(array('id' => session('uid')))->getField('password');

            // 判断是否一致
            if (md5($oldpass) != $pass) {
                $this->error('原密码输入错误,无法修改');
            }

            // 判断新旧密码是否一致
            if ($oldpass == $newpass) {
                $this->error('新旧密码相同,无法修改');
            }

            // 判断新密码两次输入是否一致
            if ($respass != $newpass) {
                $this->error('两次密码输入不一致,无法修改');
            }

            // 格式化数据
            $data['id'] = session('uid');
            $data['password'] = md5($newpass);

            // 更新密码
            if (M('users')->save($data)) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        } else {
            $this->display();
        }
    }

    /**
     * 修改公司信息
     */
    public function editorCompany(){
        // 判断提交方式
        if (IS_POST) {
            if (!empty($_FILES['logo']['name'])) {
                // 导入image类库
                import('ORG.Net.UploadFile');
                $upload = new UploadFile();// 实例化上传类
                $upload->maxSize  = 3145728 ;// 设置附件上传大小
                $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->thumb = true;
                $upload->thumbRemoveOrigin = true;
                $upload->thumbMaxWidth = '120';
                $upload->thumbMaxHeight = '120';
                $upload->savePath =  './Uploads/Logo/';// 设置附件上传目录
                if(!$upload->upload()) {// 上传错误提示错误信息
                    $this->error($upload->getErrorMsg());
                }else{// 上传成功 获取上传文件信息
                    $info =  $upload->getUploadFileInfo();
                }
                $data['logo'] = $info[0]['savename'];   // 保存上传的照片根据需要自行组装
            }


            // 保存表单数据 包括附件数据
            $data['id'] = I('post.id', 0, 'intval');    //获取当前用户的ID
            $data['comname'] = I('post.comname');   // 公司名称
            $data['remark'] = I('post.remark'); // 公司描述

            //插入头像
            if(M('company')->save($data) !==false){
                $this->success('信息修改成功');
            }else{
                $this->error('信息修改失败');
            }
        } else {
            // 查找当前管理员公司信息
            $info = M('company')->where(array('admin_id' => fid()))->find();

            $this->assign('info', $info);
            $this->display();
        }
    }

    /**
     * 发送邮件-主要用户邀请用户时发送邀请
     * @param $data 需要发送邮件的邮件地址数组
     * @param $title 邮件的标题
     * @param $content 邮件的内容
     * @return bool 发送状态
     */
    protected function sendMail($data, $title, $content){
        // 载入邮件发送类库
        Vendor('PHPMailer.PHPMailerAutoload');

        $mail = new PHPMailer;

        $mail->isSMTP();                //设置PHPMailer使用SMTP服务器发送Email
        $mail->Host = C('MAIL_HOST');   //指定SMTP服务器 可以是smtp.126.com, gmail, qq等服务器 自行查询
        $mail->SMTPAuth = true;
        $mail->CharSet='UTF-8';         //设置字符集 防止乱码
        $mail->Username = C('MAIL_LOGINNAME');  //发送人的邮箱账户
        $mail->Password = C('MAIL_PASSWORD');   //发送人的邮箱密码
        $mail->Port = 25;                       //SMTP服务器端口

        $mail->From = C('MAIL_LOGINNAME');      //发件人邮箱地址
        $mail->FromName = C('MAIL_FORM');       //发件人名称
        $mail->WordWrap = 50;                   // 换行字符数
        $mail->isHTML(true);                    // 设置邮件格式为HTML

        $mail->Subject = $title;       //邮件标题

        //判断是否是多个邮箱  循环发送邮件
        if (is_array($data)) {
            foreach ($data as $email) {
                $mail->addAddress($email);      // 收件人邮箱地址 此处可以发送多个
                $mail->Body = $content;  //邮件内容

                // 有发送失败的地址就返回false
                if(!$mail->send()) {
                    return false;
                }

                $mail->ClearAddresses(); //清除收件人
            }
        }

        // 全部发送成功 返回true
        return true;
    }
}