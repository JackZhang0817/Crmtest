<?php
/**
  *  WeChat  API  Controller
  *  Author : gaorenhua
  *  Date : 2015-04-14
  *  Email : 597170962@qq.com
  */
// 定义微信令牌
define("TOKEN", "D3n6j5E24a7H5k4m8778aycp2b7g6");

class WeiChatAction extends Action
{
    /**
     * 公共入口
     */
    public function index()
    {
        // 验证签名是否有效
       // if($this->checkSignature()){
	        //$eee = $_GET['echostr'];
            // 调用用户信息处理函数
	        //echo $eee;
            $this->responseMsg();
       // }
    }

    /**
     * 自定义菜单响应事件处理函数
     */
    public function menuEvent($postObj='')
    {
        // 获取自定义菜单的key值
        $key = $postObj->EventKey;
        // 判断菜单的Key值  响应不同的事件
        switch ($key) {
            case 'myCustomer':
                $result = "检索我的客户信息";
                break;
            case 'repertProgress':
                $result = "报告客户装修进度";
                break;
            case 'chkComplaint':
                $result = "查询客户投诉";
                break;
            case 'chkRepair':
                $result = "查询客户报修";
                break;
            case 'chkStaff':
                $msg = "员工身份验证需要crm用户名和登录密码" . "\n\n" . "点击" .
                "\n" . "<a href='http://demo.zxicrm.com/crm.php/WeiChat/chkStaff/cid/0/openid/$postObj->FromUserName'>我的认证</a>";
                $result = $this->chkAuth(0, $postObj, $msg);
                break;
            case 'cusService':
                $result = "业主资讯我的客服";
                break;
            case 'myProgress':
                $result = "业主查询我的装修进度";
                break;
            case 'myComplaint':
                $result = "业主投诉";
                break;
            case 'myRepair':
                $result = "业主报修";
                break;
            case 'chkCustomer':
                $msg = "业主身份验证需要crm用户名和登录密码" . "\n\n" . "点击" .
        "\n" . "<a href='http://demo.zxicrm.com/crm.php/WeiChat/chkCustomer/cid/1/openid/$postObj->FromUserName'>我的认证</a>";
                $result = $this->chkAuth(1, $postObj, $msg);
                break;
            case 'contact':
                $result = "联系我们";
                break;
            default:
                $result = "虽然不知道你怎么点进来的,但是感觉你好屌啊.";
                break;
        }

        return $result;
    }

    /**
     * 员工身份绑定
     */
    public function chkStaff()
    {
        $this->chkLogin();
    }

    /**
     * 业主身份绑定
     */
    public function chkCustomer()
    {
        $this->chkLogin();
    }

    /**
     * 我的客户列表
     */
    public function customerList()
    {
        $this->display();
    }

    /**
     * 解除身份绑定
     */
    private function deleteAuth($openid)
    {
        $where['openid'] = "$openid";
        M('wechat_auth')->where($where)->delete();
    }

    /**
     * 通用身份验证函数
     * @param $cid  身份属性  0 - 员工   1 - 业主
     * @param $postObj  xml数据对象
     * @param $msg  提示信息
     * @return string
     */
    public function chkAuth($cid, $postObj, $msg)
    {
        // 判断用户是否已经通过验证, 避免重复操作
        $user = M('wechat_auth')->where(array('uid' => 8))->find();
        if ($user) {
            if ($user['cid'] == $cid) {
                $result = "您已通过验证" ;
            } else {
                if ($user['cid'] == 0) {
                    $result = "您已通过员工验证, 不能进行业主验证" ;
                } else {
                    $result = "您已通过业主验证, 不能进行员工验证" ;
                }

            }
        } else {
            $result =$msg;
        }

        echo json_encode($result);
    }

    /**
     * 通用身份绑定函数
     */
    private function chkLogin()
    {
        if (IS_POST) {
            // 提取POST数据
            $data['cid']    =   I('post.cid', 0, 'intval');     // 身份属性   0 - 员工   1 - 业主
            $data['openid']    =   I('post.openid');        // 公众号唯一标识符
            $data['username']    =   trim(I('post.username', '', 'htmlspecialchars'));      // crm平台用户名
            $password    =   trim(I('post.password', '', 'htmlspecialchars'));      // crm平台密码
            $data['createtime']    =   date('Y-m-d H:i:s', time());
            $data['status']    =   0;

            // 检查用户名是否已验证
            $result = M('wechat_auth')->where(array('username' => $data['username']))->find();
            if ($result && $result['openid'] != $data['openid']) {
                $this->assign('msg', "该账户已被验证,请核对后重新验证");
                $this->display('msg');
                exit();
            }

            // 根据身份属性 匹配员工或业主信息
            if ($data['cid'] == 0) {
                // 匹配员工基本信息
                $user = M('users')->field("id,password,auth,status")->where(array('username' => $data['username']))->find();
                $data['uid']   =   $user['id'];     // 匹配用户id
                $pass = $user['password'] == md5($password) ? true : false;

                // 检查用户帐户是否被禁用
                if ($user  && (!$user['auth'] || !$user['status'])) {
                    $this->assign('msg', "该账户被禁用或未授权,请联系公司管理员");
                    $this->display('msg');
                    exit();
                }
            } else {
                // 匹配业主基本信息
                $user = M('customer_platform')->field("customer_id,password,open")->where(array('cusname' => $data['username']))->find();
                $data['uid']   =   $user['customer_id'];     // 匹配用户id
                $pass = $user['password'] == $password ? true : false;

                // 检查业主账户是否被禁用
                if ($user && $user['open']) {
                    $this->assign('msg', "该账户被禁用,请联系公司管理员");
                    $this->display('msg');
                    exit();
                }
            }

            // 验证数据是否有效
            if ($user && $pass) {
                // 插入数据表 以便后期进行身份验证
                if (M('wechat_auth')->add($data)) {
                    $this->assign('msg', "身份验证成功");
                } else {
                    $this->assign('msg', "身份验证失败,请重新验证");
                }
            } else {
                $this->assign('msg', "抱歉,身份验证失败,用户名或密码错误");
            }

            $this->display('msg');
        } else {
            // 提取GET数据
            $cid    =   I('get.cid');       // 获取身份属性 0 - 员工   1 - 业主
            $openid    =   I('get.openid');         // 获取用户唯一标识符 即Openid

            // 判断用户是否已经通过验证, 避免重复操作
            $user = M('wechat_auth')->where(array('openid' => $openid))->find();
            if ($user) {
                if ($user['cid'] == $cid) {
                    $result = "您已通过验证" ;
                } else {
                    if ($user['cid'] == 0) {
                        $result = "您已通过员工验证, 不能进行业主验证" ;
                    } else {
                        $result = "您已通过业主验证, 不能进行员工验证" ;
                    }
                }

                $this->assign('msg', $result);
                $this->display('msg');
            } else {
                $this->assign('openid', I('get.openid'));
                $this->assign('cid', $cid);      // 身份属性 0 - 员工   1 -  业主
                $this->display('chkLogin');
            }
        }
    }

    /**
     * 创建菜单
     */
    public function createmenu()
    {
        $access_token = $this->token();
        $menu_url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $access_token;
        $data = '{"button":[{
            "name":"员工专区",
            "sub_button":[
                {
                    "type":"click",
                    "name":"我的客户",
                    "key":"myCustomer"
                },
                {
                    "type":"click",
                    "name":"报进度",
                    "key":"repertProgress"
                },
                {
                    "type":"click",
                    "name":"查投诉",
                    "key":"chkComplaint"
                },
                {
                    "type":"click",
                    "name":"查报修",
                    "key":"chkRepair"
                },
                {
                    "type":"click",
                    "name":"身份验证",
                    "key":"chkStaff"
                }
            ]
        },
        {
            "name":"业主专区",
            "sub_button":[
                {
                    "type":"click",
                    "name":"我的客服",
                    "key":"cusService"
                },
                {
                    "type":"click",
                    "name":"我的进度",
                    "key":"myProgress"
                },
                {
                    "type":"click",
                    "name":"我要投诉",
                    "key":"myComplaint"
                },
                {
                    "type":"click",
                    "name":"我要报修",
                    "key":"myRepair"
                },
                {
                    "type":"click",
                    "name":"身份验证",
                    "key":"chkCustomer"
                }
            ]
        },
        {
            "type":"click",
            "name":"联系我们",
            "key":"contact"
        }]
}';

        // 初始化一个curl回话
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $menu_url);   // 需要获取的url地址
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $info = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Errno'.curl_error($ch);
        }

        curl_close($ch);
        var_dump($info);

    }

    /**
     * 查询菜单
     */
    public function getmenu()
    {
        $access_token = $this->token();
        $menu_url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=" . $access_token;

        $cu = curl_init();
        curl_setopt($cu, CURLOPT_URL, $menu_url);
        curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
        $menu_json = curl_exec($cu);
        $menu = json_decode($menu_json);
        curl_close($cu);

        var_dump($menu_json);
    }

    /**
     * 删除菜单
     */
    public function deletemenu()
    {
        $access_token = $this->token();
        $menu_url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=" . $access_token;

        $cu = curl_init();
        curl_setopt($cu, CURLOPT_URL, $menu_url);
        curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
        $info = curl_exec($cu);
        $res = json_decode($info);
        curl_close($cu);

        if($res->errcode == "0"){
            echo "菜单删除成功";
        }else{
            echo "菜单删除失败";
        }
    }

    /**
     * 获取微信凭证
     */
    private function token()
    {
        $appid = "wx3d7ec2f609468ab5";      // 应用ID
        $appsecret = "7ac79c2d4d24b44882982b0217e127d7";        // 应用密钥

        $token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
        $json = file_get_contents($token_url);
        $result = json_decode($json, true);
        $access_token = $result['access_token'];

        return $access_token;
    }

    /**
     * 验证微信签名
     * @return bool
     * @throws Exception
     */
    private function checkSignature()
    {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}
