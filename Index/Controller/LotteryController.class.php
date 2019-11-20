<?php
namespace index\Controller;
use Think\Controller;
class LotteryController extends Controller {
	//流程是，点击抽奖时，若查session中当前为空，则弹出输入手机号框，则可以抽奖，
    public function index(){

        //大转盘活动
        header("Content-Type:Text/html;charset=UTF-8");
		$hour = 'login'.date("Y-m-d H",time());
	
		if(md5($hour) == I('post.logact')) {		//提交过来登录的手机号
			//將这个号记录到 openid表中
			$telephone = I('post.telephone');
			$condition_tel['tel'] = $telephone;

			$open_info = M('openid')->where($condition_tel)->find();
			if(empty($open_info)) {		//若为空则插入
				$condition_tel['denglutime'] = date('Y-m-d H:i:s',time());
				$userid = M('openid')->data($condition_tel)->add();
			} else {
				$userid = $open_info['id'];
			}
			$_SESSION['userid'] = $userid;		//openid中的 id
			$_SESSION['tel'] = $telephone;		// openid中的 tel
		}
		
        $hdid = 483; 		//活动的主id
		//$wid = $_GET['wid'];			// 如 117 
		//$wxid = $_GET['wxid'];			// 如	ow_6HjiveN97O7fiPMsGU-YZH0RE
        //活动中记录的参加者的信息
		$condition['tel'] = $_SESSION['tel'];
        $op_array = M('openid')->where($condition)->find();
        $this->assign('op_array',$op_array);
		//var_dump($op_array,M('openid')->_sql());
		//$hd = M('xydzp');			//活动表
		$now_hd = M('xydzp')->find($hdid);
		$this->assign('now_hd',$now_hd);

		if(strtotime($now_hd['kssj'])>time()){          //活动开始时间与当前时间的比较;
			$this->display('activitynotscratch');     //未开始
		}elseif(strtotime($now_hd['jssj'])<time()){         //已结束
			//Page::view('activityend');
			$this->display('activityend');
		}else{//正在进行中
            $hdlog = M('xydzp_record');
			//出奖次数
			$hasjingpin = true;
			//这里若没有登录，则概率是0;
			if(empty($_SESSION['tel'])) {$hasjingpin = false;}
            //今天这个活动的所有中奖的个数总计   $map['id'] = array('neq',100);
			$cjcs = $hdlog->where(array('jx'=>array('neq',0),'jdate'=>date('Y-m-d')))->count();

			$zdcs = intval($now_hd['mrzd']);        // 活动规定每天中奖次数
			//var_dump($cjcs >= $zdcs,$cjcs,$zdcs,$hdlog->_sql());
			if($zdcs>0 && $cjcs >= $zdcs){      //若活动规定每天中奖次数大于0，而且今天活动中奖个数总计大于等于规定第天中奖次数，则中奖概率为0;
				$hasjingpin = false;
			}
			//已经抽奖总次数———— 微信wxid这个人抽奖的总次数
			$yjzcs = $hdlog->where(array('tel'=>$_SESSION['tel']))->count();
			//已经抽奖次数————微信wxid这个人今天抽奖的次数
			$yjcs = $hdlog->where(array('tel'=>$_SESSION['tel'],'jdate'=>date('Y-m-d')))->count();
			//找到最后一个参加活动的人手机号
			$last_ren = $hdlog->where(" jx <> 0 and tel is not null")->order('id desc')->find();
            //若最后一个中奖人的手机号找到而且手机号位数是11位
			if(!empty($last_ren) && strlen($last_ren['tel'])==11){
                //则奖这个中奖的手机号隐藏部分位数显示
				$last_ren['tel'] = substr($last_ren['tel'], 0,5).'****'.substr($last_ren['tel'], 9,2);
			}
			$this->assign('last_ren',$last_ren);
			//剩余机会————活动每天中奖的数量 - 已经抽奖次数
			$sycs = intval($now_hd['mtsl'])-$yjcs;
			//剩余总机会————活动每人中奖次数 - 已经抽奖总次数
			$syzcs = intval($now_hd['mrzs'])-$yjzcs;
            // 剩余次数 = 剩余机会 小于 剩余总机会 ? 剩余机会 : 剩余总机会
			$sycs = $sycs < $syzcs ? $sycs : $syzcs;				//取机会最小的那个
			//var_dump($sycs,$syzcs);
			$this->assign('sycs',$sycs);
			$jxmc = '谢谢参与';
			$jx = '0';          //奖项
			unset($_SESSION['jiang']);			// 清除上次的种奖等级记录

			$gljs = 1;//概率基数
			
			if($sycs >0) {      //若剩余次数大于0
				//var_dump($hasjingpin);
				
				if($hasjingpin){    // 若有奖品
                    //随机定下奖项
                    for($i=1;$i<9;$i++){
                        $mc = 'j'.$i.'mc';
                        $ms = 'j'.$i.'ms';
                        $gl = 'j'.$i.'gl';
                        $sl = 'j'.$i.'sl';
                        $yj = 'j'.$i.'yj';
                        if(trim($now_hd["$mc"])==''){
                            break;
                        }
                        if(intval($now_hd["$sl"])-intval($now_hd["$yj"])>0){
                            //还有剩余奖品
                            $gls = rand(0,100000000);
                            if($gls<doubleval($now_hd["$gl"])*1000000){
                                $jx = $i;
                                $jxmc = $now_hd["$mc"];
                                $jxms = $now_hd["$ms"];
                                //Session::set($wxid.'jiang',$jx);
                                $_SESSION['jiang'] = $jx;
                                break;
                            }
                        }
                    }
				}
				
				$this->assign('jx',$jx);
				$this->assign('jxmc',$jxmc);
				//这里判断是否从微信消息体中得到了如下两个变量 ，若得到才可以抽奖，否则不可抽奖，提示关注公众号并发送“大转盘”三个字才可以参加
				$flag_chou = !empty($_SESSION['tel']); //$flag_chou = true;
				$flag_num = $flag_chou ? 1 : 0;
				$this->assign('flag_num',$flag_num);
				
				$this->assign('actmy',md5($hour));			//登录提交动作
				$this->display();
				
				//var_dump($jx,$op_array,$_SESSION);
			}else{
				$this->display('chanceend');  // 今日的机会用完了的提示
			}
		}
    }

    //抽奖后转动中ajax传给后台的接收处理程序
    public function toendhd () {
		//读取session记录
		//看是否传来的post表单
		//若是post则处理表单记录到数据库，若无post则输入参数
        header("Content-Type:Text/html;charset=UTF-8");
        $openid = $_SESSION['userid'];			//用户的id号
		//echo $openid;echo "<br />";
        $telephone =  $_SESSION['tel'];
        $hdlog = M('Xydzp_record');
		$dataaa['id'] = $_SESSION['uid'];
		$open_infoaa = M('user')->where($dataaa)->find();
        if($_POST['lid']) {
			$record_id = I('post.lid');  
			//echo $record_id;
			$hdlog_array = $hdlog->find($record_id);		//根据传来的记录id查找记录信息
			
			//var_dump($hdlog_array);exit;
			if($hdlog_array['openid'] == $openid ) {			// 若当前人的微信的号与记录中的微信的号相同，则修改传来的信息到这条记录中
				$data['openid'] = $openid;
				$data['tel'] = I('post.sjh');
				$data['un'] = I('post.un');				//姓名
				$data['dizhi'] = I('post.dizhi');		//收货地址
				$data['jx'] = $_SESSION['jiang'];
				$hdlog->where('id = '.$record_id)->save($data);		//保存到参加记录表中
				//将活动表中的数据中奖的数量加上1
				$hd_id = 483;
				$hd_array = M('xydzp')->find('id='.$hd_id);
				$jxcoln = 'j'.$data['jx'].'yj';
				$data[$jxcoln] = intval($hd_array[$jxcoln])+1;
				M('xydzp')->where('id='.$hd_id)->save($data);
			}
        } else {
            //第一遍出结果了要写入的内容
            $data['openid'] = $openid;
            $data['jx'] = '0';
            $data['jtime'] = date("Y-m-d H:i:s",time());
            $data['jdate'] = date("Y-m-d",time());
			 $data['tel'] = $telephone;
            //$hdlog->bz = "test";		//2014/11/4    抽奖测试字段;
            $record_id = $hdlog->add($data);			//新加到记录表中
			//var_dump($hdlog->_sql());
            //$jmxm = Session::get($hdlog->wxid.'jiang');
            $jmxm = empty($_SESSION['jiang']) ? 0 : $_SESSION['jiang'];  // 传递是否中奖，若大于0则表示中奖;若等于0则没有跤中奖
            //Response::json(array($jmxm,$record_id));
			$result_json = json_encode(array('jmxm'=>$jmxm,'recordid'=>$record_id));
			
			echo $result_json;
        }
		//echo 0;
    }

	public function login() {
		$data['id'] = $_SESSION['uid'];
		$open_infoaa = M('user')->where($data)->find();
	//	var_dump($open_infoaa);
		//$hour = 'login'.date("Y-m-d H",time());
		if($open_infoaa['phone']) {		//提交过来登录的手机号
			//將这个号记录到 openid表中
			$condition_tel['tel'] = $open_infoaa['phone'];
			$open_info = M('openid')->where($condition_tel)->find();
			if(empty($open_info)) {		//若为空则插入
				$condition_tel['denglutime'] = date('Y-m-d H:i:s',time());
				$userid = M('openid')->data($condition_tel)->add();
			} else {
				$userid = $open_info['id'];
			}
			$_SESSION['userid'] = $open_infoaa['id'];		//openid中的 id
			$_SESSION['tel'] = $open_infoaa['phone'];		// openid中的 tel
			
		$result = 1;
		} else {
			$result = 0;
		}
		echo $_SESSION['userid'];
		//echo $result;
	}
	
		public function kouchu(){
			
			

			$uid = $_SESSION['uid'];
			$open_infoaa = M('xydzp')->find();
			$useraa = M('user')->where(['id'=>$uid])->find();
				 
			
			$num = $open_infoaa['sl'];
			
			if($useraa['jifen'] < $num){
			     	$result = 0;
			}else{
				$User = M('user'); // 实例化User对象
				$res = $User->where(['id'=>$uid])->setDec('jifen',$open_infoaa['sl']); // 用户的积分减5
			
				if($res){ 
						$result = 1;
					
				}else{
						$result = 0;
				}
			}
		
		
			
			echo $result;
		}
	
}