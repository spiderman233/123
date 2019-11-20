<?php
//dezend by http://www.yunlu99.com/
namespace index\Controller;

class AdminController extends \Think\Controller
{
	public function _initialize()
	{
		if (!strpos($_SERVER['REQUEST_URI'], 'login')) {
			if (empty($_SESSION['isAdminLogin']) || empty($_SESSION['isAdminUser'])) {
				header('Location:' . U('login'));
			}
		}
	}

	public function index()
	{
		$this->display();
	}

	public function project_list()
	{
		$where = sProjectList();
		$this->assign('where', $where);
		$Page = getPage('item', $where, 10);
		$this->assign('page', $Page->show());
		$this->assign('limit', $Page->firstRow . ',' . $Page->listRows);
		$this->display();
	}

	public function project_alreay()
	{
		$where = sProjectAlreay();
		$this->assign('where', $where);
		$Page = getPage('invest', $where, 10);
		$this->assign('page', $Page->show());
		$this->assign('limit', $Page->firstRow . ',' . $Page->listRows);
		$this->display();
	}

	public function project_return()
	{
		$where = sProjectReturn();
		$this->assign('where', $where);
		$Page = getPage('invest_list', $where, 10);
		$this->assign('page', $Page->show());
		$this->assign('limit', $Page->firstRow . ',' . $Page->listRows);
		$this->display();
	}

	public function project_add()
	{
		if ($_POST) {
			$data = array('title' => getValue('title'), 'desc' => getValue('desc'), 'total' => getValue('total'), 'rate' => getValue('rate'), 'percent' => getValue('percent'), 'day' => getValue('day'), 'type' => getValue('type'), 'min' => getValue('min'), 'max' => getValue('max'), 'num' => getValue('num'), 'guarantee' => getValue('guarantee'), 'time' => getValue('time'), 'content' => getContent('content'), 'sort' => getValue('sort', 'int'));
			$data['time'] = date('Y-m-d H:i:s', strtotime($data['time']));

			if (!empty($_FILES['img']['tmp_name'])) {
				$data['img'] = uploadImg($_FILES['img'], './Public/uploads/item/');
			}

			if (addData('item', $data)) {
				msg('添加成功！', 2, U('project_list'));
			}
			else {
				msg('添加失败！', 2, U('project_add'));
			}
		}
		else {
			$this->display();
		}
	}

	public function project_edit()
	{
		$id = getValue('id', 'int');

		if (empty($id)) {
			msg('参数缺失！', 2, U('project_list'));
		}

		if ($_POST) {
			$data = array('title' => getValue('title'), 'desc' => getValue('desc'), 'total' => getValue('total'), 'rate' => getValue('rate'), 'percent' => getValue('percent'), 'day' => getValue('day'), 'type' => getValue('type'), 'min' => getValue('min'), 'max' => getValue('max'), 'num' => getValue('num'), 'guarantee' => getValue('guarantee'), 'time' => getValue('time'), 'content' => getContent('content'), 'sort' => getValue('sort', 'int'));
			$data['time'] = date('Y-m-d H:i:s', strtotime($data['time']));

			if (!empty($_FILES['img']['tmp_name'])) {
				$data['img'] = uploadImg($_FILES['img'], './Public/uploads/item/');
			}

			if (editData('item', $data, 'id=\'' . $id . '\'')) {
				msg('修改成功！', 2, U('project_list'));
			}
			else {
				msg('修改失败！', 2, U('project_edit', 'id=' . $id));
			}
		}
		else {
			$data = getData('item', 1, 'id=\'' . $id . '\'');
			$data['content'] = htmlspecialchars_decode($data['content']);
			$this->assign('id', $id);
			$this->assign('data', $data);
			$this->display();
		}
	}

	public function project_detials()
	{
		$iid = getValue('iid', 'int');

		if (empty($iid)) {
			msg('参数缺失！', 2, U('project_alreay'));
		}

		$this->assign('iid', $iid);
		$this->display();
	}

	public function finance_payment()
	{
		$where = sFinancePayment();
		$this->assign('where', $where);
		$Page = getPage('recharge', $where, 10);
		$this->assign('page', $Page->show());
		$this->assign('limit', $Page->firstRow . ',' . $Page->listRows);

		if ($_POST) {
			$type = getValue('type');

			if ($type == 1) {
			}
			else {
				$id = getValue('id');
				$recharge = getData('recharge', 1, 'id=\'' . $id . '\'');

				if (empty($recharge)) {
					msg('参数缺失！');
				}
				else if ($recharge['status'] == '0') {
					$money = $recharge['money'];
					$uid = $recharge['uid'];
					$type = $recharge['type'];

					if (editData('recharge', array('status' => 1, 'time2' => date('Y-m-d H:i:s')), 'id = \'' . $id . '\'')) {
						addFinance($uid, $money, $type . '入款' . $money . '元', 1, getUserField($uid, 'money'));
						setNumber('user', 'money', $money, 1, 'id=\'' . $uid . '\'');
						$tid = getUserField($uid, 'top');
						setRechargeRebate($tid, $money);
						sendSms(getUserPhone($uid), '18005', $money);
						msg('入款成功！');
					}
					else {
						msg('入款失败！');
					}
				}
				else {
					msg('请勿重复入款！');
				}
			}
		}
		else {
			$reject = getValue('reject', 'int');

			if (!empty($reject)) {
				$recharge = getData('recharge', 1, 'id=\'' . $reject . '\'');
				$uid = $recharge['uid'];
				$money = $recharge['money'];
				$data = array('status' => 2, 'time2' => date('Y-m-d H:i:s'));
				sendSms(getUserPhone($uid), '18006', $money);

				if (editData('recharge', $data, 'id=\'' . $reject . '\'')) {
					msg('拒绝入款成功！', 2, U('finance_payment'));
				}
				else {
					msg('拒绝入款失败！', 2, U('finance_payment'));
				}
			}

			$warn = getValue('warn', 'int');

			if (!empty($warn)) {
				$data = array('warn' => 1);

				if (editData('recharge', $data, 'id=\'' . $warn . '\'')) {
					msg('已忽略提醒！', 2, U('finance_payment'));
				}
				else {
					msg('忽略提醒失败！', 2, U('finance_payment'));
				}
			}

			$this->display();
		}
	}

	public function finance_invoice()
	{
		$where = sFinanceInvoice();
		$this->assign('where', $where);
		$Page = getPage('cash', $where, 10);
		$this->assign('page', $Page->show());
		$this->assign('limit', $Page->firstRow . ',' . $Page->listRows);

		if ($_GET) {
			$id = getValue('id', 'int');
			$type = getValue('type', 'int');
			$cash = getData('cash', 1, 'id=\'' . $id . '\'');
			if ($cash['status'] == '1' || $cash['status'] == '2') {
				msg('请勿重复操作！', 2, U('finance_invoice'));
			}

			$uid = $cash['uid'];
			$money = $cash['money'];

			if ($type == 1) {
				$data = array('status' => 1, 'time2' => date('Y-m-d H:i:s'));

				if (editData('cash', $data, 'id=\'' . $id . '\'')) {
					sendSms(getUserPhone($uid), '18007', $cash['money']);
					msg('提现成功！', 2, U('finance_invoice'));
				}
				else {
					msg('提现失败！', 2, U('finance_invoice'));
				}
			}
			else {
				if ($type == 2) {
					$data = array('status' => 2, 'time2' => date('Y-m-d H:i:s'));
					sendSms(getUserPhone($uid), '18008', $money);
					$money = $cash['money'];
					addFinance($uid, $money, '提现失败，返还金额' . $money . '元', 1, getUserField($uid, 'money'));
					setNumber('user', 'money', $money, 1, 'id=\'' . $uid . '\'');

					if (editData('cash', $data, 'id=\'' . $id . '\'')) {
						msg('操作成功！', 2, U('finance_invoice'));
					}
					else {
						msg('操作失败！', 2, U('finance_invoice'));
					}
				}
			}

			$this->display();
		}
		else {
			$this->display();
		}
	}

	public function finance_stream()
	{
		$where = sFinanceStream();
		$this->assign('where', $where);
		$Page = getPage('finance', $where, 10);
		$this->assign('page', $Page->show());
		$this->assign('limit', $Page->firstRow . ',' . $Page->listRows);
		$this->display();
	}

	public function user_list()
	{
		$where = sUserList();
		$this->assign('where', $where);
		$Page = getPage('user', $where, 10);
		$this->assign('page', $Page->show());
		$this->assign('limit', $Page->firstRow . ',' . $Page->listRows);

		if (stristr(PHP_OS, 'win')) {
			$this->assign('isWin', 1);
		}
		else {
			$this->assign('isWin', 0);
		}

		$this->display();
	}

	public function user_analysis()
	{
		$invest = getData('invest', 'all', '', '', 'sum(money) desc', 'uid');
		$user = array();

		foreach ($invest as $i) {
			$uid = $i['uid'];
			$user[$uid]['id'] = $uid;
			$user[$uid]['phone'] = getUserField($uid, 'phone');
			$user[$uid]['top'] = getUserField(getUserField($uid, 'top'), 'phone');
			$user[$uid]['name'] = getUserField($uid, 'name');
			$mid = getUserField($uid, 'member');
			$member = getData('user_member', 1, 'id = \'' . $mid . '\'');
			$mname = $member['name'] ?: '普通会员';
			$user[$uid]['member'] = $mname;
			$user[$uid]['money'] = getUserField($uid, 'money');
			$user[$uid]['shop_num'] = getUserCashMoney($uid);
			$user[$uid]['getUserInvestMoney'] = getUserInvestMoney($uid);
			$user[$uid]['getUserUntreatedPrincipal'] = getUserUnPrincipal($uid);
			$user[$uid]['getUserUntreatedInterest'] = getUserUnIncome($uid);
			$user[$uid]['getUserInterest'] = getUserIncome($uid);
			$user[$uid]['time1'] = getUserField($uid, 'time');
			$user[$uid]['time2'] = date('Y-m-d H:i:s', getUserField($uid, 'logintime'));
			$uinv = getData('invest', 'all', 'uid=\'' . $uid . '\'', '', 'time desc');

			foreach ($uinv as $ui) {
				$user[$uid]['uindvest'][] = $ui['time'] . '<br/>使用' . $ui['money'] . '元投资“' . $ui['title'] . '”项目';
			}

			$user[$uid]['uindvest'] = implode('<hr/>', $user[$uid]['uindvest']);
		}

		$_SESSION['whereAnalysis'] = $user;
		header('Location:' . U('excelAnalysis'));
	}

	public function excelAnalysis()
	{
		$xlsName = 'analysis';
		$xlsCell = array(
			array('id', '用户ID'),
			array('phone', '手机号'),
			array('top', '推荐人'),
			array('name', '姓名'),
			array('member', 'vip等级'),
			array('money', '余额'),
			array('shop_num', '提现金额'),
			array('getUserInvestMoney', '投资金额'),
			array('getUserUntreatedPrincipal', '待收本金'),
			array('getUserUntreatedInterest', '待收利息'),
			array('getUserInterest', '已收利息'),
			array('time1', '注册时间'),
			array('time2', '最后登录'),
			array('uindvest', '投资的项目')
			);
		$xlsData = $_SESSION['whereAnalysis'];
		$xlsData2 = array();

		foreach ($xlsData as $data) {
			if (!empty($data['id'])) {
				$data['uindvest'] = str_replace('<br/>', '
', $data['uindvest']);
				$data['uindvest'] = str_replace('<hr/>', '

', $data['uindvest']);
				$xlsData2[] = $data;
			}
		}

		exportExcel('分析数据_' . date('YmdHis'), $xlsName, $xlsCell, $xlsData2);
	}

	public function user_update_status()
	{
		$id = getValue('id', 'int');
		$type = getValue('type', 'int');
		$re = getValue('re');
		if (empty($id) || empty($type)) {
			msg('参数缺失！', 2, U('user_list'));
		}

		$data['clock'] = $type - 1;

		if (editData('user', $data, 'id=\'' . $id . '\'')) {
			msg('修改成功！', 2, $re);
		}
		else {
			msg('修改失败！', 2, $re);
		}
	}

	public function user_add()
	{
		if ($_POST) {
			$top = getValue('top');
			$phone = getValue('phone');
			$pwd = getValue('pwd');
			$pwd2 = getValue('pwd2');
			$data = array('member' => 0, 'logintime' => 0, 'money' => 0, 'clock' => 0, 'value' => 0, 'time' => date('Y-m-d H:i:s'));

			if (0 < count(getData('user', 1, 'phone=\'' . $phone . '\''))) {
				msg('该手机号码已注册！');
			}

			if (!empty($top) && !judge($top, 'phone')) {
				msg('请输入正确的推荐人手机号码！');
			}

			if (!judge($phone, 'phone')) {
				msg('请输入正确的手机号码！');
			}

			if (strlen($pwd) != 0 && (strlen($pwd) < 6 || 16 < strlen($pwd))) {
				msg('请输入6-16个字符的登录密码！');
			}

			if (strlen($pwd2) != 0 && (strlen($pwd2) < 6 || 16 < strlen($pwd2))) {
				msg('请输入6-16个字符的交易密码！');
			}

			$data['phone'] = $phone;

			if (strlen($pwd) == 0) {
				$data['password'] = md5('123456');
			}
			else {
				$data['password'] = md5($pwd);
			}

			if (strlen($pwd2) == 0) {
				$data['password2'] = md5('123456');
			}
			else {
				$data['password2'] = md5($pwd2);
			}

			$data['top'] = getUserId($top) ?: 0;

			if (addData('user', $data)) {
				msg('添加成功！', 2, U('user_list'));
			}
			else {
				msg('添加失败！');
			}
		}
		else {
			$this->display();
		}
	}

	public function user_setmoney()
	{
		if ($_POST) {
			$phone = getValue('phone');
			$money = getValue('money', 'float');
			$type = getValue('type', 'int');
			$reason = getValue('reason');
			if (empty($phone) || empty($money) || empty($type) || empty($reason)) {
				msg('参数缺失！');
			}

			if (!judge($phone, 'phone')) {
				msg('手机号码格式不正确！');
			}

			if ($money <= 0) {
				msg('请输入大于0的金额！');
			}

			if (strlen($reason) < 6) {
				msg('请输入大于6个字符的理由！');
			}

			$user = getData('user', 1, 'phone=\'' . $phone . '\'');

			if (empty($user)) {
				msg('会员不存在！');
			}

			if ($type == 1) {
				addFinance(getUserId($phone), $money, $reason, 1, getUserField(getUserId($phone), 'money'));
				setNumber('user', 'money', $money, 1, 'phone=\'' . $phone . '\'');
				sendSms($phone, '18005', $money);
				msg('成功增加金额' . $money . '元！');
			}
			else {
				addFinance(getUserId($phone), $money, $reason, 2, getUserField(getUserId($phone), 'money'));
				setNumber('user', 'money', $money, 2, 'phone=\'' . $phone . '\'');
				msg('成功减少金额' . $money . '元！');
			}
		}
		else {
			$this->display();
		}
	}

	public function user_vip()
	{
		if ($_POST) {
			$type = getValue('type', 'int');

			if ($type == 1) {
				$name = getValue('name');
				$value = getValue('value');
				$rate = getValue('rate');
				$data = array('name' => $name, 'value' => $value, 'rate' => $rate);

				if (addData('user_member', $data)) {
					msg('添加成功！');
				}
				else {
					msg('添加失败！');
				}
			}
			else {
				$id = getValue('id', 'int');
				$name = getValue('name');
				$value = getValue('value');
				$rate = getValue('rate');
				$data = array('name' => $name, 'value' => $value, 'rate' => $rate);

				if (editData('user_member', $data, 'id=\'' . $id . '\'')) {
					msg('修改成功！');
				}
				else {
					msg('修改失败！');
				}
			}
		}
		else {
			$this->display();
		}
	}

	public function user_relation()
	{
		if ($_GET) {
			$where = sUserRelation();
			$this->assign('where', $where);
			$Page = getPage('user', $where, 10);
			$this->assign('page', $Page->show());
			$user = getData('user', 'all', $where, $Page->firstRow . ',' . $Page->listRows, 'id desc');
			$this->assign('user', $user);
		}
		else {
			$this->assign('user', array());
		}

		$this->display();
	}

	public function user_details()
	{
		$id = getValue('id', 'int');
		$user = getData('user', 1, 'id=\'' . $id . '\'');

		if (empty($id)) {
			msg('参数缺失！', 2, U('user_list'));
		}

		if ($_POST) {
			$data = array('phone' => getValue('phone'), 'name' => getValue('name'), 'idcard' => getValue('idcard'), 'value' => getValue('value'), 'clock' => getValue('clock'), 'qq' => getValue('qq', 'int') ?: 0);
			$pwd = getValue('pwd');
			$pwd2 = getValue('pwd2');

			if (!empty($pwd)) {
				$data['password'] = md5($pwd);
			}

			if (!empty($pwd2)) {
				$data['password2'] = md5($pwd2);
			}

			$topphone = getValue('top');

			if (!empty($topphone)) {
				$top = getData('user', 1, 'phone = \'' . $topphone . '\'');

				if (!empty($top)) {
					$data['top'] = $top['id'];
				}
			}

			$money = getValue('money');

			if ($user['money'] != $money) {
				if ($user['money'] < $money) {
					$newmoney = $money - $user['money'];
					$orderid = 'PAY' . time() . rand(100, 999);
					$data2 = array('orderid' => $orderid, 'uid' => $id, 'money' => $newmoney, 'type' => '系统充值', 'status' => 1, 'time' => date('Y-m-d H:i:s'), 'time2' => '0000-00-00 00:00:00');
					addData('recharge', $data2);
					addFinance($id, $newmoney, '系统充值增加会员余额' . $newmoney . '元', 1, $user['money']);
					setNumber('user', 'money', $newmoney, 1, 'id=\'' . $id . '\'');
					setRechargeRebate($id, $money);
				}

				if ($money < $user['money']) {
					$newmoney = $user['money'] - $money;
					addFinance($id, $newmoney, '系统减少会员余额' . $newmoney . '元', 2, $user['money']);
					setNumber('user', 'money', $newmoney, 2, 'id=\'' . $id . '\'');
				}
			}

			$value = getValue('value');
			setUserMember($id, $value);
			editData('user', $data, 'id=\'' . $id . '\'');
			msg('修改成功！', 2, U('user_details', 'id=' . $id));
		}
		else {
			$this->assign('user', $user);
			$this->display();
		}
	}

	public function article_type_add()
	{
		$name = getValue('name', 'str');
		$sort = getValue('sort', 'int');
		$show = getValue('show', 'int');
		$ico = getValue('ico');
		$data = array('name' => $name, 'sort' => $sort, 'show' => $show, 'ico' => $ico);

		if (addData('article_type', $data)) {
			msg('添加成功！', 2, U('article_type'));
		}
		else {
			msg('添加失败！', 2, U('article_type'));
		}
	}

	public function article_type_edit()
	{
		$id = getValue('id', 'int');

		if (empty($id)) {
			msg('参数缺失！', 2, U('article_type'));
		}

		$name = getValue('name', 'str');
		$sort = getValue('sort', 'int');
		$show = getValue('show', 'int');
		$ico = getValue('ico');
		$data = array('name' => $name, 'sort' => $sort, 'show' => $show, 'ico' => $ico);

		if (editData('article_type', $data, 'id=\'' . $id . '\'')) {
			msg('修改成功！', 2, U('article_type'));
		}
		else {
			msg('修改失败！', 2, U('article_type'));
		}
	}

	public function article_list()
	{
		$where = sArticleList();
		$this->assign('where', $where);
		$Page = getPage('article', $where, 10);
		$this->assign('page', $Page->show());
		$this->assign('limit', $Page->firstRow . ',' . $Page->listRows);
		$this->display();
	}

	public function article_add()
	{
		if ($_POST) {
			$title = getValue('title');
			$keyword = getValue('keyword');
			$type = getValue('type', 'int');
			$content = getContent('content');
			$show = getValue('show', 'int');

			if (empty($title)) {
				msg('请输入标题！');
			}

			if (empty($content)) {
				msg('请输入内容！');
			}

			$data = array('title' => $title, 'keyword' => $keyword, 'type' => $type, 'content' => $content, 'show' => $show, 'time' => date('Y-m-d H:i:s'));

			if (addData('article', $data)) {
				msg('添加成功！', 2, U('article_list'));
			}
			else {
				msg('添加失败！', 2, U('article_list'));
			}
		}
		else {
			$this->display();
		}
	}

	public function article_edit()
	{
		$id = getValue('id', 'int');

		if (empty($id)) {
			msg('参数缺失', 2, U('article_list'));
		}

		if ($_POST) {
			$title = getValue('title');
			$keyword = getValue('keyword');
			$type = getValue('type', 'int');
			$content = getContent('content');
			$show = getValue('show', 'int');

			if (empty($title)) {
				msg('请输入标题！');
			}

			if (empty($content)) {
				msg('请输入内容！');
			}

			$data = array('title' => $title, 'keyword' => $keyword, 'type' => $type, 'content' => $content, 'show' => $show, 'time' => date('Y-m-d H:i:s'));

			if (editData('article', $data, 'id=\'' . $id . '\'')) {
				msg('修改成功！', 2, U('article_list'));
			}
			else {
				msg('修改失败！', 2, U('article_list'));
			}
		}
		else {
			$data = getData('article', 1, 'id=\'' . $id . '\'');
			$data['content'] = htmlspecialchars_decode($data['content']);
			$this->assign('id', $id);
			$this->assign('data', $data);
			$this->display();
		}
	}

	public function system_info()
	{
		if ($_POST) {
			$data = array('webname' => getValue('webname'), 'company' => getValue('company'), 'tel' => getValue('tel'), 'address' => getValue('address'), 'notice' => getValue('notice'), 'service' => getValue('service') ?: '/mobile/kefu.html', 'app' => getValue('app') ?: '/mobile/app.html', 'icp' => getValue('icp'), 'wechat' => getValue('wechat'), 'qq' => getValue('qq'), 'cash' => getValue('cash'), 'ranking' => getValue('ranking'), 'contract' => getContent('contract'), 'jiesuan' => getValue('jiesuan', 'int'), 'web' => getValue('web', 'int'), 'template' => getValue('template'), 'video' => getValue('video') ?: '无', 'smsname' => getValue('smsname'), 'smskey' => getValue('smskey'), 'token' => getValue('token'));
			$jiesuan = getInfo('jiesuan');

			if (editData('info', $data)) {
				if ($data['jiesuan'] != $jiesuan && $data['jiesuan'] == 1) {
					vpost('http://' . $_SERVER['SERVER_NAME'] . '/handle/jiesuan', array());
				}

				msg('修改成功！');
			}
			else {
				msg('修改失败！');
			}
		}
		else {
			$this->display();
		}
	}

	public function system_reward()
	{
		if ($_POST) {
			$data = array('register' => getValue('register', 'float'), 'register2' => getValue('register2', 'float'), 'recharge' => getValue('recharge', 'float'), 'invest1' => getValue('invest1', 'float'), 'invest2' => getValue('invest2', 'float'), 'invest3' => getValue('invest3', 'float'), 'qiandao' => getValue('qiandao', 'float'));

			if (editData('reward', $data)) {
				msg('修改成功！');
			}
			else {
				msg('修改失败！');
			}
		}
		else {
			$this->display();
		}
	}

	public function system_contract()
	{
		if (!empty($_FILES['img']['tmp_name'])) {
			uploadImg($_FILES['img'], './Public/uploads/', 'contract');
			msg('修改成功！');
		}
		else {
			$this->display();
		}
	}

	public function system_img()
	{
		if ($_POST) {
			uploadImg($_FILES['logo'], './Public/uploads/', 'logo');
			uploadImg($_FILES['logo2'], './Public/uploads/', 'logo2');
			uploadImg($_FILES['wechat'], './Public/uploads/', 'wechat');
			uploadImg($_FILES['app'], './Public/uploads/', 'app');
			uploadImg($_FILES['mlogo2'], './Public/uploads/', 'mlogo2');
			msg('修改成功！');
		}
		else {
			$this->display();
		}
	}

	public function system_payment()
	{
		if ($_POST) {
			$type = getValue('type', 'int');

			if ($type == 1) {
				$data = array('bid' => getValue('bid'), 'appid' => getValue('appid'), 'appkey' => getValue('appkey'), 'domain' => getValue('domain'));
				editData('online', $data, '1');
				editData('info', array('online_wechat' => getValue('online_wechat')), '1');
				msg('修改成功！');
			}
			else {
				$data = array('pay_bank' => getValue('pay_bank'), 'pay_bank_type' => getValue('pay_bank_type'), 'pay_bank_name' => getValue('pay_bank_name'), 'pay_bank_account' => getValue('pay_bank_account'), 'pay_bank_status' => getValue('pay_bank_status'), 'qr_alipay' => getValue('qr_alipay'), 'qr_wechat' => getValue('qr_wechat'), 'qr_alipay_status' => getValue('qr_alipay_status'), 'qr_wechat_status' => getValue('qr_wechat_status'));

				if (!empty($_FILES['wechat']['tmp_name'])) {
					$data['qr_wechat_img'] = uploadImg($_FILES['wechat'], './Public/uploads/scan/');
				}

				if (!empty($_FILES['alipay']['tmp_name'])) {
					$data['qr_alipay_img'] = uploadImg($_FILES['alipay'], './Public/uploads/scan/');
				}

				editData('info', $data);
				msg('修改成功！');
			}
		}
		else {
			$online = getData('online', 1);
			$this->assign('online', $online);
			$this->display();
		}
	}

	public function system_banner()
	{
		$where = sSystemBanner();
		$this->assign('where', $where);
		$Page = getPage('slide', $where, 10);
		$this->assign('page', $Page->show());
		$this->assign('limit', $Page->firstRow . ',' . $Page->listRows);
		$this->display();
	}

	public function system_banner_add()
	{
		if ($_POST) {
			$data = array('url' => getValue('url'), 'sort' => getValue('sort', 'int'), 'show' => getValue('show', 'int'), 'type' => getValue('type', 'type'));

			if (!empty($_FILES['img']['tmp_name'])) {
				$data['path'] = uploadImg($_FILES['img'], './Public/uploads/slide/');
			}
			else {
				msg('请上传幻灯片图片！');
			}

			if (addData('slide', $data)) {
				msg('上传成功！', 2, U('system_banner'));
			}
			else {
				msg('上传失败！');
			}
		}
		else {
			$this->display();
		}
	}

	public function system_banner_edit()
	{
		$id = getValue('id', 'int');

		if (empty($id)) {
			msg('参数缺失！', 2, U('system_banner'));
		}

		if ($_POST) {
			$data = array('url' => getValue('url'), 'sort' => getValue('sort', 'int'), 'show' => getValue('show', 'int'), 'type' => getValue('type', 'type'));

			if (!empty($_FILES['img']['tmp_name'])) {
				$data['path'] = uploadImg($_FILES['img'], './Public/uploads/slide/');
			}

			if (editData('slide', $data, 'id=\'' . $id . '\'')) {
				msg('修改成功！', 2, U('system_banner'));
			}
			else {
				msg('修改失败！', 'id=\'' . $id . '\'');
			}
		}
		else {
			$slide = getData('slide', 1, 'id=\'' . $id . '\'');
			$this->assign('slide', $slide);
			$this->display();
		}
	}

	public function system_pwd()
	{
		if ($_POST) {
			$old = getValue('old');
			$pwd = getValue('pwd');
			$pwd2 = getValue('pwd2');
			$admin = getData('admin', 1);

			if (md5($old) != $admin['password']) {
				msg('原密码错误！');
			}

			if (strlen($pwd) < 6 || 16 < strlen($pwd)) {
				msg('请输入6-16个字符的新密码！');
			}

			if (strlen($pwd2) < 6 || 16 < strlen($pwd2)) {
				msg('请再次输入6-16个字符的新密码！');
			}

			if ($pwd != $pwd2) {
				msg('两次密码不一致！');
			}

			if (editData('admin', array('password' => md5($pwd)), '1')) {
				msg('修改成功！');
			}
			else {
				msg('修改失败！');
			}
		}
		else {
			$this->display();
		}
	}

	public function system_activity()
	{
		if ($_POST) {
			$data = array('activity_url' => getValue('activity_url'), 'activity_status' => getValue('activity_status', 'int'));

			if (!empty($_FILES['img']['tmp_name'])) {
				uploadImg($_FILES['img'], './Public/uploads/', 'activity');
			}

			editData('info', $data);
			msg('修改成功！', 2, U('system_activity'));
		}
		else {
			$this->display();
		}
	}

	public function seeNum()
	{
		$cash = count(getData('cash', 'all', 'status = 0'));
		$recharge = count(getData('recharge', 'all', 'status = 0 AND warn = 0'));
		$domain = $_SERVER['SERVER_NAME'];
		
		$rechargeState = getValue('rechargeState', 'int') ?: 0;

		if ($rechargeState == 0) {
			$recharge = 0;
		}

		$data = array();
		if (0 < $cash && 0 < $recharge) {
			$data['code'] = '001';
			$data['msg'] = '您有' . $cash . '条提现记录和' . $recharge . '条充值记录，请查看！';
		}
		else {
			if (0 < $cash && $recharge == 0) {
				$data['code'] = '002';
				$data['msg'] = '您有' . $cash . '条提现记录，请查看！';
			}
			else {
				if ($cash == 0 && 0 < $recharge) {
					$data['code'] = '003';
					$data['msg'] = '您有' . $recharge . '条充值记录，请查看！';
				}
				else {
					$data['code'] = '000';
					$data['msg'] = '';
					$data['url'] = '';
				}
			}
		}

		$data['url'] = 'https://c.jiangsuxy.com/Index/speech?txt=' . $data['msg'] . '&re=' . $domain . '&token=' . $token;
		$this->ajaxReturn($data);
	}

	public function delList()
	{
		$data = getValue('data');
		$list = getValue('list');
		$re = getValue('re');
		$id = explode(',', $list);
		$database = array('bank', 'cash', 'finance', 'info', 'invest', 'invest_list', 'reward', 'sms', 'user');

		if (in_array($data, $database)) {
			msg('非法操作！', 2, U('index'));
		}

		foreach ($id as $i) {
			delData($data, 'id=\'' . $i . '\'');
		}

		msg('删除成功！', 2, $re);
	}

	public function login()
	{
		if ($_POST) {
			if (time() < $_SESSION['adminLoginError']['end']) {
				msg('连续输错5次，请' . date('H:i', $_SESSION['adminLoginError']['end']) . '后重新登录！', 5, U('login'));
			}

			$user = getValue('user');
			$pwd = getValue('pwd');
			$code = getValue('code');

			if (empty($code)) {
				setError('adminLoginError');
				msg('请输入验证码！');
			}

			if ($_SESSION['adminRandCode'] != $code) {
				setError('adminLoginError');
				msg('验证码不正确！');
			}

			unset($_SESSION['adminRandCode']);
			$admin = getData('admin', 1, 'account = \'' . $user . '\'');

			if (empty($admin)) {
				setError('adminLoginError');
				msg('管理员账号不存在！');
			}

			if (md5($pwd) != $admin['password']) {
				setError('adminLoginError');
				msg('管理员密码不正确！');
			}
			else {
				unset($_SESSION['adminLoginError']);
				$_SESSION['isAdminLogin'] = 1;
				$_SESSION['isAdminUser'] = $user;
				header('Location:' . U('index'));
			}
		}
		else {
			$rand = rand(1000, 9999);
			$_SESSION['adminRandCode'] = $rand;
			$this->assign('rand', $rand);
			$this->display();
		}
	}

	public function loginRand()
	{
		$rand = rand(1000, 9999);
		$_SESSION['adminRandCode'] = $rand;
		$this->ajaxReturn($rand);
	}

	public function logout()
	{
		unset($_SESSION['isAdminLogin']);
		unset($_SESSION['isAdminUser']);
		msg('退出成功！', 2, U('login'));
	}
}

?>
