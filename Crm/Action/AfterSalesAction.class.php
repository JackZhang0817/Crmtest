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
    public function alertAfterSale()
    {

    }

    public function delAfterSale()
    {

    }
}