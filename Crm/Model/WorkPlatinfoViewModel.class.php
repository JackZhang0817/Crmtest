<?php
/**
 * Author: gaorenhua	
 * Date: 2014-12-12	
 * Email: 597170962@qq.com
 * 施工详情视图模型 :: 施工详情与施工工序与用户关联关系
 */
class WorkPlatinfoViewModel extends ViewModel{
	/**
	 * 定义视图模型包含的字段
	 */
	public $viewFields = array(
     'work_platinfo'=>array('id','uid','pid','customer_id','title','thumb','content','entrytime'),
     'work_project'=>array('pname','sort', 'pid' => 'pp', '_on'=>'work_platinfo.pid = work_project.id'),
     'users'=>array('realname', '_on'=>'work_platinfo.uid = users.id')
   );
}