<?php

/**
 * Created by Yansor.
 * User: 468012316 <468012316@qq.com>
 * Date: 15-3-7
 * Time: 上午9:48
 * 交易记录
 */
class OrdersModel extends RelationModel
{
    /**
     * 客户信息自动验证
     */
    protected $_validate = array(
        array('ordprice', 'require', '充值金额不能为空'),
        array('ordprice', 'number', '只能输入整数'),
        array('ordprice', 'checkOrdprice', '充值金额不能低于1000', 0,'function'),

    );

    /**
     * 附加信息 关联关系
     */
    protected $_link = array(
        'user' => array(
            'mapping_type' => HAS_ONE,
            'class_name' => 'users',
            'foreign_key' => 'userid',
        ),
    );


    /**
     * 检查金额不能低于1000
     * @param $data
     * @return bool
     */
    protected function checkOrdprice($data){
        if($data < 1000){
            return false;
        }else{
            return true;
        }
    }
}