<?php

namespace index\Controller;

use Think\Controller;

class GsController extends Controller{


    /**
     * 原点 2018年11月7日10:53:15 操作订单
     */
    public function index(){
		
		echo "121212";
		
	}
    public function operation_order(){
        if (I("post.")) {
				$aa = I("post.aa");
			
                $order_id = I("post.order_id");
            //    $order = M("order")->where(['order_id'=>$order_id])->find();
                $type = I("post.type");
              //  if(empty($order) || !$type){
              //      $this->error("操作失败！");
               // }
                switch ($type){
                    case 1:
                        //删除订单
                        $order = M('order')->where(['order_id'=>$order_id])->delete();
                        $order_goods = M('order_details')->where(['order_id'=>$order_id])->delete();
                        if($order && $order_goods){
                            $this->success(['code'=>1,"msg"=>"已删除"]);
                        }else{
                            $this->error("操作失败！");
                        }
                        break;
                    case 3:
                        //提醒发货
                        $res = M('order')->where(['order_id'=>$order_id])->save(['tixingfahuo'=>$aa]);
                        if($res){
                            $this->success(['code'=>3,"msg"=>"已提醒发货"]);
                        }else{
                            $this->error("操作失败！");
                        }
                        break;
                    case 4:
                        //物流信息
                        $order['fh_time'] = date("Y-m-d",$order['fh_time']);
                        $this->success(['code'=>4,'data'=>$order]);
                        break;
                    case 5:
                        $res = M('order')->where(['order_id'=>$order_id])->save(['state'=>4,'sh_time'=>time()]);
                        if($res){
                            $this->success(['code'=>5,"msg"=>"收货成功"]);
                        }else{
                            $this->error("操作失败！");
                        }
                        break;
                    default:
                        $this->error("操作失败！");
                        break;
                }

          
        } else {
            $this->error("提交方式不对！");
        }
    }

    /**
     * 原点 2018年11月6日09:47:20 获取分类下的商品
     */
    public function get_class_goods(){
        if (I("post.")) {
          
                $class_id = I('post.class_id');
                $goods = M('goods')->where(['classify_id'=>$class_id,'state'=>1])->select();
                foreach ($goods as $k=>$v){
                    $goods[$k]['photo'] = ($v['photo']);
                }
                $this->success($goods);
          
        } else {
            $this->error("提交方式不对！");
        }
    }

    /**
     * 获取商品分类
     */



    public function sm_class(){
		
		$this->display();
	}   
	public function sm_list(){
		
			$this->assign('class_id',I("get.class_id"));
			$this->assign('title', I("get.title"));
		$this->display();
	}
    public function get_sm_class(){
      
        
                $goods_classify = M("goods_classify")->where(['state'=>1])->order('add_time desc')->select();
                foreach ($goods_classify as $k=>$v){
                    $goods_classify[$k]['photo'] = ($v['photo']);
                }
                $this->success($goods_classify);
         
	
	}
    /*** 商品开始*/
    public function get_commodity(){

 
			 					$this->display();
			 
	
      
    }
 public function get_commodityaa(){
	  
			   $goods = M("goods")->where(['state'=>1,'tuijian'=>1])->order('add_time')->select();
			
                foreach ($goods as $k=>$v){
                    $goods[$k]['photo'] = ($v['photo']);
                }
				echo  json_encode($goods);
 }
    /**
     * 原点 2018年11月5日13:21:34 商品详情
     */
    public function get_commodity_details(){
		//var_dump(I("get.goods_id"));exit;
        if (I("get.goods_id")) {
     
          
               $goods_id = I("get.goods_id");
               $goods = M('goods')->where(['id'=>$goods_id])->find();
               if(empty($goods)){
                   $this->error("商品不存在或已下架！");
               }
               $goods['tujilist'] = unserialize($goods['tujilist']);
               foreach ($goods['tujilist'] as $k=>$v){
                   $goods['tujilist'][$k] = "http://" . $_SERVER['SERVER_NAME'] .$v;
               }
               $goods['photo'] = ($goods['photo']);
               $goods['goods_content'] = stripslashes(htmlspecialchars_decode($goods['goods_content']));
    // var_dump($goods);
			   	$this->assign('goods_id', $goods_id);
			   	$this->assign('goods', $goods);
			   $this->display();
          
        } else {
            $this->error("提交方式不对！");
        }
    }

    public function set_sm_order(){
        if (I("get.")) {
        
                $goods_id = I("get.goods_id");
                $goods_num = I('get.goods_num');
                $goods = M('goods')->where(['id'=>$goods_id])->field('goods_name,photo,goods_price,goods_kc,goods_freight')->find();
                $goods['photo'] = ($goods['photo']);
                if(empty($goods)){
                    $this->error("商品不存在或已下架！");
                }
                if($goods_num > $goods['goods_kc']){
                    $this->error("库存不足！");
                }
                $total = $goods['goods_price'] * $goods_num;
                $freight = $goods['goods_freight'] * $goods_num;
                $order_param['goods_id'] = $goods_id;
                $order_param['goods'] = $goods;
                $order_param['goods_total'] = $total;
                $order_param['goods_num'] = $goods_num;
                $order_param['goods_freight'] = $freight;
                $order_param['total'] = $total + $freight;
                session('order_param',$order_param);
				
			
			//	var_dump($uid);
				   	$this->assign('goods', $goods);
				   	$this->assign('order_param', $order_param);
			   $this->display();
              //  $this->success(1);
        
        } else {
            $this->error("提交方式不对！");
        }
    }

    /**
     * 原点 2018年11月5日13:36:12 获取待提交订单
     */
    public function get_sm_order(){
		
        if (true) {
         
                $order_param = session('order_param');
					$uid = $_SESSION['uid'];
                //抓取默认地址
                $address = M('address')->where(['address_id'=>session('address_id')])->find();
                if(empty($address)){
                    $address = M('address')->where(['user_id'=>$uid,'default'=>1])->find();
                    if(empty($address)){
                        $address = M('address')->where(['user_id'=>$uid])->order('time desc')->find();
                    }
                }
                $order_param['address'] = $address;
                session('order_param',$order_param);
               if($order_param){
                   $this->success($order_param);
               }else{
                   $this->error("加载失败");
               }
          
        } else {
            $this->error("提交方式不对！");
        }
    }

    /**
     *  原点 2018年11月8日13:22:25  获取订单详情
     */
    public function sm_order_details(){
		$order_id=I("get.order_id");
		 	$this->assign('order_id', $order_id);
		   $this->display();
		
	}
    public function get_order_details(){
        if (I("post.")) {
          
                $order_id = I("post.order_id");
                $order = M("order")->where(['order_id'=>$order_id])->find();
                if(empty($order)){
                    $this->error("订单不存在！");
                }
                $data['order'] = $order;
                $data['address'] = M("address")->where(['address_id'=>$order['address_id']])->find();
                $data['goods'] = M("order_details")->where(['order_id'=>$order_id])->select();
                foreach ($data['goods'] as $k=>$v){
                    $data['goods'][$k]['goods_photo'] = ($v['goods_photo']);
                }
                $this->success($data);
          
        } else {
            $this->error("提交方式不对！");
        }
    }

    /**
     * 原点 2018年11月5日15:50:49 产生待付款订单
     */
    public function sub_order(){
        
         $uid = $_SESSION['uid'];
                $order_param = session('order_param');
				//var_dump( $order_param);exit;
                if(!$order_param){
                    $this->error("提交失败");
                }
                $order['order_sn'] = date("Ymd").time();
                $order['address_id'] =$order_param['address']['address_id'];
                $order['member_id'] =$uid;
                $order['total'] = $order_param['total'];
                $order['freight'] = $order_param['goods_freight'];
                $order['add_time'] = time();
                $order_id = M('order')->add($order);
                $goods = M('goods')->where(['id'=>$order_param['goods_id']])->find();
                M('order_details')->add([
                    'order_id'=>$order_id,
                    'goods_id'=>$goods['goods_id'],
                    'goods_name' => $goods['goods_name'],
                    'goods_price' => $goods['goods_price'],
                    'goods_num' => $order_param['goods_num'],
                    'goods_photo' =>$goods['photo'],
                ]);
                session('order_param',null);
                session('order_id',$order_id);
                $this->success("已提交订单，请输入二级密码进行支付");
          
     
    }
    public function set_address(){
     
                $address_id = I("post.address_id");
                if(!$address_id){
                    $this->error("提交方式不对！");
                }else{
                    session('address_id',$address_id);
                    $this->success(1);
                }
        
    }

    /**
     * 原点 2018年11月5日15:51:16 支付订单
     */
    public function pay_order(){
     
				$uid = $_SESSION['uid'];
          
                $order_id = session('order_id');
				$res = M("user")->where(['id'=>$uid])->find();
			//	var_dump($res['jifen']);
                $order = M('order')->where(['order_id'=>$order_id])->find();
                if(empty($order)){
                    $this->error("订单不存在！");
                }


                if($res['jifen'] < $order['total']){
                    $this->error(['code'=>2,'msg'=>"可用余额不足"]);
                }

                M("user")->where(['id'=>$uid])->setDec('jifen',$order['total']);
                M("order")->where(['order_id'=>$order_id])->save(['pay_time'=>time(),'state'=>2]);
                session('order_id',null);
                $this->success("购买成功");
         
    
    }

    public function my_order(){
        if (I("get.")) {
        $uid = $_SESSION['uid'];
                $state = I('get.state');
                if($state == "all"){
                    $order = M('order')->where(['member_id'=> $uid])->order('add_time desc')->select();
                }else{
                    $order = M('order')->where(['member_id'=> $uid,'state'=>$state])->order('add_time desc')->select();
                }
                foreach ($order as $k=>$v){
                    $order[$k]['goods'] = M('order_details')->where(['order_id'=>$v['order_id']])->select();
                    $order[$k]['order_time'] = date("Y-m-d",$v['add_time']);
                    foreach ($order[$k]['goods'] as $key=>$vo){
                        $order[$k]['goods'][$key]['goods_pic'] = ($order[$k]['goods'][$key]['goods_photo']);
                    }
                }
				//var_dump($order);
				  	$this->assign('order', $order);
			   $this->display();
               // $this->success($order);
          
        } else {
            $this->error("提交方式不对！");
        }
    }  
	public function my_order_alaj(){
        if (I("get.")) {
        $uid = $_SESSION['uid'];
                $state = I('get.state');
                if($state == "all"){
                    $order = M('order')->where(['member_id'=> $uid])->order('add_time desc')->select();
                }else{
                    $order = M('order')->where(['member_id'=> $uid,'state'=>$state])->order('add_time desc')->select();
                }
                foreach ($order as $k=>$v){
                    $order[$k]['goods'] = M('order_details')->where(['order_id'=>$v['order_id']])->select();
                    $order[$k]['order_time'] = date("Y-m-d",$v['add_time']);
                    foreach ($order[$k]['goods'] as $key=>$vo){
                        $order[$k]['goods'][$key]['goods_pic'] = ($order[$k]['goods'][$key]['goods_photo']);
                    }
                }
				//var_dump($order);
		//	echo $order;
              $this->success($order);
          
        } else {
            $this->error("提交方式不对！");
        }
    }

    public function get_address(){
      
			$uid = $_SESSION['uid'];

                $address = M('address')->where(['user_id'=>$uid])->select();

                $this->success($address);

         
   
    }


    public function sm_address(){
			 
			   $this->display();
		
	}
    public function save_address(){
		var_dump(I('post.name'));
      $uid = $_SESSION['uid'];
                $data['name'] = I('post.name');
                $data['phone'] = I('post.phone');
                $data['area'] = I('post.area');
                $data['details'] = I('post.details');
                $data['user_id'] = $uid;
                $data['time'] = time();
                $res = M('address')->add($data);
                if($res){
                    $this->success(1);
                }else{
                    $this->error("添加失败！");
                }
        
    }

    public function default_address(){
				$uid = $_SESSION['uid'];
                $address_id = I('post.address_id');
                $ress = M('address')->where(['user_id'=>$uid])->save(['default'=>0]);
                $res = M('address')->where(['address_id'=>$address_id])->save(['default'=>1]);


                if($res && $ress){
                    $this->success(1);
                }else{
                    $this->error("操作失败！");
                }
          
    }

    public function sm_add_address(){
		   $this->display();
		
	}
    public function del_address(){

       
                $address_id = I('address_id');
                $result = M('address')->where(['address_id'=>$address_id])->delete();

                if($result){
                    $this->success(1);
                }else{
                    $this->error("操作失败！");
                }
            
       
    }


    /**
     * @param $wangluo
     * 原点 2018年11月5日13:21:49  验证网络
     */
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


}
