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
        if($this->isPost()){
            $type_id = $this->_param('type_id');
            $where['marterial_type'] = $type_id;
            if($type_id == 0) {
                $list = $material->select();
            }else{
                $list = $material->where($where)->order('use_times desc, martrial_name asc')->select();
            }
            $this->assign('list', $list);
            $this->display('ajaxMaterialList');
        }else{
            $list = $material->order('marterial_type asc,use_times desc')->select();
            $material_type = D('MaterialType');
            $type = $material_type->select();
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
            $list = $material->where($where)->select();
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
            $material = D('Material');
            $list = $material->where($where)->field('marterial_type, marterial_name, use_times')->select();
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
            $list = $material_type->select();
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