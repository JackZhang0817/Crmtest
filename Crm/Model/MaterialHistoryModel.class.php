<?php
/**
 * Created by PhpStorm.
 * User: zhanghuan
 * Date: 2018/5/17
 * Time: 下午9:40
 */

class MaterialHistoryModel extends Model
{
    protected $_auto = array(
        array('create_time', 'time', '1', 'function'),
        array('update_time', 'time', '2', 'function'),
    );

    /**
     * 添加材料使用历史操作
     * @param $customer_id
     * @param $info
     * @return bool
     */
    public function handleHistory($customer_id, $info)
    {
        $material_info = json_decode($info, true);
        foreach ($material_info as $k => $value){
            if($value != 0){ //value为0说明没有选择此类型材料，或者取消选择此类型材料
                $map = array(
                    'customer_id' => $customer_id,
                    'material_type' => $k,
                );
                $res = $this->where($map)->find();
                if($res){  //判断数据库是否已经存在此客户此类型的信息
                    if($res['material_id'] != $value){
                        $res2 = $this->where($map)->setField('material_id', $value);
                        if(!$res2)
                            return false;
                    }
                }else{
                    $create_info = array(
                        'customer_id' => $customer_id,
                        'material_type' => $k,
                        'material_id' => $value,
                        'create_time' => time()
                    );
                    $res2 = $this->add($create_info);
                    if(!$res2)
                        return false;
                }
            }else{
                $map = array(
                    'customer_id' => $customer_id,
                    'material_type' => $k,
                );
                $res = $this->where($map)->find();
                if($res) {  //判断数据库是否已经存在此客户此类型的信息
                    $res2 = $this->where($map)->delete();
                    if(!$res2)
                        return false;
                }
            }
        }
        return true;
    }
}