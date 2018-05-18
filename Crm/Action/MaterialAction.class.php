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
     *
     */
    public function addMaterial()
    {
        $material_type = D('MaterialType');
        $list = $material_type->select();
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 添加材料类型
     */
    public function addMaterialType()
    {
        $this->display();
    }
}