<?php
/**
 * Author: gaorenhua	
 * Date: 2014-11-19	
 * Email: 597170962@qq.com
 * 客户管理模型 :: 自动验证  状态 装修方式 来源 回访记录关联关系
 */
class CustomerModel extends RelationModel {
	/**
	 * 客户信息自动验证
	 */
	protected $_validate = array(
		array('ConsultDate', 'require', '咨询时间不能为空'),
		array('CName', 'require', '客户姓名不能为空'),
		//array('Tel', '/^0?(13[0-9]|15[012356789]|18[0-9]|17[0678]|14[57])[0-9]{8}$/','手机号码格式不正确'),
		array('Tel', '/^[0-9]*$/','只能填写数字'),
		array('Address', 'require', '所在小区不能为空'),
		//array('Space', 'number','只能输入整数', 2),
		//array('OrdersValue', 'number','只能输入整数', 2),
	);

	/**
	 * 附加信息 关联关系
	 */
	protected $_link = array(
		'attached' => array(
			'mapping_type'	=> HAS_ONE,
			'class_name'	=> 'customer_attached',
			'foreign_key'	=> 'customer_id',
		),
		'record'  => array(
			'mapping_type'  => HAS_ONE,
			'foreign_key'   => 'customer_id'
		) 
	);
}