<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/hzw/common.fun.php');
define(CHARSET,$cfg_soft_lang);
$_GET=array_merge($_GET, $_POST);
$_G['siteurl']='http://'.$_SERVER['HTTP_HOST'];
if (!empty($_GET["getremoteurl"])) {
	$csdn123_url = "http://www.csdn123.net/zd_version/zd9_3/getContent.php?siteurl=" . urlencode($_G['siteurl']) . '&charset=' . CHARSET . '&'. $_SERVER['QUERY_STRING'];
	$csdn123_return = csdn123_dfsockopen($csdn123_url);
	echo $csdn123_return;
}
if (!empty($_GET["csdn123_jianTextData"])) {
	$csdn123_url = "http://www.csdn123.net/zd_version/zd9_3/convert_GBK_BIG_onearticle.php";
	$csdn123_data = array('convertType' => $_GET['convertType'], 'textdata' => urlencode($_GET["csdn123_jianTextData"]), 'siteurl' => urlencode($_G["siteurl"]), 'ip' => $_SERVER['REMOTE_ADDR'], 'charset' => CHARSET);
	$csdn123_return = csdn123_dfsockopen($csdn123_url, 0, $csdn123_data);
	echo $csdn123_return;
}
if (!empty($_GET["searchtype"])) {

	if(preg_match('/^http/',$_GET['query'])==1)
	{
		$source_link = urldecode($_GET['query']);
		$csdn123callback = $_GET["csdn123callback"];
		$returnArr = array();
		$remoteUrl = array();
		$remoteUrl['zw01O1'] = $_G['siteurl'];
		$remoteUrl['zhiwu'] = $_COOKIE['zhiwu'];
		$remoteUrl['zw0lO1'] = $cfg_basehost;
		$remoteUrl['ip'] = $_SERVER['REMOTE_ADDR'];
		$remoteUrl['url'] = $source_link;
		$remoteUrl['zwOl01'] = 'http://'. $_SERVER['HTTP_HOST'];
		$remoteUrl['zwl0lO'] = 'http://' . $_SERVER['SERVER_NAME'];
		$fetchUrl = "http://www.csdn123.net/zd_version/zd9_3/catch_content.php";
		$htmlcode = csdn123_dfsockopen($source_link);
		if (strlen($htmlcode) < 100) {
			$htmlcode = csdn123_dfsockopen($source_link, 0, '', '', FALSE, '', 15, TRUE, 'URLENCODE', FALSE);
		}		
		if (strlen($htmlcode) < 100) {
			$returnArr['total']=-1;
			echo $csdn123callback . '(' . json_encode($returnArr) . ')';
			exit;
		}
		$htmlcode = base64_encode($htmlcode);
		$remoteUrl['htmlcode'] = $htmlcode;
		$htmlcode = csdn123_dfsockopen($fetchUrl, 0, $remoteUrl);
		if (strlen($htmlcode) < 100) {
			$returnArr['total']=-1;
			echo $csdn123callback . '(' . json_encode($returnArr) . ')';
			exit;
		}
		$htmlcode = preg_replace('/^\s+|\s+$/', '', $htmlcode);
		$htmlcode = dunserialize($htmlcode);
		if (is_array($htmlcode) === false) {
			$returnArr['total']=-1;
			echo $csdn123callback . '(' . json_encode($returnArr) . ')';
			exit;
		}
		$htmlcode['total']=0;		
		echo $csdn123callback . '(' . json_encode($htmlcode) . ')';
		
	} else {
			
		$csdn123_url = "http://www.csdn123.net/zd_version/zd9_3/main_news.php?siteurl=" . urlencode($_G['siteurl']) . '&charset=' . CHARSET . '&'. $_SERVER['QUERY_STRING'];
		$csdn123_return = csdn123_dfsockopen($csdn123_url);
		echo $csdn123_return;
	
	}
}
if (!empty($_GET["likearticleData"])) {
	$csdn123_url = "http://www.csdn123.net/zd_version/zd9_3/getKeywords.php";
	$likearticleData = $_GET["likearticleData"];
	$csdn123_data = array('likearticleData' => $likearticleData, 'ip' => $_SERVER['REMOTE_ADDR'], 'siteurl' => urlencode($_G["siteurl"]), 'charset' => CHARSET);
	$csdn123_return = csdn123_dfsockopen($csdn123_url, 0, $csdn123_data);
	echo $csdn123_return;
}
if (!empty($_GET["originality"])) {
	
	$db->Execute("hzw","SELECT word1,word2 FROM #@__csdn123zd_weiyanchang");
	while($arr = $db->GetArray("hzw"))
	{
		$wordRs[]=$arr;
	}
	$wordStr = "";
	foreach ($wordRs as $wordValue) {
		$word1 = $wordValue['word1'];
		$word2 = $wordValue['word2'];
		$word2OneStr = mb_substr($word2,1,1,CHARSET);
		$word2hzw = '_hzw_' . $word2OneStr;
		$word2 = mb_ereg_replace($word2OneStr,$word2hzw,$word2);
		$wordStr = $word1 . '=' . $word2 . ',' . $wordStr;
	}
	echo substr($wordStr,0,-1);
	
}
if (!empty($_GET["csdn123_localimg"]) && $_GET["csdn123_localimg"] == "yes") {
	$csdn123_localimgUrl = $_GET["csdn123_localimgUrl"];
	$csdn123_localimgUrl = preg_replace('/^\/\//','http://',$csdn123_localimgUrl);
	$csdn123_return = $_G['siteurl'] . "source/plugin/csdn123_news/display_picture.php?url=" . $csdn123_localimgUrl;
	if(stripos($csdn123_localimgUrl,'.jp')===false && stripos($csdn123_localimgUrl,'gif')===false && stripos($csdn123_localimgUrl,'png')===false)
	{
		$csdn123_return=$csdn123_return . '&.jpg';
	}
	echo $csdn123_return;
}



?>