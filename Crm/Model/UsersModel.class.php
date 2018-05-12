<?php
/**
 * Author: gaorenhua
 * Date: 2014-11-06
 * Email: 597170962@qq.com
 * 登录验证模型  用户组 子用户关联模型
 */
class UsersModel extends RelationModel {
    /**
     * 自动验证提交表单
     */
    protected $_validate = array(
        // 验证是否符合规则
        array('realname', 'require', '真实姓名不能为空'),
        array('tel', '/^0?(13[0-9]|15[012356789]|18[0-9]|17[0678]|14[57])[0-9]{8}$/','手机号码格式不正确'),
        //array('email', 'email','邮箱地址格式不正确'),
        array('username', '/^[a-zA-Z][\w]{4,16}$/','用户名需以字母开头, 数字相结合, 可使用下划线_, 长度为5-17位'),
        array('password', '/^[a-zA-Z][\w]{4,16}$/','密码需以字母开头, 数字相结合, 可使用下划线_, 长度为5-17位'),
        array('rpassword', 'password','两次输入密码不一致', '0', 'confirm'),

        // 验证是否存在
        array('tel', '', '该联系方式已经注册!', 0, 'unique', 1),
        array('email', '', '该邮箱已经被注册!', 0, 'unique', 1),
        array('username', '', '用户名已经存在！', 0, 'unique', 1)
    );

    /**
     * 子用户和用户组关联模型
     */
    protected $_link = array(
        'group' => array(
            'mapping_type'  => HAS_ONE,
            'class_name'    => 'users_group',
            'foreign_key'   => 'uid',
            'as_fields'     => 'uid,group_id'
        ),
        'defined' => array(
            'mapping_type'  => HAS_ONE,
            'class_name'    => 'user_defined',
            'foreign_key'   => 'uid'
        )
    );
}
