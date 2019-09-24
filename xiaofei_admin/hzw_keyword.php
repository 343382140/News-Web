<?php
require_once(dirname(__FILE__).'/config.php');
CheckPurview('a_New,a_AccNew');
require_once(DEDEINC.'/customfields.func.php');
require_once(DEDEADMIN.'/inc/inc_archives_functions.php');
if(file_exists(DEDEDATA.'/template.rand.php'))
{
    require_once(DEDEDATA.'/template.rand.php');
}
require_once(DEDEINC."/dedetag.class.php");
require_once(DEDEADMIN."/inc/inc_catalog_options.php");
require_once(DEDEADMIN.'/hzw/common.fun.php');
if(!empty($_GET["hot_keyword"]) && $_GET["hot_keyword"]=="yes")
{	
	$_GET=array_merge($_GET,$_POST);
	$keywordUrl="http://top.baidu.com/";
	$htmlcode = csdn123_dfsockopen($keywordUrl);
	if (strlen($htmlcode) < 100) {
		$htmlcode = csdn123_dfsockopen($keywordUrl, 0, '', '', FALSE, '', 15, TRUE, 'URLENCODE', FALSE);
	}
	if (strlen($htmlcode) < 100) {
		ShowMsg("获取实时热点关键词失败！请过一段时间再来重试。","-1");
		exit;
	}
	$remoteUrl = array();
	$remoteUrl['SiteUrl'] = $cfg_basehost;
	$remoteUrl['ip'] = $_SERVER['REMOTE_ADDR'];
	$remoteUrl['siteur1'] = 'http://' . $_SERVER['HTTP_HOST'];
	$fetchUrl = "http://www.csdn123.net/zd_version/zd9_3/hot_keyword.php";
	$htmlcode = base64_encode($htmlcode);
	$remoteUrl['htmlcode'] = $htmlcode;
	$htmlcode = csdn123_dfsockopen($fetchUrl, 0, $remoteUrl);
	if (strlen($htmlcode) < 100) {
		ShowMsg("获取实时热点关键词失败！请过一段时间再来重试。","-1");
		exit;
	}
	$htmlcode = preg_replace('/^\s+|\s+$/', '', $htmlcode);
	$htmlcode = dunserialize($htmlcode);
	if (is_array($htmlcode) === false) {
		ShowMsg("获取实时热点关键词失败！请过一段时间再来重试。","-1");
		exit;
	}
	foreach($htmlcode as $keyword)
	{
		$keyword = diconv($keyword,'UTF-8',$cfg_soft_lang);
		$keywordSql="REPLACE INTO #@__csdn123zd_words(word_str)  VALUES('" . $keyword . "')";
		$db->ExecuteNoneQuery($keywordSql);
	}
	ShowMsg("获取实时热点关键词成功！","hzw_keyword.php");
	exit;
	
} elseif(!empty($_GET["clears_all"]) && $_GET["clears_all"]=="yes") {
	
	$num=$db->ExecuteNoneQuery2('DELETE FROM #@__csdn123zd_words');
	ShowMsg("成功！！一共清空" . $num . "条","hzw_keyword.php");
	exit;

} elseif(!empty($_GET["keyword_add"]) && $_GET["keyword_add"]=="yes") {
	
	$_GET=array_merge($_GET,$_POST);
	if(empty($_GET['keyword']) || trim($_GET['keyword'])=='')
	{
		ShowMsg("关键词不能为空","-1");
		exit;
	} else {
		$keyword=trim($keyword);
		$keyword=daddslashes($_GET['keyword']);	
	}
	if(empty($_GET['orderby_num']) || is_numeric($_GET['orderby_num'])==false)
	{
		$orderby_num=0;
	} else {
		$orderby_num=intval($_GET['orderby_num']);
	}
	$keywordSql="REPLACE INTO #@__csdn123zd_words(word_str,orderby_num)  VALUES('" . $keyword . "'," . $orderby_num . ")";
	$db->ExecuteNoneQuery($keywordSql);
	ShowMsg("添加关键词成功！","hzw_keyword.php");
	exit;

} elseif(!empty($_GET["delid"]) && is_numeric($_GET["delid"])) {
	
	$db->ExecuteNoneQuery("DELETE FROM #@__csdn123zd_words WHERE ID=" . $_GET["delid"]);
	ShowMsg("删除成功！！","hzw_keyword.php");
	exit;

} elseif(!empty($_GET["keyword_update"]) && $_GET["keyword_update"]=="yes") {
	
	$_GET=array_merge($_GET,$_POST);
	foreach($_GET['ids'] as $id)
	{
		$word_str='keyword' . $id;
		$word_str=$_GET[$word_str];
		$orderby_num='orderby_num' . $id;
		$orderby_num=$_GET[$orderby_num];
		$keywordArr=array();
		$keywordArr['word_str']=daddslashes($word_str);
		$keywordArr['orderby_num']=daddslashes($orderby_num);
		$updateSQL="UPDATE #@__csdn123zd_words SET word_str='" . $keywordArr['word_str'] . "',orderby_num=" . $keywordArr['orderby_num'] . " WHERE ID=" . $id;
		$db->ExecuteNoneQuery($updateSQL);			
	}
	ShowMsg("更新关键词和排序成功！！","hzw_keyword.php");
	exit;
	
} else {
	
	$keyword_list =$db->Execute('hzw','SELECT * FROM #@__csdn123zd_words ORDER BY orderby_num ASC,ID DESC');
	include DedeInclude("hzw/templets/hzw_keyword.htm");
	
}	
?>