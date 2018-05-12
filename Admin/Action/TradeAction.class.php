<?php

class TradeAction extends CommonAction
{
    public function index()
    {
        if(IS_AJAX){
            $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
            $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 30;

            $realname = I('realname');
            $ordid = I('ordid');
            $productid = I('productid');
            $startdate = I('startdate');
            $enddate = I('enddate');

            $realname ? $map['realname'] = array('LIKE', array("%$realname%")) : '';
            $ordid ? $map['ordid'] = array('LIKE', array("%$ordid%")) : '';
            $productid ? $map['productid'] = array('EQ', $productid) : '';
            $startdate ? $map['ordtime'] = array('EGT', strtotime($startdate)) : '';
            $enddate ? $map['_string'] = ' ordtime <= '.strtotime($enddate)  : '';

            $count = M('Orders o')->join(C('DB_PREFIX').'users u ON u.id = o.userid')->where($map)->count();
            $data = M('Orders o')->join(C('DB_PREFIX').'users u ON u.id = o.userid')->where($map)->order('o.id DESC')->limit(($page-1)*$rows, $rows)->select();

            $rechargeWhere = $map;
            $rechargeWhere['ordfee'] = array('GT', 0);
            $rechargeWhere['ordstatus'] = array('EQ', 1);
            $rechargeSum = M('Orders o')->join(C('DB_PREFIX').'users u ON u.id = o.userid')->where($rechargeWhere)->sum('ordfee');
            $deductWhere = $map;
            $deductWhere['ordfee'] = array('LT', 0);
            $deductWhere['ordstatus'] = array('EQ', 1);
            $deductSum = M('Orders o')->join(C('DB_PREFIX').'users u ON u.id = o.userid')->where($deductWhere)->sum('ordfee');

            foreach($data as $key=> $value){
                $data[$key]['ordtime'] = date('Y-m-d H:i:s', $data[$key]['ordtime']);
                $data[$key]['productid'] = $data[$key]['productid'] === '1' ? '支付宝充值' : ($data[$key]['productid'] === '2' ? '系统扣款' : '');
                $data[$key]['ordstatus'] = $data[$key]['ordstatus'] ? '<font color="green">交易成功</font>' : '<font color="red">交易失败</font>';
            }

            $data = array(
                'total' => $count,
                'rows'=>$data === null ? array() : $data,
                'footer'=> array(
                    array('ordfee'=>$rechargeSum, 'ordtitle'=>'充值总额'),
                    array('ordfee'=>$deductSum, 'ordtitle'=>'系统扣款总额'),
                )
            );

            echo json_encode($data);
        }else{
            $this->display();
        }

    }

    public function addDiscount()
    {
        $userid = I('userid', '', 'intval');
        $discount = I('discount', '', 'floatval');
        $ordbody = I('ordbody');

        if($userid){
            $order = M('Orders');
            $username = M('Users')->where("id=$userid")->getField('username');
            $data = array(
                'userid' => $userid,
                'ordid' => createOrderno(),
                'ordtime' => time(),
                'productid' => 3,
                'ordtitle' => '优惠',
                'ordbuynum' => 1,
                'ordprice' => $discount, //I('post.ordprice'),
                'ordfee' => $discount, //I('post.ordprice'),
                'ordstatus' => 1,
                'ordbody' => "给$username 赠送$discount 的优惠",
            );
            $id = $order->data($data)->add();
        }else{
            $users = M('Users')->where('pid=0 AND id>8 AND status=1')->select();

            $this->assign('users', $users);
            $this->display();
        }
    }


}