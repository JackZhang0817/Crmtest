<?php
/**.
 * User: GRH
 * Date: 14-12-18  Time: 下午5:33
 * 用户社区控制器
 */
class ClubAction extends Action {
    /**
     * 用户社区首页 帖子列表
     */
    public function clubIndex(){
        // 获取查询条件
        $id = I('get.id', 0, 'intval');
        empty($id) ? '' : $where['cid'] =$id;
        // 导入分页类
        import('ORG.Util.Page');
        $count  = M('details')->where($where)->count();        // 查询记录总数
        $Page   = new Page($count,20);			 // 实例化分页类 传入总记录数
        $Page->setConfig('header','篇帖子');    // 定制分页样式
        $show   = $Page->show();				 // 分页显示输出
        $list = M('details')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('top desc,id desc')->select();

        $this->assign('list',$list);		    // 赋值数据集
        $this->assign('page',$show);			// 赋值分页输出
        $this->display();
    }

    /**
     * 我要发帖
     */
    public function addpost(){
        // 判断POST提交
        if (IS_POST) {
            // 验证只能输入汉字和字符
            $validate = array(
                array('title', 'require', '标题不能为空'),
                array('content', 'require', '内容不能为空'),
                array('cid', 'number','客户ID只能输入整数', 1),
            );
            D('details')->setProperty("_validate",$validate);

            // 创建数据集
            $data = D('details')->create();
            if (!$data) {
                $this->error(D('details')->getError(), __SELF__, 1);
            }

            // 获取附加信息
            $data['uid'] = session('uid');
            $data['entrytime'] = $_SERVER['REQUEST_TIME'];

            // 插入数据库
            if (M('details')->add($data)) {
                $this->success('发布成功', U('clubIndex'), 1);
            } else {
                $this->error('发布失败', __SELF__, 1);
            }
        } else {
            // 判断有木有登录
            if (isset($_SESSION['uid'])) {
                $this->display();
            } else {
                // 记录登录前的地址,登录后直接跳回地址
                session('jump_url', $_SERVER['HTTP_REFERER']);

                // 显示登录按钮 员工登录 业主登录
                $this->show(style('游客不能发布帖子,请先登录! 如您没有帐号,可点击此处申请试用,我们的工作人员会在24小时内联系您审核通过<br/><a href="/crm.php/Login/login.html">员工登录</a>'));
            }
        }
    }

    /**
     * 编辑帖子
     */
    public function editorDetail(){
        // 判断POST提交
        if (IS_POST) {
            // 验证只能输入汉字和字符
            $validate = array(
                array('title', 'require', '标题不能为空'),
                array('content', 'require', '内容不能为空'),
                array('cid', 'number','客户ID只能输入整数', 1),
            );
            D('details')->setProperty("_validate",$validate);

            // 创建数据集
            $data = D('details')->create();
            if (!$data) {
                $this->error(D('details')->getError(), __SELF__, 1);
            }

            // 更改文件添加时间
            $data['entrytime'] = strtotime(I('entrytime'));

            // 更新信息
            if (M('details')->save($data)) {
                $this->success('编辑成功', U('clubIndex'), 1);
            } else {
                $this->error('编辑失败', __SELF__, 1);
            }
        } else {
            // 获取帖子ID
            $where['id'] = I('get.id', 0, 'intval');

            // 查询帖子信息
            $info = M('details')->where($where)->find();

            // 不是发帖人不能编辑帖子
            if ($info['uid'] != session('uid')) {
                $this->error('您无权编辑他人帖子');
            }

            $this->assign('info', $info);
            $this->display();
        }
    }

    /**
     * 删除帖子
     */
    public function deleteDetail(){
        // 获取帖子ID
        $where['id'] = I('get.id', 0, 'intval');

        // 查询帖子信息
        $uid = M('details')->where($where)->getField('uid');

        // 不是发帖人不能编辑帖子
        if ($uid != session('uid')) {
            $this->error('您无权删除他人帖子');
        }

        // 删除帖子
        if (M('details')->where($where)->delete()) {
            $this->success('删除成功', U('myDetail'), 1);
        } else {
            $this->error('删除失败', U('myDetail'), 1);
        }
    }

    /**
     * 我的帖子
     */
    public function myDetail(){
        // 查询条件
        $where['uid'] = session('uid');

        // 导入分页类
        import('ORG.Util.Page');
        $count  = M('details')->where($where)->count();        // 查询记录总数
        $Page   = new Page($count,20);			 // 实例化分页类 传入总记录数
        $Page->setConfig('header','篇帖子');    // 定制分页样式
        $show   = $Page->show();				 // 分页显示输出
        $list = M('details')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('top desc,id desc')->select();

        $this->assign('list',$list);		    // 赋值数据集
        $this->assign('page',$show);			// 赋值分页输出
        $this->display();
    }

    /**
     * 查看帖子
     */
    public function detail(){
        // 判断GET提交
        if (!IS_GET) {
            $this->error('您请求的页面不存在');
        }

        // 获取GET传值(帖子ID)
        $where['id'] = I('get.id', 0, 'intval');

        // 查询该帖内容
        $info = M('details')->where($where)->find();

        // 获取评论
        $comment = M('comments')->where(array('post_id' => $where['id']))->select();

        $this->assign('info', $info);
        $this->assign('comment', $comment);
        $this->display();
    }

    /**
     * 搜索帖子
     */
    public function search(){
        // 模糊查询
        $where['title'] = I('post.q');
        // 导入分页类
        import('ORG.Util.Page');
        $count  = M('details')->where($where)->count();        // 查询记录总数
        $Page   = new Page($count,20);			 // 实例化分页类 传入总记录数
        $Page->setConfig('header','篇帖子');    // 定制分页样式
        $show   = $Page->show();				 // 分页显示输出
        $list = M('details')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('top desc,id desc')->select();

        $this->assign('list',$list);		    // 赋值数据集
        $this->assign('page',$show);			// 赋值分页输出
        $this->display('clubIndex');
    }

    /**
     * 发布评论
     */
    public function comments(){
        // 判断POST提交
        if (!IS_POST) {
            $this->error('您请求的页面不存在');
        }

        // 获取POST数据
        $data['post_id'] = I('post.post_id', 0, 'intval');
        $data['uid'] = session('uid');
        $data['content'] = I('post.content');
        $data['entrytime'] = $_SERVER['REQUEST_TIME'];

        // 插入评论
        if (M('comments')->add($data)) {
            $this->success('评论成功', U('detail', array('id' => $data['post_id'])), 1);
        } else {
            $this->error('评论失败', U('detail', array('id' => $data['post_id'])), 1);
        }
    }

    /**
     * 注册页面
     */
    public function register(){
        //判断是否登录
        if (is_login()) {
            $this->error('您已申请使用', C('INDEX_PATH'), 3);
        } else {
            isMobile() ? $this->display('mobileRegister') : $this->display();
        }
    }

    public function viewNums() {
        // 判断提交方式
        if (!IS_POST) {
            $this->error('您请求的页面不存在');
        }

        // 提取POST数据
        $id = I('post.ggd', 0, 'intval');

        // 获取原有阅读次数
        $view = M('details')->where(array('id' => $id))->getField('view');

        // 刷新一次页面加1
        $data['view'] = $view + 1;
        $data['id'] = $id;

        // 插入数据
        M('details')->save($data);
    }
}