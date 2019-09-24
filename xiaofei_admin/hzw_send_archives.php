<?php
define('DEDEADMIN', str_replace("\\", '/', dirname(__FILE__) ) );
require_once(DEDEADMIN.'/../include/common.inc.php');
require_once(DEDEADMIN.'/hzw/common.fun.php');
require_once(DEDEADMIN.'/inc/inc_archives_functions.php');
require_once(DEDEINC.'/image.func.php');
require_once(DEDEINC.'/oxwindow.class.php');
class hzw_cuserLogin
{
	private $userID;
	public function hzw_cuserLogin($userID)
	{
		$this->userID=$userID;
	}
	public function getUserID()
	{
		return $this->userID;
	}
	
}
if (!function_exists('AddMyAddon'))
{	
	function AddMyAddon($fid, $filename)
	{
		$cacheFile = DEDEDATA.'/cache/addon-'.session_id().'.inc';
		if(!file_exists($cacheFile))
		{
			$fp = fopen($cacheFile, 'w');
			fwrite($fp, '<'.'?php'."\r\n");
			fwrite($fp, "\$myaddons = array();\r\n");
			fwrite($fp, "\$maNum = 0;\r\n");
			fclose($fp);
		}
		include($cacheFile);
		$fp = fopen($cacheFile, 'a');
		$arrPos = $maNum;
		$maNum++;
		fwrite($fp, "\$myaddons[\$maNum] = array('$fid', '$filename');\r\n");
		fwrite($fp, "\$maNum = $maNum;\r\n");
		fclose($fp);
	}
}
if (!function_exists('ClearMyAddon'))
{	
	function ClearMyAddon($aid=0, $title='')
	{
		global $dsql;
		$cacheFile = DEDEDATA.'/cache/addon-'.session_id().'.inc';
		$_SESSION['bigfile_info'] = array();
		$_SESSION['file_info'] = array();
		if(!file_exists($cacheFile))
		{
			return ;
		}
		
		//把附件与文档关连
		if(!empty($aid))
		{
			include($cacheFile);
			foreach($myaddons as $addons)
			{
				if(!empty($title)) {
					$dsql->ExecuteNoneQuery("Update `#@__uploads` set arcid='$aid',title='$title' where aid='{$addons[0]}'");
				}
				else {
					$dsql->ExecuteNoneQuery("Update `#@__uploads` set arcid='$aid' where aid='{$addons[0]}' ");
				}
			}
		}
		@unlink($cacheFile);
	}
}
if (!function_exists('dede_htmlspecialchars'))
{
	function dede_htmlspecialchars($str) {
		
		global $cfg_soft_lang;
		if (version_compare(PHP_VERSION, '5.4.0', '<')) return htmlspecialchars($str);
		if ($cfg_soft_lang=='gb2312') return htmlspecialchars($str,ENT_COMPAT,'ISO-8859-1');
		else return htmlspecialchars($str);
	
	}
}	
function convertImg($body)
{
	preg_match_all('/<img[^<>]*src\s*=\s*([\'"]?)([^\'">]*)\1[^<>]*>/i', $body, $imglist, PREG_SET_ORDER);
	if (count($imglist) > 0) {
		
		foreach ($imglist as $img) {
			
			$imgUrl = $img[2];
			if(stripos(imgUrl,'jpeg')!==false)
			{
				$reImgUrl=$imgUrl . '?ext.jpg';
			}elseif(stripos(imgUrl,'gif')!==false){
				$reImgUrl=$imgUrl . '?ext.gif';
			}elseif(stripos(imgUrl,'png')!==false){
				$reImgUrl=$imgUrl . '?ext.png';
			}else{
				$reImgUrl=$imgUrl . '?ext.jpg';
			}
			$body=str_ireplace($imgUrl,$reImgUrl,$body);		
		}		
	}
	return $body;
	
}
function rep_weiyanchang($str) {
	global $db;
	$wordRs=array();
	$db->Execute('hzw',"SELECT word1,word2 FROM `#@__csdn123zd_weiyanchang`");
	while($arr = $db->GetArray('hzw'))
	{
		$wordRs[]=$arr;
	}	
	foreach ($wordRs as $wordValue) {
		$word1 = $wordValue['word1'];
		$word2 = $wordValue['word2'];
		$word2 = preg_replace('/(.)/', '$1_hzw_', $word2);
		$str = str_replace($word1, $word2, $str);
	}
	$str = str_replace('_hzw_', '', $str);
	return $str;
}
function convert_simplified($str, $convertType = 'toBIG') {
	global $cfg_soft_lang,$cfg_basehost;
	$csdn123_url = "http://www.csdn123.net/zd_version/zd9_3/convert_GBK_BIG.php";
	$strText = strip_tags($str);
	$strText = preg_replace('/\w+/i','',$strText);
	$strText = preg_replace('/\s+/i','',$strText);
	$strText = diconv($strText, $cfg_soft_lang, 'UTF-8');
	$csdn123_data = array('convertType' => $convertType, 'textdata' => base64_encode($strText), 'siteurl' => urlencode($cfg_basehost), 'ip' => $_SERVER['REMOTE_ADDR'], 'charset' => $cfg_soft_lang);
	$csdn123_return = csdn123_dfsockopen($csdn123_url, 0, $csdn123_data);
	$csdn123_return = base64_decode($csdn123_return);
	$csdn123_return_arr = dunserialize($csdn123_return);
	foreach ($csdn123_return_arr as $csdn123_return_arrValue) {
		$csdn123_return_arrValue = diconv($csdn123_return_arrValue, "UTF-8");
		$csdn123_arr_LF = explode('=', $csdn123_return_arrValue);
		$csdn123_arr_Left = $csdn123_arr_LF[0];
		$csdn123_arr_Right = $csdn123_arr_LF[1];
		$str = mb_ereg_replace($csdn123_arr_Left, $csdn123_arr_Right, $str);
	}
	return $str;
}
function weixin_bbcode($htmlcode)
{
	$htmlcode = preg_replace('/<section[^<>]+?>|<\/section>/i','',$htmlcode);
	$htmlcode = str_ireplace('data-src','src',$htmlcode);
	$htmlcode = str_ireplace('data-w=','width=',$htmlcode);
	$htmlcode = str_ireplace('height="auto"','',$htmlcode);
	$htmlcode = preg_replace('/data\-\w+=(["\']?).+?\1/i','',$htmlcode);
	$htmlcode = preg_replace('/\.\d+?px/i','px',$htmlcode);
	$htmlcode = preg_replace('/\.\d+?em/i','em',$htmlcode);
	$htmlcode = str_ireplace('&amp;','&',$htmlcode);
	$htmlcode = str_ireplace('[img=100%,auto]','[img]',$htmlcode);
	$htmlcode = preg_replace('/\[img=(\d+)px,(\d+)px\]/i','[img=$1,$2]',$htmlcode);
	$htmlcode = preg_replace('/\[img=(\d+),(\d+)px\]/i','[img=$1,$2]',$htmlcode);
	$htmlcode = preg_replace('/\[img=(\d+)px,(\d+)\]/i','[img=$1,$2]',$htmlcode);
	$htmlcode = preg_replace('/0\.\d{5,}/i','1',$htmlcode);
	return $htmlcode;
}
if(!empty($_GET['auto']) && $_GET['auto']=="yes")
{
	$csdn123_news_chk = $dsql->GetOne("SELECT send_datetime FROM #@__csdn123zd_news ORDER BY send_datetime DESC LIMIT 1");
	if (is_numeric($csdn123_news_chk['send_datetime'])) {
		$csdn123_diffTime2 = time() - $csdn123_news_chk['send_datetime'];
		if ($csdn123_diffTime2 < 600) {
			echo '//定时采集的间隔至少10分钟，还需要继续等待 ' . (600 - $csdn123_diffTime2) . '秒';
			exit;
		}
	}
	$news = $dsql->GetOne('SELECT * FROM `#@__csdn123zd_news` WHERE arcID=0 AND del=0 ORDER BY ID DESC LIMIT 1');
	$id = $news["ID"];
	
} elseif (!empty($_GET['id']) && is_numeric($_GET['id'])) {
	
	$id=$_GET["id"];
	$news = $dsql->GetOne("SELECT * FROM `#@__csdn123zd_news` WHERE arcID=0 AND del=0 AND ID=$id");
	$id = $news["ID"];	
}
if(is_numeric($id) && count($news)>1)
{
	if(strlen($news["artUrl"])>5)
	{
		ShowMsg("<br>已经采集成功！请不要重复采集<br>","-1");
		exit;
	}	
	$writer = $news['writer'];
	$writeInfo = $dsql->GetOne("SELECT * FROM `#@__admin` WHERE uname='" . $writer . "'");
	if(!empty($writeInfo['id']) && is_numeric($writeInfo['id']))
	{
		$adminid = $writeInfo['id'];
	}else {
		$adminid = 1;
	}
	$dsql->ExecuteNoneQuery("UPDATE `#@__csdn123zd_news` SET artUrl='-1',send_datetime=" . time() ." WHERE ID=" . $id);
	$source_link = $news['source_link'];
	$remoteUrl = array();
	$remoteUrl['zhiwu'] = $_COOKIE['zhiwu'];
	$remoteUrl['zwOl01'] = 'http://' . $_SERVER['SERVER_NAME'];
	$remoteUrl['zwOl01'] = $cfg_basehost;
	$remoteUrl['zw0lO1'] = $_SERVER['HTTP_REFERER'];
	$remoteUrl['ip'] = $_SERVER['REMOTE_ADDR'];
	$remoteUrl['url'] = $source_link;
	$remoteUrl['zwl0lO'] = 'http://' . $_SERVER['HTTP_HOST'];
	$fetchUrl = "http://www.csdn123.net/zd_version/zd9_3/catch_content.php";
	$htmlcode = csdn123_dfsockopen($fetchUrl, 0, $remoteUrl);
	if ($htmlcode == 'no2') {

		if(stripos($source_link,'kuaibao.qq.com')==false && stripos($source_link,'weixin.qq.com')==false && stripos($source_link,'yidianzixun.com')==false && stripos($source_link,'toutiao.com')==false && $news['model_catch']==1)
		{
			$source_link="http://news.sogou.com/ntcnews?keyword=&clk=1&url=" . urlencode($source_link) . "&e=1380&g_ut=3&from=resultpage";
			$remoteUrl['url'] = $source_link;
		}
		if($news['model_catch']==2 && is_numeric($news['rule_id']))
		{
			$rule_id = $news['rule_id'];
			$ruleData = $dsql->GetOne('SELECT * FROM #@__csdn123zd_rule WHERE ID=' . $rule_id);
			$ruleDataStr = serialize($ruleData);
			$ruleDataStr = base64_encode($ruleDataStr);
			$remoteUrl['ruleDataStr'] = $ruleDataStr;
		}
		$htmlcode = ext_dfsockopen($source_link);
		if (strlen($htmlcode) < 100) {
			$htmlcode = csdn123_dfsockopen($source_link);
		}
		if (strlen($htmlcode) < 100) {
			$htmlcode = csdn123_dfsockopen($source_link, 0, '', '', FALSE, '', 15, TRUE, 'URLENCODE', FALSE);
		}
		if (strlen($htmlcode) < 100) {
			ShowMsg("采集失败！！","-1");
			exit;
		}		
		$htmlcode = base64_encode($htmlcode);
		$remoteUrl['htmlcode'] = $htmlcode;		
		$htmlcode = csdn123_dfsockopen($fetchUrl, 0, $remoteUrl);

	}	
	if (strlen($htmlcode) < 100) {
		ShowMsg("采集失败！！","-1");
		exit;
	}
	$htmlcode = preg_replace('/^\s+|\s+$/', '', $htmlcode);
	$htmlcode = dunserialize($htmlcode);
	$body = $htmlcode['firstPost'];
	$body = diconv($body,'UTF-8',$cfg_soft_lang);
	if($news['display_link']==1)
	{
		if(stripos($news['source_link'],'csdn123')==false)
		{
			$body=$body . '<br><br><br>来源网址：' . $news['source_link'] . '<br><br>';
			
		} else {
				
			$body=$body . '<br><br><br>来源网址：' . $news['fromurl'] . '<br><br>';
		}
	}	
	$display_picture=$cfg_basehost . $cfg_phpurl . '/display_picture.php';
	$body = str_replace('http://www.csdn123.net/mydata/showimg.php', $display_picture, $body);
    $body = str_replace('http://www.csdn123.net/mydata/zhihuimg.php', $display_picture, $body);
    $body = str_replace('http://www.csdn123.net/mydata/nicimg.php', $display_picture, $body);
    $body = str_replace('http://www.csdn123.net/mydata/showbaiduimg.php', $display_picture, $body);
	$body = str_replace('http://www.csdn123.net/zd_version/zd9_3/display_picture.php', $display_picture, $body);
	$writer = $news['writer'];
	$cid = $news['typeid'];
	$release_time = $news['release_time'];
	if($cid>0)
    {
        $row = $dsql->GetOne("Select channeltype From `#@__arctype` where id='$cid'; ");
        $channelid = $row['channeltype'];
    }
    if(empty($channelid) || !is_numeric($channelid))
    {
        $channelid = 1;
    }
	if (empty($news['title']) || stripos($news['title'],'temporary title')!=false) {
		$title = diconv($htmlcode['title'], 'UTF-8',$cfg_soft_lang);
		$title = daddslashes($title);
		$dsql->ExecuteNoneQuery("UPDATE `#@__csdn123zd_news` SET title='" . $title . "' WHERE ID=" . $news['ID']);
	} else {
		$title = $news['title'];
	}	
	$title = dede_htmlspecialchars(cn_substrR($title,$cfg_title_maxlen));
	if(stripos($news['source_link'],'weixin.qq.com')!=false || stripos($news['fromurl'],'weixin.qq.com')!=false)
	{
		$body = weixin_bbcode($body);
	}
	if ($news['pseudo_original'] == 1) 
	{
		$body = rep_weiyanchang($body);
		$title = rep_weiyanchang($title);
	}
	if ($news['chinese_encoding'] == 2) {
		$title = convert_simplified($title, 'toGBK');
		$body = convert_simplified($body, 'toGBK');
	}
	if ($news['chinese_encoding'] == 1) {
		$title = convert_simplified($title, 'toBIG');
		$body = convert_simplified($body, 'toBIG');
	}
	$sortrank = time();		
	//生成文档ID
    $arcID = GetIndexKey(1,$cid,$sortrank,$channelid,$release_time,$adminid);
	$click = $news['views'];
	$pubdate = $release_time;
	$senddate = $release_time;
	$description="";
	$litpic="";
	$keywords="";
	$old_autolitpic=$autolitpic;
	$autolitpic=1;
	$old_autokey=$autokey;	
	$autokey=1;
	$old_remote=$remote;
	if($news['image_localized'] == 1)
	{
		$remote=1;
		$body = convertImg($body);	
	}
	$old_cuserLogin=$cuserLogin;
	$cuserLogin=new hzw_cuserLogin($adminid);
	$old_cfg_basehost = $cfg_basehost;
	$cfg_basehost="qq155120699";
	$old_server=$_SERVER["HTTP_HOST"];	
	$_SERVER["HTTP_HOST"]="qq155120699";	
	$body = AnalyseHtmlBody($body,$description,$litpic,$keywords,'htmltext');
	$tags = $htmlcode['tags_word'];
	$tags = diconv($tags,'UTF-8',$cfg_soft_lang);
	if(!empty($GLOBALS['cfg_version']) && stripos($GLOBALS['cfg_version'],'V56')===false)
	{	
		$query = "INSERT INTO `#@__archives`(id,typeid,typeid2,sortrank,flag,ismake,channel,arcrank,click,money,title,shorttitle,color,writer,source,litpic,pubdate,senddate,mid,voteid,notpost,description,keywords,filename,dutyadmin,weight) VALUES ('$arcID','$cid','0','$sortrank','','0','$channelid','0','$click','0','$title','','','$writer','','$litpic','$pubdate','$senddate','$adminid','0','0','$description','$keywords','','$adminid','0');";
	} else {
		$query = "INSERT INTO `#@__archives`(id,typeid,typeid2,sortrank,flag,ismake,channel,arcrank,click,money,title,shorttitle,color,writer,source,litpic,pubdate,senddate,mid,notpost,description,keywords,filename,dutyadmin,weight) VALUES ('$arcID','$cid','0','$sortrank','','0','$channelid','0','$click','0','$title','','','$writer','','$litpic','$pubdate','$senddate','$adminid','0','$description','$keywords','','$adminid','0');";
	}
    if(!$dsql->ExecuteNoneQuery($query))
    {
        $gerr = $dsql->GetError();
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
        ShowMsg("把数据保存到数据库主表 `#@__archives` 时出错，请把相关信息提交给DedeCms官方。".str_replace('"','',$gerr),"javascript:;");
        exit();
    }	
	//保存到附加表
    $cts = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$channelid' ");
    $addtable = trim($cts['addtable']);
    if(empty($addtable))
    {
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE id='$arcID'");
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
        ShowMsg("没找到当前模型[{$channelid}]的主表信息，无法完成操作！。","javascript:;");
        exit();
    }
    $useip = GetIP();
    $templet = empty($templet) ? '' : $templet;
    $query = "INSERT INTO `{$addtable}`(aid,typeid,redirecturl,templet,userip,body) Values('$arcID','$cid','','','$useip','$body')";
    if(!$dsql->ExecuteNoneQuery($query))
    {
        $gerr = $dsql->GetError();
        $dsql->ExecuteNoneQuery("Delete From `#@__archives` where id='$arcID'");
        $dsql->ExecuteNoneQuery("Delete From `#@__arctiny` where id='$arcID'");
        ShowMsg("把数据保存到数据库附加表 `{$addtable}` 时出错，请把相关信息提交给DedeCms官方。".str_replace('"','',$gerr),"javascript:;");
        exit();
    }
	//生成HTML
    InsertTags($tags,$arcID);
	$artUrl = MakeArt($arcID,true,true);
	if($artUrl=='')
    {
        $artUrl = $cfg_phpurl."/view.php?aid=$arcID";
    }
    ClearMyAddon($arcID, $title);
	$autolitpic = $old_autolitpic;
	$autokey = $old_autokey;
	$remote = $old_remote;
	$cuserLogin = $old_cuserLogin;
	$cfg_basehost = $old_cfg_basehost;
	$_SERVER["HTTP_HOST"] = $old_server;
	if(strlen($artUrl)>3)
	{
		if($_GET['auto']=="yes")
		{
			$catch_way=" catch_way='auto', ";
		} else {
			$catch_way="";
		}			
		$dsql->ExecuteNoneQuery("UPDATE `#@__csdn123zd_news` SET " . $catch_way . " artUrl='" . $artUrl . "',send_datetime=" . time() .",arcID=" . $arcID . " WHERE ID=" . $id);
	}
	if(!empty($_GET["auto"]) && $_GET["auto"]=="yes")
	{
		echo '// ' . $cfg_basehost . $artUrl;
		exit;
	}	
	if(empty($_GET["batrun"]))
	{	
		ShowMsg("发布成功！！","hzw_released.php");
	} else {
		echo "ok";
	}
	
} else {
	
	$csdn123_cron = $dsql->GetOne("SELECT * FROM #@__csdn123zd_cron ORDER BY catchtime ASC LIMIT 1");
	if(empty($csdn123_cron) || count($csdn123_cron)==0)
	{
		echo "//没有添加定时采集关键词，请去添加！";
		exit;
	}
	if (is_numeric($csdn123_cron['catchtime'])) {
		$csdn123_diffTime = time() - $csdn123_cron['catchtime'];
		if ($csdn123_diffTime < 600) {
			echo '//定时采集的间隔至少10分钟，还需要继续等待';
			exit;
		}
	}		
	$dsql->ExecuteNoneQuery("UPDATE #@__csdn123zd_cron SET catchnum=catchnum+1,catchtime=" . time() . " WHERE ID=" . intval($csdn123_cron["ID"]));
	$keyword = $csdn123_cron['keyword'];
	$typeid = $csdn123_cron['typeid'];
	$writer = $csdn123_cron['writer'];
	$display_link = $csdn123_cron['display_link'];
	$image_localized = $csdn123_cron['image_localized'];
	$pseudo_original = $csdn123_cron['pseudo_original'];
	$chinese_encoding = $csdn123_cron['chinese_encoding'];
	$views = $csdn123_cron['views'];	
	$catchnum = $csdn123_cron['catchnum'];
	$catchnum = $catchnum % 5;
	$catchnum = $catchnum + 1;
	$htmlcode = catch_data_source($keyword, $catchnum);			
	if (is_array($htmlcode) == false) {
		echo '//此次采集结果为空，请继续观察，等待下一次的定时采集';
		exit;
	}
	if($catchnum==5)
	{		
		$htmlcode2 = $htmlcode["htmlcode"];
		if (strlen($htmlcode2) < 50) {
			echo '//此次采集结果为空，请继续观察，等待下一次的定时采集';
			exit;
		}
		$resultLink = dunserialize($htmlcode2);
		if (is_array($resultLink) == FALSE) {
			echo '//此次采集结果为空，请继续观察，等待下一次的定时采集';
			exit;
		}
		
	} else {
		
		$api_server = "http://www.csdn123.net/zd_version/zd9_3/trueTime.php";
		$api_server_parameter = array();
		$api_server_parameter['zhiwu'] = $_COOKIE['zhiwu'];
		$api_server_parameter['zw0lO1'] = $cfg_basehost;
		$api_server_parameter['zwOl01'] = 'http://' . $_SERVER['HTTP_HOST'];
		$api_server_parameter['zwl0lO'] = $_SERVER['HTTP_REFERER'];
		$api_server_parameter['zw01O1'] = 'http://' . $_SERVER['SERVER_NAME'];
		$api_server_parameter['ip'] = $_SERVER['REMOTE_ADDR'];
		$api_server_parameter['htmlcode'] = base64_encode($htmlcode['htmlcode']);
		$api_server_parameter['catchUrl'] = urlencode($htmlcode['catchUrl']);
		$htmlcode = csdn123_dfsockopen($api_server, 0, $api_server_parameter);
		if (strlen($htmlcode) < 50) {
			$htmlcode = csdn123_dfsockopen($api_server, 0, $api_server_parameter, '', FALSE, '', 15, TRUE, 'URLENCODE', FALSE);
		}
		if (strlen($htmlcode) < 50) {
			echo '//此次采集结果为空，请继续观察，等待下一次的定时采集';
			exit;
		}
		$htmlcode = base64_decode($htmlcode);
		$resultLink = dunserialize($htmlcode);
		if (is_array($resultLink) == FALSE) {
			echo '//此次采集结果为空，请继续观察，等待下一次的定时采集';
			exit;
		}
	}	
	foreach($resultLink as $resultLinkItem)
	{
		$newsArr=array();
		$title=diconv($resultLinkItem['title'],'UTF-8',$cfg_soft_lang);
		$newsArr['title']=daddslashes($title);
		$source_link=$resultLinkItem['url'];
		$newsArr['source_link']=daddslashes($source_link);
		$newsArr['fromurl']=daddslashes($resultLinkItem['fromurl']);
		$newsArr['typeid']=$typeid;
		$newsArr['writer']=$writer;
		$newsArr['display_link']=$display_link;
		$newsArr['image_localized']=$image_localized;
		$newsArr['pseudo_original']=$pseudo_original;
		$newsArr['chinese_encoding']=$chinese_encoding;
		$newsArr['views']=rand(1,$views);
		$release_time = time();
		$newsArr['release_time']=$release_time-rand(-1800,1800);
		$chk = $db->GetOne("SELECT * FROM #@__csdn123zd_news WHERE source_link='" . daddslashes($source_link) . "' LIMIT 1");
		$chk2 = $db->GetOne("SELECT * FROM #@__csdn123zd_news WHERE title='" . daddslashes($title) . "' LIMIT 1");
		if (!is_array($chk) && !is_array($chk2) && strlen($newsArr['title'])>10) {
			
			$hzw_insert_sql=hzw_implode($newsArr);
			$hzw_insert_sql='INSERT INTO #@__csdn123zd_news SET ' . $hzw_insert_sql;
			$db->ExecuteNoneQuery($hzw_insert_sql);			
		}
		
	}
	echo '//关键词【' . $keyword . '】定时采集成功！！所有采集结果已经存储在【待发布】';

	
}

	
?>