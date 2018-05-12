<?php

/**
 * Created by Yansor.
 * User: 468012316 <468012316@qq.com>
 * Date: 15-3-7
 * Time: 上午9:48
 * 充值\扣款
 */
class TradeAction extends CommonAction
{
    //在类初始化方法中，引入相关类库
    public function _initialize()
    {
        parent::_initialize();
        vendor('Alipay.lib.Corefunction');
        vendor('Alipay.Md5function');
        vendor('Alipay.Notify');
        vendor('Alipay.Submit');
    }

    /**
     * 给所有账户扣费
     */
    public function exe_deduct_money()
    {
        set_time_limit(0);
        $map['id'] = array('NOT IN', C('ADMINISTRATOR'));
        $map['pid'] = array('eq', 0);
        $admins = M('Users')->where($map)->select();
        foreach($admins as $admin){
            deduct_money($admin['id']);
        }
    }

    /**
     * 交易记录
     */
    public function lists()
    {
        $sum = M('Orders')->where('ordstatus=1 AND userid='.session('uid'))->sum('ordfee');

        import('ORG.Util.Page');
        $count  = D('Orders')->where('userid='.session('uid'))->count();  // 查询记录总数
        $page = new Page($count, 100);
        //$page->setConfig('header','个客户');     // 定制分页样式
        $get = array_filter($_GET);
        foreach($get as $key=>$val) {
            $page->parameter .= "$key=".urlencode($val)."&";
        }

        // 分页显示输出
        $show   = $page->show();
        $list = D('Orders')->where('userid='.session('uid'))->limit($page->firstRow, $page->listRows)->order("id DESC")->select();

        $this->assign('sum', $sum);
        $this->assign('count', $count);
        $this->assign('lists',$list);       // 赋值数据集
        $this->assign('page',$show);            // 赋值分页输出
        $this->display();
    }

    /**
     * 账户充值
     */
    public function recharge()
    {
        if (IS_POST) {
            $order = M('Orders');
            $data = array(
                'userid' => session('uid'),
                'ordid' => createOrderno(),
                'ordtime' => time(),
                'productid' => 1,
                'ordtitle' => '充值',
                'ordbuynum' => 1,
                'ordprice' => I('post.ordprice'),
                'ordfee' => I('post.ordprice'),
                'ordstatus' => 2,//暂时为'未支付'
                //'ordbody' => '充值成功',
                'show_url' => R('Trade/lists'),
            );
            $id = $order->data($data)->add();
            if($id)
                $this->doalipay($data);
        } else {
            $this->display();
        }
    }

    /**
     * 未支付的订单继续支付
     * @param $ordid
     */
    public function goOnAlipay(){
        // 判断get提交
        if (!IS_GET) {
            $this->error('您请求的页面不存在');
        }

        // 获取客户ID
        $ordid = I('get.ordid', 0, 'addslashes');
        $data = array(
            'ordid' => $ordid,
            'userid' => fid(),
        );
        // 更新数据
        if ($data = M('orders')->where($data)->find()) {
            $this->doalipay($data);
        } else {
            $this->error('您请求的页面不存在');
        }
    }

    /**
     * 作废订单
     */
    public function unsetOrder(){
        // 判断get提交
        if (!IS_GET) {
            $this->error('您请求的页面不存在');
        }

        // 获取客户ID
        $ordid = I('get.ordid', 0, 'addslashes');
        $data = array(
            'ordid' => $ordid,
            'userid' => fid(),
        );

        $order = M('orders');
        // 更新数据
        if ($data = $order->where($data)->find()) {
            // 要修改的数据对象属性赋值
            $data = array(
                'ordstatus' => 9,
            );
            $id = $order->where(array('userid'=>fid(), 'ordid'=>$ordid))->save($data);
            $id > 0 ? $this->success('已将该订单作废') : $this->error('操作失败');
        } else {
            $this->error('您请求的页面不存在');
        }
    }

    /**
     * alipay支付方法
     * @param $data
     * $data = array(
     * 'userid' => session('uid'),
     * 'ordid' => createOrderno(),
     * 'ordtime' => time(),
     * 'productid' => 1,
     * 'ordtitle' => '充值',
     * 'ordbuynum' => 1,
     * 'ordprice' => I('post.ordprice'),
     * 'ordfee' => I('post.ordprice'),
     * 'ordstatus' => 0,
     * );
     */
    public function doalipay($data)
    {
        /*********************************************************
         * 把alipayapi.php中复制过来的如下两段代码去掉，
         * 第一段是引入配置项，
         * 第二段是引入submit.class.php这个类。
         * 为什么要去掉？？
         * 第一，配置项的内容已经在项目的Config.php文件中进行了配置，我们只需用C函数进行调用即可；
         * 第二，这里调用的submit.class.php类库我们已经在PayAction的_initialize()中已经引入；所以这里不再需要；
         *****************************************************/
        // require_once("alipay.config.php");
        // require_once("lib/alipay_submit.class.php");

        //这里我们通过TP的C函数把配置项参数读出，赋给$alipay_config；
        $alipay_config = C('alipay_config');

        /**************************请求参数**************************/
        $payment_type = "1"; //支付类型 //必填，不能修改
        $notify_url = C('alipay.notify_url'); //服务器异步通知页面路径
        $return_url = C('alipay.return_url'); //页面跳转同步通知页面路径
        $seller_email = C('alipay.seller_email'); //卖家支付宝帐户必填

        $out_trade_no = $data['ordid']; //商户订单号 通过支付页面的表单进行传递，注意要唯一！
        $subject = $data['ordtitle']; //订单名称 //必填 通过支付页面的表单进行传递
        $total_fee = $data['ordfee']; //付款金额  //必填 通过支付页面的表单进行传递
        $body = $data['ordbody']; //订单描述 通过支付页面的表单进行传递
        $show_url = $data['show_url']; //商品展示地址 通过支付页面的表单进行传递

        $anti_phishing_key = ""; //防钓鱼时间戳 //若要使用请调用类文件submit中的query_timestamp函数
        $exter_invoke_ip = get_client_ip(); //客户端的IP地址
        /************************************************************/

        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "create_direct_pay_by_user",
            "partner" => trim($alipay_config['partner']),
            "payment_type" => $payment_type,
            "notify_url" => ($notify_url),
            "return_url" => ($return_url),
            "seller_email" => $seller_email,
            "out_trade_no" => $out_trade_no,
            "subject" => $subject,
            "total_fee" => $total_fee,
            "body" => $body,
            "show_url" => $show_url,
            "anti_phishing_key" => $anti_phishing_key,
            "exter_invoke_ip" => $exter_invoke_ip,
            "_input_charset" => trim(strtolower($alipay_config['input_charset']))
        );
        //建立请求
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, "post", "确认");
        echo $html_text;
    }

    /*
        页面跳转处理方法；
    */
    public function returnurl()
    {
        //头部的处理跟上面两个方法一样，这里不罗嗦了！
        $alipay_config = C('alipay_config');
        $alipayNotify = new AlipayNotify($alipay_config); //计算得出通知验证结果
        $verify_result = $alipayNotify->verifyReturn();

        if ($verify_result) {
            //验证成功
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
            $out_trade_no = $_GET['out_trade_no']; //商户订单号
            $trade_no = $_GET['trade_no']; //支付宝交易号
            $trade_status = $_GET['trade_status']; //交易状态
            $total_fee = $_GET['total_fee']; //交易金额
            $notify_id = $_GET['notify_id']; //通知校验ID。
            $notify_time = $_GET['notify_time']; //通知的发送时间。
            $buyer_email = $_GET['buyer_email']; //买家支付宝帐号；

            $parameter = array(
                "out_trade_no" => $out_trade_no, //商户订单编号；
                "trade_no" => $trade_no, //支付宝交易号；
                "total_fee" => $total_fee, //交易金额；
                "trade_status" => $trade_status, //交易状态
                "notify_id" => $notify_id, //通知校验ID。
                "notify_time" => $notify_time, //通知的发送时间。
                "buyer_email" => $buyer_email, //买家支付宝帐号
            );

            if ($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                if (!checkorderstatus($out_trade_no)) {
                    orderhandle($parameter); //进行订单处理，并传送从支付宝返回的参数；
                }
                $this->success('支付成功,正在跳转至列表页...', U(C('alipay.successpage')), 3);
            } else {
                echo "trade_status=" . $_GET['trade_status'];
                $this->error('支付失败,正在跳转至充值页...', U(C('alipay.errorpage')), 3);
            }
        } else {
            //验证失败
            $this->error('支付失败,正在跳转至充值页...', U(C('alipay.errorpage')), 3);
        }
    }

    /******************************
     * 服务器异步通知页面方法
     *******************************/
    public function notifyurl()
    {
        //这里还是通过C函数来读取配置项，赋值给$alipay_config
        $alipay_config = C('alipay_config');
        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if ($verify_result) {
            //验证成功
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            $out_trade_no = $_POST['out_trade_no']; //商户订单号
            $trade_no = $_POST['trade_no']; //支付宝交易号
            $trade_status = $_POST['trade_status']; //交易状态
            $total_fee = $_POST['total_fee']; //交易金额
            $notify_id = $_POST['notify_id']; //通知校验ID。
            $notify_time = $_POST['notify_time']; //通知的发送时间。格式为yyyy-MM-dd HH:mm:ss。
            $buyer_email = $_POST['buyer_email']; //买家支付宝帐号；
            $parameter = array(
                "out_trade_no" => $out_trade_no, //商户订单编号；
                "trade_no" => $trade_no, //支付宝交易号；
                "total_fee" => $total_fee, //交易金额；
                "trade_status" => $trade_status, //交易状态
                "notify_id" => $notify_id, //通知校验ID。
                "notify_time" => $notify_time, //通知的发送时间。
                "buyer_email" => $buyer_email, //买家支付宝帐号；
            );
            if ($_POST['trade_status'] == 'TRADE_FINISHED') {
                //
            } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                if (!checkorderstatus($out_trade_no)) {
                    orderhandle($parameter);
                    //进行订单处理，并传送从支付宝返回的参数；
                }
            }
            echo "success"; //请不要修改或删除
        } else {
            //验证失败
            echo "fail";
        }
    }


}