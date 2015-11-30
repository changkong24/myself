<?php
namespace Extend;
/**
 * 微信客户端类
 * Class WeChat
 * @package Extend
 */
class WeChat {

    //取得用户信息
    public function getUser( $openid )
    {
        $access_token = $this->getAccessToken();
        //if( empty($openid) )    $openid = $this->_openid();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$openid&lang=zh_CN";
        $json = $this->http_get($url);
        $data = json_decode($json, true);

        return $data;
    }
    public function getAccessToken() {
    	// access_token 应该全局存储与更新，以下代码以写入到文件中做示例
    	$data = json_decode(file_get_contents("./Key/access_token.json"));
    	if ($data->expire_time < time()) {
    		// 如果是企业号用以下URL获取access_token
    		// $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
    		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
    		$res = json_decode($this->http_get($url));
    		$access_token = $res->access_token;
    		if ($access_token) {
    			$data->expire_time = time() + 7000;
    			$data->access_token = $access_token;
    			$fp = fopen("./Key/access_token.json", "w");
    			fwrite($fp, json_encode($data));
    			fclose($fp);
    		}
    	} else {
    		$access_token = $data->access_token;
    	}
    	return $access_token;
    }
//     //取是access_token
//     private function _access_token()
//     {
//         $AppID = C('APPID');
//         $AppSecret = C('APPSERCERT');
//         $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$AppID&secret=$AppSecret";
//         $json = $this->http_get($url);
//         $data = json_decode($json, true);

//         return $data['access_token'];
//     }

    //取是openid
    private function _openid()
    {
        /*$openid = trim($_GET['openid']);
        return $openid;*/
    }

    //curl请求
    public function http_get($url)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }
  
}

