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
        if($this->isPost()){
            $data = M('AfterService')->create();
            $data['state'] = 0;
            $data['service_date'] = strtotime($data['service_date']);
            $data['mobile'] = intval($data['mobile']);
            $data['create_time'] = time();
            $res = M('AfterService')->add($data);
            if($res) {
                $this->success('添加成功', U('AfterSales/afterSaleList'));
            }
        }else{
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
        $list = M('AfterService')->select();

        $this->assign('list', $list);
        $this->display();
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
        if(IS_POST){
            $type_id = $this->_param('id');
            $where = array(
                'material_type' => $type_id,
            );
            $num = D('AfterService')->where($where)->count();
            $list = D('AfterService')
                ->where($where)
                ->field('material_id, count(after_id) use_times')
                ->group('material_id')->select();
            foreach($list as &$value){
                $value['marterial_name'] = getMaterialName($value['material_id']);
                $value['proportion'] = round($value['use_times'] / $num * 100 , 2) . "％";
            }
            $this->ajaxReturn($list);
        }else{
            $list = D('AfterService')->field('material_type id')->group('material_type')->select();
            foreach ($list as &$value){
                $value['name'] = getMaterialTypeName($value['id']);
            }
            $this->assign('list', $list);
            $this->display();
        }
    }
}