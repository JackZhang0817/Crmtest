<?php
/**
 * Author: gaorenhua	
 * Date: 2014-11-09	
 * Email: 597170962@qq.com
 * 用户组和权限组关系模型   添加用户组验证模型
 */
class GroupModel extends RelationModel {
	/**
	 * 自动验证表单数据
	 */
	protected $_validate = array(
		// 验证是否为空
		array('title', 'require', '部门名称不能为空'),
		array('remark', 'require', '部门描述不能为空'),
	);

	/**
	 * 用户组和权限组关系模型
	 */
	protected $_link = array(
		'group' => array(
			'mapping_type' 	=> HAS_MANY,
			'class_name' 	=> 'users_group',
			'foreign_key'  	=> 'group_id',
			'mapping_fields'  => 'uid, group_id',
		)
	);
}