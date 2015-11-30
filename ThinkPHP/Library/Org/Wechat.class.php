<?php
/**
* 微信红包类
*/
namespace Org;

class Wechat
{
	var $parameters; //cft 参数
    var $params;
	function __construct()
	{
		
	}
	function setParameter($parameter, $parameterValue) {
		$this->parameters[WechatCommonUtil::trimString($parameter)] = WechatCommonUtil::trimString($parameterValue);
	}
	function getParameter($parameter) {
		return $this->parameters[$parameter];
	}
	protected function create_noncestr( $length = 16 ) {  
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";  
		$str ="";  
		for ( $i = 0; $i < $length; $i++ )  {  
			$str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		}  
		return $str;  
	}
	function check_sign_parameters(){
		if($this->parameters["nonce_str"] == null || 
			$this->parameters["mch_billno"] == null || 
			$this->parameters["mch_id"] == null || 
			$this->parameters["wxappid"] == null || 
			$this->parameters["nick_name"] == null || 
			$this->parameters["send_name"] == null ||
			$this->parameters["re_openid"] == null || 
			$this->parameters["total_amount"] == null || 
			$this->parameters["max_value"] == null || 
			$this->parameters["total_num"] == null || 
			$this->parameters["wishing"] == null || 
			$this->parameters["client_ip"] == null || 
			$this->parameters["act_name"] == null || 
			$this->parameters["remark"] == null || 
			$this->parameters["min_value"] == null
			)
		{
			return false;
		}
		return true;

	}
	/**
	  例如：
	 	appid：    wxd930ea5d5a258f4f
		mch_id：    10000100
		device_info：  1000
		Body：    test
		nonce_str：  ibuaiVcKdpRxkhJA
		第一步：对参数按照 key=value 的格式，并按照参数名 ASCII 字典序排序如下：
		stringA="appid=wxd930ea5d5a258f4f&body=test&device_info=1000&mch_i
		d=10000100&nonce_str=ibuaiVcKdpRxkhJA";
		第二步：拼接支付密钥：
		stringSignTemp="stringA&key=192006250b4c09247ec02edce69f6a2d"
		sign=MD5(stringSignTemp).toUpperCase()="9A0A8659F005D6984697E2CA0A
		9CF3B7"
	 */
	protected function get_sign(){
		try {
			if (null == C('PARTNERKEY') || "" == C('PARTNERKEY') ) {
				throw new SDKRuntimeException("密钥不能为空！" . "<br>");
			}
			if($this->check_sign_parameters() == false) {   //检查生成签名参数
			   throw new SDKRuntimeException("生成签名参数缺失！" . "<br>");
		    }
			$commonUtil = new WechatCommonUtil();
			ksort($this->parameters);
			$unSignParaString = $commonUtil->formatQueryParaMap($this->parameters, false);

			$md5SignUtil = new WechatMD5SignUtil();
			return $md5SignUtil->sign($unSignParaString,$commonUtil->trimString(C('PARTNERKEY')));
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}

	}

	function create_hongbao_xml($retcode = 0, $reterrmsg = "ok"){
		 try {
		    $this->setParameter('sign', $this->get_sign());
		    $commonUtil = new WechatCommonUtil();
		    return  $commonUtil->arrayToXml($this->parameters);
		   
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}		

	}
	
	function curl_post_ssl($url, $vars, $second=30,$aHeader=array())
	{
		$ch = curl_init();
		//超时时间
		curl_setopt($ch,CURLOPT_TIMEOUT,$second);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		//这里设置代理，如果有的话
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/Key/wechat/apiclient_cert.pem');
 		curl_setopt($ch,CURLOPT_SSLKEY,getcwd().'/Key/wechat/apiclient_key.pem');
 		curl_setopt($ch,CURLOPT_CAINFO,getcwd().'/Key/wechat/rootca.pem');
	 
		if( count($aHeader) >= 1 ){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
		}
	 
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
		$data = curl_exec($ch);
		if($data){
			curl_close($ch);
			return $data;
		}
		else { 
			$error = curl_errno($ch);
			curl_close($ch);
			return false;
		}
	}


    function setParam($param, $paramValue) {
        $this->params[WechatCommonUtil::trimString($param)] = WechatCommonUtil::trimString($paramValue);
    }
    function getParam($param) {
        return $this->params[$param];
    }
    function check_sign_params(){
        if($this->params["nonce_str"] == null ||
            $this->params["mch_billno"] == null ||
            $this->params["mch_id"] == null ||
            $this->params["appid"] == null ||
            $this->params["bill_type"] == null
        )
        {
            return false;
        }
        return true;

    }
    protected function get_hb_sign(){
        try {
            if (null == C('PARTNERKEY') || "" == C('PARTNERKEY') ) {
                throw new SDKRuntimeException("密钥不能为空！" . "<br>");
            }
            if($this->check_sign_params() == false) {   //检查生成签名参数
                throw new SDKRuntimeException("生成签名参数缺失！" . "<br>");
            }
            $commonUtil = new WechatCommonUtil();
            ksort($this->params);
            $unSignParaString = $commonUtil->formatQueryParaMap($this->params, false);

            $md5SignUtil = new WechatMD5SignUtil();
            return $md5SignUtil->sign($unSignParaString,$commonUtil->trimString(C('PARTNERKEY')));
        }catch (SDKRuntimeException $e)
        {
            die($e->errorMessage());
        }

    }
    function create_hb_xml($retcode = 0, $reterrmsg = "ok"){
        try {
            $this->setParam('sign', $this->get_hb_sign());
            $commonUtil = new WechatCommonUtil();
            return  $commonUtil->arrayToXml($this->params);
        }catch (SDKRuntimeException $e)
        {
            die($e->errorMessage());
        }

    }
}


?>