<?php
/**
 * Author: gaorenhua	
 * Date: 2014-12-12	
 * Email: 597170962@qq.com
 * 施工详情视图模型 :: 施工详情与施工工序与用户关联关系
 */
class PlatinfoViewModel extends ViewModel{
	/**
	 * 定义视图模型包含的字段
	 */
	public $viewFields = array(
     'platinfo'=>array('id','uid','pid','customer_id','title','thumb','content','entrytime'),
     'project'=>array('pname', 'pid' => 'pp', '_on'=>'platinfo.pid = project.id'),
     'users'=>array('realname', '_on'=>'platinfo.uid = users.id')
   );
}