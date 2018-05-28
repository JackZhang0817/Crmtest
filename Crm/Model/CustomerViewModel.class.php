<?php
/**
 * Author: gaorenhua	
 * Date: 2014-11-19	
 * Email: 597170962@qq.com
 * 客户管理视图模型 ::  状态 装修方式 来源 回访记录关联关系
 */
class CustomerViewModel extends ViewModel {
	/**
	 * 定义视图模型包含的字段
	 */
	public $viewFields = array(
		'customer' => array('id','Userid','ConsultDate','sremind','dremind','premind','CName','Tel','Address','Space','Way','Channel','Designer','Project','State','ComeTime','OrderTime','HetongTime','StartTime','EndTime','Number','Deposit','Captain','OrdersValue','status','Drawing','Material','xiaoguotu','jiaofangtime','liangfangtime','huxing','fixed','shejifei','guanlifei','qingfu','zhucai','once','twice','tirth','others','IDNo','Birthday','Profession','IM','RoomType','Style','Address2','CancelDate','CancelAmount','CAD','Pic','DiscountCode','Detail','FirstPay','FirstPayDate','MidPay','MidPayDate','EndPay','EndPayDate','Content','taochan','zaojia','level','FirstPay2','MidPay2','EndPay2','men','chuju','zuobian','diban','diaoding','shuidian','xiaojian','bizhi','longtou','xiaogui','weishengjian','chufang','gongqi','yanqi','material_info','baoxiuriqi','CancelTime','CancelDeposit','_type'=>'LEFT'),
		'customer_attached' => array('markcolor', '_on' => 'customer.id = customer_attached.customer_id', '_type'=>'RIGHT'),
		'users' => array('realname', '_on' => 'customer.Userid = users.id','_type'=>'LEFT'),
		'channel' => array('channelname', '_on' => 'customer.Channel = channel.id','_type'=>'LEFT'), 		
		'way' => array('wayname', '_on' => 'customer.Way = way.id', '_type'=>'LEFT'),
		'room_type' => array('room_type_name', '_on' => 'customer.RoomType = room_type.id'),
	);
}