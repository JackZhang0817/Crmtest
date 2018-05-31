<?php
/**
 * Author: gaorenhua
 * Date: 2014-11-18
 * Email: 597170962@qq.com
 * 客户管理控制器
 */
class CustomerAction extends CommonAction {
    /**
     * 我的客户
     */
    public function customerList(){
        // 点击排序
        $sort = I('get.sort');
        $status = I('get.status') ? 0 : 1;

        // 调用公共分页
        $this->pageCommon($map, $sort, $status);
        $this->display();
    }

    /**
     * 添加客户
     */
    public function addCustomer(){
        // 判断是否POST提交
        if (IS_POST) {
            // 创建数据集
            $data = D('Customer')->create();

            // 判断手机号是否重复
            $map['pid'] = fid();
            $user = array_column(M('users')->where($map)->field('id')->select(), 'id');
            if (M('customer')->where(array('Userid' => array('IN', $user)))->where("Tel =".I('post.Tel'))->select()) {
                $this->error('该客户已存在', __SELF__, 1);
            }

            // 自动验证
            if (!$data) {
                $this->error(D('Customer')->getError(), __SELF__, 1);
            }

            // 业务员不能为空
            if (!$data['Userid']) {
                $this->error('业务员不能为空', __SELF__, 1);
            }

            // 处理状态
            $data['State'] = empty($data['State']) ? 0 : implode(',', $data['State']);

            // 处理标记颜色
            $color = I('post.markcolor');
            $arr = array(session('uid') => $color);     //每个层级的用户对应一个颜色
            $arr = empty($color) ? $color : serialize($arr);    //不为空的话,序列化数组

            // 提取附加表的POST数据 标记颜色
            $data['attached'] = array(
                'entrytime' => $_SERVER['REQUEST_TIME'],
                'markcolor' => $arr
            );

            // 插入追踪记录
            $content = I('post.content');
            if (!empty($content)) {
                $data['record'] = array('entrytime' => $_SERVER['REQUEST_TIME'], 'content' => $content, 'uid' => session('uid'));
            }

            $data['material_info'] = json_encode($data['material_info']);
            // 添加客户 成功 返回记录ID 失败返回false
            $id = D('Customer')->relation(true)->add($data);
            if ($id) {
                // 添加操作记录 以便及时提醒相关人员
                $this->addnews($data['Designer'], $data['Userid'], $data['Project'], $id, 'Customer/visitRecord', '添加了一条客户信息');
                $this->_handleMaterialHistory($id, $data['material_info']);

                $this->success('添加成功', U('customerList'), 1);
            } else {
                $this->error('添加失败', __SELF__, 1);
            }
        } else {
            // 查询工程组属性的部门
            $where = array(
                'admin_id' => fid(),
                'class' => 5
            );
            $materialType = D('MaterialType');
            $materialTypeList = $materialType->select();
            $group = M('group')->where($where)->field('id,title')->select();

            $this->assign('materialTypeList', $materialTypeList);
            $this->group = $group;
            $this->attachedInfo();
            $this->display();
        }
    }

    /**
     * 检查是否有重复客户
     */
    public function checktel(){
        $tel = I('post.Tel');
        if(empty($tel)){
            echo '';
        }else{
            // 获取当前公司的所有员工
            $user = M('users')->where(array('pid' => fid()))->getField('id', true);

            // 查询符合条件的客户信息
            $where['Userid'] = array('IN', $user);
            $where['Tel'] = $tel;
            $re = M('customer')->where($where)->find();
            if(!empty($re)){
                echo "<a style='font-size:12px; color:red;' target='_balnk' href=".U('Customer/visitRecord',array('id' => $re['id'])).">已存在，点击查看</a>";
            }else{
                echo '';
            }
        }
    }

    /**
     * 修改客户信息
     */
    public function updateCustomer(){
        // 判断是否POST提交
        if (IS_POST) {
            // 创建数据集
            $data = D('Customer')->create();

            // 自动验证
            if (!$data) {
                $this->error(D('Customer')->getError(), __SELF__, 1);
            }

            // 业务员不能为空
            if (!$data['Userid']) {
               $this->error('业务员不能为空', __SELF__, 1);
            }

            // 处理状态
            $data['State'] = empty($data['State']) ? 0 : implode(',', $data['State']);

            // 获取附加数据  标记颜色
            $color = I('post.markcolor');

            // 处理标记颜色
            $oldcolor = M('customer_attached')->where(array('customer_id' => $data['id']))->getField('markcolor');

            // 判断原有颜色是否空 如果空 不处理  不为空的话 反序列化一下
            if (!empty($oldcolor)) {
                $array = unserialize($oldcolor);

                // 判断该用户ID是否标记颜色 如果标记了则更新 没标记 则插入原有数组
                if (!empty($color)) {
                    if (!empty($array[session('uid')])) {
                        $array[session('uid')] = $color;
                    } else {
                        $array = merge_array($array, array(session('uid') => $color));
                    }
                    $data['attached'] = array('markcolor' => serialize($array));
                } else {
                    if (!empty($array[session('uid')])) {
                        unset($array[session('uid')]);  //更新标记颜色为空的时候 删除掉原有颜色
                        $data['attached'] = array('markcolor' => serialize($array));
                    } else {
                        $data['attached'] = array('markcolor' => $oldcolor);
                    }
                }
                $data['material_info'] = json_encode($data['material_info']);

            } else {
                // 判断该用户ID是否标记颜色 如果标记了则更新 没标记 则插入原有数组
                if (!empty($color)) {
                    $data['attached'] = array(
                        'markcolor' => serialize(array(session('uid') => $color))
                    );
                }
            }

            // 更新客户信息
            if (D('Customer')->relation(true)->save($data) !== false) {
                // 添加操作记录 以便及时提醒相关人员
                $this->addnews($data['Designer'], $data['Userid'], $data['Project'], $data['id'], 'Customer/visitRecord', '更新了一条客户信息');
                $this->_handleMaterialHistory($data['id'], $data['material_info']);

                $this->success('更新成功', U('customerList'), 1);
            } else {
                $this->error('更新失败', __SELF__, 1);
            }
        } else {
            // 查询当前客户信息
            $info = D('CustomerView')->where(array('id' => $this->_get('id')))->find();
//            $info['material_info'] = json_decode($info['material_info'], true);

            // 查询工程组属性的部门
            $where = array(
                'admin_id' => fid(),
                'class' => 5
            );
            $group = M('group')->where($where)->field('id,title')->select();

            $materialType = D('MaterialType');
            $materialTypeList = $materialType->select();

            $this->group = $group;
            $this->assign('materialTypeList', $materialTypeList);
            $this->attachedInfo();
            $this->assign('info', $info);
            $this->display();
        }
    }

    /**
     * 快捷操作
     */
    public function fastUpdate(){
        //判断提交方式
        if (!IS_POST) {
            $this->error('您请求的页面不存在');
        }

        // 提取POST数据
        $data['id'] = I('post.id', 0, 'intval');    // 客户ID
        $field = I('post.name');    // 需要修改的字段
        $value = I('post.value');   // 新数据
        $data[''.$field.''] = trim($value);

        // 更新数据
        if (M('customer')->save($data) !== false) {
            echo true;
        } else {
            echo false;
        }
    }

    /**
     * 删除客户
     */
    public function deleteCustomer(){
        // 判断是否GET传值
        if (!IS_GET) {
            $this->error('请求错误');
        }

        // 提取客户ID 更改status状态
        $data['id'] = I('get.id', 0, 'intval');
        $data['status'] = '1';

        // 插入附加信息 删除人 删除时间
        $data['attached'] = array(
            'del_time' => $_SERVER['REQUEST_TIME'],
            'del_name' => session('uid')
        );

        // 删除客户
        if (D('Customer')->relation(true)->save($data)) {
            $this->success('删除成功,删除客户均可在回收站查看!', U('customerList'), 1);
        } else {
            $this->error('删除失败', U('customerList'), 1);
        }
    }

    /**
     * 回收站
     */
    public function trash(){
        // 调用公共分页
        $this->pageCommon($map);
        $this->display();
    }

    /**
     * 恢复已删除的客户
     */
    public function recovery(){
        // 判断get提交
        if (!IS_GET) {
            $this->error('您请求的页面不存在');
        }

        // 获取客户ID
        $data['id'] = I('get.id', 0, 'intval');
        $data['status'] = '0';

        // 更新数据
        if (M('customer')->save($data)) {
            $this->success('恢复成功', U('trash'), 1);
        } else {
            $this->error('恢复失败', U('trash'), 1);
        }
    }

    /**
     * 彻底删除客户
     */
    public function deleteForever(){
        // 判断get提交
        if (!IS_GET) {
            $this->error('您请求的页面不存在');
        }

        // 获取客户ID
        $data['id'] = I('get.id', 0, 'intval');

        // 判断该客户是否开通客户平台 开通客户平台的客户不能删除
        $cid = M('customer_platform')->where(array('customer_id' => $data['id']))->find();
        if ($cid) {
            $this->error('该客户已开通客户平台,不能删除!!');
        }

        // 更新数据
        if (M('customer')->where($data)->delete()) {
            $this->success('删除成功', U('trash'), 1);
        } else {
            $this->error('删除失败', U('trash'), 1);
        }
    }

    /**
     * 追踪记录
     */
    public function visitRecord(){
        // 判断POST提交
        if (IS_POST) {
            // 提取POST数据
            $data['uid'] = session('uid');
            $data['customer_id'] = I('customer_id', 0, 'intval');
            $data['content'] = I('post.record');
            $data['entrytime'] = $_SERVER['REQUEST_TIME'];

            // 追踪记录是否空
            if (empty($data['content'])) {
                $this->error('追踪记录内容不能为空', __SELF__, 1);
            }

            // 查询是否存在该客户
            $c = M('customer')->field('id,Designer,Userid,Project')->where(array('id' => $data['customer_id']))->find();
            if (!$c) {
                $this->error('不存在该客户', __SELF__, 1);
            }

            // 添加记录
            if (M('record')->add($data)) {
                // 添加操作记录 以便及时提醒相关人员
                $this->addnews($c['Designer'], $c['Userid'], $c['Project'], $c['id'], 'Customer/visitRecord', '添加了一条回访信息');

                $this->success('添加成功', __SELF__, 1);
            } else {
                $this->error('添加失败', __SELF__, 1);
            }
        } else {
            // 客户信息
            $info = D('CustomerView')->where(array('id' => $this->_get('id')))->find();

            // 上一个客户  下一个客户
            $data = array(
                'id' => array('LT',I('get.id', 0, 'intval')),
                'status' => 0,
                '_complex' => $this->where()
            );
            $data2 = array(
                'id' => array('GT',I('get.id', 0, 'intval')),
                'status' => 0,
                '_complex' => $this->where()
            );

            $pre = M('customer')->where($data)->order('id desc')->getField('id');
            $next = M('customer')->where($data2)->order('id asc')->getField('id');

            // 追踪记录
            $record = M('record')->where(array('customer_id' => $this->_get('id')))->select();

            $this->assign('record', $record);
            $this->assign('info', $info);
            $this->pre = $pre;
            $this->next = $next;
            $this->display();
        }
    }

    /**
     * 回访追踪记录
     */
    public function remindvisitRecord(){
        // 判断POST提交
        if (IS_POST) {
            // 提取POST数据
            $data['uid'] = session('uid');
            $data['customer_id'] = I('customer_id', 0, 'intval');
            $data['content'] = I('post.record');
            $data['entrytime'] = $_SERVER['REQUEST_TIME'];

            // 追踪记录是否空
            if (empty($data['content'])) {
                $this->error('追踪记录内容不能为空', __SELF__, 1);
            }

            // 查询是否存在该客户
            $c = M('customer')->field('id,Designer,Userid,Project')->where(array('id' => $data['customer_id']))->find();
            if (!$c) {
                $this->error('不存在该客户', __SELF__, 1);
            }

            // 添加记录
            if (M('record')->add($data)) {
                // 添加操作记录 以便及时提醒相关人员
                $this->addnews($c['Designer'], $c['Userid'], $c['Project'], $c['id'], 'Customer/visitRecord', '添加了一条回访信息');

                $this->success('添加成功', __SELF__, 1);
            } else {
                $this->error('添加失败', __SELF__, 1);
            }
        } else {
            // 回访时间段设置 1-当天 2-本周 3-当月 4-自定义[待开发]
            $times = I('get.times', 0, 'intval');

            //当前时间 和 本周最后一天  周日为每周的开始
            $thisdaytime = date('Y-m-d');
            $yesterday = date("Y-m-d",strtotime("-1 day")); // 昨天
            $this_week_last_day = date('Y-m-d',time() + 24 * 60 * 60 * 6);

            // 判断属于哪个部门
            if (is_salesman()) {
                if ($times == 1) {
                    $map['sremind'] = $thisdaytime;
                } elseif ($times == 2) {
                    $map['sremind'] = array('BETWEEN', array("$thisdaytime", "$this_week_last_day"));
                } else {
                    $map['sremind'] = array('BETWEEN', array("2010-01-01", "$yesterday"));
                }
            } elseif (is_designer()) {
                if ($times == 1) {
                    $map['dremind'] = $thisdaytime;
                } elseif ($times == 2) {
                    $map['dremind'] = array('BETWEEN', array("$thisdaytime", "$this_week_last_day"));
                } else {
                    $map['dremind'] = array('BETWEEN', array("2010-01-01", "$yesterday"));
                }
            } elseif (is_project()) {
                if ($times == 1) {
                    $map['premind'] = $thisdaytime;
                } elseif ($times ==2) {
                    $map['premind'] = array('BETWEEN', array("$thisdaytime", "$this_week_last_day"));
                } else {
                    $map['premind'] = array('BETWEEN', array("2010-01-01", "$yesterday"));
                }
            } elseif (is_admin()) {
                if ($times == 1) {
                    $map['_string'] = "`sremind`='$thisdaytime' OR `dremind`='$thisdaytime' OR `premind`='$thisdaytime'";
                } elseif ($times == 2) {
                    $map['_string'] = "`sremind` between '$thisdaytime' AND '$this_week_last_day' OR `dremind` between '$thisdaytime' AND '$this_week_last_day' OR `premind` between '$thisdaytime' AND '$this_week_last_day'";
                } else {
                    $map['_string'] = "`sremind` between '2010-01-01' AND '$yesterday' OR `dremind` between '2010-01-01' AND '$yesterday' OR `premind` between '2010-01-01' AND '$yesterday'";
                }
            } elseif (is_manager()) {
                // 获取职务
                $job = M('users')->where(array('id' => session('uid')))->getField('job');
                if ($job == 1){
                    if ($times == 1) {
                        $map['sremind'] = $thisdaytime;
                    } elseif ($times == 2) {
                        $map['sremind'] = array('BETWEEN', array("$thisdaytime", "$this_week_last_day"));
                    } else {
                        $map['sremind'] = array('BETWEEN', array("2010-01-01", "$yesterday"));
                    }
                } elseif ($job == 2) {
                    if ($times == 1) {
                        $map['dremind'] = $thisdaytime;
                    } elseif ($times == 2) {
                        $map['dremind'] = array('BETWEEN', array("$thisdaytime", "$this_week_last_day"));
                    } else {
                        $map['dremind'] = array('BETWEEN', array("2010-01-01", "$yesterday"));
                    }
                }
            }

            // 客户信息
            $info = D('CustomerView')->where(array('id' => $this->_get('id')))->find();

            // 上一个客户  下一个客户
            $data = array(
                'id' => array('lt',I('get.id', 0, 'intval')),
                'status' => 0,
                '_complex' => $this->where()
            );
            $data2 = array(
                'id' => array('gt',I('get.id', 0, 'intval')),
                'status' => 0,
                '_complex' => $this->where()
            );

            $pre = M('customer')->where($data)->where($map)->order('id desc')->getField('id');
            $next = M('customer')->where($data2)->where($map)->order('id asc')->getField('id');

            // 追踪记录
            $record = M('record')->where(array('customer_id' => $this->_get('id')))->select();

            $this->assign('record', $record);
            $this->assign('info', $info);
            $this->pre = $pre;
            $this->next = $next;
            $this->display();
        }
    }

    /**
     * 更新回访时间
     */
    public function upremind(){
        // 判断提交方式
        if(!IS_POST){
            $this->error('您请求的页面不存在');
        }

        // 获取客户ID
        $data['id'] = I('post.id', 0, 'intval');

        // 获取回访时间
        if(is_salesman()){
            $data['sremind'] = I('post.sremind');
        }elseif(is_designer()){
            $data['dremind'] = I('post.dremind');
        }elseif(is_project()){
            $data['premind'] = I('post.premind');
        }else{
            $this->error('参数不正确');
        }

        //更新
        if(M('customer')->save($data)){
            $this->success('更新成功');
        } else{
            $this->error('更细失败');
        }
    }

    /**
     * 更新追踪记录
     */
    public function updateRecord(){
        // POST提交
        if (IS_POST) {
            // 提取POST数据
            $data['id'] = I('post.record_id', 0, 'intval');
            $data['content'] = I('post.record');
            $cid = I('post.customer_id', 0, 'intval');

            // 更新客户的回访记录
            // 获取回访时间
            $map['id'] = I('post.customer_id', 0, 'intval');
            if(is_salesman()){
                $map['sremind'] = I('post.sremind');
            }elseif(is_designer()){
                $map['dremind'] = I('post.dremind');
            }elseif(is_project()){
                $map['premind'] = I('post.premind');
            }else{
                $this->error('参数不正确');
            }

            // 追踪记录是否空
            if (empty($data['content'])) {
                $this->error('追踪记录内容不能为空', U('visitRecord', array('id' => $cid)), 1);
            }

            // 查询是否该用户录入的记录
            if (!M('record')->where(array('id' => $data['id'], 'uid' => session('uid')))->find()) {
                $this->error('仅能修改当前用户的记录', U('visitRecord', array('id' => $cid)), 1);
            }

            // 修改记录
            if (M('record')->save($data) || M('customer')->save($map)) {
                $this->success('修改成功', U('visitRecord', array('id' => $cid)), 1);
            } else {
                $this->error('修改失败', U('visitRecord', array('id' => $cid)), 1);
            }
        } else {
            // ajax传值 被点击修改的追踪记录ID
            $uprecord = M('record')->where(array('id' => $this->_get('id')))->find();

            $hidden = "<input type='hidden' name='record_id' value='".$uprecord['id']."'>";  //标记需要修改的记录ID
            $cid = "<input type='hidden' name='customer_id' value='".$uprecord['customer_id']."'>";  //标记客户ID-跳回修改前的页面
            $content = "<textarea class='form-control' name='record' style='height:100px;'>".$uprecord['content']."</textarea>";
            echo $hidden.$cid.$content;
        }
    }

    /**
     * 删除追踪记录
     */
    public function deleteRecord(){
        // 判断是否GET提交
        if (!IS_GET) {
            $this->error('您的请求不存在', __SELF__, 1);
        }

        // 提取POST数据
        $data['id'] = I('get.id', 0, 'intval');
        $cid = I('get.cid', 0, 'intval');

        // 查询是否该用户录入的记录
        if (!M('record')->where(array('id' => $data['id'], 'uid' => session('uid')))->find()) {
            $this->error('仅能删除当前用户的记录', U('visitRecord', array('id' => $cid)), 1);
        }

        // 删除操作ID的记录
        if (M('record')->where($data)->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 获取被点击状态下的所有客户信息
     */
    public function getStateCustomer(){
        // 判断是否GET提交
        if (!IS_GET) {
            $this->error('请求的页面不存在', U('customerList'), 1);
        }

        // 获取当前状态下的状态ID
        $state = '\','.$this->_get('state').',\'';
        $map['_string'] = "POSITION($state IN CONCAT(',',State,','))";

        // 调用公共分页
        $this->pageCommon($map);
        $this->display('customerList');
    }

    /**
     * 搜索模块
     */
    public function search(){
        // 判断是否GET提交
        if (!IS_GET) {
            $this->error('您的请求不存在', U('customerList'), 1);
        }

        // 提取GET数据
        $customername = I('get.CName');
        $tel = I('get.Tel');
        $address = I('get.Address');
        $userid = I('get.Userid');
        $designer = I('get.Designer');
        $way = I('get.Way');
        $channel = I('get.Channel');
        $consultdate = I('get.ConsultDate');
        $consultdate1 = I('get.ConsultDate1');
        $state = I('get.State');
        $cometime = I('get.ComeTime');
        $cometime1 = I('get.ComeTime1');
        $ordertime = I('get.OrderTime');
        $ordertime1 = I('get.OrderTime1');
        $hetongtime = I('get.HetongTime');
        $hetongtime1 = I('get.HetongTime1');

        // 客户姓名
        if(isset($customername) && !empty($customername)){
            $map['CName'] = $customername;
        }
        if(isset($tel) && !empty($tel)){
            $map['Tel'] = $tel;
        }
        if(isset($address) && !empty($address)){
            $map['Address'] = $address;
        }
        if(isset($way) && !empty($way)){
            $map['Way'] = $way;
        }
        if(isset($channel) && !empty($channel)){
            $map['Channel'] = $channel;
        }
        if(isset($designer) && !empty($designer)){
            $map['Designer'] = $designer;
        }
        if(isset($state) && !empty($state)){
            // $state = implode(',', $state);
            $map['_string'] = "POSITION($state IN CONCAT(',',State,','))";
        }
        if(isset($userid) && !empty($userid)){
            $map['Userid'] = $userid;
        }
        if(isset($consultdate) && !empty($consultdate)){
            if(isset($consultdate1) && !empty($consultdate1)){
                $map['ConsultDate'] = array('between',array("$consultdate","$consultdate1"));
            }else{
                $map['ConsultDate'] = $consultdate;
            }
        }
        if(isset($cometime) && !empty($cometime)){
            if(isset($cometime) && !empty($cometime)){
                $map['ComeTime'] = array('between',array("$cometime","$cometime1"));
            }else{
                $map['ComeTime'] = $cometime;
            }
        }
        if(isset($ordertime) && !empty($ordertime)){
            if(isset($ordertime1) && !empty($ordertime1)){
                $map['OrderTime'] = array('between',array("$ordertime","$ordertime1"));
            }else{
                $map['OrderTime'] = $ordertime;
            }
        }
        if(isset($hetongtime) && !empty($hetongtime)){
            if(isset($hetongtime1) && !empty($hetongtime1)){
                $map['HetongTime'] = array('between',array("$hetongtime","$hetongtime1"));
            }else{
                $map['HetongTime'] = $hetongtime;
            }
        }

        // 点击排序
        $sort = I('get.sort');
        $status = I('get.status') ? 0 : 1;

        // 调用公共分页
        $this->pageCommon($map, $sort, $status);
        $sum  = D('CustomerView')->where($map)->Sum('OrdersValue');  // 符合查询条件的总单值
        $this->assign('sum', $sum);
        $this->display();
    }

    /**
     * 搜索模块
     */
    public function t_search(){
        // 判断是否GET提交
        if (!IS_GET) {
            $this->error('您的请求不存在', U('customerList'), 1);
        }

        // 提取GET数据
        $customername = I('get.CName');
        $tel = I('get.Tel');
        $address = I('get.Address');
        $userid = I('get.Userid');
        $designer = I('get.Designer');
        $way = I('get.Way');
        $channel = I('get.Channel');
        $consultdate = I('get.ConsultDate');
        $consultdate1 = I('get.ConsultDate1');
        $state = I('get.State');
        $cometime = I('get.ComeTime');
        $cometime1 = I('get.ComeTime1');
        $ordertime = I('get.OrderTime');
        $ordertime1 = I('get.OrderTime1');
        $hetongtime = I('get.HetongTime');
        $hetongtime1 = I('get.HetongTime1');

        // 客户姓名
        if(isset($customername) && !empty($customername)){
            $map['CName'] = $customername;
        }
        if(isset($tel) && !empty($tel)){
            $map['Tel'] = $tel;
        }
        if(isset($address) && !empty($address)){
            $map['Address'] = $address;
        }
        if(isset($way) && !empty($way)){
            $map['Way'] = $way;
        }
        if(isset($channel) && !empty($channel)){
            $map['Channel'] = $channel;
        }
        if(isset($designer) && !empty($designer)){
            $map['Designer'] = $designer;
        }
        if(isset($state) && !empty($state)){
            // $state = implode(',', $state);
            $map['_string'] = "POSITION($state IN CONCAT(',',State,','))";
        }
        if(isset($userid) && !empty($userid)){
            $map['Userid'] = $userid;
        }
        if(isset($consultdate) && !empty($consultdate)){
            if(isset($consultdate1) && !empty($consultdate1)){
                $map['ConsultDate'] = array('between',array("$consultdate","$consultdate1"));
            }else{
                $map['ConsultDate'] = $consultdate;
            }
        }
        if(isset($cometime) && !empty($cometime)){
            if(isset($cometime) && !empty($cometime)){
                $map['ComeTime'] = array('between',array("$cometime","$cometime1"));
            }else{
                $map['ComeTime'] = $cometime;
            }
        }
        if(isset($ordertime) && !empty($ordertime)){
            if(isset($ordertime1) && !empty($ordertime1)){
                $map['OrderTime'] = array('between',array("$ordertime","$ordertime1"));
            }else{
                $map['OrderTime'] = $ordertime;
            }
        }
        if(isset($hetongtime) && !empty($hetongtime)){
            if(isset($hetongtime1) && !empty($hetongtime1)){
                $map['HetongTime'] = array('between',array("$hetongtime","$hetongtime1"));
            }else{
                $map['HetongTime'] = $hetongtime;
            }
        }

        $map['status'] = 1;

        // 点击排序
        $sort = I('get.sort');
        $status = I('get.status') ? 0 : 1;

        // 调用公共分页
        $this->pageCommon($map, $sort, $status);
        $sum  = D('CustomerView')->where($map)->Sum('OrdersValue');  // 符合查询条件的总单值

        $this->assign('sum', $sum);
        $this->display();
    }

    /**
     * 获取当前登录用户的用户id, realname信息
     * return array
     */
    protected function thisUser(){
        return M('users')->field('id,realname')->where(array('id' => session('uid')))->select();
    }

    /**
     * 根本不同用户的个性化选择  定义不同的客户列表  以显示不同的字段 主要用于客户管理模块
     */
    public function displayFields(){
        //判断是否POST提交
        if (!IS_POST) {
            $this->error('您请求的页面不存在');
        }

        //提取POST数据
        $data['uid'] = session('uid');
        $data['display_field'] = $this->_post('tijiao');

        M('user_defined')->save($data);
    }

    /**
     * 根本不同用户的个性化选择  定义不同的客户列表  以显示不同的字段 主要用于工程管理模块
     */
    public function projectFields(){
        //判断是否POST提交
        if (!IS_POST) {
            $this->error('您请求的页面不存在');
        }

        //提取POST数据
        $data['uid'] = session('uid');
        $data['project_field'] = $this->_post('tijiao');

        M('user_defined')->save($data);
    }

    /**
     * 选择需要显示的状态客户
     */
    public function chooseState(){
        if(IS_POST){
            // 提取POST数据
            $state = implode(',', I('post.state'));

            $data = array(
                'uid' => session('uid'),
                'state' => $state
            );
            // 写入数据库
            if(M('user_defined')->save($data)){
                $this->success('保存成功');
            }else{
                $this->error('保存失败');
            }
        }else{
            // 获取当前公司的所有客户状态
            $state = M('com_state')->field('id,state_id')->where(array('admin_id' => fid()))->order('sort asc')->select();

            // 获取当前用户选中的已选中的客户状态
            $stated = M('user_defined')->where(array('uid' => session('uid')))->getField('state');
            $stated = explode(',', $stated);

            // 循环替换状态名
            foreach($state as $k => $v){
                $state[$k]['state_id'] = stateName($v['state_id']);
            }

            $this->assign('state', $state);
            $this->assign('stated', $stated);
            $this->display();
        }
    }

    /**
     * 获取业务员 设计师 装修方式 客户状态等等
     */
    protected function attachedInfo(){
        // 判断是否存在父级ID 如果存在直接调用父级ID(pid)  若不存在直接调用session('uid')
        $fid = M('users')->where(array('id' => session('uid')))->getField('pid');

        // 获取职务
        $job = M('users')->where(array('id' => session('uid')))->getField('job');

        // 判断是否是超级管理员 或者 经理组用户
        if (is_admin() || is_manager() || is_project()) {
            // 判断总监  此处的1-2和组属性的1-2对应
            if ($job == '1') {   //业务总监
                $user = array_merge(each_group_users($job), $this->thisUser());
            } elseif ($job == '2'){  //设计总监
                $user = array_merge(each_group_users(1), each_group_users($job), $this->thisUser());
            } else {
                $map['pid'] = fid();
                $map['username'] = array('NEQ', '');
                $user = M('users')->where($map)->field('id,realname')->select();

                // 此处判断一下公司管理员是否添加了员工
                $user = empty($user) ? array('0' => array('id' => '0', 'realname'=> '请先添加公司员工')) : $user;
            }
        }

        // 判断是否是设计师 如果是设计师 添加客户的时候需要选择业务员
        if (is_designer()) {
            // 判断部门经理
            if ($job == '3') {
                $map1['id'] = array('IN', departusers());
                $map1['username'] = array('NEQ', '');
                $user = M('users')->field('id,realname')->where($map1)->select();
                $user = array_merge($user, each_group_users('1'));
            } else{
                $user = array_merge(each_group_users('1'), $this->thisUser());  //合并当前用户
            }
        }

        // 判断业务员
        if (is_salesman()) {
            // 判断部门经理
            $user = each_group_users('1');
        }

        // 调用当前管理员添加的状态 方式 来源
        $where['admin_id'] = fid();

        // 跟踪状态
        $state = M('com_state')->field('id,state_id')->where($where)->order('sort')->select();
        session('state', $state);

        // 客户来源
        $channel = M('channel')->field('id,channelname')->where($where)->order('sort')->select();
		
		//房屋类型
		$room_type = M('room_type')->field('id,room_type_name')->where($where)->order('sort')->select();
		
        // 装修方式
        $way = M('way')->field('id,wayname')->where($where)->order('sort')->select();

        // 自定义显示字段
        $fields = M('user_defined')->field('display_field')->where(array('uid' => session('uid')))->find();
        session('display_field', $fields);

        // 接单设计师  设计组 2
        $designer = each_group_users('2');

        // 工程监理  工程组 5
        $projecter = each_group_users('5');

        // 施工队长 施工组 6
        $captioner = each_group_users('6');

        // CAD制图组
        $drawer = each_group_users('7');

        // 效果图制图组
        $xiaoguotu = each_group_users('8');

        // 材料员
        $material = each_group_users('9');

        $this->assign('users', $user);
        $this->assign('state', $state);
        $this->assign('channel', $channel);
		$this->assign('room_type', $room_type);
        $this->assign('way', $way);
        $this->assign('designer', $designer);
        $this->assign('project', $projecter);
        $this->assign('caption', $captioner);
        $this->assign('drawer', $drawer);
        $this->assign('xiaoguotu', $xiaoguotu);
        $this->assign('material', $material);
    }

    /**
     * 综合查询条件
     */
    protected function where(){
        // 判断是否是超级管理员
        if (in_array(session('uid'), C('ADMINISTRATOR'))) {
            $user = array_column(M('users')->field('id')->select(), 'id');
            return $where = array('Userid' => array('IN', $user));
//            return $where = array();
        } else {
            // 获取职务
            $job = M('users')->where(array('id' => session('uid')))->getField('job');

            // 判断管理员
            if (is_admin() || is_manager() || is_finance()) {
                // 判断总监  此处的1-2和组属性的1-2对应
                if ($job == '1' || $job == '2') {
                    $user = array_column(each_group_users($job), 'id');
                } else {
                    $map['pid'] = fid();
                    $user = array_column(M('users')->where($map)->field('id')->select(), 'id');
                }

                // 查询符合条件的客户信息
                return $where = array('Userid' => array('IN', $user));
            }

            // 判断业务员
            if (is_salesman()) {
                // 判断经理
                if ($job == '3') {
                    return $where = array('Userid' => array('IN', departusers()));
                } else { //普通员工
                    return $where = array('Userid' => session('uid'));
                }
            }

            // 判断设计师
            if (is_designer()) {
                // 判断经理
                if ($job == '3') {
                    return $where = array('Userid' => array('IN', departusers()), 'Designer' => array('IN', departusers()), '_logic' => 'or');
                } else { //普通员工
                    return $where = array('Userid' => session('uid'), 'Designer' => session('uid'), '_logic' => 'or');
                }
            }

            // 判断工程监理
            if (is_project()) {
                // 判断经理
                if ($job == '3') {
                    return $where = array('Project' => array('IN', departusers()));
                } else {
                    return $where = array('Project' => session('uid'));
                }
            }

            // 判断工长
            if (is_captain()) {
                // 判断经理
                if ($job == '3') {
                    return $where = array('Captain' => array('IN', departusers()));
                } else {
                    return $where = array('Captain' => session('uid'));
                }
            }

            // 判断制图员
            if (is_drawing()) {
                // 判断经理
                if ($job == '3') {
                    return $where = array('Drawing' => array('IN', departusers()));
                } else {
                    return $where = array('Drawing' => session('uid'));
                }
            }

            // 判断材料员
            if (is_material()) {
                // 判断经理
                if ($job == '3') {
                    return $where = array('Material' => array('IN', departusers()));
                } else {
                    return $where = array('Material' => session('uid'));
                }
            }
        }
    }

    /**
     * 公共分页
     * @param $map           查询条件
     * @param string $sort   需要排序的字段
     * @param int $status    排序规则 升序 or 降序
     */
    public function pageCommon($map, $sort='null', $status=0){
        // 判断是否是回收站
        if (ACTION_NAME == 'trash') {
            $map['status'] = '1';   // 回收站客户
        } else {
            $map['status'] = isset($map['status']) ? $map['status'] : 0;   // 没有被删除的客户
        }

        // 获取当前用户选择的客户ID
        $state = M('user_defined')->where(array('uid' => session('uid')))->getField('state');
        $state = explode(',', $state);
        $state = implode('|', $state);
        if(!empty($state)){
            if(isset($map['_string'])) {
                $map['_string'] = $map['_string'];
            } else {
                $map['_string'] = "`State` REGEXP '$state'";
            }
        }

	
        $map['_complex'] = $this->where(); //并入查询, 否则会按照 OR 的方式查询
 
        // 导入分页类
        import('ORG.Util.Page');
        $count  = D('CustomerView')->where($map)->count();  // 查询记录总数
        $Page   = new Page($count,30);          // 实例化分页类 传入总记录数
        $Page->setConfig('header','个客户');       // 定制分页样式
		//dump(D('CustomerView')->getLastSql());
		//dump($count);
        // 分页跳转的时候保证查询条件
        $get = array_filter($_GET);
        foreach($get as $key=>$val) {
            $Page->parameter .= "$key=".urlencode($val)."&";
        }

        // 分页显示输出
        $show   = $Page->show();

        if (!empty($sort)) {
            if ($status) {
                $list = D('CustomerView')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order("$sort desc,id desc")->select();
            } else {
                $list = D('CustomerView')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order("$sort asc,id desc")->select();
            }
            $this->assign('status', $status);
        } else {
            $list = D('CustomerView')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();
        }
        // 没有客户信息的时候提示
        $empty = '<tr><td colspan="12" style="font-size:14px;height:60px;line-height:60px;color:#D64635;">暂无此状态客户.</td></tr>';

        // 符合查询条件的总单值
        $sum1  = D('CustomerView')->where($map)->Sum('OrdersValue');
        $dingjin  = D('CustomerView')->where($map)->Sum('Deposit');
        $space  = D('CustomerView')->where($map)->Sum('Space');
        $shejifei  = D('CustomerView')->where($map)->Sum('shejifei');
        $guanlifei  = D('CustomerView')->where($map)->Sum('guanlifei');
        $qingfu  = D('CustomerView')->where($map)->Sum('qingfu');
        $zhucai  = D('CustomerView')->where($map)->Sum('zhucai');
        $once  = D('CustomerView')->where($map)->Sum('once');
        $twice  = D('CustomerView')->where($map)->Sum('twice');
        $tirth  = D('CustomerView')->where($map)->Sum('tirth');
        $others  = D('CustomerView')->where($map)->Sum('others');

        $this->assign('sum1', $sum1);
        $this->assign('dingjin', $dingjin);
        $this->assign('space', $space);
        $this->assign('shejifei', $shejifei);
        $this->assign('guanlifei', $guanlifei);
        $this->assign('qingfu', $qingfu);
        $this->assign('zhucai', $zhucai);
        $this->assign('once', $once);
        $this->assign('twice', $twice);
        $this->assign('tirth', $tirth);
        $this->assign('others', $others);
        $this->assign('count', $count);
        $this->assign('customer',$list);        // 赋值数据集
        $this->assign('page',$show);            // 赋值分页输出
        $this->assign('empty',$empty);          // 赋值分页输出
        $this->attachedInfo();
    }

    /**
     * 插入消息提示
     * @param $designer 设计师ID
     * @param $salesman 业务员ID
     * @param $projecter 工程监理ID
     * @param $cid 客户ID
     * @param $url 查看消息的连接
     * @param $operate 进行了什么操作
     * @return mixed　需要推送消息的人员ID组合
     */
    protected function addnews($designer, $salesman, $projecter, $cid, $url, $operate){
        // 获取需要提示的人员ID
        $viewid[] = fid();   // 管理员ID
        // 如果是业务员
        if (is_salesman()) {
            $viewid[] = M('users')->where(array('job' => 1))->getField('id'); // 总监ID
        }

        if (is_designer()) {
            $viewid[] = M('users')->where(array('job' => 2))->getField('id'); // 总监ID
        }

        $viewid[] = M('users')->where(array('id' => array('IN', departusers()), 'job' => 3))->getField('id'); // 部门经理ID
        $viewid[] = $designer;      // 设计师ID
        $viewid[] = $salesman;     // 业务员ID
        $viewid[] = $projecter;     // 工程监理ID

        // 去除数据内的空元素
        $viewid = array_filter($viewid);

        // 去除数组中当前用户的ID
        function isHave($viewid){
            if($viewid!= session('uid')) return true;
        }
        $viewid = array_filter($viewid,"isHave");

        // 循环插入消息
        foreach ($viewid as $vid) {
            $notice['fid'] = fid();  // 公司ID 防止信息错乱
            $notice['cid'] = $cid;    // 客户ID
            $notice['uid'] = session('uid');            // Who 操作人
            $notice['rurl'] = U(''.$url.'', array('id' => $cid));  // 添加的客户信息连接 以便相关人员查看
            $notice['operate'] = $operate;      // How 什么操作
            $notice['entrytime'] = $_SERVER['REQUEST_TIME']; // Time 什么时间
            $notice['viewid'] = $vid;

            // 插入消息
            M('news')->add($notice);
        }
    }

    /**
     * 设置已查看信息的状态
     */
    public function updatenews(){
        // 判断POST提交
        if (!IS_POST) {
            $this->error('您请求的页面不存在');
        }

        // 获取消息ID值
        $where['id'] = I('post.id', 0, 'intval');
        $where['status'] = 1; // 状态1为已查看

        // 更新已查看信息的状态
        M('news')->save($where);
    }

    /**
     * 待回访客户
     */
    public function remindCustomer(){
        // 判断提交方式
        if (!IS_GET) {
            $this->error('您请求的页面不存在');
        }

        // 回访时间段设置 1-当天 2-本周 3-当月 4-自定义[待开发]
        $times = I('get.times', 0, 'intval');

        //当前时间 和 本周最后一天  周日为每周的开始
        $thisdaytime = date('Y-m-d');
        $yesterday = date("Y-m-d",strtotime("-1 day")); // 昨天
        $this_week_last_day = date('Y-m-d',time() + 24 * 60 * 60 * 6);

        // 判断属于哪个部门
        if (is_salesman()) {
            if ($times == 1) {
                $map['sremind'] = $thisdaytime;
            } elseif ($times == 2) {
                $map['sremind'] = array('BETWEEN', array("$thisdaytime", "$this_week_last_day"));
            } else {
                $map['sremind'] = array('BETWEEN', array("2010-01-01", "$yesterday"));
            }
        } elseif (is_designer()) {
            if ($times == 1) {
                $map['dremind'] = $thisdaytime;
            } elseif ($times == 2) {
                $map['dremind'] = array('BETWEEN', array("$thisdaytime", "$this_week_last_day"));
            } else {
                $map['dremind'] = array('BETWEEN', array("2010-01-01", "$yesterday"));
            }
        } elseif (is_project()) {
            if ($times == 1) {
                $map['premind'] = $thisdaytime;
            } elseif ($times ==2) {
                $map['premind'] = array('BETWEEN', array("$thisdaytime", "$this_week_last_day"));
            } else {
                $map['premind'] = array('BETWEEN', array("2010-01-01", "$yesterday"));
            }
        } elseif (is_admin()) {
            if ($times == 1) {
                $map['_string'] = "customer.sremind='$thisdaytime' OR customer.dremind='$thisdaytime' OR customer.premind='$thisdaytime'";
            } elseif ($times == 2) {
                $map['_string'] = "customer.sremind between '$thisdaytime' AND '$this_week_last_day' OR customer.dremind between '$thisdaytime' AND '$this_week_last_day' OR customer.premind between '$thisdaytime' AND '$this_week_last_day'";
            } else {
                $map['_string'] = "customer.sremind between '2010-01-01' AND '$yesterday' OR customer.dremind between '2010-01-01' AND '$yesterday' OR customer.premind between '2010-01-01' AND '$yesterday'";
            }
        } elseif (is_manager()) {
            // 获取职务
            $job = M('users')->where(array('id' => session('uid')))->getField('job');
            if ($job == 1){
                if ($times == 1) {
                    $map['sremind'] = $thisdaytime;
                } elseif ($times == 2) {
                    $map['sremind'] = array('BETWEEN', array("$thisdaytime", "$this_week_last_day"));
                } else {
                    $map['sremind'] = array('BETWEEN', array("2010-01-01", "$yesterday"));
                }
            } elseif ($job == 2) {
                if ($times == 1) {
                    $map['dremind'] = $thisdaytime;
                } elseif ($times == 2) {
                    $map['dremind'] = array('BETWEEN', array("$thisdaytime", "$this_week_last_day"));
                } else {
                    $map['dremind'] = array('BETWEEN', array("2010-01-01", "$yesterday"));
                }
            }
        }

        // 调用公共分页类
        $this->pageCommon($map);
        $this->display();
    }

    /**
     * 导出客户信息EXCEL表
     */
    public function phpExcel(){
        // 导入thinkphp第三方类库
        Vendor ('PhpExcel.PHPExcel');

        // 创建一个读Excel模版的对象
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
        $objPHPExcel = $objReader->load('templets.xls');//读取模板，模版放在根目录
        // 获取当前活动的表
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setTitle('客户信息表');//设置excel标题
        $objActSheet->getDefaultStyle()->getFont()->setName( 'Microsoft YaHei');    //设置字体
        $objActSheet->getDefaultStyle()->getFont()->setSize(10);    //设置字体大小
        $objActSheet->getRowDimension('1')->setRowHeight(39.75);    //设置第一行行高
        $objActSheet->getDefaultRowDimension()->setRowHeight(20);   // 默认行高
        $objActSheet->getDefaultColumnDimension()->setWidth(12);   //内容自适应
        $objActSheet->getStyle('A1:AL1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFC6EFCE'); //设置背景色
        //$objActSheet->getStyle('A1')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)->getColor()->setARGB('FF000000');//顶部边框的颜色

        // 输入客户列表列标题 具体有多少列，有多少就写多少，跟下面的填充数据对应上就可以
        $objActSheet->setCellValue('A1', '客户姓名');
        $objActSheet->setCellValue('B1', '联系方式');
        $objActSheet->setCellValue('C1', '固定电话');
        $objActSheet->setCellValue('D1', '所在小区');
        $objActSheet->setCellValue('E1', '面积');
        $objActSheet->setCellValue('F1', '户型');
        $objActSheet->setCellValue('G1', '交房时间');
        $objActSheet->setCellValue('H1', '量房时间');
        $objActSheet->setCellValue('I1', '装修方式');
        $objActSheet->setCellValue('J1', '来源渠道');
        $objActSheet->setCellValue('K1', '客户状态');
        $objActSheet->setCellValue('L1', '业务员');
        $objActSheet->setCellValue('M1', '设计师');
        $objActSheet->setCellValue('N1', '咨询时间');
        $objActSheet->setCellValue('O1', '到店时间');
        $objActSheet->setCellValue('P1', '定金时间');
        $objActSheet->setCellValue('Q1', '合同时间');
        $objActSheet->setCellValue('R1', '定金金额');
        $objActSheet->setCellValue('S1', '合同金额');
        $objActSheet->setCellValue('T1', '业务员下次回访时间');
        $objActSheet->setCellValue('U1', '设计师下次回访时间');
        $objActSheet->setCellValue('V1', '工程监理下次回访时间');
        $objActSheet->setCellValue('W1', '效果图制图员');
        $objActSheet->setCellValue('X1', 'CAD制图员');
        $objActSheet->setCellValue('Y1', '材料员');
        $objActSheet->setCellValue('Z1', '工程监理');
        $objActSheet->setCellValue('AA1', '工长');
        $objActSheet->setCellValue('AB1', '开工时间');
        $objActSheet->setCellValue('AC1', '竣工时间');
        $objActSheet->setCellValue('AD1', '合同编号');
        $objActSheet->setCellValue('AE1', '设计费');
        $objActSheet->setCellValue('AF1', '服务费');
        $objActSheet->setCellValue('AG1', '轻辅费');
        $objActSheet->setCellValue('AH1', '主材费');
        $objActSheet->setCellValue('AI1', '第一笔人工费');
        $objActSheet->setCellValue('AJ1', '第二笔人工费');
        $objActSheet->setCellValue('AK1', '第三笔人工费');
        $objActSheet->setCellValue('AL1', '其他费用');

        // 现在就开始填充数据了 （从数据库中）
        $baseRow = 2; //数据从N-1行开始往下输出 这里是避免头信息被覆盖

        $list = D('CustomerView')->where($this->where())->order('id desc')->select();

        foreach ($list as $r => $dataRow){
            $row = $baseRow + $r;
            //将数据填充到相对应的位置，对应上面输出的列头
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $dataRow['CName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $dataRow['Tel']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $dataRow['fixed']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $dataRow['Address']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, $dataRow['Space']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $row, $dataRow['huxing']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $row, $dataRow['jiaofangtime']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $row, $dataRow['liangfangtime']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $row, $dataRow['wayname']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $row, $dataRow['channelname']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $row, state($dataRow['State']));
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $row, $dataRow['realname']);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $row, realname($dataRow['Designer']));
            $objPHPExcel->getActiveSheet()->setCellValue('N' . $row, $dataRow['ConsultDate']);
            $objPHPExcel->getActiveSheet()->setCellValue('O' . $row, $dataRow['ComeTime']);
            $objPHPExcel->getActiveSheet()->setCellValue('P' . $row, $dataRow['OrderTime']);
            $objPHPExcel->getActiveSheet()->setCellValue('Q' . $row, $dataRow['HetongTime']);
            $objPHPExcel->getActiveSheet()->setCellValue('R' . $row, $dataRow['Deposit']);
            $objPHPExcel->getActiveSheet()->setCellValue('S' . $row, $dataRow['OrdersValue']);
            $objPHPExcel->getActiveSheet()->setCellValue('T' . $row, $dataRow['sremind']);
            $objPHPExcel->getActiveSheet()->setCellValue('U' . $row, $dataRow['dremind']);
            $objPHPExcel->getActiveSheet()->setCellValue('V' . $row, $dataRow['premind']);
            $objPHPExcel->getActiveSheet()->setCellValue('W' . $row, realname($dataRow['xiaoguotu']));
            $objPHPExcel->getActiveSheet()->setCellValue('X' . $row, realname($dataRow['Drawing']));
            $objPHPExcel->getActiveSheet()->setCellValue('Y' . $row, realname($dataRow['Material']));
            $objPHPExcel->getActiveSheet()->setCellValue('Z' . $row, realname($dataRow['Project']));
            $objPHPExcel->getActiveSheet()->setCellValue('AA' . $row, realname($dataRow['Captain']));
            $objPHPExcel->getActiveSheet()->setCellValue('AB' . $row, $dataRow['StartTime']);
            $objPHPExcel->getActiveSheet()->setCellValue('AC' . $row, $dataRow['EndTime']);
            $objPHPExcel->getActiveSheet()->setCellValue('AD' . $row, $dataRow['Number']);
            $objPHPExcel->getActiveSheet()->setCellValue('AE' . $row, $dataRow['shejifei']);
            $objPHPExcel->getActiveSheet()->setCellValue('AF' . $row, $dataRow['guanlifei']);
            $objPHPExcel->getActiveSheet()->setCellValue('AG' . $row, $dataRow['qingfu']);
            $objPHPExcel->getActiveSheet()->setCellValue('AH' . $row, $dataRow['zhucai']);
            $objPHPExcel->getActiveSheet()->setCellValue('AI' . $row, $dataRow['once']);
            $objPHPExcel->getActiveSheet()->setCellValue('AJ' . $row, $dataRow['twice']);
            $objPHPExcel->getActiveSheet()->setCellValue('AK' . $row, $dataRow['tirth']);
            $objPHPExcel->getActiveSheet()->setCellValue('AL' . $row, $dataRow['others']);
        }

        //导出
        $filename ='客户信息表-'.date('Ymd');//excel文件名称
        $filename = iconv('utf-8', 'gb2312', $filename);//转换名称编码，防止乱码
        header('Content-Type: application/vnd.ms-excel;charset=utf-8');
        header('Content-Disposition: attachment;filename="'.$filename.'.xls"'); //”‘.$filename.’.xls”
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); //在内存中准备一个excel2003文件
        $objWriter->save ('php://output');
    }

    /**
     * 导入客户信息
     */
    public function importExcel()
    {
        if(!IS_POST) $this->error('您请求的页面不存在');

        import('ORG.Net.UploadFile');
        $upload = new UploadFile();// 实例化上传类
        $upload->maxSize = 20*1024*1024 ;// 设置附件上传大小
        $upload->allowExts = array('xlsx', 'xlx');// 设置附件上传类型
        $upload->savePath = './Uploads/';// 设置附件上传目录

        if($upload->upload()) {// 上传错误提示错误信息
            // 上传成功
            $file = $upload->getUploadFileInfo();
            //$filename = $file[0]['savename'];

            // 导入thinkphp第三方类库
            Vendor ('PhpExcel.PHPExcel');

            $c = C('IMPORT_CUSTOMER_EXCEL_FIELDS');//读取客户表excel配置
            $aid = fid();//管理员id

            // 查询所在公司所有客户电话, 逗号分隔
            $allUser = M('Customer')->query('SELECT  GROUP_CONCAT(c.Tel) as tel FROM '.C('DB_PREFIX').'customer c
                                          INNER JOIN '.C('DB_PREFIX').'users u ON c.Userid = u.id
                                          WHERE u.pid='.$aid);

            // 用户姓名, 得到 姓名=>id 键值对
            $result = M('Users')->where(array('pid'=>$aid))->select();
            $employee = array();//数组格式 array('姓名'=>id)
            foreach($result as $value){
                $employee[trim($value['realname'])] = $value['id'];
            }



            //装修方式, 得到 装修方式=>id 键值对
            $result = M('Way')->where(array('admin_id'=>$aid))->select();
            $way = array();
            foreach($result as $value){
                $way[trim($value['wayname'])] = $value['id'];
            }

            // 渠道, 得到 渠道名称=>id 键值对
            $result = M('Channel')->where(array('admin_id'=>$aid))->select();
            $channel = array();
            foreach($result as $value){
                $channel[trim($value['wayname'])] = $value['id'];
            }

            // 客户状态
            $sql = 'SELECT cs.id, s.statename FROM '.C('DB_PREFIX').'state as s INNER JOIN '.C('DB_PREFIX').'com_state as cs ON cs.state_id = s.id WHERE cs.admin_id='.$aid;
            $result = M('State')->query($sql);
            $state = array();
            foreach($result as $value){
                $state[trim($value['statename'])] = $value['id'];
            }

            $objPHPExcel = PHPExcel_IOFactory::load($file[0]['savepath'] . $file[0]['savename']);
            $arrExcel = $objPHPExcel->getSheet(0)->toArray();
            array_shift($arrExcel);
            $data = array();
            $failData = array();
            $i=0;
            foreach($arrExcel as $key => $value){
                $f = false;
                $j = 0;
                foreach($c as $k => $v){
                    $value[$j] = trim($value[$j]);
                    switch($v){
                        case 'Userid':
                            if(array_key_exists($value[$j], $employee))
                                $data[$i][$v] = $employee[$value[$j]];
                            else{
                                $value[$j] = $value[$j] . '{业务员不存在}';
                                array_push($failData, $value);
                                $f = true;
                            }
                            break;
                        case 'Designer':
                        case 'Project':
                        case 'Captain':
                        case 'Drawing':
                        case 'Material':
                        case 'xiaoguotu':
                        case 'Captain':
                            $data[$i][$v] = array_key_exists($value[$j], $employee) ? $employee[$value[$j]] : 0;
                            break;
                        case 'Way':
                            $data[$i][$v] = array_key_exists($value[$j], $way) ? $way[$value[$j]] : 0;
                            break;
                        case 'State':

                            $eclStateArr = explode(',', $value[$j]);

                            foreach($eclStateArr as $kecl => $vecl){
                                if(($kecl+1) == count($eclStateArr)){
                                    array_key_exists($vecl, $state) ? $data[$i][$v] .= $state[$vecl] : 0;
                                }else{
                                    array_key_exists($vecl, $state) ? $data[$i][$v] .= $state[$vecl].',' : 0;
                                }

                            }
                            sort($data[$i][$v]);
                            break;
                        case 'Tel':
                            //重复的手机号不入库
                            if(false === strpos($allUser[0]['tel'], $value[$j])){
                                $data[$i][$v] = $value[$j];
                            }else {
                                $value[$j] = $value[$j] . '{手机已存在}';
                                array_push($failData, $value);
                                $f = true;
                            }
                            break;
                        case 'Channel':
                            $data[$i][$v] = array_key_exists($value[$j], $channel) ? $channel[$value[$j]] : 0;
                            break;
                        case 'ConsultDate':
                        case 'ComeTime':
                        case 'OrderTime':
                        case 'HetongTime':
                        case 'sremind':
                        case 'dremind':
                        case 'premind':
                        case 'StartTime':
                        case 'EndTime':
                            //日期格式不正确
                            if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $value[$j]) || $value[$j] == null || $value[$j] == '')
                                $data[$i][$v] = $value[$j];
//                            else {
//                                array_push($failData, $value);
//                                $f = true;
//                            }
                            break;
                        case 'space':
                        case 'OrdersValue':
                        case 'shejifei':
                        case 'guanlifei':
                        case 'qingfu':
                        case 'zhucai':
                        case 'once':
                        case 'twice':
                        case 'tirth':
                        case 'others':
                            $data[$i][$v] = floatval($value[$j]);
                            break;
                        default:
                            $data[$i][$v] = $value[$j];
                    }
                    $j++;
                    if(true === $f) break;
                }
                if(true === $f) unset($data[$i]);
                $i++;
            }

            // 重置索引  避免第一条客户数据存在错误的时候不能导入的问题
            $data = array_values($data);

            D('customer')->addAll($data);
            @unlink($file[0]['savepath'] . $file[0]['savename']);

            $this->assign('data', $data);
            $this->assign('failData', $failData);
            $this->display();
        } else{
            $this->error($upload->getErrorMsg());
        }
    }

    /**
     * 下载客户信息导入示例
     */
    public function downloadCustomerDemo()
    {
        $file = "./Uploads/crm_download_demo.xlsx";
        $filename = 'CRM客户信息导入示例.xlsx';
        if(file_exists($file)){
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Type: application/vnd.ms-excel");

            //处理中文文件名
            $ua = $_SERVER["HTTP_USER_AGENT"];
            $encoded_filename = rawurlencode($filename);
            if (preg_match("/MSIE/", $ua)) {
                header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
            } else if (preg_match("/Firefox/", $ua)) {
                header("Content-Disposition: attachment; filename*=\"utf8''" . $filename . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $filename . '"');
            }

            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ". filesize($file));
            @readfile($file);
        }else
            $this->error('该文件不存在');
    }

    /**
     * 处理材料历史数据
     * @param $customer_id
     * @param $type_info
     * @return bool
     */
    public function _handleMaterialHistory($customer_id, $type_info)
    {
        $material_history = new MaterialHistoryModel();
        $material_history->startTrans();
        $info = $material_history->handleHistory($customer_id, $type_info);
        if(!$info){
            $material_history->rollback();
            return false;
        }
        $material_history->commit();
        return true;
    }

    /**
     *
     */
    public function addCustomerArea()
    {
        if($this->isPost()){
            $info = M('CustomerArea')->create();
            $info['create_time'] = time();
            $res = M('CustomerArea')->add();
            if($res){
                $this->success();
            }
        }else{
            $this->display();
        }
    }

    /**
     *
     */
    public function customerAreaList()
    {
        $list = M('CustomerArea')->select();
        $this->assign('list', $list);
        $this->display();
    }
}
