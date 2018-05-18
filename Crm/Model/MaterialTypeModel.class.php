<?php
/**
 * Created by PhpStorm.
 * User: zhanghuan
 * Date: 2018/5/17
 * Time: 下午9:40
 */
namespace Crm\Model;
use Think\Model;

class MaterialTypeModel extends Model
{
    protected $_validata = array(
        array('type_name','', '此材料类型已经存在',0, 'unique', 1),
    );

}