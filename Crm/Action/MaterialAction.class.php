<?php
/**
 * Created by Jackzhang.
 * User: zhanghuan
 * Date: 2018/5/15
 * Time: 下午11:26
 */

class MaterialAction extends CommonAction
{
    /**
     *添加材料
     */
    public function addMaterial()
    {
        if($this->isPost()){
            //new model
            $Material = D('Material');

            if(!$Material->create()){  //check the data
                $res['info'] = $Material->getError();
                $this->ajaxReturn($res, '', 0);
            }else{  //add the data
                $res = $Material->add();
                if(false == $res){
                    $res['info'] = $Material->getError();
                    $this->ajaxReturn($res, '添加失败', 0);
                }else{
                    $this->ajaxReturn($res, '添加成功', 1);
                }
            }
        }else{
            $material_type = D('MaterialType');
            $list = $material_type->select();
            $this->assign('list', $list);
            $this->display();
        }
    }

    /**
     * 修改材料
     */
    public function alertMaterial()
    {
        if ($this->isPost()){
            $type_id = $this->_param('marterial_id');
            $type_name = $this->_param('marterial_name');

            $material = D('Material');
            $info = $material->where(array('marterial_name' => $type_name))->find();
            if($info){
                $this->ajaxReturn(array('code' => 0, 'msg' => '材料名称重复'));
            }
            $res = $material->where(array('marterial_id' => $type_id))->save(array('marterial_name' => $type_name));
            if($res){
                $this->ajaxReturn(array('code' => 1, 'msg' => '成功'));
            }
        }
    }

    /**
     * 删除材料
     */
    public function delMaterial()
    {
        if($this->isPost()){
            $material_id = $this->_param('marterial_id');

            $material = D('Material');

            $res = $material->where(array('marterial_id' => $material_id))->delete();
            if($res) {
                $this->ajaxReturn(array('code' => 1, 'msg' => '成功'));
            }
        }
    }

    /**
     * 材料列表
     */
    public function materialList()
    {
        $material = D('Material');
        $material_history = D('MaterialHistory');
        $materialview = D('MaterialView');
        if($this->isPost()){
            $type_id = $this->_param('type_id');
            $page_num = $this->_param('page_num');
            if($page_num == ''){
                $page_num = 1;
            }
            $where['marterial_type'] = $type_id;
            if($type_id == 0) {
                $list = $materialview->group('marterial_id')->order('`use_time` desc')->page($page_num, 10)->select();
                $total_num = $material->count();
            }else{
                $list = $materialview->where($where)->group('marterial_id')->order('`use_time` desc')->page($page_num, 10)->select();
                $total_num = $material->where($where)->count();
            }
            foreach ($list as $k => &$v){
                $v['use_time'] = $material_history->where(array('material_id' => $v['marterial_id']))->count();
            }
            $list = array_sort($list, 'use_time', 'desc');
            if($total_num % 10 == 0){
                $page_total = intval($total_num / 10);
            }else{
                $page_total = intval($total_num / 10) + 1;
            }
            $this->assign('page_num', $page_num);
            $this->assign('page_total',$page_total);
            $this->assign('total_num', $total_num);
            $this->assign('list', $list);
            $this->display('ajaxMaterialList');
        }else{
            $total_num = $material->count();
            if($total_num % 10 == 0){
                $page_total = intval($total_num / 10);
            }else{
                $page_total = intval($total_num / 10) + 1;
            }
            $list = $materialview->group('marterial_id')->order('`use_time` desc')->page(1, 10)->select();
//            $list = $material->order('marterial_type asc,use_times desc')->page(1, 10)->select();
            $list = array_sort($list, 'use_time', 'desc');
            $material_type = D('MaterialType');
            $type = $material_type->select();
            $this->assign('page_num', 1);
            $this->assign('page_total',$page_total);
            $this->assign('total_num', $total_num);
            $this->assign('type', $type);
            $this->assign('list', $list);
            $this->display();
        }
    }

    /**
     *根据type获取材料名称列表
     */
    public function getMaterialList()
    {
        if($this->isPost()) {
            $material = D('Material');
            $type_id = $this->_param('type_id');
            $where['marterial_type'] = $type_id;
            $list = $material->where($where)->order('convert(marterial_name using gbk) asc')->select();
            $new_arr = $list == null ? [] : $list;
            $this->ajaxReturn($new_arr);
        }
    }

    /**
     * 添加材料类型
     */
    public function addMaterialType()
    {
        if($this->isPost()){
            $materialType = D('MaterialType');
            if(!$materialType->create()){  //check the data
                $res['info'] = $materialType->getError();
                $this->ajaxReturn($res, '', 0);
            }else{  //add the data
                $res = $materialType->add();
                if(false == $res){
                    $res['info'] = $materialType->getError();
                    $this->ajaxReturn($res, '添加失败', 0);
                }else{
                    $this->ajaxReturn($res, '添加成功', 1);
                }
            }
        }
        $this->display();
    }

    /**
     * 材料报表
     */
    public function materialChart()
    {
        if($this->isPost()){
            $info = $this->_param();
            $where = array(
                array('marterial_type' => $info['type_id'])
            );
            $materialview = D('MaterialView');

            $list = $materialview->where($where)->group('marterial_id')->order('`use_time` desc')->select();
            foreach ($list as &$value){
                $value['use_times'] = $value['use_time'];
            }
            $new_arr = $list == null ? [] : $list;
            $this->ajaxReturn($new_arr);
        }else{
            $materialType = D('MaterialType');
            $list = $materialType->select();
            $this->assign('typeList', $list);
            $this->display();
        }
    }

    /**
     * 获取材料类型列表
     */
    public function materialTypeList()
    {
        if($this->isPost()){
            $material_type = D('MaterialType');
            $list = $material_type->order('sort asc')->select();
            $new_arr = $list == null ? [] : $list;
            if($new_arr != null){
                foreach ($new_arr as $key => $value){
                    $new_arr[$key]['material_num'] = getMaterialTypeNum($value['type_id']);
                }
            }
            $this->ajaxReturn($new_arr);

        }
    }

    /**
     *修改材料类型
     */
    public function alertMaterialType()
    {
        if ($this->isPost()){
            $status = $this->_param('status');
            if($status == ''){
                $status = 1;
            }
            if($status == 2){
                $type_id = $this->_param('type_id');
                $sort = $this->_param('sort');
                $material_type = D('MaterialType');
                $res = $material_type->where(array('type_id' => $type_id))->save(array('sort' => $sort));
                if($res){
                    $this->ajaxReturn(array('code' => 1, 'msg' => '成功'));
                }
            }
            $type_id = $this->_param('type_id');
            $type_name = $this->_param('type_name');

            $material_type = D('MaterialType');
            $res = $material_type->where(array('type_id' => $type_id))->save(array('type_name' => $type_name));
            if($res){
                $this->ajaxReturn(array('code' => 1, 'msg' => '成功'));
            }
        }
    }

    /**
     * 删除材料类型
     */
    public function deleteMaterialType()
    {
        if($this->isPost())
        {
            $type_id = $this->_param('type_id');
            if($type_id !== ''){
                $material = D('Material');
                $info = $material->where(array('marterial_type' => $type_id))->select();
                if($info){
                    $this->ajaxReturn(array('code' => 0, 'msg' => '存在数据，无法删除'));
                }
                $material_type = D('MaterialType');
                $res = $material_type->where(array('type_id' => $type_id))->delete();
            }
            if($res){
                $this->ajaxReturn(array('code' => 1, 'msg' => '成功'));
            }
        }
    }

}