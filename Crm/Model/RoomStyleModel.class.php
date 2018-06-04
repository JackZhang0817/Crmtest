<?php
/**
 * Created by PhpStorm.
 * User: zhanghuan
 * Date: 2018/5/17
 * Time: 下午9:40
 */

class RoomStyleModel extends Model
{
    protected $_validate = array(
        array('style_name', '', '此风格已经存在!', '0', 'unique', 1),
    );
    protected $_auto = array(
        array('create_time', 'time', '1', 'function'),
    );

}