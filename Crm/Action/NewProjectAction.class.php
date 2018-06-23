<?php
/**
 * Created by PhpStorm.
 * User: zhanghuan
 * Date: 2018/5/29
 * Time: 下午9:37
 */

class NewProjectAction extends CommonAction
{
    public function index()
    {

    }

    /**
     * 装修风格
     */
    public function addProject()
    {
        if (IS_POST) {
            $action = $this->_param('action');
            if ($action == 'add') {
                $info = D('Xiangmu')->create();
                if (!$info)
                    $this->ajaxReturn(array('code' => 0, 'msg' => D('Xiangmu')->getError()));
                $res = D('Xiangmu')->add($info);
                if ($res)
                    $this->ajaxReturn(array('code' => 1, 'msg' => '添加成功'));
            } elseif ($action == 'list') {
                $list = D("Xiangmu")->select();
                $this->assign('list', $list);
                $this->display('ajaxProject');
            } elseif ($action == 'update') {
                $info = D('Xiangmu')->create();
                if (!$info) {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '没有任何修改'));
                }
                $res = D('Xiangmu')->where(array('project_id' => $info['project_id']))->save($info);
                if ($res) {
                    $this->ajaxReturn(array('code' => 1, 'msg' => '修改成功'));
                } else {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '修改失败'));
                }
            } elseif ($action == 'delete') {
                $class_id = $this->_param('id');
                $res = D('Xiangmu')->where(array('project_id' => $class_id))->delete();
                if ($res) {
                    $this->ajaxReturn(array('code' => 1, 'msg' => '删除成功'));
                }
            }
        } else {
            $list = D("Xiangmu")->select();
            $this->assign('list', $list);
            $this->display();
        }
    }

    /**
     *添加施工管理
     */
    public function addCustomerPro()
    {
        if (IS_POST) {
            $action = $this->_param('action');
            if ($action == 'add') {
                $info = D('CustomerPro')->create();
                if (!$info)
                    $this->ajaxReturn(array('code' => 0, 'msg' => D('Xiangmu')->getError()));
                $info['start_time'] = strtotime($info['start_time']);
                $info['end_time'] = strtotime($info['end_time']);
                $info['create_by'] = session('uid');
                $info['create_time'] = time();
                $res = D('CustomerPro')->add($info);
                if ($res)
                    $this->ajaxReturn(array('code' => 1, 'msg' => '添加成功'));
            } elseif ($action == 'list') {
                $customer_id = $this->_param('customer_id');
                $where = array('customer_id' => $customer_id);
                $list = D('CustomerPro')->where($where)->select();
                $customer_info = D('customer')->where(array('id' => $customer_id))->field('id, CName, Tel, Address, Captain')->find();
                foreach ($list as &$value) {
                    $value['customer_name'] = $customer_info['CName'];
                    $value['project_name'] = D('Xiangmu')->where(array('project_id' => $value['project_id']))->getField('project_name');
                    $value['tel'] = $customer_info['Tel'];
                    $value['address'] = $customer_info['Address'];
                    $value['Caption'] = realname($customer_info['Caption']);
                    $value['day'] = ($value['end_time'] - $value['start_time']) / 86400;
                }
                $this->assign('list', $list);
                $this->display('ajaxCustomerPro');
            } elseif ($action == 'update') {
                $info = D('Xiangmu')->create();
                if (!$info) {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '没有任何修改'));
                }
                $res = D('Xiangmu')->where(array('project_id' => $info['id']))->save($info);
                if ($res) {
                    $this->ajaxReturn(array('code' => 1, 'msg' => '修改成功'));
                } else {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '修改失败'));
                }
            } elseif ($action == 'delete') {
                $class_id = $this->_param('id');
                $res = D('CustomerPro')->where(array('id' => $class_id))->delete();
                if ($res) {
                    $this->ajaxReturn(array('code' => 1, 'msg' => '删除成功'));
                }
            }
        } else {
            $map['status'] = 0;   // 没有被删除的客户
            $map['_complex'] = $this->where();
            $c_list = D('customer')
                ->where($map)
                ->field('id, CName, Tel, Captain')->select();
            $p_list = D('Xiangmu')->select();

            $this->assign('c_list', $c_list);
            $this->assign('p_list', $p_list);
            $this->display();
        }
    }

    /**
     * 综合查询条件
     */
    protected function where()
    {
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
     *施工列表
     */
    public function projectList()
    {
        if (IS_POST) {

        } else {
            $customer_pro = M('CustomerPro');
            $Customer = M('Customer');
            $list = $customer_pro->group('customer_id')->select();
            foreach ($list as &$value) {
                $where = [
                    'customer_id' => $value['customer_id'],
                ];
                $value['customer_info'] = $Customer->where(array('id' => $value['customer_id']))->field('id, CName, Tel, Address, Project')->find();
                $value['project_id'] = $customer_pro->where($where)->order('create_time desc')->getField('project_id');
                $value['project_name'] = $this->_getProjectName($value['project_id']);
            }
            $this->assign('list', $list);
            $this->display();
        }
    }

    /**
     * create by Mr.Zhang time 2018/6/12 19:48
     */
    public function getProContent()
    {
        $customer_id = $this->_param('customer_id');
        $customer_pro = M('CustomerPro');
        $customer = M('customer');
        $where = [
            'customer_id' => $customer_id,
        ];
        $list = $customer_pro->where($where)->select();
        $min_time = $customer_pro->where($where)->order('start_time asc')->getField('start_time');
        $max_time = $customer_pro->where($where)->order('end_time desc')->getField('end_time');
        $info = $this->_getMarginData(1524931200, $max_time);
        $width = 35 * $info['day'] + 100;

        $customer_info = $customer->where($where)->field('CName, Tel, Address, Project')->find();
        foreach ($list as &$v) {
            $v['project_name'] = $this->_getProjectName($v['project_id']);
            $v['date_arr'] = $this->_getProClass(1524931200, $v['start_time'], $v['end_time'], $info['day']);
        }
        $this->assign('width',$width);
        $this->assign('info', $info);
        $this->assign('list', $list);
        $this->assign('customer_info', $customer_info);
        $this->display();
    }

    /**
     * 获取日期间隔的全部日期
     * @param $min
     * @param $max
     * create by Mr.Zhang time 2018/6/23 11:51
     * @return array
     */
    private function _getMarginData($min, $max)
    {
        $day = ($max - $min) / 86400;
        $run = 0;
        if (date('L', $min) == 1) {
            $run = 1;
        } else {
            $run = 0;
        }
        $month_list = [1,3,5,7,8,10,12];
        $start_month = intval(date('m',$min));
        $start_day = intval(date('d', $min));
        $arr = [];
        for($i = 0; $i <= $day; $i ++){
            if(in_array($start_month, $month_list)){
                if($start_day == 31){
                    $arr[$start_month][] = $start_day;
                    $start_month++;
                    $start_day = 1;
                }else{
                    $arr[$start_month][] = $start_day;
                    $start_day++;
                }
            }elseif($start_month == 2){
                if($start_day == 28){
                    $arr[$start_month][] = $start_day;
                    $start_month++;
                    $start_day = 1;
                }else{
                    $arr[$start_month][] = $start_day;
                    $start_day++;
                }
            }else{
                if($start_day == 30){
                    $arr[$start_month][] = $start_day;
                    $start_month++;
                    $start_day = 1;
                }else{
                    $arr[$start_month][] = $start_day;
                    $start_day++;
                }
            }
        }
        foreach ($arr as $k=> $v){
            $m_arr[] = [
                'month' => $k,
                'day_num' => count($v),
            ];
        }
        return ['start_time' => date('Y-m-d', $min), 'end_time' => date('Y-m-d', $max), 'day' => $day, 'm_arr' => $m_arr, 'result' => $arr];
    }

    /**
     * 处理施工日期在日期间隔中的日期
     * @param $min
     * @param $start
     * @param $end
     * @param $day
     * create by Mr.Zhang time 2018/6/23 11:51
     * @return array
     */
    private function _getProClass($min, $start, $end, $day)
    {
        $start_day = ($start - $min) / 86400;
        $end_day = ($end - $min) / 86400;
        $arr = [];
        for($i = 0; $i <= $day; $i++){
            if($i >= $start_day && $i <= $end_day){
                $arr[] = 1;
            }else{
                $arr[] = 0;
            }
        }
        return $arr;
    }

    /**
     * @param $project_id
     * create by Mr.Zhang time 2018/6/12 19:36
     * @return mixed
     */
    private function _getProjectName($project_id)
    {
        $where = ['project_id' => $project_id];
        $pro = M('Xiangmu');
        $pro_name = $pro->where($where)->getField('project_name');

        return $pro_name;
    }
}