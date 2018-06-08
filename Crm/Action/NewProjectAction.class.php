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
                $res = D('Xiangmu')->where(array('project_id' => $info['id']))->save($info);
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
                $info = D('Xiangmu')->create();
                if (!$info)
                    $this->ajaxReturn(array('code' => 0, 'msg' => D('Xiangmu')->getError()));
                $res = D('Xiangmu')->add($info);
                if ($res)
                    $this->ajaxReturn(array('code' => 1, 'msg' => '添加成功'));
            } elseif ($action == 'list') {
                $list = D("Xiangmu")->select();
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
                $res = D('Xiangmu')->where(array('project_id' => $class_id))->delete();
                if ($res) {
                    $this->ajaxReturn(array('code' => 1, 'msg' => '删除成功'));
                }
            }
        } else {
            $c_list = D('customer')
                ->where(array('status' => 0))
                ->field('id, CName, Tel, Captain')->select();
            $p_list = D('Xiangmu')->select();

            $this->assign('c_list', $c_list);
            $this->assign('p_list', $p_list);
            $this->display();
        }
    }

    /**
     *施工列表
     */
    public function projectList()
    {

    }
}