<?php
/**
 * Created by PhpStorm.
 * User: zhanghuan
 * Date: 2018/5/17
 * Time: 下午9:40
 */

class MaterialTypeModel extends Model
{
    protected $_validate = array(
        array('type_name','', '此材料类型已经存在',0, 'unique', 1),
    );
    protected $_auto = array(
        array('create_time', 'time', '1', 'function'),
        array('update_time', 'time', '2', 'function'),
    );
}