<?php
//   file_put_contents("aa.txt", date("Y-m-d H:i:s"));
namespace index\Controller;

use Think\Controller;

class ApiController extends Controller
{
public function index(){
	echo "dssssssssss";
	
}
    public function uploads_qrcode(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     512000 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->savePath = '/images/';
        $info=$upload->upload();
        $img_url = $info['file']['savepath'] . $info['file']['savename'];
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError() . "大小限制为500KB");
        }else{// 上传成功
            $this->success($img_url);
        }


    }
    public function get_setting_network(){
        $network_type = M("sys")->where(['sys_id'=>1])->getField('network');
        if($network_type == 1){
            $network = "4G蜂窝网络";
        }else if($network_type == 2){
            $network = "WiFi网络";
        }else{
            $network = "不限制";
        }
        $this->success($network);
    }

    /**
     * 原点 2018年11月6日11:43:12 获取公告
     */
    public function get_gonggao(){
        if (I("post.")) {
            $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
            $this->TestingNetWork($wangluo);
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {
                $this->error("登录过期！请重新登录！");
            } else {
                $info = M('said')->where(['s_view'=>1])->order('create_time desc')->select();
                foreach ($info as $k=>$v){
                    $info[$k]['create_time'] = date("Y/m/d",$v['create_time']);
                    $info[$k]['s_content'] = substr($v['s_content'],0,50);
                }
                $this->success($info);
            }
        } else {
            $this->error("提交方式不对！");
        }
    }

    public function get_gonggao_details(){
        if (I("post.")) {
            $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
            $this->TestingNetWork($wangluo);
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {
                $this->error("登录过期！请重新登录！");
            } else {
                $info = M('said')->where(['s_id'=>I("post.s_id")])->find();

                $info['create_time'] = date("Y/m/d",$info['create_time']);

                $this->success($info);
            }
        } else {
            $this->error("提交方式不对！");
        }
    }

    /**
     * 原点 2018年10月27日14:30:02 定时任务每月执行一次，发联合创始人的奖励
     */
    public function MonthlyAward(){
        $member = M('member_list')->where(['is_cf'=>1])->field('id,username,openid')->select();
        if($member){
            $sys = M('sys')->where(['sys_id'=>1])->field('one_cf_money,two_cf_money,there_cf_money')->find();
            foreach ($sys as $k=>$v){
                $one = M('relation_reco')->where(['userid'=>$v['id'],'cengshu'=>1])->count();
                if($one > 0){
                    $money = $one * $sys['one_cf_money'];
                    M('member_list')->where(['id'=>$v['id']])->setInc('zongmoney',$money);
                    M('member_list')->where(['id'=>$v['id']])->setInc('cfmoney',$money);
                    M('tuijian_log')->add([
                        'uid' => $v['id'],
                        'username' => $v['username'],
                        'ly_username' => "联合创始人一级推荐分润",
                        'money' => $money,
                        'time' => time(),
                        'msg' => "联合创始人一级推荐分润",
                        'cengshu' => 1
                    ]);
                }
                $two = M('relation_reco')->where(['userid'=>$v['id'],'cengshu'=>2])->count();
                if($two > 0){
                    $two_money = $two * $sys['two_cf_money'];
                    M('member_list')->where(['id'=>$v['id']])->setInc('zongmoney',$two_money);
                    M('member_list')->where(['id'=>$v['id']])->setInc('cfmoney',$two_money);
                    M('tuijian_log')->add([
                        'uid' => $v['id'],
                        'username' => $v['username'],
                        'ly_username' => "联合创始人二级推荐分润",
                        'money' => $two_money,
                        'time' => time(),
                        'msg' => "联合创始人二级推荐分润",
                        'cengshu' => 2
                    ]);
                }
                $there = M('relation_reco')->where(['userid'=>$v['id'],'cengshu'=>3])->count();
                if($there > 0){
                    $there_money = $there * $sys['there_cf_money'];
                    M('member_list')->where(['id'=>$v['id']])->setInc('zongmoney',$there_money);
                    M('member_list')->where(['id'=>$v['id']])->setInc('cfmoney',$there_money);
                    M('tuijian_log')->add([
                        'uid' => $v['id'],
                        'username' => $v['username'],
                        'ly_username' => "联合创始人三级推荐分润",
                        'money' => $there_money,
                        'time' => time(),
                        'msg' => "联合创始人三级推荐分润",
                        'cengshu' => 2
                    ]);
                }
            }

        }
    }


    /**
     * 原点 2018年11月9日15:52:55  获取静态广告
     */
    public function get_static(){
        if (I("post.")) {
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
            $this->TestingNetWork($wangluo);
            if (empty($member)) {

                $this->error("登录过期！请重新登录！");
            } else {
                $guanggao = M("article_static")->field("a_id,ip,fjh")->where("state=1")->limit(1)->order('rand()')->find();
                $this->success($guanggao);
            }

        } else {

            $this->error("提交方式不正确！");
        }
    }

    /**
     * 原点 2018年11月10日11:22:24 浏览静态广告增加
     */
    public function static_jiesuan(){
        if (I("post.")) {
            $openid = I('openid', '', 'htmlspecialchars');
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {
                $this->error("登录过期！请重新登录！");
            } else {
                //总的金额
                $sum_money =  M('ggll_list')->where(['uid'=>$member['id']])->sum('money');
                if($sum_money && $sum_money > 0){
                    //如果会员一生获得的总金额大于后台设定的金额，则无法获得更多奖励
                    if($member['max_fr'] > 0 &&  $sum_money >= $member['max_fr']){
                        $this->error("您已获得最大金额，无法在获得更多奖励");
                    }
                }
                $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
                $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
                $day_map['addtime'] =  array('between',array($beginToday,$endToday),'AND');
                $day_map['uid'] = array('eq',$member['id']);
                //抓取今日赚的金额
                $day_money = M('ggll_list')->where($day_map)->sum('money');
                if($day_money && $day_money > 0){
                    //如果会员单独设置的每日可获得金额大于0，说明该会员单独设置每日可获得金额
                    if($member['day_max_fr'] > 0 ){
                        //如果今日的金额>=会员单独设置的每日可获得金额，将无法获得更多奖励
                        if($day_money >= $member['day_max_fr']){
                            $this->error("您今日已获得最大金额，无法在获得更多奖励");
                        }
                    }else{
                        //否则，统一追寻后台设定的每日可获得金额，
                        $llgg_day = M('sys')->where(['sys_id'=>1])->getField('llgg_day');
                        //如果后台设置的金额 > 0并且会员今日获得的金额>=后台设置的金额将无法获得更多奖励
                        if($llgg_day > 0 && $day_money >= $llgg_day){
                            $this->error("您今日已获得最大金额，无法在获得更多奖励");
                        }
                    }

                }
                $ggid = trim(I('post.ggid', '', 'htmlspecialchars'));

                $guanggao = M("article_static")->where("a_id=" . $ggid)->find();
                $money = $guanggao["money"];
                $data = array();
                $data['uid'] = $member['id'];
                $data['username'] = $member['username'];
                $data['money'] = $money;
                $data['ggid'] = $ggid;
                $data['type'] = 2;
                $data['ip'] = $guanggao['ip'];
                $data['fjh'] = $guanggao['fjh'];
                $data['addtime'] = time();
                M("ggll_list")->add($data);
                M("MemberList")->where(array("openid" => $openid))->setInc("static_money", $money);
                M("MemberList")->where(array("openid" => $openid))->setInc("zongmoney", $money);
                $this->PromotionCommission($member['id'],$money);
                $this->success($money);
            }

        } else {

            $this->error("提交方式不正确！");
        }
    }


    public function get_profit(){
        if (I("post.")) {
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $type = trim(I('post.type', '', 'htmlspecialchars'));
            $page = trim(I('post.page', '', 'htmlspecialchars'))?trim(I('post.page', '', 'htmlspecialchars')):1;
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {
                $this->error("登录过期！请重新登录！");
            }
            if(!$type){
                $this->error("加载失败！");
            }
            $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
            $this->TestingNetWork($wangluo);

            $limits = 100;
            $list = array();
            switch ($type){
                case 1:
                    $list = M('ggll_list')->where(['uid'=>$member['id']])->page($page, $limits)->field('money,addtime')->order('addtime desc')->select();
                    foreach ($list as $k=>$v){
                        $list[$k]['addtime'] = date('Y/m/d H:i:s',$v['addtime']);
                    }
                    break;
                case 2:
                    $list = M('tuijian_log')->where(['uid'=>$member['id']])->page($page, $limits)->order('time desc')->select();
                    foreach ($list as $k=>$v){
                        $list[$k]['time'] = date('Y/m/d H:i:s',$v['time']);
                    }
                    break;
                case 3:
                    $list = M('tuiguang_log')->where(['uid'=>$member['id']])->page($page, $limits)->order('time desc')->select();
                    foreach ($list as $k=>$v){
                        $list[$k]['time'] = date('Y/m/d H:i:s',$v['time']);
                        $list[$k]['ly_username'] = M('member_list')->where(['id'=>$v['ly_uid']])->getField('username');
                    }
                    break;
            }

            $this->success($list);

        } else {
            $this->error("提交方式不对！");
        }
    }


    /**
     * 原点 2018年10月26日13:59:36 获取我的团队
     */
    public function get_team(){
        if (I("post.")) {
            $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
            $this->TestingNetWork($wangluo);
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $level = trim(I('post.level', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {
                $this->error("登录过期！请重新登录！");
            }
            if(!$level){
                $this->error("加载失败！");
            }
            $team_list = M('relation_reco')->where(['usernumber'=>$openid,'cengshu'=>$level])->getField('newuserid',true);
            $list = array();
            foreach ($team_list as $k=>$v){
                $member_v = M('MemberList')->where(['id'=>$v])->find();
                $list[$k]['username'] = $member_v['username'];
                $list[$k]['state'] = $member_v['state'];
            }
            $data['list'] = $list;
            $data['one'] = M('relation_reco')->where(['userid'=>$member['id'],'cengshu'=>1])->count();
            $data['two'] =M('relation_reco')->where(['userid'=>$member['id'],'cengshu'=>2])->count();
            $data['there'] =M('relation_reco')->where(['userid'=>$member['id'],'cengshu'=>3])->count();
                $this->success($data);

        } else {
            $this->error("提交方式不对！");
        }
    }


    /**
     * 原点 2018年10月26日09:48:38  获取我的提现记录
     */
    public function getTxLog(){
        if (I("post.")) {
            $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
            $this->TestingNetWork($wangluo);
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {
                $this->error("登录过期！请重新登录！");
            } else {
                $info = M('tx_log')->where(['openid'=>$openid])->order('time desc')->select();
                foreach ($info as $k=>$v){
                    $info[$k]['time'] = date("Y/m/d",$v['time']);
                    $info[$k]['money'] =$v['money'] - $v['sxf'];
                }
                $this->success($info);
            }
        } else {
            $this->error("提交方式不对！");
        }
    }

    /**
     * 原点 2018年10月26日09:48:14 获取 今日收益和推荐
     */
    public function get_info(){
        if (I("post.")) {

            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();
            $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
            if (empty($member)) {
                $this->error("登录过期！请重新登录！");
            }
            $this->TestingNetWork($wangluo);
            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
            $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
            $map['addtime']  = array('between',''.$beginToday.','.$endToday.'');
            $map['uid'] = array('eq',$member['id']);
            $member['day_money'] = M('ggll_list')->where($map)->sum('money');
            if(!$member['day_money']){
                $member['day_money'] = 0;
            }
            $map_1['create_time']  = array('between',''.$beginToday.','.$endToday.'');
            $map_1['uuid'] = array('neq',"");
            $map_1['tj_openid'] = array('eq',$member['openid']);
            $member['tj_num'] = M("MemberList")->where($map_1)->count();
            $this->success($member);

        } else {
            $this->error("提交方式不对！");
        }
    }


    /** ------------提现申请start------------*/
    /** 原点 2018年10月26日09:05:04  提现申请*/
    public function apply_tx(){
        if (I("post.")) {
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $money = trim(I('post.money', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
            $img_qrcode =  trim(I('post.img_qrcode', '', 'htmlspecialchars'));
            $this->TestingNetWork($wangluo);
            if (empty($member)) {
                $this->error("登录过期！请重新登录！");
            } else {

                $tx_mode = I('post.tx_mode');
                if(!$tx_mode){
                    $this->error("请选择提现方式！");
                }
                if($money <= 0){
                    $this->error("提现金额错误！");
                }

                $shouxufei = M('sys')->where(['sys_id'=>1])->getField('shouxufei');
                //判断总金额
                $sxf_money = $money * $shouxufei;
                $sum_money = $money + $sxf_money;
                if($sum_money > $member['zongmoney']){
                    $this->error("余额不足！");
                }

                if($tx_mode == "zfb"){
                    if(!$img_qrcode){
                        $this->error("请上传收款码1");
                    }
                    $tx_info['zfbname'] = I("post.zfbname");
                    $tx_info['zfbzhanghao'] = I("post.zfbzhanghao");
                    $tx_info['qr_code'] = $img_qrcode;
                }else if($tx_mode == "wx"){
                    if(!$img_qrcode){
                        $this->error("请上传收款码");
                    }
                    $tx_info['wxzhanghao'] = I("post.wxzhanghao");
                    $tx_info['wxname'] = I("post.wxname");
                    $tx_info['qr_code'] = $img_qrcode;
                }else{
                    $tx_info['yhkzhanghao'] = I("post.yhkzhanghao");
                    $tx_info['kaihuhang'] = I("post.kaihuhang");
                    $tx_info['yhkname'] = I("post.yhkname");
                }
                $data = array();
                $data['tx_mode'] = $tx_mode;
                $data['tx_info'] = serialize($tx_info);
                $data['openid'] = $openid;
                $data['user_id'] = $member['id'];
                $data['user_name'] = $member['username'];
                $data['money'] = $sum_money;
                $data['sxf'] = $sxf_money;
                $data['time'] = time();
                M('tx_log')->add($data);
                M('MemberList')->where(['openid'=>$openid])->setDec('zongmoney',$sum_money);
                $this->success("已提交申请，请耐心等待审核！");
            }
        } else {
            $this->error("提交方式不对！");
        }
    }
    /** ------------提现申请end------------*/
    /**
     * @return string
     * 原点 2018年10月26日08:48:52 生成唯一的 openid
     */
    public function generateNum() {
        //strtoupper转换成全大写的
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $uuid = substr($charid, 0, 8).substr($charid, 8, 4).substr($charid,12, 4).substr($charid,16, 4).substr($charid,20,12);
        return $uuid;
    }

    /**
     * 原点 2018年10月26日08:48:30 网页版注册会员接口
     */
    public function registered(){
        if(IS_POST){
            $param = I('post.');

            $tj_openid = M('MemberList')->where(['openid'=>$param['tj_openid']])->getField('id');
            if(!$tj_openid){
                $this->error("推荐人不存在");
            }
            if($param['code'] != session('code')){
                $this->error("验证码错误");
            }


            $param['openid'] = $this->generateNum();
            $param['create_time']=time();

            $res = M('MemberList')->add($param);
            session('code',null);
            if($res){
                $this->tuijiancalculate($res);

                $this->success("注册成功");
            }else{
                $this->error("注册失败");
            }

        }else{
            $openid = I('openid');
            $member = M('MemberList')->where(['openid'=>$openid])->getField('username');
            $this->assign('member',$member);
            $this->assign('openid',$openid);
            $this->display();
        }
    }

    /**
     * 原点 2018年11月6日14:19:32 修改手机号
     */
    public function save_phone(){
        if (I("post.")) {
            $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
            $this->TestingNetWork($wangluo);
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {
                $this->error("登录过期！请重新登录！");
            } else {
                $data['phone'] = I("post.phone");
                $data['code'] = I("post.code");
                $flag = M("member_list")->where(['phone'=>$data['phone']])->find();
                if($flag){
                    $this->error("该手机号已被绑定！");
                }
                if($data['code'] != session('code')){
                    $this->error("验证码错误！");
                }

                $res = M("member_list")->where(['id'=>$member['id']])->save($data);
                if($res){
                    session("code",null);
                    $this->success("已更改");
                }else{
                    $this->error("修改失败！");
                }

            }
        } else {
            $this->error("提交方式不对！");
        }
    }

    /**
     * 原点 2018年11月6日14:19:40  发送验证码
     */
    public function send_yzm(){
        $phone = I("post.phone");
        $res = sedMsg($phone);

        if($res == 0){
            $this->success("已发送至手机");
        }else{
            $this->error("发送失败");
        }
    }


    /**
     * @param $member_id - 新会员ID
     * 原点 2018年10月26日08:48:17 记录推荐关系
     */
    public function tuijiancalculate($member_id)
    {
        $admin = M('Member_list');
        $huiyuan = $admin->where(array('id' => $member_id))->find();
        $cengcount = 1;
        $a = 0;
        if ($huiyuan['tj_openid']) {
            $fujiuserid = $huiyuan['tj_openid'];
            // $shichang=$huiyuan['shichang'];
            while ($fujihuiyuan = $admin->where(array('openid' => $fujiuserid))->find()) {
//                dump($fujiuserid);die;
                $a++;
                if($a == 10){
                    break;
                }
                if ($fujihuiyuan) {
                    $data['userid'] = $fujihuiyuan['id'];
                    $data['usernumber'] = $fujihuiyuan['openid'];
                    $data['status'] = 0;
                    $data['create_time'] = time();
                    $data['newuserid'] = $member_id;
                    $data['cengshu'] = $cengcount;

                    M('relation_reco')->add($data);
                    $fujiuserid = $fujihuiyuan['tj_openid'];
//                    dump($fujiuserid);
                }
                $cengcount++;
            }
        }
    }


    /**
     * 原点 2018年10月26日08:53:36 生成推荐二维码
     */
    public function get_qr_code()
    {
        if (I("post.")) {
            $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
            $this->TestingNetWork($wangluo);
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {

                $this->error("登录过期！请重新登录！");
            } else {

                $url = "http://" . $_SERVER['SERVER_NAME'] . "/Home/Api/registered?openid=" . $openid;
                Vendor('Phpqrcode.phpqrcode');
                // 纠错级别：L、M、Q、H
                $level = 'L';
                // 点的大小：1到10,用于手机端4就可以了
                $size = 4;
                // 下面注释了把二维码图片保存到本地的代码,如果要保存图片,用$fileName替换第二个参数false
                $path = "Uploads/qr_code/";
                // 生成的文件名
                $fileName = $path.$openid.'.png';
                \QRcode::png($url, $fileName, $level, $size);
                $this->success("http://" . $_SERVER['SERVER_NAME'] ."/" .$fileName);
            }
        } else {
            $this->error("提交方式不对！");
        }
    }

    /**
     * 原点 2018年11月6日10:47:24 获取我的资料
     */
    public function get_user_info(){
        if (I("post.")) {
            $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
            $this->TestingNetWork($wangluo);
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {
                $this->error("登录过期！请重新登录！");
            } else {
                $this->success($member);
            }
        } else {
            $this->error("提交方式不对！");
        }
    }
    /**
     * 原点 2018年11月6日10:47:24 修改我的资料
     */
    public function save_user_info(){
        if (I("post.")) {
            $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
            $this->TestingNetWork($wangluo);
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {
                $this->error("登录过期！请重新登录2！");
            } else {
                $param = I("post.");
                unset($param['wangluo']);
                unset($param['openid']);
                $res = M('member_list')->where(['id'=>$member['id']])->save($param);
                if($res){
                    $this->success("修改成功");
                }else{
                    $this->error("提交方式不对！");
                }
            }
        } else {
            $this->error("提交方式不对！");
        }
    }

    public function TestingNetWork($wangluo){
        $setting_network = M("sys")->where(['sys_id'=>1])->getField('network');
        if($setting_network == 1){
            $network = "4G蜂窝网络";
        }else if($setting_network == 2){
            $network = "WiFi网络";
        }else{
            $network = "不限制";
        }

        if ($network != "不限制" && $wangluo != $network) {
            $this->error("请使用".$network."！");
        }
    }

    public function login()
    {
        if (I("post.")) {
            $username = trim(I('post.username', '', 'htmlspecialchars'));
            $password = trim(I('post.password', '', 'htmlspecialchars'));
            $uuid = trim(I('post.uuid', '', 'htmlspecialchars'));
            $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
            $this->TestingNetWork($wangluo);
            $map = array("username" => $username, "password" => $password);

            $member = M("MemberList")->where($map)->find();
            if (empty($member)) {

                $this->error("用户不存在或密码不正确！");
            }

            if ($member['state'] == 0) {

                $this->error("用户过期或关闭，请联系管理员！");
            }
            $uuidarr = explode(",", $uuid);
            $uuidtype = 0;
            if ($member['uuid'] == $uuid) {
                $uuidtype = 1;
            }
            foreach ($uuidarr as $v) {
                if ($v == $member['uuid']) {
                    $uuidtype = 1;
                }

            }
//            if ($uuidtype != 1) {
//                $this->error("设备不匹配");
//            } else {
                M("MemberList")->where($map)->save(array("logingqtime" => time() + 86400));

                $sys = M("Sys")->find(1);
                $this->success(array("openid" => $member['openid'], "jgtime" => $sys['loginddtime']));
//            }
        } else {

            $this->error("提交方式不对！");
        }


    }

    /**
     * @param $member_id
     * @param $member_time
     * @param $member_level
     * 原点 2018年11月15日13:19:13 检测是否升级
     */
    public function test_level($member_id,$member_time,$member_level){
        $now_time = time();
        $level = ($now_time - $member_time)/86400;
        $now_level = M('member_level')->where('level_id >'.$member_level.'')->select();
        if($now_level){
            foreach ($now_level as $k=>$v){
                //如果注册时间>=下一等级所需要的天数，证明可以升级
                if($level >= $v['level_time']){
                    M("member_list")->where(['id'=>$member_id])->save(['level_id'=>$v['level_id']]);
                }
            }
        }
    }

    public function token($uid){
        return md5($uid.time());
    }

    public function log_out(){
        if (I("post.")) {
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            M('member_list')->where(['openid'=>$openid])->save(['online'=>0]);
            $this->success(1);
        } else {
            $this->error("提交方式不对！");
        }
    }


    public function wanshanxinxi()
    {
        if (I("post.")) {
            $username = trim(I('post.username', '', 'htmlspecialchars'));
            $password = trim(I('post.password', '', 'htmlspecialchars'));
            $uuid = trim(I('post.uuid', '', 'htmlspecialchars'));
            $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
            $this->TestingNetWork($wangluo);
            $map = array("username" => $username, "password" => $password);

            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {

                $this->error("用户不存在或密码不正确！");
            }

            if ($member['state'] == 0 || $member['birthdate'] + 86400 < time()) {

                $this->error("用户过期或关闭，请联系管理员！");
            }

            $uuidarr = explode(",", $uuid);
            $uuidtype = 0;

            if ($member['uuid'] == $uuid) {

                $uuidtype = 1;
            }

            foreach ($uuidarr as $v) {
                if ($v == $member['uuid']) {
                    $uuidtype = 1;
                }

            }


            if ($uuidtype != 1) {

                $this->error("设备不匹配！");
            } else {

                $data = array();
                $data['phone'] = trim(I('post.phone', '', 'htmlspecialchars'));
                $data['qqhaoma'] = trim(I('post.qqhaoma', '', 'htmlspecialchars'));
                $data['email'] = trim(I('post.email', '', 'htmlspecialchars'));
                $data['zfbname'] = trim(I('post.zfbname', '', 'htmlspecialchars'));
                $data['zfbzhanghao'] = trim(I('post.zfbzhanghao', '', 'htmlspecialchars'));


                M("MemberList")->where($map)->save($data);
                $this->success(1);
            }


        } else {

            $this->error("提交方式不对！");
        }


    }

    public function getgrxx()
    {
        if (I("post.")) {
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {

                $this->error("登录过期！请重新登录！");
            } else {
                $sys = M("Sys")->find(1);
                $data = array();
                $data['username'] = $member['username'];
                $data['zfbname'] = $member['zfbname'];
                $data['zfbzhanghao'] = $member['zfbzhanghao'];
                $data['phone'] = $member['phone'];
                $data['qqhaoma'] = $member['qqhaoma'];
                $data['email'] = $member['email'];
                $data['password'] = $member['password'];
                $data['gqtime'] = date("Y-m-d", $member['birthdate']);
                $data['txpic'] = $sys['txpic'];
                $this->success($data);
            }

        } else {

            $this->error("提交方式不正确！");
        }

    }





    public function gonggaolist()
    {
        if (I("post.")) {
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {

                $this->error("登录过期！请重新登录！");
            } else {


                $data = M("Said")->where("s_view=1")->order('s_id desc')->select();


                $this->success($data);
            }

        } else {

            $this->error("提交方式不正确！");
        }

    }


    public function gonggaocon()
    {
        if (I("post.")) {
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $s_id = trim(I('post.sid', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {

                $this->error("登录过期！请重新登录！");
            } else {


                $data = M("Said")->where("s_id=" . $s_id)->find();

                $data['create_time'] = date("Y-m-d H:i", $data['create_time']);

                $this->success($data);
            }

        } else {

            $this->error("提交方式不正确！");
        }

    }


    public function getsysinfo()
    {

        if (I("post.")) {
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {

                $this->error("登录过期！请重新登录！");
            } else {

                $sys = M("Sys")->find(1);
                $this->success($sys['ggjgsh']);
            }

        } else {

            $this->error("提交方式不正确！");
        }
    }

    public function getggnr()
    {

        if (I("post.")) {
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {

                $this->error("登录过期！请重新登录！");
            } else {
                $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
                $this->TestingNetWork($wangluo);
                $sys = M("Sys")->find(1);

                $ggid = trim(I('post.ggid', '', 'htmlspecialchars'));

                $wenzhang = M("wenzhang")->find($ggid);

                $guanggao = M("article")->field("a_id,laiyuan_url,photo")->where("state=1")->limit(1)->order('rand()')->select();


                $this->success(array("ggjgsh" => $sys['ggjgsh'], "title" => $wenzhang['a_title'], "content" => stripslashes(htmlspecialchars_decode($wenzhang['a_content'])), "ggid" => $guanggao[0]['a_id'], "ggurl" => $guanggao[0]['laiyuan_url'], 'photo' => 'Uploads' . $guanggao[0]['photo']));
            }

        } else {

            $this->error("提交方式不正确！");
        }
    }

    public function getwenzhang()
    {

        if (I("post.")) {
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {

                $this->error("登录过期！请重新登录！");
            } else {
                $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
                $this->TestingNetWork($wangluo);
                $guanggao = M("wenzhang")->field("a_id")->where("state=1")->limit(1)->order('rand()')->select();

                $this->success(array("ggid" => $guanggao[0]['a_id']));
            }

        } else {

            $this->error("提交方式不正确！");
        }
    }


    public function getguanggao()
    {
         file_put_contents("aa.txt", date("Y-m-d H:i:s"));
        if (I("post.")) {
            file_put_contents("aa.txt", json_encode(I("post.")));
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
            $this->TestingNetWork($wangluo);
            if (empty($member)) {

                $this->error("登录过期！请重新登录！");
            } else {
                $guanggao = M("article")->field("a_id,laiyuan_url")->where("state=1")->limit(1)->order('rand()')->select();
                $this->success(array("ggid" => $guanggao[0]['a_id'], "ggurl" => $guanggao[0]['laiyuan_url']));
            }

        } else {

            $this->error("提交方式不正确！");
        }
    }


    /**
     * 原点 2018年10月26日09:20:13 浏览广告增加余额
     */
    public function ggjiesuan()
    {

        if (I("post.")) {

            $openid = I('openid', '', 'htmlspecialchars');

            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {

                $this->error("登录过期！请重新登录！");
            } else {
                //总的金额
                $sum_money =  M('ggll_list')->where(['uid'=>$member['id']])->sum('money');
                if($sum_money && $sum_money > 0){
                    //如果会员一生获得的总金额大于后台设定的金额，则无法获得更多奖励
                    if($member['max_fr'] > 0 &&  $sum_money >= $member['max_fr']){
                        $this->error("您已获得最大金额，无法在获得更多奖励");
                    }
                }
                $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
                $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
                $day_map['addtime'] =  array('between',array($beginToday,$endToday),'AND');
                $day_map['uid'] = array('eq',$member['id']);
                //抓取今日赚的金额
                $day_money = M('ggll_list')->where($day_map)->sum('money');
                if($day_money && $day_money > 0){
                    //如果会员单独设置的每日可获得金额大于0，说明该会员单独设置每日可获得金额
                    if($member['day_max_fr'] > 0 ){
                        //如果今日的金额>=会员单独设置的每日可获得金额，将无法获得更多奖励
                        if($day_money >= $member['day_max_fr']){
                            $this->error("您今日已获得最大金额，无法在获得更多奖励");
                        }
                    }else{
                        //否则，统一追寻后台设定的每日可获得金额，
                        $llgg_day = M('sys')->where(['sys_id'=>1])->getField('llgg_day');
                        //如果后台设置的金额 > 0并且会员今日获得的金额>=后台设置的金额将无法获得更多奖励
                        if($llgg_day > 0 && $day_money >= $llgg_day){
                            $this->error("您今日已获得最大金额，无法在获得更多奖励");
                        }
                    }

                }
                $ggid = trim(I('post.ggid', '', 'htmlspecialchars'));
                $guanggao = M("article")->where("a_id=" . $ggid)->find();
                //获取会员等级
                $level_money = M('member_level')->where(['level_id'=>$member['level_id']])->find();
//                $money = $guanggao["money"];
                $money = $level_money['level_price']; //根据等级获取相应的金额
                $data = array();

                $data['uid'] = $member['id'];
                $data['username'] = $member['username'];
                $data['money'] = $money;
                $data['type'] = 0;
                $data['yue'] = $member['money'] + $money;
                $data['beizhu'] = "浏览广告增加！";
                $data['addtime'] = time();

                $cid = M("caiwu_list")->add($data);

                if ($cid) {
                    $data = array();
                    $data['uid'] = $member['id'];
                    $data['username'] = $member['username'];
                    $data['money'] = $money;
                    $data['ggid'] = $ggid;
                    $data['ggurl'] = $guanggao['laiyuan_url'];
                    $data['addtime'] = time();
                    $aaid = M("ggll_list")->add($data);
                    M("MemberList")->where(array("openid" => $openid))->setInc("money", $money);
                    M("MemberList")->where(array("openid" => $openid))->setInc("zongmoney", $money);
                    $this->PromotionCommission($member['id'],$money);
                }


                $this->success($money);
            }

        } else {

            $this->error("提交方式不正确！");
        }
    }
    public function PromotionCommission($uid,$money){
        for($i=1;$i<4;$i++){
           //查找我的1.2.3代推荐人
            $tj_member = M('relation_reco')->where(['newuserid'=>$uid,'cengshu'=>$i])->getField('userid');
            $tj_user = M('member_list')->where(['id'=>$tj_member])->field('id,username')->find();
            if($tj_user){
                if($i == 1){
                    $tuiguang = M('sys')->where(['sys_id'=>1])->getField('one_tuiguang');
                }else if($i == 2){
                    $tuiguang = M('sys')->where(['sys_id'=>1])->getField('two_tuiguang');
                }else{
                    $tuiguang = M('sys')->where(['sys_id'=>1])->getField('there_tuiguang');
                }
                $tj_money = $money*$tuiguang;
                M('tuiguang_log')->add([
                    'uid' =>$tj_user['id'],
                    'username' => $tj_user['username'],
                    'ly_uid' => $uid,
                    'cengshu' => $i,
                    'time' => time(),
                    'msg' => "第" . $i. "代推广佣金",
                    'money' => $tj_money
                ]);
                M("member_list")->where(array("id" => $tj_user['id']))->setInc("tuiguangmoney", $tj_money);
                M("member_list")->where(array("id" => $tj_user['id']))->setInc("zongmoney", $tj_money);
            }
        }
    }

    public function get_time(){
        $this->success(M('sys')->where(['sys_id'=>1])->getField('ggjgsh'));
    }


    public function get_gonglue(){
        $openid = I('openid', '', 'htmlspecialchars');
        $map = array("openid" => $openid);
        $member = M("MemberList")->where($map)->find();

        if (empty($member)) {
            $this->error("登录过期！请重新登录！");
        }
        $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
        $this->TestingNetWork($wangluo);
        $ac = M('wenzhang')->where(['a_id'=>43])->find();
        $this->success(["title" => $ac['a_title'], "content" => stripslashes(htmlspecialchars_decode($ac['a_content'])),]);
    }

    public function get_kf(){
        $openid = I('openid', '', 'htmlspecialchars');
        $map = array("openid" => $openid);
        $member = M("MemberList")->where($map)->find();

        if (empty($member)) {
            $this->error("登录过期！请重新登录！");
        }
        $wangluo = trim(I('post.wangluo', '', 'htmlspecialchars'));
        $this->TestingNetWork($wangluo);
        $info['kf_phone'] = M('sys')->where(['sys_id'=>1])->getField('kf_phone');
        $info['kf_qq'] = M('sys')->where(['sys_id'=>1])->getField('kf_qq');
        $info['kf_wx'] = M('sys')->where(['sys_id'=>1])->getField('kf_wx');
        $this->success($info);
    }

    public function get_paomadeng(){

        $list = M("said")->where(['s_view'=>1])->getField('title',true);
        $html = "";
        foreach ($list as $k=>$v){
            $html .= '<li><i></i> '.$v.'</li>';
        }

        $this->success($html);
    }


    /**
     * 原点 2018年11月6日15:13:46 获取提现金额
     */
    public function get_tx_money(){

        if (I("post.")) {
            $openid = trim(I('post.openid', '', 'htmlspecialchars'));
            $map = array("openid" => $openid);
            $member = M("MemberList")->where($map)->find();

            if (empty($member)) {

                $this->error("登录过期！请重新登录！");
            } else {
                $time = date("H");

                $min_tx = M('sys')->where(['sys_id'=>1])->getField('tx_min_time');
                $max_tx = M('sys')->where(['sys_id'=>1])->getField('tx_max_time');

                if($time >= $min_tx && $time<$max_tx){
                    $tx_money = M('sys')->where(['sys_id'=>1])->getField('tx_money');
                    $html = "";
                    foreach (unserialize($tx_money) as $k=>$v){
                        $html .= '<option value="'.$v.'">'.$v.'</option>';
                    }
                    $this->success($html);
                }else{
                    $this->error("提现开启时间为".$min_tx."-".$max_tx."点！");
                }
            }

        } else {

            $this->error("提交方式不正确！");
        }


    }




}
