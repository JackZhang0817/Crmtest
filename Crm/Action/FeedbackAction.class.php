<?php
/**
 * Created by gaorenhua.
 * User: 597170962 <597170962@qq.com>
 * Date: 2015/6/27
 * Time: 10:11
 */
class FeedbackAction extends CommonAction {
    /*
     * 客户投诉
     */
    public function complaints()
    {
        // 实例化模型
        $complaint = M('complaint');
        // 查询条件
        $where = array();
        $where['uid'] = session('uid');

        // 排序
        $order = 'status asc,id desc';

        // 分页
        parent::page($complaint, $where, $order);

        $this->display();
    }

    /**
     * 查看详情
     * @param string $table 需要查询的数据表
     * @return string 投诉详情
     */
    public function viewinfo($table='complaint')
    {
        $id = I('post.id', 0, 'intval');
        // 查询
        $info = M("$table")->where("id = $id")->find();

        // 投诉类型
        $class = array('1' => '材料问题', '2' => '设计问题', '3' => '施工问题', '4' => '服务态度',);

        // 格式化模版
        $template = '<table class="table table-bordered" style="margin-bottom:0px;">
                        <tbody>
                            <tr>
                                <td width="85">投诉时间：</td>
                                <td>'. date('Y-m-d H:i', $info['entrytime']) .'</td>
                                <td width="85">投诉类型：</td>
                                <td>'. $class[$info['com_class']] .'</td>
                            </tr>
                            <tr>
                                <td width="85">投诉业主：</td>
                                <td>'. $info['name'] .'</td>
                                <td width="85">联系方式：</td>
                                <td>'. $info['tel'] .'</td>
                            </tr>
                            <tr>
                                <td width="85">投诉问题：</td>
                                <td colspan="3">'. $info['question'] .'</td>
                            </tr>
                            <tr>
                                <td width="85">处理意见：</td>
                                <td colspan="3">'. $info['remark'] .'</td>
                            </tr>
                        </tbody>
                    </table><input type="hidden" name="id" value="'.$id.'"  />';

        echo $template;
    }

    /**
     * 投诉列表  标记为 已处理
     */
    public function complaint_handle()
    {
        $where = array();
        // 提取数据
        $where['id'] = I('post.id', 0, 'intval');

        // 更新状态
        $result = M('complaint')->where($where)->setField('status', '1');
        if($result) {
            $this->success('标记成功...');
        } else {
            $this->error('标记失败...');
        }
    }

    /**
     * 删除投诉
     */
    public function complaint_delete()
    {
        // id
        $id = I('get.id', 0, 'intval');

        // 删除
        $result = M('complaint')->delete($id);
        if ($result) {
            $this->success('删除成功...');
        } else {
            $this->error('删除失败...');
        }
    }

    /*
     * 客户报修
     */
    public function repair()
    {
        // 实例化模型
        $repair = M('repair');
        // 查询条件
        $where = array();
        $where['uid'] = session('uid');

        // 排序
        $order = 'status asc,id desc';

        // 分页
        parent::page($repair, $where, $order);

        $this->display();
    }

    /**
     * 查询报修详情
     * @param string $table
     */
    public function viewrepair($table = 'repair')
    {
        $id = I('post.id', 0, 'intval');
        // 查询
        $info = M("$table")->where("id = $id")->find();

        // 格式化模版
        $template = '<table class="table table-bordered" style="margin-bottom:0px;">
                        <tbody>
                            <tr>
                                <td width="85">投诉时间：</td>
                                <td colspan="3">'. date('Y-m-d H:i', $info['entrytime']) .'</td>
                            </tr>
                            <tr>
                                <td width="85">投诉业主：</td>
                                <td>'. $info['name'] .'</td>
                                <td width="85">联系方式：</td>
                                <td>'. $info['tel'] .'</td>
                            </tr>
                            <tr>
                                <td width="85">投诉问题：</td>
                                <td colspan="3">'. $info['question'] .'</td>
                            </tr>
                        </tbody>
                    </table><input type="hidden" name="id" value="'.$id.'"  />';

        echo $template;
    }

    /**
     * 我要报修 标志为已处理
     */
    public function repair_handle()
    {
        $where = array();
        // 提取数据
        $where['id'] = I('post.id', 0, 'intval');

        // 更新状态
        $result = M('repair')->where($where)->setField('status', '1');
        if($result) {
            $this->success('标记成功...');
        } else {
            $this->error('标记失败...');
        }
    }

    /**
     * 删除已处理的报修记录
     */
    public function repair_delete()
    {
        // id
        $id = I('get.id', 0, 'intval');

        // 删除
        $result = M('repair')->delete($id);
        if ($result) {
            $this->success('删除成功...');
        } else {
            $this->error('删除失败...');
        }
    }

    /**
     * 打卡记录
     */
    public function punchlist()
    {
        // 实例化模型
        $punch = M('punch');

        // 查询条件
        //$where = array();
        //$where['uid'] = session('uid');

        // 排序
        $order = 'id desc';

        // 分页
        parent::page($punch, $where, $order);
        $this->display();
    }

    /**
     * 员工绑定微信列表
     */
    public function wechat_user_list()
    {
        $where = array();

        $list = M('wechat_auth')->where('id>0')->order('id desc')->select();

        $this->assign('list', $list);
        $this->display();
    }
}