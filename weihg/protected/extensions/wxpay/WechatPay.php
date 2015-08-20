<?php
/*
**@version 2015-2-2
**@author  yuzixiu
**@΢��֧��
*/
class WechatPay extends CApplicationComponent{
	private $APPID = "wx95f6bdf230047a68"; //appid
	private $APPKEY = "vjvK9nNStPXs5JpXgKqG5DTUjGcs44TCza32aDKRxnqH7NCLxTMeDcZuTx70RRiNYqEcVo0NMfHSGDkNDlGlTkkv6YjchmzrMvqawXDmR62qzIrCee3Z07NlSkq0eJkr"; //paysign key
	private $SIGNTYPE = "sha1"; //method
	private $PARTNERKEY = "75eacd0c9a224ce5da9d526c9b309889"; //ͨ���ܴ�
	private $APPSERCERT = "ffe1eb21c7976986caa9813ead47fab7";
	var $parameters; //cft ����
	
	/**
	 * 
	 * 
	 * @param toURL
	 * @param paras
	 * @return
	 */
	function genAllUrl($toURL, $paras) {
		$allUrl = null;
		if(null == $toURL){
			die("toURL is null");
		}
		if (strripos($toURL,"?") =="") {
			$allUrl = $toURL . "?" . $paras;
		}else {
			$allUrl = $toURL . "&" . $paras;
		}

		return $allUrl;
	}
	function create_noncestr( $length = 16 ) {  
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";  
		$str ="";  
		for ( $i = 0; $i < $length; $i++ )  {  
			$str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
			//$str .= $chars[ mt_rand(0, strlen($chars) - 1) ];  
		}  
		return $str;  
	}
	/**
	 * 
	 * 
	 * @param src
	 * @param token
	 * @return
	 */
	function splitParaStr($src, $token) {
		$resMap = array();
		$items = explode($token,$src);
		foreach ($items as $item){
			$paraAndValue = explode("=",$item);
			if ($paraAndValue != "") {
				$resMap[$paraAndValue[0]] = $parameterValue[1];
			}
		}
		return $resMap;
	}
	
	/**
	 * trim 
	 * 
	 * @param value
	 * @return
	 */
	function trimString($value){
		$ret = null;
		if (null != $value) {
			$ret = $value;
			if (strlen($ret) == 0) {
				$ret = null;
			}
		}
		return $ret;
	}
	
	function formatQueryParaMap($paraMap, $urlencode){
		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v){
			if (null != $v && "null" != $v && "sign" != $k) {
			    if($urlencode){
				   $v = urlencode($v);
				}
				$buff .= $k . "=" . $v . "&";
			}
		}
		$reqPar;
		if (strlen($buff) > 0) {
			$reqPar = substr($buff, 0, strlen($buff)-1);
		}
		return $reqPar;
	}
	function formatBizQueryParaMap($paraMap, $urlencode){
		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v){
		//	if (null != $v && "null" != $v && "sign" != $k) {
			    if($urlencode){
				   $v = urlencode($v);
				}
				$buff .= strtolower($k) . "=" . $v . "&";
			//}
		}
		$reqPar;
		if (strlen($buff) > 0) {
			$reqPar = substr($buff, 0, strlen($buff)-1);
		}
		return $reqPar;
	}
	function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
        	 if (is_numeric($val))
        	 {
        	 	$xml.="<".$key.">".$val."</".$key.">"; 

        	 }
        	 else
        	 	$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
        }
        $xml.="</xml>";
        return $xml; 
    }
	
	function sign($content, $key) {
	    try {
		    if (null == $key) {
			   throw new SDKRuntimeException("�Ƹ�ͨǩ��key����Ϊ�գ�" . "<br>");
		    }
			if (null == $content) {
			   throw new SDKRuntimeException("�Ƹ�ͨǩ�����ݲ���Ϊ��" . "<br>");
		    }
		    $signStr = $content . "&key=" . $key;
		
		    return strtoupper(md5($signStr));
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
	
	function verifySignature($content, $sign, $md5Key) {
		$signStr = $content . "&key=" . $md5Key;
		$calculateSign = strtolower(md5($signStr));
		$tenpaySign = strtolower($sign);
		return $calculateSign == $tenpaySign;
	}
	
//---	WxPayHelper.php-------
	function setParameter($parameter, $parameterValue) {
		$this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
	}
	function getParameter($parameter) {
		return $this->parameters[$parameter];
	}

	function check_cft_parameters(){
		if($this->parameters["bank_type"] == null || $this->parameters["body"] == null || $this->parameters["partner"] == null || 
			$this->parameters["out_trade_no"] == null || $this->parameters["total_fee"] == null || $this->parameters["fee_type"] == null ||
			$this->parameters["notify_url"] == null || $this->parameters["spbill_create_ip"] == null || $this->parameters["input_charset"] == null
			)
		{
			return false;
		}
		return true;

	}
	protected function get_cft_package(){
		try {
			
			if (null == $this->PARTNERKEY || "" == $this->PARTNERKEY ) {
				throw new SDKRuntimeException("��Կ����Ϊ�գ�" . "<br>");
			}
		//	$commonUtil = new CommonUtil();
			ksort($this->parameters);
			$unSignParaString = $this->formatQueryParaMap($this->parameters, false);
			$paraString = $this->formatQueryParaMap($this->parameters, true);

		//	$md5SignUtil = new MD5SignUtil();
			return $paraString . "&sign=" . $this->sign($unSignParaString,$this->trimString($this->PARTNERKEY));
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}

	}
	protected function get_biz_sign($bizObj){
		 foreach ($bizObj as $k => $v){
			 $bizParameters[strtolower($k)] = $v;
		 }
		 try {
		 	if($this->APPKEY == ""){
		 			throw new SDKRuntimeException("APPKEYΪ�գ�" . "<br>");
		 	}
		 	$bizParameters["appkey"] = $this->APPKEY;
		 	ksort($bizParameters);
		 	//var_dump($bizParameters);
		// 	$commonUtil = new CommonUtil();
		 	$bizString = $this->formatBizQueryParaMap($bizParameters, false);
		 	//var_dump($bizString);
		 	return sha1($bizString);
		 }catch (SDKRuntimeException $e)
		 {
			die($e->errorMessage());
		 }
	}
	//����app֧������json
	/*
    {
	"appid":"wwwwb4f85f3a797777",
	"traceid":"crestxu",
	"noncestr":"111112222233333",
	"package":"bank_type=WX&body=XXX&fee_type=1&input_charset=GBK&notify_url=http%3a%2f%2f
		www.qq.com&out_trade_no=16642817866003386000&partner=1900000109&spbill_create_ip=127.0.0.1&total_fee=1&sign=BEEF37AD19575D92E191C1E4B1474CA9",
	"timestamp":1381405298,
	"app_signature":"53cca9d47b883bd4a5c85a9300df3da0cb48565c",
	"sign_method":"sha1"
	}
	*/
	function create_app_package($traceid=""){
		//echo $this->create_noncestr();
        try {
           //var_dump($this->parameters);
		   if($this->check_cft_parameters() == false) {
			   throw new SDKRuntimeException("����package����ȱʧ��" . "<br>");
		    }
		    $nativeObj["appid"] = $this->APPID;
		    $nativeObj["package"] = $this->get_cft_package();
		    $nativeObj["timestamp"] = '"'.time().'"';
		    $nativeObj["traceid"] = $traceid;
		    $nativeObj["noncestr"] = $this->create_noncestr();
		    $nativeObj["app_signature"] = $this->get_biz_sign($nativeObj);
		    $nativeObj["sign_method"] = $this->SIGNTYPE;


		   
		    return   json_encode($nativeObj);

		   
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}		
	}
	//����jsapi֧������json
	/*
	"appId" : "wxf8b4f85f3a794e77", //���ں����ƣ����̻�����
	"timeStamp" : "189026618", //ʱ�����������ʹ����һ��ֵ
	"nonceStr" : "adssdasssd13d", //�����
	"package" : "bank_type=WX&body=XXX&fee_type=1&input_charset=GBK&notify_url=http%3a%2f
	%2fwww.qq.com&out_trade_no=16642817866003386000&partner=1900000109&spbill_create_i
	p=127.0.0.1&total_fee=1&sign=BEEF37AD19575D92E191C1E4B1474CA9",
	//��չ�ֶΣ����̻�����
	"signType" : "SHA1", //΢��ǩ����ʽ:sha1
	"paySign" : "7717231c335a05165b1874658306fa431fe9a0de" //΢��ǩ��
	*/
	function create_biz_package(){
		 try {
		  
			if($this->check_cft_parameters() == false) {   
			   throw new SDKRuntimeException("����package����ȱʧ��" . "<br>");
		    }
		    $nativeObj["appId"] = $this->APPID;
		    $nativeObj["package"] = $this->get_cft_package();
		    $nativeObj["timeStamp"] = '"'.time().'"';
		    $nativeObj["nonceStr"] = $this->create_noncestr();
		    $nativeObj["paySign"] = $this->get_biz_sign($nativeObj);
		    $nativeObj["signType"] = $this->SIGNTYPE;
		   
		    return   json_encode($nativeObj);
		   
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}		   
		
	}
	//����ԭ��֧��url
	/*
	weixin://wxpay/bizpayurl?sign=XXXXX&appid=XXXXXX&productid=XXXXXX&timestamp=XXXXXX&noncestr=XXXXXX
	*/
	function create_native_url($productid){

		//	$commonUtil = new CommonUtil();
		    $nativeObj["appid"] = $this->APPID;
		    $nativeObj["productid"] = urlencode($productid);
		    $nativeObj["timestamp"] = '"'.time().'"';
		    $nativeObj["noncestr"] = $this->create_noncestr();
		    $nativeObj["sign"] = $this->get_biz_sign($nativeObj);
		    $bizString = $this->formatBizQueryParaMap($nativeObj, false);
		    return "weixin://wxpay/bizpayurl?".$bizString;
		    
	}
	//����ԭ��֧������xml
	/*
	<xml>
    <AppId><![CDATA[wwwwb4f85f3a797777]]></AppId>
    <Package><![CDATA[a=1&url=http%3A%2F%2Fwww.qq.com]]></Package>
    <TimeStamp> 1369745073</TimeStamp>
    <NonceStr><![CDATA[iuytxA0cH6PyTAVISB28]]></NonceStr>
    <RetCode>0</RetCode>
    <RetErrMsg><![CDATA[ok]]></ RetErrMsg>
    <AppSignature><![CDATA[53cca9d47b883bd4a5c85a9300df3da0cb48565c]]>
    </AppSignature>
    <SignMethod><![CDATA[sha1]]></ SignMethod >
    </xml>
	*/
	function create_native_package($retcode = 0, $reterrmsg = "ok"){
		 try {
		   if($this->check_cft_parameters() == false && $retcode == 0) {   //����������ķ��أ� ���Ƹ�ͨ�Ĳ���
			   throw new SDKRuntimeException("����package����ȱʧ��" . "<br>");
		    }
		    $nativeObj["AppId"] = $this->APPID;
		    $nativeObj["Package"] = $this->get_cft_package();
		    $nativeObj["TimeStamp"] = '"'.time().'"';
		    $nativeObj["NonceStr"] = $this->create_noncestr();
		    $nativeObj["RetCode"] = $retcode;
		    $nativeObj["RetErrMsg"] = $reterrmsg;
		    $nativeObj["AppSignature"] = $this->get_biz_sign($nativeObj);
		    $nativeObj["SignMethod"] = $this->SIGNTYPE;
		//    $commonUtil = new CommonUtil();

		    return  $this->arrayToXml($nativeObj);
		   
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}		

	}	
}