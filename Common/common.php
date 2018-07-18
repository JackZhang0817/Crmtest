<?php
/**
 * Author: gaorenhua
 * Date: 2014-11-06
 * Email: 597170962@qq.com
 * 公用函数库
 */

/**
 * 自定义打印数组
 * @param $arr 需要打印的数组
 */
function p($arr)
{
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}

/**
 * 检查是否登录
 */
function is_login()
{
    if (!empty($_SESSION['uid'])) {
        return true;
    } else {
        return false;
    }
}

/**
 * 异位或加密解密字符串
 * @param $value 需要加密的字符串
 * @param int $type 操作方式 0-加密  1-解密
 * @return int|mixed 加密或解密后的字符串
 */
function encryption($value, $type = 0)
{
    $key = md5(C('ENCRYPTION_KEY'));

    //加密
    if (!$type) {
        return str_replace('=', '', base64_encode($value ^ $key));
    }

    //解密
    $value = base64_decode($value);
    return $value ^ $key;
}

/**
 * 递归重组节点信息为多维数组
 * @param $node 待处理的数组
 * @param null $access
 * @param string $pid 父级ID
 * @return array 重组后的数组
 */
function node_merge($node, $access = null, $pid = '0')
{
    $arr = array();

    foreach ($node as $v) {
        if (is_array($access)) {
            $v['access'] = in_array($v['id'], $access) ? 1 : 0;
        }
        if ($v['pid'] == $pid) {
            $v['child'] = node_merge($node, $access, $v['id']);
            $arr[] = $v;
        }
    }
    return $arr;
}

/**
 * [合并数组  避免array_merge()造成的重置索引]
 * @param  [array] $a [数组]
 * @param  [array] $b [数组]
 * @return [array]    [数组]
 */
function merge_array($a, $b)
{
    foreach ($a as $key => $value) {
        $c[$key] = $value;
    }
    foreach ($b as $key => $value) {
        $c[$key] = $value;
    }
    return $c;
}

/**
 * 发送邮件-主要用户邀请用户时发送邀请
 * @param $data 需要发送邮件的邮件地址数组
 * @param $title 邮件的标题
 * @param $content 邮件的内容
 * @param $type 1-默认, 2-系统扣款通知
 * @return bool 发送状态
 */
function sendMail($data, $title, $content, $type = 1)
{
    // 载入邮件发送类库
    Vendor('PHPMailer.PHPMailerAutoload');

    $mail = new PHPMailer;

    $mail->isSMTP(); //设置PHPMailer使用SMTP服务器发送Email
    $mail->Host = C('MAIL_HOST'); //指定SMTP服务器 可以是smtp.126.com, gmail, qq等服务器 自行查询
    $mail->SMTPAuth = true;
    $mail->CharSet = 'UTF-8'; //设置字符集 防止乱码
    $mail->Username = C('MAIL_LOGINNAME'); //发送人的邮箱账户
    $mail->Password = C('MAIL_PASSWORD'); //发送人的邮箱密码
    $mail->Port = 25; //SMTP服务器端口

    $mail->From = C('MAIL_LOGINNAME'); //发件人邮箱地址
    $mail->FromName = C('MAIL_FORM'); //发件人名称
    $mail->WordWrap = 50; // 换行字符数
    $mail->isHTML(true); // 设置邮件格式为HTML

    $mail->Subject = $title; //邮件标题

    //判断是否是多个邮箱  循环发送邮件
    if (is_array($data)) {
        foreach ($data as $email) {
            $mail->addAddress($email); // 收件人邮箱地址 此处可以发送多个
            $url = C('MAIL_URL') . U('Login/inviteRegister', array('code' => encryption($email))); //获取加密的发送地址
            if ($type === 1) {
                $mail->Body = $content . '<a href=' . $url . '>' . $url . '</a>'; //邮件内容
            } else {
                $mail->Body = $content;
            }

            // 有发送失败的地址就返回false
            if (!$mail->send()) {
                return false;
            }

            $mail->ClearAddresses(); //清除收件人
        }
    }

    // 全部发送成功 返回true
    return true;
}

/**
 * 返回数组中指定的一列
 * @param array $input [要处理的多维数组]
 * @param varchar $columnKey [需要返回的列,可以是索引]
 * @param varcahr $indexKey [作为返回数组的索引/键的列]
 */
if (!function_exists('array_column')) {
    function array_column($input, $columnKey, $indexKey = null)
    {
        $columnKeyIsNumber = (is_numeric($columnKey)) ? true : false;
        $indexKeyIsNull = (is_null($indexKey)) ? true : false;
        $indexKeyIsNumber = (is_numeric($indexKey)) ? true : false;
        $result = array();
        foreach ((array)$input as $key => $row) {
            if ($columnKeyIsNumber) {
                $tmp = array_slice($row, $columnKey, 1);
                $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : null;
            } else {
                $tmp = isset($row[$columnKey]) ? $row[$columnKey] : null;
            }
            if (!$indexKeyIsNull) {
                if ($indexKeyIsNumber) {
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && !empty($key)) ? current($key) : null;
                    $key = is_null($key) ? 0 : $key;
                } else {
                    $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                }
            }
            $result[$key] = $tmp;
        }
        return $result;
    }
}

/**
 * 获取指定ID的用户名
 * @param $id 用户ID
 * @return mixed|string 用户名称
 */
function username($id)
{
    $username = M('users')->where(array('id' => $id))->getField('username');
    return $username ? $username : '--';
}

/**
 * 获取置指定ID用户的真实姓名或昵称
 * @param $id 用户ID
 * @return mixed|string 真实姓名或昵称
 */
function realname($id)
{
    $realname = M('users')->where(array('id' => $id))->getField('realname');
    return $realname ? $realname : '--';
}

/**
 * 获取指定用户的头像
 * @param $id 用户ID
 * @return mixed|string 头像地址
 */
function photo($id)
{
    $photo = M('users')->where(array('id' => $id))->getField('photo');
    return $photo ? $photo : '0';
}

/**
 * 获取指定用户的联系方式
 * @param $id 用户ID
 * @return mixed|string 联系方式
 */
function tel($id)
{
    $tel = M('users')->where(array('id' => $id))->getField('Tel');
    return $tel ? $tel : '--';
}

/**
 * 清除已存在的cookie session
 */
function del_cookie_session()
{
    cookie('auto', null); //清楚cookie  防止恢复账户后有效期内的自动登录
    session('uid', null); //清楚session  防止恢复账户后越过验证
}

/**
 * 显示用户组属性
 */
function className($id)
{
    $class = C('GROUP_LIST'); //组类别
    echo $class[$id];
}

/**
 * 返回用户所在组的属性 1-业务组 2-设计组 3-财务组 4-管理组 5-工程组
 */
function classid()
{
    $uid = D('Users')->where(array('id' => session('uid')))->relation(true)->find();

    // 获取当前用户所在组的属性 1-业务组  2-设计组
    return $class = M('group')->where(array('id' => $uid['group_id']))->getField('class');
}

/**
 * 获取父级ID(当前用户的所在公司ID, 也就是超级管理员的ID)
 */
function fid()
{
    // 判断是否存在父级ID 如果存在直接调用父级ID(pid)  若不存在直接调用session('uid')
    $fid = M('users')->where(array('id' => session('uid')))->getField('pid');
    $pid = M('users')->where(array('id' => $fid))->getField('pid');

    return $pid ? $pid : $fid ? $fid : session('uid');
    //return $fid ? $fid : session('uid');
}

/**
 * 判断是否是超级管理员
 */
function is_admin()
{
    $pid = M('users')->where(array('id' => session('uid')))->getField('pid');
    return $pid ? false : true;
}

/**
 * 判断是否是业务员
 */
function is_salesman()
{
    if (classid() == '1') {
        return true;
    } else {
        return false;
    }
}

/**
 * 判断是否是设计师
 */
function is_designer()
{
    if (classid() == '2') {
        return true;
    } else {
        return false;
    }
}

/**
 * 判断是否是财务
 */
function is_finance()
{
    if (classid() == '3') {
        return true;
    } else {
        return false;
    }
}

/**
 * 判断是否是管理
 */
function is_manager()
{
    if (classid() == '4') {
        return true;
    } else {
        return false;
    }
}

/**
 * 判断是否是工程监理组
 */
function is_project()
{
    if (classid() == '5') {
        return true;
    } else {
        return false;
    }
}

/**
 * 判断是否是工程监理组
 */
function is_captain()
{
    if (classid() == '6') {
        return true;
    } else {
        return false;
    }
}

/**
 * 判断是否是制图组
 */
function is_drawing()
{
    if (classid() == '7') {
        return true;
    } else {
        return false;
    }
}

/**
 * 判断是否是材料组
 */
function is_material()
{
    if (classid() == '8') {
        return true;
    } else {
        return false;
    }
}

/**
 * 获取当前用户所在公司 业务组, 设计组, 财务组, 管理组各组中的所有成员用户
 * @param $id 组属性ID, 1-业务组 2-设计组 3-财务组 4-管理组 5-监理组 6-工长组
 * @return mixed 组内成员ID集合
 */
function each_group_users($id)
{
    $group = M('group')->field('id')->where(array('admin_id' => fid(), 'class' => $id))->select();
    $arr = array_column($group, 'id'); //返回一维数组
    $uid = M('users_group')->field('uid')->where(array('group_id' => array('IN', $arr)))->select();
    $uid = array_column($uid, 'uid'); //返回一维数组
    $users = M('users')->field('id,realname')->where(array('id' => array('IN', $uid), 'username' => array('NEQ', '')))->select();
    return $users;
}

/**
 * 显示自定义显示客户列表字段的函数
 * @param $v 需要显示的字段id
 * @param $field 区分是客户模块 还是工程模块
 * @return string 选中
 */
function xianshi($v, $field)
{
    $arr = explode(',', $_SESSION[$field][$field]);
    $arr = array_filter($arr);

    if (in_array($v, $arr)) {
        return "checked";
    }
}

/**
 * 获取每个可见客户的最后一条追踪记录
 * @param $id 客户id
 * @return string 最后一条追踪记录
 */
function lastRecord($id)
{
    $record = M('record')->where(array('customer_id' => $id))->order('id desc')->find();
    return $record ? "[" . date('Y-m-d', $record['entrytime']) . "]" . $record['content'] : '';
}

/**
 * 获取每个可见客户的最后一条追踪记录
 * @param $id 客户id
 * @return string 最后一条追踪记录
 */
function workRecord($id)
{
    $record = M('work_record')->where(array('customer_id' => $id))->order('id desc')->find();
    return $record ? "[" . date('Y-m-d', $record['entrytime']) . "]" . $record['content'] : '';
}

/**
 * @return array 部门成员ID
 */
function departusers()
{
    $group = M('users_group')->where(array('uid' => session('uid')))->getField('group_id');
    $users = M('users_group')->field('uid')->where(array('group_id' => $group))->select();
    return array_column($users, 'uid');
}

/**
 * @param $id 员工ID
 * @return string 当前ID员工的职务
 */
function job($id)
{
    // 获取父级ID 和 职务标识
    $arr = M('users')->field('pid,job')->where(array('id' => $id))->find();

    // 判断职务
    if ($arr['pid'] == '0' && $arr['job'] == '0') {
        return '超级管理员';
    } elseif ($arr['pid'] != '0' && $arr['job'] == '1') {
        return '业务总监';
    } elseif ($arr['pid'] != '0' && $arr['job'] == '2') {
        return '设计总监';
    } elseif ($arr['pid'] != '0' && $arr['job'] == '3') {
        return '部门经理';
    } else {
        return '普通员工';
    }
}

/**
 * @return array 需要显示的左侧栏菜单
 */
function checkAuth()
{
    // 获取当前用户所在的组权限
    $group_id = M('users_group')->where(array('uid' => session('uid')))->getField('group_id');
    $auth = M('group')->where(array('id' => $group_id))->getField('rules');
    $rule = explode(',', $auth);

    // 查询属于上述权限中的规则

    // 判断超级管理员
    if (in_array(session('uid'), C('ADMINISTRATOR'))) {
        $arr = M('rule')->where(array('type' => '0'))->order('sort asc')->select();
    } else {
        $arr = M('rule')->where(array('id' => array('IN', $rule), 'type' => '0'))->order('sort asc')->select();
    }
    $arr = node_merge($arr);

    return $arr;
}

/**
 * @param $var  需要拆分的字符串
 * @param $id   拆分后数组的索引(KEY) 0 - MODULE_NAME / 1 - ACTION_NAME
 * @return mixed  模块名 / 方法名
 */
function moduleName($var, $id)
{
    $arr = explode('/', $var);
    return $arr[$id];
}

/**
 * @return mixed 返回左侧栏 状态菜单
 */
function stateMenu()
{
    // 查询该父级ID下的所有状态
    $state = M('com_state')->field('id,state_id')->where(array('admin_id' => fid()))->order('sort asc')->select();

    return $state;
}

/**
 * @param $str  需要截取的字符串
 * @param int $start 截取开始位置
 * @param $length  截取长度
 * @param string $charset 编码格式
 * @param bool $suffix
 * @return string 截取后的字符串
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
{
    if (function_exists("mb_substr"))
        return mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        $ret = iconv_substr($str, $start, $length, $charset);
        //for iconv_substr:
        //If str is shorter than offset characters long, FALSE will be returned.
        if (empty($ret)) {
            $ret = '';
        }
        return $ret;
    }
    $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("", array_slice($match[0], $start, $length));
    if ($suffix) return $slice;
    return $slice;
}

/**
 * @param $array  经过序列化的 颜色数组
 * @return string  返回当前用户标记的颜色
 */
function markcolor($array)
{
    $array = unserialize($array);
    return empty($array) ? '' : $array[session('uid')];
}

/**
 * 处理客户状态
 */
function state($array)
{
    $array = explode(',', $array);

    for ($i = 0; $i < count(session('state')); $i++) {
        $arr[$_SESSION['state'][$i]['id']] = $_SESSION['state'][$i]['state_id'];
    }

    foreach ($array as $key) {
        $arrs[] = stateName($arr[$key]);
    }

    $string = implode(',', $arrs);

    return !empty($string) ? $string : '--';
}

/**
 * 更新客户信息 处理客户状态
 */
function checked($id, $arr)
{
    $array = explode(',', $arr);
    if (in_array($id, $array)) {
        echo "checked";
    }
}

/**
 * 公司选择客户状态 disable
 */
function disabled($id, $arr)
{
    if (in_array($id, $arr)) {
        echo "disabled";
    }
}

/**
 * 状态名称
 */
function stateName($id)
{
    $state = M('state')->where(array('id' => $id))->getField('statename');
    return $state;
}

/**
 * 状态ID
 */
function stateID($id)
{
    $state = M('com_state')->where(array('admin_id' => fid(), 'state_id' => $id))->getField('id');
    return $state;
}

/**
 * 渠道名称
 */
function channelName($id)
{
    $channel = M('channel')->where(array('id' => $id))->getField('channelname');
    return $channel;
}

/**
 * 获取本月第一天和最后一天
 */
function Month_f_l()
{
    // 获取本月的第一天和最后一天
    $firstday = date('Y-m-01', strtotime(date('Y-m-d')));
    $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));

    return array('between', array("$firstday", "$lastday"));
}

/**
 * 处理缩略图
 * [string] $string 被序列化之后的图片路径
 */
function mythumb($string)
{
    // 判断是否是数组为空
    if (empty($string)) {
        return null;
    }
    // 反序列化
    $array = unserialize($string);
    // 遍历出图片地址
    foreach ($array as $value) {
        $img .= "<img src='/Uploads/Project/thumb/" . $value . "' />";
    }
    return $img;
}


/**
 * 处理施工工序类别
 */
function pname($id)
{
    $pname = M('project')->where(array('id' => $id))->getField('pname');
    return $pname;
}

/**
 * @param $id 客户ID
 * @return mixed|int 状态ID
 */
function isopen($id)
{
    // 判断是否开通客户平台   开通的话则返回平台状态 0-开启 1-关闭  没开通返回 2
    $status = M('customer_platform')->where(array('customer_id' => $id))->getField('open');
    $status = isset($status) ? $status : '2';

    return $status;
}

/**
 * @param $id 客户ID
 * @return mixed|int 状态ID
 */
function wisopen($id)
{
    // 判断是否开通客户平台   开通的话则返回平台状态 0-开启 1-关闭  没开通返回 2
    $status = M('work_platform')->where(array('customer_id' => $id))->getField('open');
    $status = isset($status) ? $status : '2';

    return $status;
}

/**
 * 随机输出一些小提示
 */
function rand_number()
{
    $suiji = array("Come on！Fighting！", "Very good，keeping！", "小伙伴们，加油！", "签单 So Easy！", "神都无法阻挡！", "奋起直追！", "刮目相看！", "有能力，就是这么任性！", "你这么强大,我也是醉了");
    echo $suiji[array_rand($suiji)];
}

/**
 * @return mixed|string  返回当前用户的系统主题
 */
function theme()
{
    $theme = M('user_defined')->where(array('uid' => session('uid')))->getField('theme');
    $theme = !empty($theme) ? $theme : 'default';
    return $theme;
}

/**
 * @param $info  提示信息
 * @return string  返回美化后的提示信息
 */
function style($info)
{
    return '<div style="width:600px; font-family:Microsoft YaHei,微软雅黑; font-weight:bold; font-size:18px; color:#333; margin:200px auto; padding:40px; border:#3D9A01 solid 1px; background:#EDFFCD;">' . $info . '</div>';
}

/**
 * 获取帖子的最后评论
 * @param $id   帖子ID
 * @param $key  需要输出的值
 */
function last_comment($id, $key)
{
    $last = M('comments')->field('uid,entrytime')->where(array('post_id' => $id))->order('id desc')->find();
    return $last[$key];
}

/**
 * 当前帖子的评论总数
 * @param $id  帖子ID
 */
function comment_nums($id)
{
    $count = M('comments')->where(array('post_id' => $id))->count();
    return $count;
}

/**
 * @param $id   公司ID
 * @return mixed 公司名称
 */
function comname($id)
{
    $name = M('company')->where(array('id' => $id))->getField('comname');
    return $name;
}

/**
 * @param $uid  用户ID
 * @return mixed  公司名称
 */
function club_comname($uid)
{
    $cid = M('users')->where(array('id' => $uid))->getField('cid');
    $name = M('company')->where(array('id' => $cid))->getField('comname');
    return $name;
}

/**
 * @param $key 需要输入的值
 * @return mixed 未读消息数量 和 未读消息列表 的数组
 */
function news($key)
{
    // 查询条件
    $where['viewid'] = session('uid');
    $where['status'] = 0;
    $where['fid'] = fid();
    // 未读消息数量
    $info['count'] = M('news')->where($where)->count();
    // 消息提醒
    $info['data'] = M('news')->where($where)->order('id desc')->select();

    return $info[$key];
}

/**
 * 将一个字符串部分字符用*替代隐藏
 * @param string $string 待转换的字符串
 * @param int $bengin 起始位置，从0开始计数，当$type=4时，表示左侧保留长度
 * @param int $len 需要转换成*的字符个数，当$type=4时，表示右侧保留长度
 * @param int $type 转换类型：0，从左向右隐藏；1，从右向左隐藏；2，从指定字符位置分割前由右向左隐藏；3，从指定字符位置分割后由左向右隐藏；4，保留首末指定字符串
 * @param string $glue 分割符
 * @return string   处理后的字符串
 */
function hideStr($string, $bengin = 1, $len = 1, $type = 4, $glue = "@")
{
    if (empty($string))
        return false;
    $array = array();
    if ($type == 0 || $type == 1 || $type == 4) {
        $strlen = $length = mb_strlen($string);
        while ($strlen) {
            $array[] = mb_substr($string, 0, 1, "utf8");
            $string = mb_substr($string, 1, $strlen, "utf8");
            $strlen = mb_strlen($string);
        }
    }
    if ($type == 0) {
        for ($i = $bengin; $i < ($bengin + $len); $i++) {
            if (isset($array[$i]))
                $array[$i] = "*";
        }
        $string = implode("", $array);
    } else if ($type == 1) {
        $array = array_reverse($array);
        for ($i = $bengin; $i < ($bengin + $len); $i++) {
            if (isset($array[$i]))
                $array[$i] = "*";
        }
        $string = implode("", array_reverse($array));
    } else if ($type == 2) {
        $array = explode($glue, $string);
        $array[0] = hideStr($array[0], $bengin, $len, 1);
        $string = implode($glue, $array);
    } else if ($type == 3) {
        $array = explode($glue, $string);
        $array[1] = hideStr($array[1], $bengin, $len, 0);
        $string = implode($glue, $array);
    } else if ($type == 4) {
        $left = $bengin;
        $right = $len;
        $tem = array();
        for ($i = 0; $i < ($length - $right); $i++) {
            if (isset($array[$i]))
                $tem[] = $i >= $left ? "*" : $array[$i];
        }
        $array = array_chunk(array_reverse($array), $right);
        $array = array_reverse($array[0]);
        for ($i = 0; $i < $right; $i++) {
            $tem[] = $array[$i];
        }
        $string = implode("", $tem);
    }
    return $string;
}

/**
 * @param $field 需要进行排序的字段
 * @param $get   GET得到的排序字段
 * @param $status 排序状态 1-降序 0-升序
 * @return string 排序class
 */
function sortCustomer($field, $get, $status)
{
    if ($field == $get) {
        if ($status) {
            return 'sorting_desc';
        } else {
            return 'sorting_asc';
        }
    } else {
        return 'sort';
    }
}

/**
 * 全局过滤输入的数据
 * @param $value
 */
function filter_default(&$value)
{
    $value = strip_tags(trim($value));
}

/**
 * 生成唯一订单号
 * @return string
 */
function createOrderno()
{
    return date('YmdHis') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
}

/**
 * 检查订单状态
 * @param $ordid
 * @return bool
 */
function checkorderstatus($ordid)
{
    $Ord = M('Orders');
    $ordstatus = $Ord->where('ordid=' . $ordid)->getField('ordstatus');
    if ($ordstatus == 1) {
        return true;
    } else {
        return false;
    }
}

/**
 * 充值成功后处理订单
 * @param $parameter
 */
function orderhandle($parameter)
{
    $ordid = $parameter['out_trade_no'];
    $data['payment_type'] = $parameter['payment_type'];
    $data['payment_trade_no'] = $parameter['trade_no'];
    $data['payment_trade_status'] = $parameter['trade_status'];
    $data['payment_notify_id'] = $parameter['notify_id'];
    $data['payment_notify_time'] = $parameter['notify_time'];
    $data['payment_buyer_email'] = $parameter['buyer_email'];
    $data['ordstatus'] = 1;
    $Ord = M('Orders');
    $Ord->where('ordid=' . $ordid)->save($data);
}

/**
 * 系统定时每月扣款
 *
 * 设定每月10日扣款
 * 每月10日0点, 判断用户数量(启用状态), 若大于等于设定免费用户数量,则扣除管理员 数量*单价/月 的金额
 * 1. 若统计金额为负数, 所有账号无法使用, 发送消息通知管理员, 员工登录时同样提示不能登录, 管理员可以登录
 * 2. 公司管理员充值后, 判断账户统计金额是否为负数, 是则提示并扣款, 每次扣款发消息通知
 * 3. 赠送账户不做扣款  flag 0-普通账户, 1-赠送账户
 * 4. 公司必须大于系统设定免费人数
 * @param int $aid 公司管理员id
 * @return bool
 */
function deduct_money($aid)
{
    $employee_num = M('Users')->where(array('pid' => $aid, 'status' => '1'))->count();
    $com_admin = M('Users')->where('id=' . $aid)->find();

    $balance = M('Orders')->where('userid=' . $aid)->sum('ordfee');
    $total_money = $employee_num * C('ONE_USER_PRICE_MONTH');

    if ($com_admin['flag'] === '0' && $employee_num > C('MAX_USER_NUMS') && $balance != null && $balance >= 0) {
        //扣款条件
        $map = array(
            'userid'    => $aid, //管理员ID
            'productid' => 2, //账户扣款
            'ordtime'   => array('LT', strtotime(date('Y-m'))) //当月是否有账户扣款记录
        );

        //若当月未扣款, 则扣款, 并保存交易记录和发送通知邮件
        if (!M('Orders')->where($map)->find()) {
            $datetime = date('Y-m-d H:i:s');
            $body = "您的账户于{$datetime}扣款{$total_money}元, 共{$employee_num}人";

            //扣款成功, 插入交易记录数据
            $order = M('Orders');
            $data = array(
                'userid'    => $aid,
                'ordid'     => createOrderno(),
                'ordtime'   => time(),
                'productid' => 2,
                'ordtitle'  => '系统扣款',
                'ordbuynum' => 1,
                'ordprice'  => $total_money,
                'ordfee'    => $total_money,
                'ordstatus' => 1,
                'ordbody'   => $body,
            );
            $ordid = $order->data($data)->add();

            //扣款成功, 发送邮件通知
            $email_title = '系统扣款';
            sendMail(array($com_admin['email']), $email_title, $body, 2);
        }
    }

}

/**
 * 工程管理-开通客户, 大于设定人数, 则扣费
 */
function deduct_add_platform()
{
    $customer_num = M('customer_platform')->where('admin_id=' . fid())->count();
    if ($customer_num >= C('MAX_PROJECT_USER_NUMS')) {
        $order = M('Orders');
        $order_data = array(
            'userid'    => fid(),
            'ordid'     => createOrderno(),
            'ordtime'   => time(),
            'productid' => 3,
            'ordtitle'  => '系统扣款',
            'ordbuynum' => 1,
            'ordprice'  => C('MAX_PROJECT_USER_PRICE'),
            'ordfee'    => -intval(C('MAX_PROJECT_USER_PRICE')), //I('post.ordprice'),
            'ordstatus' => 1,
            'ordbody'   => "开通工程管理用户",
        );
        $id = $order->data($order_data)->add();
    }
}

/**
 * 对所有公司进行扣款
 */
function exe_deduct_money()
{
    set_time_limit(0);
    $map['id'] = array('NOT IN', C('ADMINISTRATOR'));
    $map['pid'] = array('eq', 0);
    $admins = M('Users')->where($map)->select();
    foreach ($admins as $admin) {
        deduct_money($admin['id']);
    }
}

/**
 * 检测是否为手机客户端
 * @return bool
 */
function isMobile()
{
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])) {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

/**
 * 获取材料类型名称
 * @param $type_id
 * @return mixed
 */
function getMaterialTypeName($type_id)
{
    $info = M('material_type')->where(array('type_id' => $type_id))->getField('type_name');
    return $info;
}

/**
 * 获取材料类型下面的材料数量
 * @param $type_id
 * @return mixed
 */
function getMaterialTypeNum($type_id)
{
    $material = M('material');
    $info = $material->where(array('marterial_type' => $type_id))->count(marterial_id);
//    return $material->getLastSql();
    return $info;
}

function getMaterialName($material_id)
{
    $info = M('material')->where(array('marterial_id' => $material_id))->getField('marterial_name');
    return $info;
}

/**
 * 按照指定键值排血
 * @param $arr
 * @param $keys
 * @param string $type
 * @return array
 */
function array_sort($arr, $keys, $type = 'asc')
{
    $keysvalue = $new_array = array();
    foreach ($arr as $k => $v) {
        $keysvalue[$k] = $v[$keys];
    }
    if ($type == 'asc') {
        asort($keysvalue);
    } else {
        arsort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k => $v) {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}

/**
 * 获取套餐名称
 * @param $id
 * @return mixed
 */
function getPackageName($id)
{
    $info = M('package')->where(array('id' => $id))->getField('package_name');
    return $info;
}

/**
 * 获取风格名称
 * @param $id
 * @return mixed
 */
function getRoomStyle($id)
{
    $info = M('room_style')->where(array('id' => $id))->getField('style_name');
    return $info;
}

/**
 * 获取户型名称
 * @param $id
 * @return mixed
 */
function getHouseType($id)
{
    $info = M('house_type')->where(array('type_id' => $id))->getField('type_name');
    return $info;
}

/**
 * 获取房屋类型
 * @param $id
 * @return mixed
 */
function getRoomType($id)
{
    $info = M('room_type')->where(array('id' => $id))->getField('room_type_name');
    return $info;
}
