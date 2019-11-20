<?php
//dezend by http://www.sucaihuo.com/
namespace index\Controller;

class PrizeController extends \Think\Controller
{
	public function _initialize()
	{
		require_once './ThinkPHP/Common/reader.php';
		$isreader = IsReader();
		$isreader = json_decode($isreader, true);

		if ($isreader['ico'] != 'Txad14') {
			header('Location:/mobile');
		}
	}

	public function index()
	{
		$data = getData('prize', 1);
		$surplus = 100 - $data['odds1'] - $data['odds2'] - $data['odds3'] - $data['odds4'] - $data['odds5'];

		if (0 < $surplus) {
			$data['odds6'] = $surplus;
		}
		else {
			$data['odds6'] = 0;
		}

		$data['rule'] = str_replace('
', '<br/>', $data['rule']);
		$data['content'] = str_replace('
', '<br/>', $data['content']);
		$data['content'] = str_replace(',', ' * * * * * * ', $data['content']);
		$this->assign('data', $data);
		$time = array('Y' => date('Y', strtotime($data['endtime'])), 'm' => date('m', strtotime($data['endtime'])), 'd' => date('d', strtotime($data['endtime'])), 'H' => date('H', strtotime($data['endtime'])), 'i' => date('i', strtotime($data['endtime'])));
		$this->assign('time', $time);
		$uid = $_SESSION['uid'];
		$user = getData('user', 1, 'id = \'' . $uid . '\'');
		$count = $user['prize'] ?: 0;
		$this->assign('count', $count);
		$this->display();
	}

	public function start()
	{
		$item = getValue('item', 'int');

		if (empty($item)) {
			$data = array('code' => 0, 'msg' => '参数缺失，请刷新后重试！' . $item);
		}
		else if (!isLogin()) {
			$data = array('code' => 2, 'msg' => '请登录后再进行抽奖！');
		}
		else {
			$uid = $_SESSION['uid'];
			$user = getData('user', 1, 'id = \'' . $uid . '\'');
			$count = $user['prize'];

			if ($count <= 0) {
				$data = array('code' => 0, 'msg' => '抽奖次数不足，请投资后再进行抽奖！');
			}
			else {
				$prize = getData('prize', 1);
				$name = $prize['name' . $item] ?: '谢谢参与';
				$type = $prize['type' . $item] ?: '无';
				$reason = $prize['reason' . $item] ?: '继续投资，还有机会哟！';
				$money = $prize['money' . $item] ?: 0;

				if ($prize['endtime'] < date('Y-m-d H:i:s')) {
					$data = array('code' => 0, 'msg' => '活动已结束！');
				}
				else {
					$data = array('code' => 1, 'msg' => $reason);
					$data2 = array('uid' => $uid, 'item' => $item, 'name' => $name, 'type' => $type, 'money' => $money, 'time' => date('Y-m-d H:i:s'));
					addData('prize_list', $data2);

					if ($type == 1) {
						addFinance($uid, $money, '抽奖获得' . $money . '元现金红包', 1, getUserField($uid, 'money'));
						setNumber('user', 'money', $money, 1, 'id=\'' . $uid . '\'');
					}

					setNumber('user', 'prize', 1, 2, 'id=\'' . $uid . '\'');
				}
			}
		}

		$this->ajaxReturn($data);
	}

	public function lists()
	{
		$uid = $_SESSION['uid'];

		if (empty($uid)) {
			msg('请登录后再进行抽奖！', 2, U('Mobile/login'));
		}

		$list = getData('prize_list', 'all', 'uid = \'' . $uid . '\' AND type <> 0', '', 'id desc');
		$this->assign('prize', $list);
		$data = getData('prize', 1);
		$data['rule'] = str_replace('
', '<br/>', $data['rule']);
		$this->assign('data', $data);
		$this->display();
	}

	public function test()
	{
		$a = 10;
		$b = 3;
		echo round($a / $b);
	}
}

?>
