<?php
/**
 * Created by PhpStorm.
 * User: zhanghuan
 * Date: 2018/5/29
 * Time: 下午9:37
 */

class AfterSalesAction extends CommonAction
{
    /**
     * 添加售后服务
     */
    public function addAfterSale()
    {
        if ($this->isPost()) {
            $data = M('AfterService')->create();
            $data['state'] = 0;
            $data['service_date'] = strtotime($data['service_date']);
            $data['mobile'] = intval($data['mobile']);
            $data['create_time'] = time();
            $res = M('AfterService')->add($data);
            if ($res) {
                $this->success('添加成功', U('AfterSales/afterSaleList'));
            }
        } else {
            $material_type = D('MaterialType');
            $type_list = $material_type->select();
            $user_list = M('UsersGroup')->where(array('group_id' => 14))->select();

            $this->assign('type_list', $type_list);
            $this->assign('user_list', $user_list);
            $this->display();
        }

    }

    /**
     * 售后服务列表
     */
    public function afterSaleList()
    {
        if (IS_POST) {
            $action = $this->_param('action');
            if ($action == 'edit_satisfied') {
                $id = $this->_param('after_id');
                $info['satisfied_state'] = $this->_param('state');
                $after_service = M('AfterService');
                $res = $after_service->where(array('after_id' => $id))->save($info);
                if ($res) {
                    $this->ajaxReturn(array('code' => 1, 'msg' => '修改成功'));
                } else {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '修改失败'));

                }
            }elseif($action == 'edit_complete'){
                $id = $this->_param('after_id');
                $info['complete_state'] = $this->_param('state');
                $after_service = M('AfterService');
                $res = $after_service->where(array('after_id' => $id))->save($info);
                if ($res) {
                    $this->ajaxReturn(array('code' => 1, 'msg' => '修改成功'));
                } else {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '修改失败'));

                }
            }
        } else {
            //处理列表
            $list = M('AfterService')->select();
            foreach ($list as &$value) {
                $value['satisfied'] = $value['satisfied_state'] == 1 ? '满意' : '不满意';
                $value['complete'] = $value['complete_state'] == 1 ? '已完成' : '进行中';
            }

            //处理完成率
            $total_num = M('AfterService')->count();
            $complete_num = M('AfterService')->where(['complete_state'=> 1])->count();
            $complete_pro = round($complete_num / $total_num * 100, 2) . '%';
            $complete_info = [
                'num' => $complete_num,
                'pro' => $complete_pro,
            ];

            //处理满意率
            $satisfied_num = M('AfterService')->where(['satisfied_state' => 1])->count();
            $satisfied_pro = round($satisfied_num / $total_num * 100, 2) . '%';
            $satisfied_info = [
                'num' => $satisfied_num,
                'pro' => $satisfied_pro,
            ];


            $this->assign('complete_info', $complete_info);
            $this->assign('satisfied_info', $satisfied_info);
            $this->assign('list', $list);
            $this->display();
        }
    }

    /**
     *修改售后服务
     */
    public function alertAfterSale()
    {

    }

    public function delAfterSale()
    {

    }

    /**
     * 售后使用材料的统计
     */
    public function afterChart()
    {
        if (IS_POST) {
            $type_id = $this->_param('id');
            $where = array(
                'material_type' => $type_id,
            );
            $num = D('AfterService')->where($where)->count();
            $list = D('AfterService')
                ->where($where)
                ->field('material_id, count(after_id) use_times')
                ->group('material_id')->select();
            foreach ($list as &$value) {
                $value['marterial_name'] = getMaterialName($value['material_id']);
                $value['proportion'] = round($value['use_times'] / $num * 100, 2) . "％";
            }
            $this->ajaxReturn($list);
        } else {
            $list = D('AfterService')->field('material_type id')->group('material_type')->select();
            foreach ($list as &$value) {
                $value['name'] = getMaterialTypeName($value['id']);
            }
            $this->assign('list', $list);
            $this->display();
        }
    }
}