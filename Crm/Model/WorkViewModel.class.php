<?php
/**
 * Author: gaorenhua	
 * Date: 2014-11-19	
 * Email: 597170962@qq.com
 * 客户管理视图模型 ::  状态 装修方式 来源 回访记录关联关系
 */
class WorkViewModel extends ViewModel {
	/**
	 * 定义视图模型包含的字段
	 */
	public $viewFields = array(
		'work' => array('id','Userid','Company','Contact','Position','Tel','Channel','ThirdBiz','Address','Space','Type','Amount','Content','ConsultDate','Manager','Designer','State','Way','Remark','status','rengongfei','fukuanjihua','level','Project','hetongbianhao','cailiaoyuan','kaigongshijian','jungongshijian','zhibaojin','zhibaodaoqi','_type'=>'LEFT'),
		'work_attached' => array('markcolor', '_on' => 'work.id = work_attached.customer_id', '_type'=>'RIGHT'),
		'users' => array('realname', '_on' => 'work.Userid = users.id','_type'=>'LEFT'),
		'channel' => array('channelname', '_on' => 'work.Channel = channel.id')
	);
}
