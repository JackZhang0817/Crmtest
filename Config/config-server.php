<?php
return array(
	// 数据库配置
	'DB_HOST'     => '127.0.0.1',
    'DB_NAME'     => 'crm_meibao1999',
    'DB_USER'     => 'meibao',
    'DB_PWD'       => 'meibao1999',
    'DB_PREFIX'   => 'think_',
	'DB_LIKE_FIELDS' => 'CName|Tel|Address|title',   //开启模糊查询的字段

	// Auth认证配置
	'AUTH_CONFIG' => array(
		'AUTH_ON' 			=> true, 						//认证开关
		'AUTH_TYPE' 		=> 1,   						//认证方式, 1为时时认证, 2为登录认证
		'AUTH_GROUP' 		=> 'think_group',  				//用户组数据表
		'AUTH_GROUP_ACCESS' => 'think_users_group', 		//用户组明细表
		'AUTH_RULE' 		=> 'think_rule', 				//权限规则表
		'AUTH_USER' 		=> 'think_users'				//用户信息表
	),

    // 无需权限验证
    'ADMINISTRATOR'		=> array('1'),   // 超级管理员  用户的ID
    'APPLYACTION'         => array('index','userCenter','groupUsers','header','updatenews','viewAllNews'),    // 无需验证的操作名

	// 配置邮件发送基本参数
	'MAIL_HOST' 		=> 'smtp.ym.163.com',			//SMTP服务器
	'MAIL_LOGINNAME' 	=> 'gaorenhua@qigubrother.com',	//邮箱帐号
	'MAIL_PASSWORD' 	=> '24j5E3a4j6',				//邮箱密码
	'MAIL_FORM'		=> '青岛奇古兄弟网络科技有限公司', //发件人名称
	'MAIL_URL'			=> 'http://www.zxicrm.com/',   //CRM网址 用于发送邮件时url跳转

	// 设置时区
	'DEFAULT_TIMEZONE' => 'Asia/Shanghai',

	// 设置cookie保存时间 一周
	'AUTO_LOGIN_TIME'  => $_SERVER['REQUEST_TIME'] + 24 * 7 * 3600,

	// 用于异位或加密的key值
	'ENCRYPTION_KEY'	=>	'XR1z-OK.LsvCA|G]n>(/7<"w&W%PJQ6;2ky?[V=;mb',

	// 定义模版目录结构 减少目录层次
	'TMPL_FILE_DEPR' => '_',

	// 退出登录跳转页面
	'INDEX_PATH'     => '/',

	// 显示页面Trace信息
	'SHOW_PAGE_TRACE' => true,

    // 免费用户允许添加的用户数
	'MAX_USER_NUMS'	  => 1000,

	// 部门组类别
	'GROUP_LIST' => array(
		'1' => '业务组', 
		'2' => '设计组', 
		'3' => '财务组', 
		'4' => '管理组', 
		'5' => '工程组'
	),

    // 帖子分类
    'DETAIL_CATE' => array(
        '1' => '官方发布',
        '2' => '新手上路',
        '3' => '用户反馈',
        '4' => '使用技巧',
        '5' => '闲聊灌水'
    )
);
?>