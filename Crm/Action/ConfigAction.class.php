<?php
/**
 * Author: gaorenhua
 * Date: 2014-12-19
 * Email: 597170962@qq.com
 * 系统配置控制器
 */
class ConfigAction extends CommonAction {
    /**
     * 部门排序
     */
    public function groupSort(){
        // 调用公共排序
        $this->sortCommon('group', 'Auth/groupList');
    }

    /**
     * 添加 更新 删除 排序客户来源
     */
    public function addChannel(){
        $this->addCommon('channelname', 'channel');
    }

    public function updateChannel(){
        $this->updateCommon('channelname', 'channel', 'addChannel');
    }

    public function deleteChannel(){
        $this->deleteCommon('channel');
    }

    public function channelSort(){
        // 调用公共排序
        $this->sortCommon('channel', 'addChannel');
    }

    /**
     * 添加 更新 删除客户状态
     */
    public function addState(){
        $this->addCommon('statename', 'state');
    }

    public function updateState(){
        $this->updateCommon('statename', 'state', 'addState');
    }

    public function deleteState(){
        $this->deleteCommon('state');
    }

    public function stateSort(){
        $this->sortCommon('com_state', 'checkState');
    }

    public function deleteComState(){
        $this->deleteCommon('com_state');
    }

    /**
     * 添加 更新 删除装修方式
     */
    public function addWay(){
        $this->addCommon('wayname', 'way');
    }

    public function updateWay(){
        $this->updateCommon('wayname', 'way', 'addWay');
    }

    public function deleteWay(){
        $this->deleteCommon('way');
    }

    public function waySort(){
        $this->sortCommon('way', 'addWay');
    }

    /**
     * 添加施工工序 以及 工序列表
     */
    public function projectList(){
        // 判断POST提交
        if (IS_POST) {
            // 验证只能输入汉字和字符
            $validate = array(array('pname', 'require', '工序名称不能为空'));
            D('project')->setProperty("_validate",$validate);

            // 创建数据集
            $data = D('project')->create();
            if (!$data) {
                $this->error(D('project')->getError(), __SELF__, 1);
            }

            // 添加人ID
            $data['admin_id'] = session('uid');

            // 插入数据
            if (D('project')->add($data)) {
                $this->success('添加成功', __SELF__, 1);
            } else {
                $this->error('添加失败', __SELF__, 1);
            }
        } else {
            // 筛选当前公司的所有施工工序的父级栏目
            $pname = M('project')->field('id,pname')->where(array('admin_id' => session('uid'), 'pid' => '0', 'status' => '0'))->order('sort asc')->select();

            // 施工工序列表
            $list = M('project')->field('id,pid,pname,sort')->where(array('admin_id' => session('uid'), 'status' => '0'))->order('sort asc')->select();

            // 递归重组规则信息为多维数组
            $list = node_merge($list);

            $this->assign('pname', $pname);
            $this->assign('list', $list);
            $this->display();
        }
    }


    /**
     * 添加施工工序 以及 工序列表
     */
    public function workProjectList(){
        // 判断POST提交
        if (IS_POST) {
            // 验证只能输入汉字和字符
            $validate = array(array('pname', 'require', '工序名称不能为空'));
            D('work_project')->setProperty("_validate",$validate);

            // 创建数据集
            $data = D('work_project')->create();
            if (!$data) {
                $this->error(D('work_project')->getError(), __SELF__, 1);
            }

            // 添加人ID
            $data['admin_id'] = session('uid');

            // 插入数据
            if (D('work_project')->add($data)) {
                $this->success('添加成功', __SELF__, 1);
            } else {
                $this->error('添加失败', __SELF__, 1);
            }
        } else {
            // 筛选当前公司的所有施工工序的父级栏目
            $pname = M('work_project')->field('id,pname')->where(array('admin_id' => session('uid'), 'pid' => '0', 'status' => '0'))->order('sort asc')->select();

            // 施工工序列表
            $list = M('work_project')->field('id,pid,pname,sort')->where(array('admin_id' => session('uid'), 'status' => '0'))->order('sort asc')->select();

            // 递归重组规则信息为多维数组
            $list = node_merge($list);

            $this->assign('pname', $pname);
            $this->assign('list', $list);
            $this->display();
        }
    }
    /**
     * 更新施工工序
     */
    public function updateProject(){
        // 判断POST提交
        if (IS_POST) {
            // 提取POST数据
            $data['id'] = I('post.id', 0, 'intval');
            $data['pname'] = I('post.pname');

            // 追踪记录是否空
            if (empty($data['pname'])) {
                $this->error('施工工序名称不能为空', U('projectList'), 1);
            }

            // 修改记录
            if (M('project')->save($data)) {
                $this->success('修改成功', U('projectList'), 1);
            } else {
                $this->error('修改失败', U('projectList'), 1);
            }
        } else {
            // ajax传值 被点击修改的工序ID
            $info = M('project')->where(array('id' => $this->_get('id')))->find();

            $hidden = "<input type='hidden' name='id' value='".$info['id']."'>";  //标记需要修改的记录ID
            $content = "<textarea class='form-control' name='pname' style='height:100px;'>".$info['pname']."</textarea>";
            echo $hidden.$content;
        }
    }

    /**
     * 删除施工工序
     */
    public function deleteProject(){
        // 判断GET提交
        if (!IS_GET) {
            $this->error('您请求的页面不存在');
        }

        // 获取要删除的工序ID
        $where['id'] = I('get.id', 0, 'intval');

        // 检测是否存在子类
        $result = M('project')->where(array('pid' => $where['id']))->find();
        if ($result) {
            $this->error('存在子分类,请先删除子分类', U('projectList'), 1);
        }

        // 检查该分类下是否有施工进度文章
        $re = M('platinfo')->where(array('pid' => $where['id']))->find();
        if ($re) {
            $this->error('存在施工进度展示内容,请先删除所有展示内容', U('projectList'), 1);
        }

        // 删除
        if (M('project')->where($where)->delete()) {
            $this->error('删除成功', U('projectList'), 1);
        } else {
            $this->error('删除失败', U('projectList'), 1);
        }
    }

    /**
     * 排序
     */
    public function sort(){
        $this->sortCommon('project', 'projectList');
    }

    /**
     * 选择当前公司的客户状态
     */
    public function checkState(){
        // 判断POST提交
        if (IS_POST) {
            // 获取POST数据
            $state = I('post.state_id');
            foreach ($state as $key => $value) {
                $data[$key]['admin_id'] = session('uid');
                $data[$key]['state_id'] = $value;
            }

            // 批量插入
            if (M('com_state')->addAll($data)) {
                $this->success('添加成功', __SELF__, 1);
            } else {
                $this->error('添加失败', __SELF__, 1);
            }
        } else {
            $all_state = M('state')->where(array('id' => array('GT', 0)))->select();

            $state = M('com_state')->field('id,state_id,sort')->where(array('admin_id' => session('uid')))->order('sort asc')->select();
            $states = array_column($state, 'state_id');

            $this->assign('list', $all_state);
            $this->assign('state', $state);
            $this->assign('states', $states);
            $this->display();
        }
    }

    /**
     * 状态 来源 装修方式 公共添加模块
     * @param  $field  字段名称
     * @param  $table  表名称
     */
    protected function addCommon($field, $table){
        // 判断是否POST提交
        if (IS_POST) {
            // 验证
            $validate = array(
                array(''.$field.'', 'require', '名称不能为空'),
                //array(''.$field.'', '/^([\x{4e00}-\x{9fa5}]|[a-zA-Z]){3,10}$/u','只能输入汉字和字母')
            );
            D(''.$table.'')->setProperty("_validate",$validate);
            if (!D(''.$table.'')->create()) {
                $this->error(D(''.$table.'')->getError(), __SELF__, 1);
            }

            // 获取POST数据
            $data[''.$field.''] = I(''.$field.'');
            $data['admin_id'] = session('uid');

            // 插入数据
            if (M(''.$table.'')->add($data)) {
                $this->success('添加成功');
            } else {
                $this->error('添加失败', __SELF__, 1);
            }

        } else {
            // 获取来源列表
            if ($table == 'state') {
                $list = M(''.$table.'')->where('id > 0')->select();
            } else {
                $list = M(''.$table.'')->where(array('admin_id' => session('uid')))->order('sort asc')->select();
            }

            $this->assign('list', $list);
            $this->display();
        }
    }

    /**
     * 状态 来源 装修方式 公共更新模块
     * @param  $field  字段名称
     * @param  $table  表名称
     * @param  $link   跳转地址
     */
    protected function updateCommon($field, $table, $link){
        // 判断是否POST提交
        if (IS_POST) {
            // 验证
            $validate = array(
                array(''.$field.'', 'require', '名称不能为空'),
                //array(''.$field.'', '/^([\x{4e00}-\x{9fa5}]|[a-zA-Z]){3,10}$/u','只能输入汉字和字母')
            );
            D(''.$table.'')->setProperty("_validate",$validate);
            if (!D(''.$table.'')->create()) {
                $this->error(D(''.$table.'')->getError(), U(''.$link.''), 1);
            }

            // 获取POST数据
            $data[''.$field.''] = I(''.$field.'');
            $data['id'] = I('post.id');

            // 插入数据
            if (M(''.$table.'')->save($data)) {
                $this->success('更新成功', U(''.$link.''));
            } else {
                $this->error('更新失败', U(''.$link.''), 1);
            }

        } else {
            // ajax传值 被点击修改的工序ID
            $info = M(''.$table.'')->where(array('id' => $this->_get('id')))->find();

            $hidden = "<input type='hidden' name='id' value='".$info['id']."'>";  //标记需要修改的记录ID
            $content = "<textarea class='form-control' name='$field' style='height:100px;'>".$info[''.$field.'']."</textarea>";
            echo $hidden.$content;
        }
    }

    /**
     * 状态 来源 装修方式 公共删除模块
     * @param  $table  表名称
     */
    protected function deleteCommon($table){
        // 获取要删除的信息ID
        $id = I('get.id', 0, 'intval');

        // 获取当前公司下的所有客户
        $group = array_column(M('group')->field('id')->where(array('admin_id' => session('uid')))->select(), 'id');
        $user = array_column(M('users_group')->field('uid')->where(array('group_id' => array('IN', $group)))->select(), 'uid');

        // 客户状态表
        if ($table == 'com_state') {
            $id = '\','.$id.',\'';  //重组ID成字符串 防止模糊查询
            $result = M('customer')->where(array('Userid' => array('IN', $user), '_string' => "POSITION($id IN CONCAT(',',State,','))"))->find();
        }

        // 装修方式表
        if ($table == 'way') {
            $result = M('customer')->where(array('Userid' => array('IN', $user), 'Way' => $id))->find();
        }

        // 来源渠道表
        if ($table == 'channel') {
            $result = M('customer')->where(array('Userid' => array('IN', $user), 'Channel' => $id))->find();
        }

        // 如果存在符合条件的客户信息
        if ($result) {
            $this->error('该记录下存在客户信息,不能删除,请先删除客户信息!');
        }

        if (M(''.$table.'')->delete($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 公共排序模块 对指定的表就行排序
     * @param $table    需要排序的数据表
     * @param $url      排序后的跳转路径
     */
    protected function sortCommon($table, $url){
        // 判断POST提交
        if (!IS_POST) {
            $this->error('您请求的页面不存在');
        }

        // 获取POST数据
        $data['id'] = I('post.id');         // 需要排序的ID
        $data['sort'] = I('post.sort');    // 手工排序的值
        $data = array_combine($data['id'], $data['sort']);  //合并数组, 前者的值为key, 后者的值为value

        // 更新排序 遍历更新
        foreach ($data as $key => $value) {
             M(''.$table.'')->where(array('id' => $key))->setField('sort', $value);
        }

        $this->success('排序成功', U(''.$url.''), 1);
    }

    /**
     * 小区管理
     */
    public function newClass()
    {
        if(IS_POST)
        {
            $info = D('NewClass')->create();
            if(!$info)
                $this->ajaxReturn(array('code' => 0, 'msg' => D('NewClass')->getError()));
            $res = D('NewClass')->add();
            if($res)
                $this->ajaxReturn(array('code' => 1, 'msg' => '添加成功'));
        }else{
            $list = D("NewClass")->where(array('pid' => 0))->select();
            $pname = D("NewClass")->where(array('pid' => 0))->select();
            foreach ($list as $k => $v){
                $list[$k]['child'] = D('NewClass')->where(array('pid' => $v['class_id']))->select();
            }
            $this->assign('list', $list);
            $this->assign('pname', $pname);
            $this->display();
        }
    }

    /**
     * 动态获取列表
     */
    public function ajaxNewClass()
    {
        $list = D("NewClass")->where(array('pid' => 0))->select();
        foreach ($list as $k => $v){
            $list[$k]['child'] = D('NewClass')->where(array('pid' => $v['class_id']))->select();
        }
        $this->assign('list', $list);
        $this->display('ajaxClassList');
    }

    /**
     * 修改小区
     */
    public function alertNewClass()
    {
        if(IS_POST){
            $info = D('NewClass')->create();
            if(!$info) {
                $this->ajaxReturn(array('code'=> 0, 'msg' => '没有任何修改'));
            }
            $res = D('NewClass')->where(array('class_id' => $info['class_id']))->save($info);
            if($res){
                $this->ajaxReturn(array('code'=> 1, 'msg' => '修改成功'));
            }else{
                $this->ajaxReturn(array('code'=> 0, 'msg' => '修改失败'));
            }
        }
    }

    /**
     * 删除一个小区
     */
    public function delNewClass()
    {
        if(IS_POST){
            $class_id = $this->_param('class_id');
            $info = D('NewClass')->where(array('pid' => $class_id))->select();
            if($info){
                $this->ajaxReturn(array('code' => 0, 'msg' => '存在二级数据，无法删除'));
            }
            $res = D('NewClass')->where(array('class_id' => $class_id))->delete();
            if($res){
                $this->ajaxReturn(array('code' => 1, 'msg' => '删除成功'));
            }
        }
    }
}