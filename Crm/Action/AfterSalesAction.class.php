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

        }else{
            $material_type = D('MaterialType');
            $type_list = $material_type->select();
            $user_list = M('UsersGroup')->where(array('group_id' => 14))->select();

            $this->assign('type_list', $type_list);
            $this->assign('user_list', $user_list);
            $this->display();
        }

    }
    public function afterSaleList()
    {

    }
    public function alertAfterSale()
    {

    }
    public function delAfterSale()
    {

    }
}