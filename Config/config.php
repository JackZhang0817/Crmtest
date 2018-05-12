<?php
return array(
    // 数据库配置
    'DB_HOST'     => '127.0.0.1',
    'DB_NAME'     => 'crm_meibao1999',
    'DB_USER'     => 'meibao',
    'DB_PWD'       => 'meibao1999',
    'DB_PREFIX'   => 'think_',
    'DB_LIKE_FIELDS' => 'CName|Tel|Address|title|Number|Company|Contact|Tel|hetongbianhao',   //开启模糊查询的字段

    // Auth认证配置
    'AUTH_CONFIG' => array(
        'AUTH_ON'           => true,                        //认证开关
        'AUTH_TYPE'         => 1,                           //认证方式, 1为时时认证, 2为登录认证
        'AUTH_GROUP'        => 'think_group',               //用户组数据表
        'AUTH_GROUP_ACCESS' => 'think_users_group',         //用户组明细表
        'AUTH_RULE'         => 'think_rule',                //权限规则表
        'AUTH_USER'         => 'think_users'                //用户信息表
    ),

    // 无需权限验证
    'ADMINISTRATOR'     => array('1'),   // 超级管理员  用户的ID
    'APPLYACTION'         => array('index','userCenter','groupUsers','header','updatenews','viewAllNews','delReadNews','checktel','viewNums','remindCustomer','displayFields','projectFields','phpExcel','fastUpdate','getStateCustomer','search','getAllCome','upremind','getMonthCome','chooseState','comment', 'downloadCustomerDemo','remindvisitRecord','returnurl','notifyurl','goOnAlipay','unsetOrder','openPaltform','t_search'),    // 无需验证的操作名

    // 配置邮件发送基本参数
    'MAIL_HOST'         => 'smtp.ym.163.com',           //SMTP服务器
    'MAIL_LOGINNAME'    => 'gaorenhua@qigubrother.com', //邮箱帐号
    'MAIL_PASSWORD'     => '24j5E3a4j6',                //邮箱密码
    'MAIL_FORM'     => '奇古兄弟网络科技有限公司', //发件人名称
    'MAIL_URL'          => 'http://www.zxicrm.com/',            //CRM网址 用于发送邮件时url跳转

    // 设置时区
    'DEFAULT_TIMEZONE' => 'Asia/Shanghai',

    // 设置cookie保存时间 一周
    'AUTO_LOGIN_TIME'  => $_SERVER['REQUEST_TIME'] + 24 * 7 * 3600,

    // 设置自动过滤数据
    //'VAR_FILTERS' => 'filter_default',

    // 用于异位或加密的key值
    'ENCRYPTION_KEY'    =>  'XR1z-OK.LsvCA|G]n>(/7<"w&W%PJQ6;2ky?[V=;mb',

    // 定义模版目录结构 减少目录层次
    'TMPL_FILE_DEPR' => '_',

    // 退出登录跳转页面
    'INDEX_PATH'     => '/',

    // 显示页面Trace信息
    //'SHOW_PAGE_TRACE' => true,

    // 免费用户允许添加的用户数
    'MAX_USER_NUMS'   => 1000,

    // 每个用户每月的价格, 单位元
    'ONE_USER_PRICE_MONTH' => 10,

    // 工程管理开通用户数
    'MAX_PROJECT_USER_NUMS'   => 3,

    // 工程管理开通用户的价格, 单位元
    'MAX_PROJECT_USER_PRICE' => 50,

    // 部门组类别
    'GROUP_LIST' => array(
        '1' => '业务组',
        '2' => '设计组',
        '3' => '财务组',
        '4' => '管理组',
        '5' => '监理组',
        '6' => '施工组',
             '7' => 'CAD制图组',
             '8' => '效果图制图组',
             '9' => '材料组'
    ),

    // 帖子分类
    'DETAIL_CATE' => array(
            '1' => '官方发布',
            '2' => '新手上路',
            '3' => '用户反馈',
            '4' => '使用技巧',
            '5' => '闲聊灌水'
    ),


    //支付宝配置参数
    'alipay_config'=>array(
        'partner' =>'2088811215649632',   //合作身份者id，以2088开头的16位纯数字
        'key'=>'ahwr4st6hsbyyg0xq2aqf66uurj8lss3',//安全检验码，以数字和字母组成的32位字符
        'sign_type'=>strtoupper('MD5'),//签名方式 不需修改
        'input_charset'=> strtolower('utf-8'),//字符编码格式 目前支持 gbk 或 utf-8
        'cacert'=> VENDOR_PATH . "Alipay/cacert.pem",//getcwd() .'\\ThinkPHP\\Extend\\Vendor\\Alipay\\cacert.pem',//ca证书路径地址，用于curl中ssl校验
        'transport'=> 'http',//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    ),

    'alipay'   =>array(
        'seller_email'=>'qigubrother@163.com',//卖家的支付宝账号即申请接口时注册的支付宝账号
        'notify_url'=>'http://www.zxicrm.com/crm.php/Trade/notifyurl.html',//异步通知页面url；
        'return_url'=>'http://www.zxicrm.com/crm.php/Trade/returnurl.html',//页面跳转通知url；
        'successpage'=>'Trade/lists', //支付成功跳转到的页面
        'errorpage'=>'Trade/recharge',   //支付失败跳转到的页面
    ),

    // 导入客户信息excel的字段配置
    'IMPORT_CUSTOMER_EXCEL_FIELDS' => require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config-importCustomerExcel.php',
);
?>
