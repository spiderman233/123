<?php
// 基类所有c要继承的类
namespace index\Controller;
use Think\Controller;
class CommonController extends Controller {
    // 2015/10/6 微信中分享功能
    public function _initialize()
	{
	}
	//公共的头部 要求得到当前的类名，并且返回
	public function head()
	{

		$this->display();
	}

	//用crul 提交post表单 核心函数 2015/3/1
	public function doPost($url, $fields)
	{
	   $fields = (is_array($fields)) ? http_build_query($fields) : $fields;
		//var_dump($url,"|",$fields);
	   if($ch = curl_init())
	   {
		  curl_setopt($ch, CURLOPT_URL, $url);
		  curl_setopt($ch, CURLOPT_POST, 1);				// 启用时会发送一个常规的POST请求
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	//将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
		  //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:apoplication/json'));  //一个用来设置HTTP头字段的数组
		  curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);	//全部数据使用HTTP协议中的"POST"操作来发送
		  //var_dump($fields); die();
		  $data = curl_exec($ch);
		  //var_dump("|",$data);
		  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
           //var_dump($status);      //查询状态 2015/11/17
		  curl_close($ch);
		  $result_array = json_decode($data);
            //var_dump($result_array);
			if($result_array->code == 0) {
				$data_array = (array)$result_array->data;
			} else {
                return $result_array;
            }
		  return  $data_array;
	   }
	   else
	   {
		  return false;
	   }
	}
	/**
     * 修改积分接口;参数   @$jfcode 是兑换编码  @$jfcode_tify对应编码;  @$userid 用户id  @$tuijian_num推荐人数
     * return 成功与否的json数据；
     */
	public function doupdatePost($url,$jfcode,$jfcode_tify,$userid,$tuijian_num)
	{
       $now = time()*1000;
       $fields= array('timestamp' =>$now,'systemId' => '10','accessToken' => md5('8cmdhd'.$now),
           'pPointsRegularCode' => $jfcode,'pBoIdSource' => $userid,'pBoIdDestination' => $userid, 'pChangeReason'=>'推荐满'.$tuijian_num.'人','pRegularVertify' => md5($jfcode_tify));

	   $fields = (is_array($fields)) ? http_build_query($fields) : $fields;
		//var_dump($url,"|",$fields);
	   if($ch = curl_init())
	   {
		  curl_setopt($ch, CURLOPT_URL, $url);
		  curl_setopt($ch, CURLOPT_POST, 1);				// 启用时会发送一个常规的POST请求
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	//将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
		  //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:apoplication/json'));  //一个用来设置HTTP头字段的数组
		  curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);	//全部数据使用HTTP协议中的"POST"操作来发送
		  //var_dump($fields); die();
		  $data = curl_exec($ch);
		  var_dump("|",$data);
		  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            var_dump($status);      //查询状态 2015/11/17
		  curl_close($ch);
		  $result_obj = json_decode($data);
		  return  $result_obj;
	   }
	   else
	   {
		  return false;
	   }
	}

	public function doString($url, $fields)
	{
	   $fields = (is_array($fields)) ? http_build_query($fields) : $fields;
		//var_dump($url,"|",$fields);
	   if($ch = curl_init())
	   {
		  curl_setopt($ch, CURLOPT_URL, $url);
		  curl_setopt($ch, CURLOPT_POST, 1);				// 启用时会发送一个常规的POST请求
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	//将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
		  //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:apoplication/json'));  //一个用来设置HTTP头字段的数组
		  curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);	//全部数据使用HTTP协议中的"POST"操作来发送
		  //var_dump($fields); die();
		  $data = curl_exec($ch);
		  //var_dump("|",$data);
		  //$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		  curl_close($ch);

		  return  $data;
	   }
	   else
	   {
		  return false;
	   }
	}
	function doget($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 500);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, $url);

		//curl_setopt($curl, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
		$res = curl_exec($curl);
		curl_close($curl);

		return $res;
	}
    /**
     * PHP发送Json对象数据
     *
     * @param $url 请求url
     * @param $jsonStr 发送的json字符串
     * @return array
     */
    public function http_post_json($url, $jsonStr)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($jsonStr)
            )
        );
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        //var_dump($httpCode,$response,$url); //die();
        return array($httpCode, $response);


    }



	//显示提示信息
	public function alert($msg)
	{
		echo "<Script language='JavaScript'>";
		echo "	alert('{$msg}');";
		echo "</Script>";
	}

	//跳转方法
	public function redirect_my($location)
	{
		echo "<Script language='JavaScript'>";
		echo "	window.location='{$location}';";
		echo "</Script>";
	}

}

class JSSDK {
  private $appId;
  private $appSecret;

  public function __construct($appId, $appSecret) {
    $this->appId = $appId;
    $this->appSecret = $appSecret;
  }

  public function getSignPackage() {
    $jsapiTicket = $this->getJsApiTicket();

    // 注意 URL 一定要动态获取，不能 hardcode.
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    $timestamp = time();
    $nonceStr = $this->createNonceStr();

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

    $signature = sha1($string);

    $signPackage = array(
      "appId"     => $this->appId,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
    return $signPackage;
  }

  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

  private function getJsApiTicket() {
    // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
    $data = json_decode(file_get_contents("jsapi_ticket.json"));
    if ($data->expire_time < time()) {
      $accessToken = $this->getAccessToken();
      // 如果是企业号用以下 URL 获取 ticket
      //$url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
      $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
      $res = json_decode($this->httpGet($url));
      $ticket = $res->ticket;
      if ($ticket) {
        $data->expire_time = time() + 7000;
        $data->jsapi_ticket = $ticket;
        $fp = fopen("jsapi_ticket.json", "w");
        fwrite($fp, json_encode($data));
        fclose($fp);
      }
    } else {
      $ticket = $data->jsapi_ticket;
    }

    return $ticket;
  }

  private function getAccessToken() {
    // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
    $data = json_decode(file_get_contents("access_token.json"));
    if ($data->expire_time < time()) {
      // 如果是企业号用以下URL获取access_token
      //$url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
      $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
      $res = json_decode($this->httpGet($url));
      $access_token = $res->access_token;
	  //var_dump($access_token); die();
      if ($access_token) {
        $data->expire_time = time() + 7000;
        $data->access_token = $access_token;
        $fp = fopen("access_token.json", "w");
        fwrite($fp, json_encode($data));
        fclose($fp);
      }
    } else {
      $access_token = $data->access_token;
    }
    return $access_token;
  }

  private function httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
  }
}

