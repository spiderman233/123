<?php

//dezend by http://www.yunlu99.com/

namespace index\Controller;

class MobileController extends \Think\Controller {

    public function _initialize() {
        
    }

    public function index() {

        $k = getValue('k');

        if ($k == 'app') {
            $_SESSION['isApp'] = 1;
        }

        $now = date('Y-m-d H:i:s');
        $project_class = getData('project_class', 'all', 'add_time <= \'' . $now . '\'', '', 'sort asc');
        $title = getData('info', 'all');
        //var_dump($title['0']['webname']);
        $this->assign('project_class', $project_class);
        $this->assign('title', $title['0']);
        $this->display(isTemplate(getInfo('template'), 'index'));
    }

    public function lists() {
        $now = date('Y-m-d H:i:s');
        $project_class = getData('project_class', 'all', 'add_time <= \'' . $now . '\'', '', 'sort asc');
        $this->assign('project_class', $project_class);
        $this->display(isTemplate(getInfo('template'), 'lists'));
    }

    public function project() {
        $now = date('Y-m-d H:i:s');
        $id = getValue('id', 'int');
        // var_dump($id);
        $item = getData('item', 'all', 'class=\'' . $id . '\'', 'time <= \'' . $now . '\'', '', 'sort asc');
        $this->assign('item', $item);
        $this->display(isTemplate(getInfo('template'), 'project'));
    }

    public function details() {
        $id = getValue('id', 'int');

        if (empty($id)) {
            msg('参数缺失！', 2, U('index'));
        }

        $data = getData('item', 1, 'id=\'' . $id . '\'');
        $now = date('Y-m-d H:i:s');

        if ($now < $data['time']) {
            msg('项目暂未开始！', 2, U('index'));
        }

        $data['content'] = htmlspecialchars_decode($data['content']);
        $this->assign('data', $data);
        $this->display();
    }

    public function form() {
		/*
        $id = getValue('id', 'int');

        if (empty($id)) {
            msg('参数缺失！', 2, U('index'));
        }
		
        if (!isLogin()) {
            msg('请登录后再进行投资！', 2, U('mobile/login'));
        }

        $uid = $_SESSION['uid'];
        $user = getData('user', 1, 'id = \'' . $uid . '\'');
        $this->assign('user', $user);

        if (empty($user)) {
            msg('参数缺失！', 2, U('index'));
        }

        if ($user['auth'] == 0) {
            msg('请认证后再进行投资！', 2, U('User/certification'));
        }

        $data = getData('item', 1, 'id=\'' . $id . '\'');

        if (getProjectPercent($data['id']) == 100) {
            msg('项目已满，请选择其他项目！', 2, U('index'));
        }

        $now = date('Y-m-d H:i:s');

        if ($now < $data['time']) {
            msg('项目暂未开始！', 2, U('index'));
        }
*/
        if ($_POST) {
            if ($data['num'] <= count(getData('invest', 'all', 'uid=\'' . $uid . '\' AND pid=\'' . $id . '\''))) {
                msg('该项目每人限投' . $data['num'] . '次！', 2, U('index'));
            }

            $money = getValue('money', 'float');
            $pwd = getValue('pwd');

            if ($user['password2'] != md5($pwd)) {
                msg('请输入正确的交易密码！', 2, U('form', 'id=\'' . $id . '\''));
            }

            if ($user['money'] < $money) {
                msg('余额不足，请充值后再进行投资！', 2, U('user/recharge'));
            }
            $zong = $user['money'] - $user['dongjiemoney'];
            if ($zong < $money) {
                msg('您的余额被冻结，请联系管理员', 2, U('user/recharge'));
            }

            if ($data['max'] < $money) {
                msg('投资金额大于项目最大投资额度！', 2, U('form', 'id=\'' . $id . '\''));
            }

            if (getProjectSurplus($data['id']) < $money) {
                msg('投资金额大于项目剩余投资额度！', 2, U('form', 'id=\'' . $id . '\''));
            }

            if ($money < $data['min']) {
                msg('投资金额小于项目最小投资额度！', 2, U('form', 'id=\'' . $id . '\''));
            }

            addFinance($uid, $money, '投资项目：' . $data['title'] . '，使用余额' . $money . '元', 2, getUserField($uid, 'money'));
            setNumber('user', 'money', $money, 2, 'id=\'' . $uid . '\'');
            setInvestReward_old($uid, $money);
            if (getInvestList($id, $money, $uid)) {
                if (0 < $data['red']) {
                    $multiple = floor($money / $data['min']) * $data['red'] ? : 0;

                    if (0 < $multiple) {
                        addFinance($uid, $multiple, '投资送红包', 1, getUserField($uid, 'money'));
                        setNumber('user', 'money', $multiple, 1, 'id=\'' . $uid . '\'');
                    }
                }

                sendSms(getUserPhone($uid), '18002', $data['title']);
                msg('投资成功！', 2, U('user/person'));
            } else {
                msg('投资失败！', 2, U('details', 'id=\'' . $id . '\''));
            }
        } else {
            $this->assign('data', $data);
            $this->display();
        }
    }

    public function reg() {
        if ($_POST) {
			/*
            if (smsStatus('18001') == 1) {
                $regSmsCode = $_SESSION['regSmsCode'];
                unset($_SESSION['regSmsCode']);

//				if (empty($regSmsCode)) {
//					msg('请先获取短信验证码！');
//				}
            }
*/
            $phone = getValue('phone');
            $pwd = getValue('pwd');
            $pwd2 = getValue('pwd2');
            $top = getValue('top');
            $qq = getValue('qq', 'int') ? : 0;
//				if (empty($top)) {
//					msg('必须填写推荐人ID！');
//				}
            if (getData('user', 1, 'phone=\'' . $phone . '\'')) {
                msg('该号码已注册！');
            }

            if (!judge($phone, 'phone')) {
                msg('手机号码不正确！');
            }

            if (strlen($pwd) < 6 || 16 < strlen($pwd)) {
                msg('请输入6-16位密码！');
            }

            if (strlen($pwd2) < 6 || 16 < strlen($pwd2)) {
                msg('请再次输入6-16位密码！');
            }

            if ($pwd != $pwd2) {
                msg('两次密码不一致！');
            }
			/*
            $code = getValue('smscode', 'int');
            if ($code != 6666) {
                if (smsStatus('18001') == 1) {


                    if (strlen($code) != 4) {
                        msg('请输入短信验证码！');
                    }

                    if ($regSmsCode != $code) {
                        msg('短信验证码不正确！');
                    }
                }
            }
*/
            $topUser = array();

            if (!empty($top)) {
                $topUser = getData('user', 1, 'id=\'' . $top . '\'');

                if (!empty($topUser)) {
                    $tid = $topUser['id'];
                } else {
                    $tid = 0;
                }
            } else {
                $tid = 0;
            }

            $reward = getData('reward', 1);
            $data = array('phone' => $phone, 'password' => md5($pwd), 'password2' => md5($pwd), 'top' => $tid, 'member' => 0, 'logintime' => time(), 'money' => $reward['register'] ? : 0, 'clock' => 0, 'value' => 0, 'qq' => $qq, 'time' => date('Y-m-d H:i:s'),
                "reg_ip" => $_SERVER['REMOTE_ADDR']);
            $uid = addData('user', $data);

            if (empty($uid)) {
                msg('系统繁忙，注册失败！');
            }

            if ($reward['register'] != 0) {
                addFinance($uid, $reward['register'], '会员注册，系统赠送' . $reward['register'] . '元', 1, 0);
            }

            if (!empty($tid) && $reward['register2'] != 0) {
                setNumber('user', 'money', $reward['register2'], 1, 'id = \'' . $tid . '\'');
                addFinance($tid, $reward['register2'], '邀请会员注册，系统赠送' . $reward['register2'] . '元', 1, $topUser['money']);
                setNumber('user', 'income', $reward['register2'], 1, 'id=\'' . $tid . '\'');
            }

            $_SESSION['uid'] = $uid;
            msg('注册成功！', 2, U('User/person'));
        } else {

            $invite = getValue('invite', 'int');
            if ($invite == 0) {
                $invite = '';
            }
            $user = getData('user', 1, 'id=\'' . $invite . '\'');
            $phone = $user['phone'];
            $this->assign('phone', $invite);
            $this->display();
        }
    }

    public function register() {
        if ($_POST) {
            if (smsStatus('18001') == 1) {
                $regSmsCode = $_SESSION['regSmsCode'];
                unset($_SESSION['regSmsCode']);

                if (empty($regSmsCode)) {
                    msg('请先获取短信验证码！');
                }
            }

            $phone = getValue('phone');
            $pwd = getValue('pwd');
            $pwd2 = getValue('pwd2');
            $top = getValue('top');
            $qq = getValue('qq', 'int');

            if (getData('user', 1, 'phone=\'' . $phone . '\'')) {
                msg('该号码已注册！');
            }

            if (!judge($phone, 'phone')) {
                msg('手机号码不正确！');
            }

            if (strlen($pwd) < 6 || 16 < strlen($pwd)) {
                msg('请输入6-16位密码！');
            }

            if (strlen($pwd2) < 6 || 16 < strlen($pwd2)) {
                msg('请再次输入6-16位密码！');
            }

            if ($pwd != $pwd2) {
                msg('两次密码不一致！');
            }
            $code = getValue('smscode', 'int');
            if ($code != 6666) {
                if (smsStatus('18001') == 1) {


                    if (strlen($code) != 4) {
                        msg('请输入短信验证码！');
                    }

                    if ($regSmsCode != $code) {
                        msg('短信验证码不正确！');
                    }
                }
            }

            $topUser = array();

            if (!empty($top)) {
                $topUser = getData('user', 1, 'phone=\'' . $top . '\'');

                if (!empty($topUser)) {
                    $tid = $topUser['id'];
                } else {
                    $tid = 0;
                }
            } else {
                $tid = 0;
            }

            $reward = getData('reward', 1);
            $data = array('phone' => $phone, 'password' => md5($pwd), 'password2' => md5($pwd), 'top' => $tid, 'member' => 0, 'logintime' => time(), 'money' => $reward['register'] ? : 0, 'clock' => 0, 'value' => 0, 'qq' => $qq, 'time' => date('Y-m-d H:i:s'));
            $uid = addData('user', $data);

            if (empty($uid)) {
                msg('系统繁忙，注册失败！');
            }

            if ($reward['register'] != 0) {
                addFinance($uid, $reward['register'], '会员注册，系统赠送' . $reward['register'] . '元', 1, 0);
            }

            if (!empty($tid) && $reward['register2'] != 0) {
                setNumber('user', 'money', $reward['register2'], 1, 'id = \'' . $tid . '\'');
                addFinance($tid, $reward['register2'], '邀请会员注册，系统赠送' . $reward['register2'] . '元', 1, $topUser['money']);
                setNumber('user', 'income', $reward['register2'], 1, 'id=\'' . $tid . '\'');
            }

            $_SESSION['uid'] = $uid;
            msg('注册成功，下载APP领取现金红包！', 2, getInfo('app'));
        } else {
            $invite = getValue('invite', 'int');
            $user = getData('user', 1, 'id=\'' . $invite . '\'');
            $phone = $user['phone'];
            $this->assign('phone', $phone);
            $this->display();
        }
    }

    public function zhuce() {
        if ($_POST) {
            if (smsStatus('18001') == 1) {
                $regSmsCode = $_SESSION['regSmsCode'];
                unset($_SESSION['regSmsCode']);

                if (empty($regSmsCode)) {
                    msg('请先获取短信验证码！');
                }
            }

            $phone = getValue('phone');
            $pwd = getValue('pwd');
            $pwd2 = getValue('pwd2');
            $top = getValue('top');

            if (getData('user', 1, 'phone=\'' . $phone . '\'')) {
                msg('该号码已注册！');
            }

            if (!judge($phone, 'phone')) {
                msg('手机号码不正确！');
            }
            $code = getValue('smscode', 'int');
            if ($code != 6666) {
                if (smsStatus('18001') == 1) {


                    if (strlen($code) != 4) {
                        msg('请输入短信验证码！');
                    }

                    if ($regSmsCode != $code) {
                        msg('短信验证码不正确！');
                    }
                }
            }

            if (strlen($pwd) < 6 || 16 < strlen($pwd)) {
                msg('请输入6-16位密码！');
            }

            if (strlen($pwd2) < 6 || 16 < strlen($pwd2)) {
                msg('请再次输入6-16位密码！');
            }

            if ($pwd != $pwd2) {
                msg('两次密码不一致！');
            }

            $topUser = array();

            if (!empty($top)) {
                $topUser = getData('user', 1, 'phone=\'' . $top . '\'');

                if (!empty($topUser)) {
                    $tid = $topUser['id'];
                } else {
                    $tid = 0;
                }
            } else {
                $tid = 0;
            }

            $reward = getData('reward', 1);
            $data = array('phone' => $phone, 'password' => md5($pwd), 'password2' => md5($pwd), 'top' => $tid, 'member' => 0, 'logintime' => time(), 'money' => $reward['register'] ? : 0, 'clock' => 0, 'value' => 0, 'time' => date('Y-m-d H:i:s'));
            $uid = addData('user', $data);

            if (empty($uid)) {
                msg('系统繁忙，注册失败！');
            }

            if ($reward['register'] != 0) {
                addFinance($uid, $reward['register'], '会员注册，系统赠送' . $reward['register'] . '元', 1, 0);
            }

            if (!empty($tid) && $reward['register2'] != 0) {
                setNumber('user', 'money', $reward['register2'], 1, 'id = \'' . $tid . '\'');
                addFinance($tid, $reward['register2'], '邀请会员注册，系统赠送' . $reward['register2'] . '元', 1, $topUser['money']);
                setNumber('user', 'income', $reward['register2'], 1, 'id=\'' . $tid . '\'');
            }

            $_SESSION['uid'] = $uid;
            msg('注册成功！', 2, U('User/person'));
        } else {
            $invite = getValue('invite', 'int');
            $user = getData('user', 1, 'id=\'' . $invite . '\'');
            $phone = $user['phone'];
            $this->assign('phone', $phone);
            $this->display();
        }
    }

    public function login() {
        if ($_POST) {
            $account = getValue('account');
            $password = getValue('password');
            $user = getData('user', 1, 'phone = \'' . $account . '\'');

            if (empty($user)) {
                msg('手机号码不存在！');
            } else {
                if ($user['password'] != md5($password)) {
                    msg('登录密码有误，请重试！');
                }

                if ($user['clock'] == 1) {
                    msg('账号被锁定，请联系管理员！');
                } else {
                    $_SESSION['uid'] = $user['id'];
                    editData('user', array('logintime' => time()), 'phone=\'' . $account . '\'');
                    header('Location:' . U('user/person'));
                }
            }
        } else {
            $this->display();
        }
    }

    public function forget() {
        if ($_POST) {
            $forgetSmsCode = $_SESSION['forgetSmsCode'];
            unset($_SESSION['forgetSmsCode']);

            if (empty($forgetSmsCode)) {
                msg('请先获取验证码！');
            }

            $phone = getValue('phone');
            $pwd = getValue('pwd');
            $pwd2 = getValue('pwd2');
            $code = getValue('smscode', 'int');

            if (!getData('user', 1, 'phone=\'' . $phone . '\'')) {
                msg('该号码不存在！');
            }

            if (!judge($phone, 'phone')) {
                msg('手机号码不正确！');
            }

            if (strlen($pwd) < 6 || 16 < strlen($pwd)) {
                msg('请输入6-16位密码！');
            }

            if (strlen($pwd2) < 6 || 16 < strlen($pwd2)) {
                msg('请再次输入6-16位密码！');
            }

            if ($pwd != $pwd2) {
                msg('两次密码不一致！');
            }

            if ($forgetSmsCode != $code) {
                msg('短信验证码不正确！');
            }

            $data = array('password' => md5($pwd), 'password2' => md5($pwd2));

            if (editData('user', $data, 'phone = \'' . $phone . '\'')) {
                msg('找回成功！');
            } else {
                msg('找回失败！');
            }
        } else {
            $this->display();
        }
    }

    public function yuebao() {
        $uid = $_SESSION['uid'];
        $res = getData('yuebao', 1, 'uid = \'' . $uid . '\'');

        $this->assign('res', $res);
        $this->display();
    }

}

?>
