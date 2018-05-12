<?php
/**
 * 后台首页控制器.
 * User: GRH
 * Date: 15-1-30
 * Time: 上午11:02
 */
class IndexAction extends CommonAction {
    /**
     * 系统信息
     */
    public function sysinfo(){
        // 获取当前账户的登录信息
        $info = M('users')->field('updatetime,ip')->where(array('id' => session('uid')))->find();

        $this->assign('info', $info);
        $this->assign('SERVER_SOFTWARE', $_SERVER['SERVER_SOFTWARE']);
        $this->display();
    }

    /**
     * 已审核用户
     */
    public function checked_users(){
        // 判断提交方式
        if(IS_AJAX){
            // 获取公司管理员账户列表
            $list = M('users')->field('photo,cid,job,updatetime',true)->where(array('pid'=>0))->select();

            // 遍历用户信息 处理用户状态
            foreach($list as $k => $v){
                $list[$k]['createtime'] = date('Y-m-d H:i', $list[$k]['createtime']);
                $v['auth'] ? $list[$k]['auth']='<a style="color:green">已授权</a>' : $list[$k]['auth']='<a style="color:red">未授权</a>';
                $v['ifpay'] ? $list[$k]['ifpay']='<a style="color:green">已支付</a>' : $list[$k]['ifpay']='<a style="color:red">未支付</a>';
                $v['status'] ? $list[$k]['status']='<a style="color:green">已审核</a>' : $list[$k]['status']='<a style="color:red">未审核</a>';
                $flag_txt = $v['flag'] ? '免费' : '收费';
                $list[$k]['operate'] = '<a href="">禁用</a> | <a href="">删除</a> | <a href="#" onclick="confirm_flag()" class="easyui-linkbutton">'.$flag_txt.'</a>';
            }

            $this->AjaxReturn($list,'json');
        }

        $this->display();
    }

    /**
     * 设置用户类型(收费账户或免费账户)
     */
    public function set_user_flag(){
        if(IS_AJAX){
            $userid = I('post.id', '', 'intval');
            $flag = M('Users')->where("id=$userid")->getField('flag');
            $data['flag'] = $flag ? 0 : 1;
            $id = M('Users')->where(array('id'=>$userid))->save($data);
        }
    }

}