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
                foreach ($list as &$value){
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
<<<<<<< HEAD
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

=======
     *施工列表
     */
    public function projectList()
    {
>>>>>>> 19d47e21a45c42cdf2006951f3dc2278ef197a78

    }
}