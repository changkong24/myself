<?php
/**
 * @uses 短信发送类
 * @author jhl
 */
class Send{
	public function __construct(){
		$this->accountSid = 'aaf98f8949305e36014930827e1d0045';
		$this->accountToken = 'a96870046ad3436d91b3ae1672bc1a73';
		$this->appId = 'aaf98f8949305e36014931fc7bbd0243';
		$this->serverIP = 'sandboxapp.cloopen.com';
		$this->serverPort = '8883';
		$this->softVersion = '2013-12-26';
		Vendor('Extend.Rest');
		//include_once  GMLPATH.'/shared/libraries/Rest.php';
		$this->rest = new Rest();
	}
	/**
	 * @uses 发送短信
	 * @author jhl
	 * @param $model_id:模板id
	 * @param $phone:手机号码
	 * @param $datas:参数
	 */
	public function send_news($model_id,$phone,$datas){
		$this->rest->initialize($this->serverIP, $this->serverPort, $this->softVersion);
		$this->rest->setAccount($this->accountSid, $this->accountToken);
		$this->rest->setAppId($this->appId);
		$result = $this->rest->sendTemplateSMS($phone, $datas, $model_id);
		return $result;
	}
}