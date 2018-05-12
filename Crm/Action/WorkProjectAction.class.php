<?php
/**
 * Author: gaorenhua
 * Date: 2014-12-06
 * Email: 597170962@qq.com
 * 工程管理控制器
 */
Class WorkProjectAction extends CommonAction {
	/**
	 * 开工客户
	 */
	public function projectCustomer() {
		// 调用公共分页
		$this -> pageCommon();
		$this -> assign('empty', '<tr><td colspan="18" style="font-size:14px;height:60px;line-height:60px;color:#D64635;">暂无此状态客户.</td></tr>');
		$this -> display();
	}

	/**
	 * 已开通客户平台的客户
	 */
	public function openPaltform() {
		// 当前公司下所有开通平台的客户ID
		$where['admin_id'] = fid();
		$where['open'] = I('get.open', 0, 'intval');

		$cid = M('work_platform') -> where($where) -> order('customer_id asc') -> getField('customer_id', true);

		$w['id'] = array('IN', $cid);
		$this -> pageCommon($w);
		$this -> assign('empty', '<tr><td colspan="18" style="font-size:14px;height:60px;line-height:60px;color:#D64635;">暂无此状态客户.</td></tr>');
		$this -> display('projectCustomer');
	}

	/**
	 * 选择需要显示的状态客户
	 */
	public function chooseState() {
		if (IS_POST) {
			// 提取POST数据
			$state = implode(',', I('post.pstate'));

			$data = array('uid' => session('uid'), 'pstate' => $state);
			// 写入数据库
			if ( M('user_defined') -> save($data)) {
				$this -> success('保存成功');
			} else {
				$this -> error('保存失败');
			}
		} else {
			// 获取当前公司的所有客户状态
			$state = M('work_state') -> field('id,state_id') -> where(array('admin_id' => fid())) -> order('sort asc') -> select();

			// 获取当前用户选中的已选中的客户状态
			$stated = M('user_defined') -> where(array('uid' => session('uid'))) -> getField('wpstate');
			$stated = explode(',', $stated);

			// 循环替换状态名
			foreach ($state as $k => $v) {
				$state[$k]['state_id'] = stateName($v['state_id']);
			}

			$this -> assign('state', $state);
			$this -> assign('stated', $stated);
			$this -> display();
		}
	}

	/**
	 * 获取被点击状态下的所有客户信息
	 */
	public function getStateCustomer() {
		// 判断是否GET提交
		if (!IS_GET) {
			$this -> error('请求的页面不存在', U('projectCustomer'), 1);
		}

		// 获取当前状态下的状态ID
		$state = '\',' . $this -> _get('state') . ',\'';
		$map['_string'] = "POSITION($state IN CONCAT(',',State,','))";

		// 调用公共分页
		R('Customer/pageCommon', array($map));
		$this -> display('projectCustomer');
	}

	public function search() {
		// 判断提交方式
		if (!IS_GET) {
			$this -> error('您请求的页面不存在');
		}

        // 提取GET数据
        $state = I('get.State', 0, 'intval'); // 状态ID
        $Company = I('get.Company'); // 项目名称
        $Contact = I('get.Contact'); // 项目联系人
        $Tel = I('get.Tel'); // 联系方式
        $Userid = I('get.Userid', 0, 'intval');  // 业务员id
        $Designer = I('get.Designer', 0, 'intval');  // 设计师id
        $Project = I('get.Project', 0, 'intval');    // 工程监理id
        $hetongbianhao = I('get.hetongbianhao');    // 合同编号

		// 判断搜索条件
		if (!empty($Company)) {
			$map['Company'] = I('get.Company'); // 项目名称
		}
		if (!empty($Contact)) {
			$map['Contact'] = I('get.Contact'); // 项目名称
		}
		if (!empty($Tel)) {
			$map['Tel'] = I('get.Tel'); // 联系方式
		}
		if (!empty($Userid)) {
			$map['Userid'] = I('get.Userid', 0, 'intval'); // 业务员id
		}
		if (!empty($Designer)) {
			$map['Designer'] = I('get.Designer', 0, 'intval'); // 设计师id
		}
		if (!empty($Project)) {
			$map['Project'] = I('get.Project', 0, 'intval'); // 工程监理id
		}
        if (!empty($hetongbianhao)) {
            $map['hetongbianhao'] = I('get.hetongbianhao'); // 合同编号
        }

		// 调用公共分页
		R('Work/pageCommon', array($map));

		$this -> display('projectCustomer');
	}

	/**
	 * 开通客户平台
	 */
	public function addPlatform() {
		// 判断POST提交
		$opmode=I('opmode');
		
		if ($opmode=='add') {
			// 验证只能输入汉字和字符
			//$validate = array( array('cusname', '/^[a-zA-Z][\w]{4,16}$/', '用户名需以字母开头，5-17个字符 字母、数字、下划线_'), array('password', '/^[a-zA-Z][\w]{4,16}$/', '密码需以字母开头，5-17个字符 字母、数字、下划线_'), array('cusname', '', '该用户名已经存在!', 0, 'unique', 1), array('customer_id', '', '该客户已经开通平台!', 0, 'unique', 1), );
			//D('work_platform') -> setProperty("_validate", $validate);

			// 创建数据集
			//$data = D('work_platform') -> create();
			//if (!$data) {
			//	$this -> error( D('work_platform') -> getError());
			//}

			// 获取添加人信息
			$data['admin_id'] = fid();
			$data['uid'] = session('uid');
			$data['customer_id']=I('id');

			if ( D('work_platform') -> add($data)) {
				//deduct_add_platform();
				$this -> success('开通成功', U('selectPlatform', array('id' => $data['customer_id'])), 1);
			} else {
				$this -> error('开通失败', U('addPlatform', array('id' => $data['customer_id'])), 1);
			}

		} else {
			// 客户信息
			$info = D('WorkView') -> where(array('id' => I('get.id', 0, 'intval'))) -> find();
			
			// 查询条件
			$where['customer_id'] = $info['id'];

			// 判断是否开通客户平台   开通的话则返回平台状态 0-开启 1-关闭  没开通返回 2
			$status = M('work_platform') -> where($where) -> getField('open');
			$status = isset($status) ? $status : '2';

			// 当客户平台被禁用的时候不能查看
			if ($status == '1') {
				$this -> error('该客户平台已被禁用', U('projectCustomer'), 1);
			}

			// 获取客户平台的用户名
			$user = M('work_platform') -> field('cusname,password,project') -> where($where) -> find();

			// 获取所有二级施工工序
			$parr = unserialize($user['project']);
			//反序列化成数组
			$list = M('work_project') -> where(array('id' => array('IN', $parr))) -> order('sort asc') -> select();
			$list = node_merge($list);

			// 获取当前客户的所有施工详情
			$reslut = D('WorkPlatinfoView') -> where($where) -> order('sort desc') -> select();
			// 根据父级施工工序ID重组二维数组
			foreach ($reslut as $k => $v) {
				$infos[$v['pp']][] = $v;
			}

			//krsort($infos);  //对键key进行降序排序

			// 当客户开通平台的时候   判断是否存在该客户的施工信息
			if ($status == '0') {
				if (!$infos) {
					$this -> error('该客户还没有施工进度,请添加', U('addProjectInfo', array('id' => $info['id'])), 1);
				}
			}

			//获取客户咨询问题
			$comments = M('work_comment') -> where(array('customer_id' => $info['id'])) -> select();

			// 输出
			$this -> assign('list', $list);
			$this -> assign('cname', $user['cusname']);
			$this -> assign('pass', $user['password']);
			$this -> assign('infos', $infos);
			$this -> assign('info', $info);
			$this -> assign('status', $status);
			$this -> assign('comments', $comments);
			$this -> display();
		}
	}

	/**
	 * selectPlatform 针对各个客户选择不同的施工工序
	 * @return  工序列表
	 */
	public function selectPlatform() {
		// 判断POST提交
		if (IS_POST) {
			// 获取POST数据  客户ID  被选择的施工工序ID
			$where['customer_id'] = I('customer_id', 0, 'intval');
			$data['project'] = serialize(I('post.project'));
			//序列化

			// 判断客户是否开通客户平台
			$isopen = M('work_platform') -> where($where) -> getField('id');
			if (empty($isopen)) {
				$this -> error('您还没有开通客户平台，无法完成本次操作', U('projectCustomer', array('id' => $where['customer_id'])), 2);
			}
			
			// 保存已选择的施工工序
			if ( M('work_platform') -> where($where) -> save($data)) {
				$this -> success('添加成功', U('WorkProject/projectCustomer'), 1);
			} else {
				$this -> error('更新失败');
			}
		} else {
			// 列出所有施工工序
			$list = M('work_project') -> where(array('admin_id' => fid())) -> order('sort asc') -> select();

			// 递归重组规则信息为多维数组
			$list = node_merge($list);

			// 当前客户已选施工工序
			$pro = M('work_platform') -> where(array('customer_id' => I('get.id', 0, 'intval'))) -> getField('project');
			$project = unserialize($pro);

			$this -> assign('list', $list);
			$this -> assign('project', $project);
			$this -> display();
		}
	}

	/**
	 * 查看施工详情
	 */
	public function viewProjectInfo() {
		// 判断GET提交
		if (!IS_GET) {
			$this -> error('您请求的页面不存在');
		}

		// 获取详情ID
		$where['id'] = I('get.id', 0, 'intval');

		// 获取详情
		$info = D('WorkPlatinfoView') -> where($where) -> find();

		$this -> assign('info', $info);
		$this -> display();
	}

	/**
	 * 添加施工详情
	 */
	public function addProjectInfo() {
		// 判断POST提交
		if (IS_POST) {
			// 验证必填字段是否符合规则
			$validate = array( array('pid', 'number', '施工工序不能为空', 1), array('customer_id', 'number', '客户ID只能输入整数', 1), array('title', 'require', '标题不能为空'), array('content', 'require', '施工内容不能为空'));
			D('work_platinfo') -> setProperty("_validate", $validate);

			// 创建数据集
			$data = D('work_platinfo') -> create();

			if (!$data) {
				$this -> error( D('work_platinfo') -> getError());
			}

			// 提取图片的正则表达式
			$pattern = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
			preg_match_all($pattern, $data['content'], $match);

			// 导入Image类库
			import('ORG.Util.Image');
			$url = './Uploads/Project/thumb/';
			//定义图片的路径

			// 便利数组 循环生成缩略图
			$i = 0;
			foreach ($match['1'] as $key => $value) {
				if ($i == 5)
					break;
				//thumb方法生成包含路径的缩略图  substr截取出 图片的名称(包含后缀)
				$imgarr[] = substr(Image::thumb('.' . $value, $url . $_SERVER['REQUEST_TIME'] . rand(10000, 99999), '', '150', '120'), strlen($url));
				$i++;
			}

			// 序列化图片
			$data['thumb'] = serialize($imgarr);

			// 获取附加数据
			$data['uid'] = session('uid');
			$data['entrytime'] = $_SERVER['REQUEST_TIME'];

			//添加文章
			if ( D('work_platinfo') -> add($data)) {
				$this -> success('添加成功', U('addPlatform', array('id' => $data['customer_id'])), 1);
			} else {
				$this -> error('添加失败', U('addProjectInfo', array('id' => $data['customer_id'])), 1);
			}
		} else {
			// 获取当前客户的施工工序
			$project = M('work_platform') -> where(array('customer_id' => I('get.id', 0, 'intval'))) -> getField('project');
			$parr = unserialize($project);

			// 获取施工工序
			$list = M('work_project') -> where(array('id' => array('IN', $parr))) -> order('sort asc') -> select();
			$list = node_merge($list);

			$this -> assign('list', $list);
			$this -> display();
		}
	}

	/**
	 * 编辑施工详情
	 */
	public function editorProjectInfo() {
		// 判断POST提交
		if (IS_POST) {
			// 验证必填字段是否符合规则
			$validate = array( array('pid', 'number', '只能输入整数', 1), array('title', 'require', '标题不能为空'), array('content', 'require', '内容不能为空'));
			D('work_platinfo') -> setProperty("_validate", $validate);

			// 创建数据集
			$data = D('work_platinfo') -> create();
			if (!$data) {
				$this -> error( D('work_platinfo') -> getError());
			}

			// 获取缩略图处理并删除
			$thumb = M('work_platinfo') -> where(array('id' => $data['id'])) -> getField('thumb');
			$thumb_array = unserialize($thumb);

			// 遍历数组并删除
			foreach ($thumb_array as $value) {
				@unlink('./Uploads/Project/thumb/' . $value);
			}

			// 提取图片的正则表达式
			$pattern = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";
			preg_match_all($pattern, $data['content'], $match);

			// 导入Image类库
			import('ORG.Util.Image');
			$url = './Uploads/Project/thumb/';
			//定义图片的路径

			// 便利数组 循环生成缩略图
			$i = 0;
			foreach ($match['1'] as $key => $value) {
				if ($i == 5)
					break;
				//thumb方法生成包含路径的缩略图  substr截取出 图片的名称(包含后缀)
				$imgarr[] = substr(Image::thumb('.' . $value, $url . $_SERVER['REQUEST_TIME'] . rand(10000, 99999), '', '150', '120'), strlen($url));
				$i++;
			}

			// 序列化图片
			$data['thumb'] = serialize($imgarr);

			//添加文章
			if ( D('work_platinfo') -> save($data)) {
				$this -> success('添加成功', U('viewProjectInfo', array('id' => $data['id'])), 1);
			} else {
				$this -> error('添加失败', U('editorProjectInfo', array('id' => $data['id'])), 1);
			}
		} else {
			// 获取当前公司的施工工序
			$cid=$_GET['cid'];
			$project = M('work_platform') -> where(array('customer_id' => $cid)) -> getField('project');
			$parr = unserialize($project);
			
			// 查找
			$list = M('work_project') -> where(array('id' => array('IN', $parr))) -> order('sort asc') -> select();
			$list = node_merge($list);

			// 获取详情ID
			$where['id'] = I('get.id', 0, 'intval');

			// 获取详情
			$info = D('WorkPlatinfoView') -> where($where) -> find();

			$this -> assign('list', $list);
			$this -> assign('info', $info);

			$this -> display();
		}
	}

	/**
	 * 删除施工详情
	 */
	public function deleteProjectInfo() {
		// 判断GET提交
		if (!IS_GET) {
			$this -> error('您请求的页面不存在');
		}

		// 获取该条施工详情的ID
		$where['id'] = I('get.id', 0, 'intval');
		$where['customer_id'] = I('get.url', 0, 'intval');

		// 删除
		if ( M('work_platinfo') -> where($where) -> delete()) {
			$this -> success('删除成功', U('addPlatform', array('id' => $where['customer_id'])), 1);
		} else {
			$this -> error('删除失败', U('addPlatform', array('id' => $where['customer_id'])), 1);
		}
	}

	/**
	 * 查看施工进度
	 */
	public function viewProgress() {
		// GET提交
		if (!IS_GET) {
			$this -> error('您请求的页面不存在');
		}

		// 获取当前客户的id
		$where['customer_id'] = I('get.id', 0, 'intval');

		// 获取客户平台的用户名
		$user = M('work_platform') -> field('cusname,password,project') -> where($where) -> find();

		// 获取所有二级施工工序
		$parr = unserialize($user['project']);
		//反序列化成数组
		$list = M('work_platform') -> where(array('id' => array('IN', $parr))) -> select();

		// 获取当前客户的所有施工详情
		$reslut = D('WorkPlatinfoView') -> where($where) -> order('id desc') -> select();

		// 根据父级施工工序ID重组二维数组
		foreach ($reslut as $k => $v) {
			$info[$v['pp']][] = $v;
		}

		krsort($info);
		//对键key进行降序排序

		// 判断是否存在该客户的施工信息
		if (!$info) {
			$this -> error('您请求的信息不存在');
		}

		// 输出
		$this -> assign('list', $list);
		$this -> assign('cname', $user['cusname']);
		$this -> assign('pass', $user['password']);
		$this -> assign('info', $info);
		$this -> display();
	}

	/**
	 * 启用该客户的平台
	 */
	public function openPlatform() {
		$this -> open_close_Platform('0', '启用');
	}

	/**
	 * 关闭该客户的平台
	 */
	public function closePlatform() {
		$this -> open_close_Platform('1', '关闭');
	}

	/**
	 * @param $status  状态 0-开启 1-禁用
	 * @param $info    提示信息
	 */
	protected function open_close_Platform($status, $info) {
		//判断GET传值
		if (!IS_GET) {
			$this -> error('请求的页面不存在');
		}

		// 获取GET数据
		$where['customer_id'] = I('get.id', 0, 'intval');
		$data['open'] = $status;

		// 关闭
		if ( M('work_platform') -> where($where) -> save($data)) {
			$this -> success('' . $info . '成功');
		} else {
			$this -> error('' . $info . '失败');
		}
	}

	/**
	 * 公共分页
	 * @param array $where
	 */
	public function pageCommon($where = array()) {
		// 通用判断条件
		// $where['Project'] = array('GT', '0');
		$where['status'] = 0;

		// 获取职务
		$job = M('users') -> where(array('id' => session('uid'))) -> getField('job');

		// 判断管理员
		if (is_admin() || is_manager() || is_finance()) {
			// 判断总监  此处的1-2和组属性的1-2对应
			if ($job == '1' || $job == '2') {
				$user = array_column(each_group_users($job), 'id');
			} else {
				$map['pid'] = fid();
				$user = array_column( M('users') -> where($map) -> field('id') -> select(), 'id');
			}

			// 查询符合条件的客户信息
			$where['Userid'] = array('IN', $user);
		} elseif (is_salesman()) {
			// 判断经理
			if ($job == '3') {
				$where['Userid'] = array('IN', departusers());
			} else {//普通员工
				$where['Userid'] = session('uid');
			}
		} elseif (is_designer()) {
			// 判断经理
			if ($job == '3') {
				$map = array('Userid' => array('IN', departusers()), 'Designer' => array('IN', departusers()), '_logic' => 'or');
			} else {//普通员工
				$map = array('Userid' => session('uid'), 'Designer' => session('uid'), '_logic' => 'or');
			}
			// 并入通用判断条件
			$where['_complex'] = $map;
		} elseif (is_project()) {
			// 判断经理
			if ($job == '3') {
				$where['Project'] = array('IN', departusers());
			} else {
				$where['Project'] = session('uid');
			}
		} elseif (is_captain()) {
			// 判断经理
			if ($job == '3') {
				$where['Captain'] = array('IN', departusers());
			} else {
				$where['Captain'] = session('uid');
			}
		} elseif (is_drawing()) {
			// 判断经理
			if ($job == '3') {
				$where['Drawing'] = array('IN', departusers());
			} else {
				$where['Drawing'] = session('uid');
			}
		} elseif (is_material()) {
			// 判断经理
			if ($job == '3') {
				$where['Material'] = array('IN', departusers());
			} else {
				$where['Material'] = session('uid');
			}
		} else {
			// 调用通用判断条件
			// 判断总监  此处的1-2和组属性的1-2对应
			if ($job == '1' || $job == '2') {
				$user = array_column(each_group_users($job), 'id');
			} else {
				$map['pid'] = fid();
				$user = array_column( M('users') -> where($map) -> field('id') -> select(), 'id');
			}

			// 查询符合条件的客户信息
			$where['Userid'] = array('IN', $user);
		}

		// 获取当前用户选择的客户ID
		$state = M('user_defined') -> where(array('uid' => session('uid'))) -> getField('wpstate');
		$state = explode(',', $state);
		$state = implode('|', $state);
		if (!empty($state)) {
			if (isset($where['_string'])) {
				$where['_string'] = $where['_string'];
			} else {
				$where['_string'] = "`State` REGEXP '$state'";
			}
		}

		// 导入分页类
		import('ORG.Util.Page');
		$count = D('WorkView') -> where($where) -> count();
		// 查询记录总数
		$Page = new Page($count, 30);
		// 实例化分页类 传入总记录数
		$Page -> setConfig('header', '个客户');
		// 定制分页样式

		// 跟踪状态
		$state = M('work_state') -> field('id,state_id') -> where(array('admin_id' => fid())) -> order('sort') -> select();
		session('state', $state);
		
		// 自定义显示字段
		$fields = M('user_defined') -> field('work_project_field') -> where(array('uid' => session('uid'))) -> find();
		session('work_project_field', $fields);

		// 分页跳转的时候保证查询条件
		$get = array_filter($_GET);
		foreach ($get as $key => $val) {
			$Page -> parameter .= "$key=" . urlencode($val) . "&";
		}

		// 分页显示输出
		$show = $Page -> show();

		$list = D('WorkView') -> where($where) -> limit($Page -> firstRow . ',' . $Page -> listRows) -> order('id desc') -> select();
		// 没有客户信息的时候提示
		$empty = '<tr><td colspan="12" style="font-size:14px;height:60px;line-height:60px;color:#D64635;">暂无此状态客户.</td></tr>';

		// 接单设计师  设计组 2
		$saleman = each_group_users('1');

		// 接单设计师  设计组 2
		$designer = each_group_users('2');

		// 工程监理  工程组 5
		$projecter = each_group_users('5');

		// 施工队长 施工组 6
		$captioner = each_group_users('6');

		// 符合查询条件的总单值
		$sum1 = D('WorkView') -> where($where) -> Sum('OrdersValue');
		$dingjin = D('WorkView') -> where($where) -> Sum('Deposit');
		$space = D('WorkView') -> where($where) -> Sum('Space');
		$shejifei = D('WorkView') -> where($where) -> Sum('shejifei');
		$guanlifei = D('WorkView') -> where($where) -> Sum('guanlifei');
		$qingfu = D('WorkView') -> where($where) -> Sum('qingfu');
		$zhucai = D('WorkView') -> where($where) -> Sum('zhucai');
		$once = D('WorkView') -> where($where) -> Sum('once');
		$twice = D('WorkView') -> where($where) -> Sum('twice');
		$tirth = D('WorkView') -> where($where) -> Sum('tirth');
		$others = D('WorkView') -> where($where) -> Sum('others');

		$this -> assign('sum1', $sum1);
		$this -> assign('dingjin', $dingjin);
		$this -> assign('space', $space);
		$this -> assign('shejifei', $shejifei);
		$this -> assign('guanlifei', $guanlifei);
		$this -> assign('qingfu', $qingfu);
		$this -> assign('zhucai', $zhucai);
		$this -> assign('once', $once);
		$this -> assign('twice', $twice);
		$this -> assign('tirth', $tirth);
		$this -> assign('others', $others);
		$this -> assign('count', $count);
		$this -> assign('customer', $list);
		// 赋值数据集
		$this -> assign('page', $show);
		// 赋值分页输出
		$this -> assign('empty', $empty);
		// 赋值分页输出
		$this -> assign('users', $saleman);
		$this -> assign('designer', $designer);
		$this -> assign('project', $projecter);
		$this -> assign('caption', $captioner);
		//$this->attachedInfo();
	}

	/**
	 * 工程管理模块, 回答客户提交的问题
	 */
	public function comment() {
		if (IS_AJAX) {
			$customer_id = I('post.customer_id', '', 'intval');
			$data = array('post_id' => session('uid'), 'customer_id' => $customer_id, 'content' => I('post.content', '', 'htmlspecialchars'), 'createtime' => time(), 'create_time' => date('Y-m-d H:i:s', time()), 'customer_name' => M('customer') -> where(array('id' => $customer_id)) -> getField('CName'), );
			$result = M('work_comment') -> add($data);
			echo false !== $result ? json_encode($data) : '';
		}
	}

}
