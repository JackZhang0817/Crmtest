<?php
/**
 * Author: gaorenhua	
 * Date: 2014-12-14	
 * Email: 597170962@qq.com
 * 客户查看施工进度控制器
 */
class IndexAction extends CommonAction {
	/**
	 * 施工进度
	 */
    public function index(){
		// GET提交
		if (!IS_GET) {
            $this->error('您请求的页面不存在');
        }

		// 获取客户的详情信息
		$cus_info  = M('customer')->where(array('id' => session('customer_id')))->find();

		// 获取客户平台的用户名
		$user = M('customer_platform')->field('cusname,project')->where(array('customer_id' => session('customer_id')))->find();

		// 获取所有二级施工工序
		$parr = unserialize($user['project']);
		$list = M('project')->where(array('id' => array('IN', $parr)))->select();

		// 获取当前客户的所有施工详情
		$reslut = D('PlatinfoView')->where(array('customer_id' => session('customer_id')))->order('id desc')->select();

		// 根据父级施工工序ID重组二维数组
		foreach($reslut as $k=>$v) {
	        $info[$v['pp']][] = $v; 
		}

		krsort($info);  //对键key进行降序排序

		// 判断是否存在该客户的施工信息
//		if (!$info) {
//			$this->error('您请求的信息不存在');
//		}

        //获取客户咨询问题
        $comments = M('customer_comment')->where(array('customer_id'=>session('customer_id')))->select();

		// 输出
		$this->assign('cinfo', $cus_info);
		$this->assign('user', $user['cusname']);
		$this->assign('list', $list);
		$this->assign('info', $info);
        $this->assign('comments', $comments);
		$this->display();
    }

    /**
	 * 查看施工详情
	 */
	public function viewProjectInfo(){
		// 判断GET提交
		if (!IS_GET) {
			$this->error('您请求的页面不存在');
		}

		// 获取客户的详情信息
		$cus_info  = M('customer')->where(array('id' => session('customer_id')))->find();

		// 获取客户平台的用户名
		$user = M('customer_platform')->where(array('customer_id' => session('customer_id')))->getField('cusname');

		// 获取详情ID
		$where['id'] = I('get.id', 0, 'intval');

		// 获取详情
		$info = D('PlatinfoView')->where($where)->find();

		$this->assign('cinfo', $cus_info);
		$this->assign('user', $user);
		$this->assign('info', $info);
		$this->display();
	}

    /**
     * 工程管理模块, 客户提交问题
     */
    public function comment()
    {
        if (IS_AJAX) {
            $data = array(
                'post_id' => session('customer_id'),
                'customer_id'=>session('customer_id'),
                'content' => I('post.content', '', 'htmlspecialchars'),
                'createtime'=>time(),

                'create_time'=>date('Y-m-d H:i:s', time()),
                'customer_name'=>M('customer')->where(array('id'=>session('customer_id')))->getField('CName'),
            );
            $result = M('customer_comment')->add($data);
            echo false !== $result ? json_encode($data) : '';
        }
    }
}