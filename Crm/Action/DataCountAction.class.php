<?php
/**
 * Author: gaorenhua    
 * Date: 2014-12-02 
 * Email: 597170962@qq.com
 * 数据统计控制器 
 */
Class DataCountAction extends CommonAction {
    /**
     * 获取当月到店客户
     */
    public function getMonthCome(){
        // 判断是否GET提交
        if (!IS_GET) {
            $this->error('请求的页面不存在', U('Customer/customerList'), 1);
        }

        // 获取当前状态下的状态ID
//        $state = '\','.stateID($this->_get('state')).',\'';
//        $map['_string'] = "POSITION($state IN CONCAT(',',State,','))";

        $map['ComeTime'] = Month_f_l();

        // 调用公共分页
        R('Customer/pageCommon', array($map));
        $this->display();
    }

    /**
     * 获取当月签单客户
     */
    public function getMonthOrder(){
        // 判断是否GET提交
        if (!IS_GET) {
            $this->error('请求的页面不存在', U('Customer/customerList'), 1);
        }

        // 获取当前状态下的状态ID
//      $state = '\','.stateID($this->_get('state')).',\'';
//      $map['_string'] = "POSITION($state IN CONCAT(',',State,','))";

        $map['OrderTime'] = Month_f_l();

        // 调用公共分页
        R('Customer/pageCommon', array($map));
        $this->display();
    }

    /**
     * 获取当月合同客户
     */
    public function getMonthHetong(){
        // 判断是否GET提交
        if (!IS_GET) {
            $this->error('请求的页面不存在', U('Customer/customerList'), 1);
        }

        // 获取当前状态下的状态ID
//      $state = '\','.stateID($this->_get('state')).',\'';
//      $map['_string'] = "POSITION($state IN CONCAT(',',State,','))";

        $map['HetongTime'] = Month_f_l();

        // 调用公共分页
        R('Customer/pageCommon', array($map));
        $this->display('getMonthOrder');
    }

    /**
     * 获取公司本月到店客户
     */
    public function getAllCome(){
        // 判断是否GET提交
        if (!IS_GET) {
            $this->error('请求的页面不存在', U('Customer/customerList'), 1);
        }

        // 当前公司下的所有员工
        $user = array_column(M('users')->where(array('pid' => fid()))->field('id')->select(), 'id');
        $map['Userid'] = array('IN', $user);

        // 获取类别 判断 到店 定金 合同
        $class = I('get.class', 0, 'intval');
        if ($class == 1) {
            $map['ComeTime'] = Month_f_l();
        } elseif ($class == 2) {
            $map['OrderTime'] = Month_f_l();
        } elseif ($class == 3) {
            $map['HetongTime'] = Month_f_l();
        } elseif ($class == 4) {
            $map['CancelTime'] = Month_f_l();
        } elseif ($class == 11) {
            $map['ComeTime'] = date('Y-m-d');
        } elseif ($class == 12) {
            $map['OrderTime'] = date('Y-m-d');
        } elseif ($class == 13) {
            $map['HetongTime'] =date('Y-m-d');
        } elseif ($class == 14) {
            $map['CancelTime'] = date('Y-m-d');									
        } else {
        	
            $this->error('请求错误');
        }

        $map['status'] = '0';   // 没有被删除的客户

        // 导入分页类
        import('ORG.Util.Page');
        $count  = D('CustomerView')->where($map)->count();  // 查询记录总数
        $Page   = new Page($count,30);          // 实例化分页类 传入总记录数
        $Page->setConfig('header','个客户');       // 定制分页样式

        // 分页跳转的时候保证查询条件
        $get = array_filter($_GET);
        foreach($get as $key=>$val) {
            $Page->parameter .= "$key=".urlencode($val)."&";
        }

        // 分页显示输出
        $show   = $Page->show();

        $list = D('CustomerView')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();

        // 没有客户信息的时候提示
        $empty = '<tr><td colspan="12" style="font-size:14px;height:60px;line-height:60px;color:#D64635;">暂无此状态客户.</td></tr>';

        // 符合查询条件的总单值
        $sum  = D('CustomerView')->where($map)->Sum('OrdersValue');

        $this->assign('sum', $sum);
        $this->assign('count', $count);
        $this->assign('customer',$list);        // 赋值数据集
        $this->assign('page',$show);            // 赋值分页输出
        $this->assign('empty',$empty);          // 赋值分页输出
        $this->display();
    }
}