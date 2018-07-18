<?php
/**
 * Author: gaorenhua
 * Date: 2014-11-05
 * Email: 597170962@qq.com
 * CRM首页控制器
 */
class IndexAction extends CommonAction {
    /**
     * 获取本月到店人数
     */
    public function index(){
    	
		$this->assign('project_count', $this->project_count());
		$this->assign('project_amount', $this->project_amount());

		//平均单值
        $this->assign('project_average', round($this->project_amount() / $this->_hetong_count()));
		
        // 当前用户本月到店客户
        $this->assign('user_come_count', $this->userMonthCount('ComeTime'));

        // 当前用户本月定金客户
        $this->assign('user_order_count', $this->userMonthCount('OrderTime'));
        $this->assign('user_htsum', $this->userTotal('OrderTime','OrdersValue'));
        $this->assign('user_order_sum', $this->userTotal('OrderTime','Deposit'));

        // 当前用户本月合同客户
        $this->assign('user_htong_count', $this->userMonthCount('HetongTime'));
        $this->assign('user_ht_sum', $this->userTotal('HetongTime','OrdersValue'));

        // 今日待回访客户
        $this->assign('day_count', $this->remindCustomer(1));
        // 本周待回访客户
        $this->assign('week_count', $this->remindCustomer(2));
        // 过期未回访客户
        $this->assign('no_count', $this->remindCustomer(3));

        // 输出到店人数  签单人数  合同人数
        $this->assign('come_count', $this->stateCount('ComeTime'));
        $this->assign('order_count', $this->stateCount('OrderTime'));
        $this->assign('hetong_count', $this->stateCount('HetongTime'));
		$this->assign('tuiding_count', $this->stateCount('CancelTime'));

        // 输出到店人数  签单人数  合同人数
        $this->assign('come_count_today', $this->stateTodayCount('ComeTime'));
        $this->assign('order_count_today', $this->stateTodayCount('OrderTime'));
        $this->assign('hetong_count_today', $this->stateTodayCount('HetongTime'));		
		$this->assign('tuiding_count_today', $this->stateTodayCount('CancelTime'));

        // 输出定金总额  合同总额
        $this->assign('order_total', $this->total('OrderTime'));
        $this->assign('hetong_total', $this->hetong('HetongTime'));
        $this->assign('yujihetong', $this->hetong('OrderTime'));
		$this->assign('tuiding', $this->cancel('CancelTime'));
		

        // 输出定金总额  合同总额
        $this->assign('order_total_today', $this->total_today('OrderTime'));
        $this->assign('hetong_total_today', $this->hetong_today('HetongTime'));
        $this->assign('yujihetong_today', $this->hetong_today('OrderTime'));
		$this->assign('tuiding_today', $this->cancel_today('CancelTime'));
			
		
        // 计算百分比
        $percent = $this->stateCount('OrderTime') / $this->stateCount('ComeTime') * 100;
        $this->assign('percent', $percent);

        // 业务员 设计师本月定金排行
        $this->assign('salegroup', $this->salesRank(1, 'Deposit', 'OrderTime', 'Userid'));
        $this->assign('desigroup', $this->salesRank(2, 'Deposit', 'OrderTime', 'Designer'));

        // 业务员本月合同排行
        $this->assign('htsalegroup', $this->salesRank(1, 'OrdersValue', 'HetongTime', 'Userid'));
        $this->assign('htdesigroup', $this->salesRank(2, 'OrdersValue', 'HetongTime', 'Designer'));

        // 公司公告列表
        $notice = M('notice')->where(array('admin_id' => fid()))->order('top desc, entrytime desc')->select();
        $this->assign('notice', $notice);

        // 获取当前公司所有员工
        $where['pid'] = fid();
        $user = array_column(M('users')->where($where)->field('id')->select(), 'id');  //多维数组转成一维数组
        $user = implode(',', $user);

        //查询字段 条件
        $field = "Channel, count(Channel) as count";
        $where = "`Userid` IN (".$user.") AND `status` = 0 AND `Channel` <> 0";

        // 客户来源分布饼状图
        $data = M('customer')->query("SELECT $field FROM __TABLE__ WHERE $where  GROUP BY `Channel`");

        // 公司年度业绩走势图
        $date = date('Y'); //获取当前年
        $field1 = "month(OrderTime) as month, count(id) as num, SUM(OrdersValue) as sum";  // 要查询的字段
        $state = '\','.stateID(6).',\'';  // 获取当前状态下的状态ID
        $where1 = "status = '0' AND POSITION($state IN CONCAT(',',State,',')) AND year(OrderTime)='".$date."'";  // 状态为已签单且未被删除客户
        $pref = M('customer')->query("SELECT $field1 FROM __TABLE__ WHERE $where1 group by month(OrderTime)");

        //去年
        $where2 = "status = '0' AND POSITION($state IN CONCAT(',',State,',')) AND year(OrderTime)='".($date-1)."'";  // 状态为已签单且未被删除客户
        $qpref = M('customer')->query("SELECT $field1 FROM __TABLE__ WHERE $where2 group by month(OrderTime)");

        $this->assign('pref', $pref);
        $this->assign('qpref', $qpref);
        $this->assign('data', $data);


        //执行扣款, 每10天检测一次, 完成当月扣款
        $cache = S('exe_deduct_money');
        // 判断是否有这个查询缓存
        if(!$cache){
            echo 'test';
            S('exe_deduct_money', 1, 3600 * 24 * 20);
            $fid = fid();
            deduct_money($fid);
        }

        $this->display();
    }

    /**
     * 公司公告列表
     */
    public function noticeList(){
        // 判断GET传值
        if (!IS_GET) {
            $this->error('您请求的页面不存在');
        }

        // 查询条件  公司所有公告 即 admin_id == 公司ID
        $where = array('admin_id' => fid());

        // 导入分页类
        import('ORG.Util.Page');
        $count  = M('notice')->where($where)->count();  // 查询记录总数
        $Page   = new Page($count,14);          // 实例化分页类 传入总记录数
        $Page->setConfig('header','条公告');       // 定制分页样式
        $show   = $Page->show();                // 分页显示输出

        // 公司公告列表
        $list = M('notice')->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('top desc, entrytime desc')->select();

        $this->assign('notice', $list);        // 赋值输出数据集
        $this->assign('page',$show);            // 赋值分页输出
        $this->display();
    }

    /**
     * 查看公告详情
     */
    public function viewNotice(){
        // 判断GET传值
        if (!IS_GET) {
            $this->error('您请求的页面不存在');
        }

        //获取详情
        $info = M('notice')->where(array('id' => I('get.id', 0, 'intval')))->find();

        $this->assign('info', $info);
        $this->display();
    }

    /**
     * 添加公司公告
     */
    public function addNotice(){
        //判断POST提交
        if (IS_POST) {
            // 验证只能输入汉字和字符
            $validate = array(
                array('title', '/^([\x{4e00}-\x{9fa5}]|[a-zA-Z]){3,50}$/u','标题只能输入3-50个汉字和字母'),
                array('content', 'require', '内容不能为空')
            );
            D('notice')->setProperty("_validate",$validate);

            // 创建数据集
            $data = D('notice')->create();
            if (!$data) {
                $this->error(D('notice')->getError(), __SELF__, 1);
            }

            // 获取附加信息 : 公司ID, 添加人ID, 添加时间等
            $data['admin_id'] = fid();
            $data['uid'] = session('uid');
            $data['entrytime'] = $_SERVER['REQUEST_TIME'];

            // 插入数据
            if (M('notice')->add($data)) {
                $this->success('添加成功', U('noticeList'), 1);
            } else {
                $this->error('添加失败', __SELF__, 1);
            }
        } else {
            $this->display();
        }
    }

    /**
     * 取消置顶
     */
    public function cancelTop(){
        // 判断GET提交
        if (!IS_GET) {
            $this->error('您请求的页面不存在');
        }

        // 获取GET数据
        $data['id'] = I('get.id', 0, 'intval');
        $data['top'] = I('get.tid', 0, 'intval');

        // 更新数据
        if (M('notice')->save($data)) {
            $this->success('操作成功', U('noticeList'), 1);
        } else {
            $this->error('操作失败', U('viewNotice', array('id' =>$data['id'])), 1);
        }
    }

    /**
     * 编辑公司公告
     */
    public function editorNotice(){
        // 判断POST提交
        if (IS_POST) {
            // 验证只能输入汉字和字符
            $validate = array(
                array('title', '/^([\x{4e00}-\x{9fa5}]|[a-zA-Z]){3,50}$/u','标题只能输入3-50个汉字和字母'),
                array('content', 'require', '内容不能为空')
            );
            D('notice')->setProperty("_validate",$validate);

            // 创建数据集
            $data = D('notice')->create();
            if (!$data) {
                $this->error(D('notice')->getError(), __SELF__, 1);
            }

            // 更新数据
            if (M('notice')->save($data)) {
                $this->success('更新成功', U('viewNotice', array('id' => $data['id'])), 1);
            } else {
                $this->error('更新失败', U('editorNotice', array('id' => $data['id'])), 1);
            }
        } else {
            // 获取GET数据
            $where['id'] = I('get.id', 0, 'intval');

            // 获取单篇公告详情
            $info = M('notice')->where($where)->find();

            $this->assign('info', $info);
            $this->display();
        }
    }

    /**
     * 删除公告
     */
    public function deleteNotice(){
        // 判断GET提交
        if (!IS_GET) {
            $this->error('您请求的页面不存在');
        }

        // 获取GET数据
        $id = I('get.id', 0, 'intval');

        // 更新数据
        if (M('notice')->delete($id)) {
            $this->success('操作成功', U('noticeList'), 1);
        } else {
            $this->error('操作失败', U('noticeList'), 1);
        }
    }

    /**
     * 查看更多消息
     */
    public function viewAllNews(){
        // 判断访问方式
        if (!IS_GET) {
            $this->error('您请求的页面不存在');
        }

        // 查询条件
        $where['fid'] = fid();
        $where['viewid'] = session('uid');

        // 获取消息列表
        $list = M('news')->where($where)->order('status asc, id desc')->select();

        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 清空已读信息
     */
    public function delReadNews(){
        // 判断提交方式
        if (!IS_GET) {
            $this->error('您请求的页面不存在');
        }

        // 删除条件
        $where['fid'] = fid();
        $where['viewid'] = session('uid');
        $where['status'] = 1;

        // 删除已读信息
        if (M('news')->where($where)->delete()) {
            $this->success('已读信息已清空', U('Index/viewAllNews'), 2);
        } else {
            $this->error('清空操作失败', U('Index/viewAllNews'), 2);
        }
    }

    /**
     * 更改主题颜色
     */
    public function theme(){
        // 判断POST提交
        if (!IS_POST) {
            $this->error('您请求的页面不存在');
        }

        // 获取POST数据
        $data['uid'] = session('uid');
        $data['theme'] = I('post.theme');

        // 更新
        M('user_defined')->save($data);
    }

    /**
     * 今日待回访客户
     * @param $times 回访时间段设置 1-当天 2-本周 3-过期未回访 4-自定义[待开发]
     */
    public function remindCustomer($times){
        // 判断提交方式
        if (!IS_GET) {
            $this->error('您请求的页面不存在');
        }

        //当前时间 和 最近7天
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

        $map['status'] = 0;
        $map['_complex'] = $this->wheres(); //并入查询, 否则会按照 OR 的方式查询

        // 调用公共分页类
        $count  = D('CustomerView')->where($map)->count();  // 查询记录总数

        return $count;
    }

    /**
     * 当前用户本月状态客户数量
     * @param $date  需要查询的时间字段
     * @return mixed 返回客户数
     */
    protected function userMonthCount($date){
        // 设置通用查询条件
        $map['status'] = '0';   // 没有被删除的客户
        $map[''.$date.''] = Month_f_l();    // 当前月的第一天和最后一天

        // 转换状态 设置状态筛选
//        $state = '\','.stateID(''.$state.'').',\'';
//        $map['_string'] = "POSITION($state IN CONCAT(',',State,','))";  //查询字符串

        // 根据用户属性判断客户
        $map['_complex'] = $this->wheres();

        $cout = M('customer')->where($map)->count('id');

        return $cout;
    }

    /**
     * 当前用户本月状态客户数量
     * @param $date  需要查询的时间字段
     * @param $field  需要求和的字段
     * @return mixed 返回金额总数
     */
    protected function userTotal($date,$field){
        // 设置通用查询条件
        $map['status'] = '0';   // 没有被删除的客户
        $map[''.$date.''] = Month_f_l();    // 当前月的第一天和最后一天

        // 转换状态 设置状态筛选
        //        $state = '\','.stateID(''.$state.'').',\'';
        //        $map['_string'] = "POSITION($state IN CONCAT(',',State,','))";  //查询字符串

        // 根据用户属性判断客户
        $map['_complex'] = $this->wheres();

        $cout = M('customer')->where($map)->Sum(''.$field.'');

        return $cout;
    }

    /**
     * 综合查询条件
     */
    protected function wheres(){
        // 判断是否是超级管理员
        if (in_array(session('uid'), C('ADMINISTRATOR'))) {
            return $where = array();
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
        }
    }

    /**
     * 排行榜 主要用于业务和设计师的业绩排行
     * @param  $attr  排行榜属性 1-业务员 2-设计师
     * @param  $field 需要统计的字段 一般为定金金额 或者是 合同金额
     * @param  $date  日期 主要用于判断当月
     //* @param  $state 状态ID 6-已定金 7-已合同
     * @param  $group 需要去重的字段 Userid-业务 Designer-设计
     * @return mixed  符合条件的人员数据集
     */
    protected function salesRank($attr, $field, $date, $group){
        // 获取当前管理员状态下的所有员工
        $where = array(
            'pid' => fid(),
            //'status' => 1
        );
        $uid = M('users')->where($where)->getField('id',true);

        // 判断排行榜属性 业务 or 设计
        if ($attr == 1) {
            // 获取业务组的所有员工ID
            $map['Userid'] = array('IN', $uid);
        } else {
            $map['Designer'] = array('IN', $uid);
        }

        // 设置通用查询条件
        $map['status'] = '0';   // 没有被删除的客户
        $map[''.$date.''] = Month_f_l();    // 当前月的第一天和最后一天

        //转换成功选择状态ID
        //$state = '\','.stateID(''.$state.'').',\'';
        //$map['_string'] = "POSITION($state IN CONCAT(',',State,','))";  //查询字符串

        //查询符合条件的客户ID总数
        $result = M('customer')->table('think_customer')->where($map)->order('sum DESC')->query('select '.$group.', count(id) as `count`, sum('.$field.') as `sum` from %TABLE% %WHERE% GROUP BY '.$group.' %ORDER%', true);

        return $result;
    }

    /**
     * 获取当月状态客户的总数
     * @param $field [varchar] 要查询的字段
     */
    protected function stateCount($field){
        // 没有被删除的客户
        $map['status'] = '0';

        //查询符合条件的客户ID总数
        $count = M('Customer')->where($this->where($field))->where($map)->count('id');
        return $count;
    }

    /**
     * 获取当月状态客户的总数
     * @param $field [varchar] 要查询的字段
     */
    protected function stateTodayCount($field){
        // 没有被删除的客户
        $map['status'] = '0';

        //查询符合条件的客户ID总数
        $count = M('Customer')->where($this->where_today($field))->where($map)->count('id');

        return $count;
    }
	
    /**
     * 获取当前状态客户的总定金金额
     * @param $field [varchar] 要查询的字段
     */
    protected function total($field){
        // 没有被删除的客户
        $map['status'] = '0';

        //查询符合条件的客户ID总数
        $count = M('Customer')->where($this->where($field))->where($map)->Sum('Deposit');

        return $count;
    }

    /**
     * 获取当前状态客户的总定金金额
     * @param $field [varchar] 要查询的字段
     */
    protected function total_today($field){
        // 没有被删除的客户
        $map['status'] = '0';

        //查询符合条件的客户ID总数
        $count = M('Customer')->where($this->where_today($field))->where($map)->Sum('Deposit');

        return $count;
    }
	
	
    /**
     * 获取当前状态客户的总定金金额
     * @param $field [varchar] 要查询的字段
     */
    protected function cancel($field){
        // 没有被删除的客户
        $map['status'] = '0';

        //查询符合条件的客户ID总数
        $count = M('Customer')->where($this->where($field))->where($map)->Sum('CancelDeposit');
        return $count;
    }

    /**
     * 获取当前状态客户的总定金金额
     * @param $field [varchar] 要查询的字段
     */
    protected function cancel_today($field){
        // 没有被删除的客户
        $map['status'] = '0';

        //查询符合条件的客户ID总数
        $count = M('Customer')->where($this->where_today($field))->where($map)->Sum('CancelDeposit');

        return $count;
    }	
    /**
     * 获取当前状态客户的总合同金额
     * @param $field [varchar] 要查询的字段
     */
    protected function hetong($field){
        // 没有被删除的客户
        $map['status'] = '0';

        //查询符合条件的客户ID总数
        $count = M('Customer')->where($this->where($field))->where($map)->Sum('OrdersValue');

        return $count;
    }

    /**
     * 获取当前状态客户的总合同金额
     * @param $field [varchar] 要查询的字段
     */
    protected function hetong_today($field){
        // 没有被删除的客户
        $map['status'] = '0';
        //查询符合条件的客户ID总数
        $count = M('Customer')->where($this->where_today($field))->where($map)->Sum('OrdersValue');
        return $count;
    }

    /**
     * 综合查询条件  主要查询本月状态客户
     * @param $field [varchar] 要查询的字段
     */
    protected function where($field){
        // 获取当前公司所有员工
        $where['pid'] = fid();
        $user = array_column(M('users')->where($where)->field('id')->select(), 'id');  //多维数组转成一维数组

        // 查询所有客户
        $condition = array(
            'Userid' => array('IN', $user),
            'Designer' => array('IN', $user),
            '_logic' => 'or'
        );

        $map[''.$field.''] = Month_f_l();  //当月第一天和最后一天
        $map['_complex'] = $condition;  //并入查询

        return $map;
    }
	
    /**
     * 综合查询条件  主要查询本月状态客户
     * @param $field [varchar] 要查询的字段
     */
    protected function where_today($field){
        // 获取当前公司所有员工
        $where['pid'] = fid();
        $user = array_column(M('users')->where($where)->field('id')->select(), 'id');  //多维数组转成一维数组

        // 查询所有客户
        $condition = array(
            'Userid' => array('IN', $user),
            'Designer' => array('IN', $user),
            '_logic' => 'or'
        );

        $map[''.$field.''] = date('Y-m-d');
        $map['_complex'] = $condition;  //并入查询

        return $map;
    }	
	
	protected function project_count(){
        // 没有被删除的客户
        $map['status'] = '0';
		$map['Project']=array('gt',0);
		$firstday = date('Y-01-01', strtotime(date('Y-m-d')));
//		$map['StartTime']=array('egt',$firstday);
        $map['Hetongtime']=array('egt',$firstday);
        //查询符合条件的客户ID总数
        $count = M('Customer')->where($map)->count();
        $count = D('CustomerView')->where($map)->count();  // 符合查询条件的总单值

        return $count;
    }
	
	protected function project_amount(){
        // 没有被删除的客户
        $map['status'] = '0';
		$firstday = date('Y-01-01', strtotime(date('Y-m-d')));
//		$map['StartTime']=array('egt',$firstday);
        $map['Hetongtime']=array('egt',$firstday);
        //查询符合条件的客户ID总数
        $count = D('CustomerView')->where($map)->Sum('OrdersValue');  // 符合查询条件的总单值
//        $count = M('Customer')->where($map)->Sum('OrdersValue');
//        dump(M('Customer')->getLastSql());

        return $count;
    }

    protected function _hetong_count(){
        // 没有被删除的客户
        $map['status'] = '0';
		$firstday = date('Y-01-01', strtotime(date('Y-m-d')));
//		$map['StartTime']=array('egt',$firstday);
        $map['Hetongtime']=array('egt',$firstday);
        //查询符合条件的客户ID总数
        $count = D('CustomerView')->where($map)->count();  // 符合查询条件的总单值

        return $count;
    }
}
