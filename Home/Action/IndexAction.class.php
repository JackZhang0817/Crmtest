<?php
/**
 * Author: gaorenhua
 * Date: 2014-11-05
 * Email: 597170962@qq.com
 * 网站前端控制器
 */
class IndexAction extends Action {
    /**
     * 显示Index模块内容-导航nav
     */
    public function index(){
        if(isMobile()){
            redirect('http://m.zxicrm.com');
        }else
		    $this->display();
    }
}