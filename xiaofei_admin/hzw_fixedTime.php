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
function show_keywords($page=1)
{
	global $db;
	$page=max(1,$page);
	$start_num = ($page-1) * 5;
	$db->Execute('hzw',"SELECT word_str FROM #@__csdn123zd_words ORDER BY orderby_num ASC,ID DESC LIMIT " . $start_num . ",5");
	while($arr = $db->GetArray("hzw")) {
		$keywordArr[]=$arr;
	}
	return $keywordArr;	
}
if(!empty($_GET["add"]) && $_GET["add"]=="yes")
{
	ClearMyAddon();
	$channelid = empty($channelid) ? 0 : intval($channelid);
	$cid = empty($cid) ? 0 : intval($cid);
	if(empty($geturl)) $geturl = '';  
	$keywords = $writer = $source = $body = $description = $title = '';
    if($cid>0 && $channelid==0)
    {
        $row = $dsql->GetOne("Select channeltype From `#@__arctype` where id='$cid'; ");
        $channelid = $row['channeltype'];
    }
    else
    {
        if($channelid==0)
        {
            $channelid = 1;
        }
    }

    $cInfos = $dsql->GetOne(" Select * From  `#@__channeltype` where id='$channelid' ");    
    $maxWright = $dsql->GetOne("SELECT COUNT(*) AS cc FROM #@__archives");
	if(empty($_SESSION['dede_admin_name']))
	{
		$writer="admin";
	}else{
		$writer=$_SESSION['dede_admin_name'];
	}
	if(empty($_GET['keyword_page']) || is_numeric($_GET['keyword_page'])==false)
	{
		$keyword_page=1;
	} else {
		$keyword_page=$_GET['keyword_page'];
	}
	$keywordArr=show_keywords($keyword_page);
	$server_url = "?add=yes";
	$preKeywordPage=$server_url . "&keyword_page=" . ($keyword_page - 1);
	$nextKeywordPage=$server_url . "&keyword_page=" . ($keyword_page + 1);
	include DedeInclude("hzw/templets/hzw_fixedTime_add.htm");	

}elseif(!empty($_GET["del"]) && is_numeric($_GET["del"])) {	

	$delid=$_GET["del"];
	$db->ExecuteNoneQuery('DELETE FROM #@__csdn123zd_cron WHERE ID=' . $delid);
	ShowMsg("删除成功！","hzw_fixedTime.php");
	exit;

}elseif(!empty($_GET["update_id"]) && is_numeric($_GET["update_id"])) {

	$id = $_GET["update_id"];
	$arr = $db->GetOne("SELECT * FROM #@__csdn123zd_cron WHERE ID =" . $id);
	$cid = $arr['typeid'];
	ClearMyAddon();
	$channelid = empty($channelid) ? 0 : intval($channelid);
	$cid = empty($cid) ? 0 : intval($cid);
	if(empty($geturl)) $geturl = '';  
	$keywords = $writer = $source = $body = $description = $title = '';
    if($cid>0 && $channelid==0)
    {
        $row = $dsql->GetOne("Select channeltype From `#@__arctype` where id='$cid'; ");
        $channelid = $row['channeltype'];
    }
    else
    {
        if($channelid==0)
        {
            $channelid = 1;
        }
    }
    $cInfos = $dsql->GetOne(" Select * From  `#@__channeltype` where id='$channelid' ");
	include DedeInclude("hzw/templets/hzw_fixedTime_update.htm");
	
}elseif(!empty($_GET["clears_all"]) && $_GET["clears_all"]=="yes"){
	
	$num=$db->ExecuteNoneQuery2('DELETE FROM #@__csdn123zd_cron');
	ShowMsg("成功清空" . $num . "条","hzw_fixedTime.php");
	exit;
	
}elseif(!empty($_GET["fixedTime_add"]) && $_GET["fixedTime_add"]=="yes"){
	
	$_GET=array_merge($_GET,$_POST);
	if(empty($_GET['keyword']))
	{
		ShowMsg("关键词为空！","-1");
		exit;
		
	} else {
		$keyword=$_GET['keyword'];
	}
	if(empty($_GET['typeid']) || !is_numeric($_GET['typeid']))
	{
		ShowMsg("文章主栏目错误！！","-1");
		exit;
		
	} else {
		$typeid=intval($_GET['typeid']);
	}
	if($typeid==0)
	{
		ShowMsg("请选择文章主栏目！！","-1");
		exit;
	}	
	if(empty($_GET['writer']))
	{
		$writer="admin";
	} else {
		$writer=$_GET['writer'];
	}
	$typeid=intval($_GET['typeid']);
	$display_link=intval($_GET['display_link']);
	$image_localized=intval($_GET['image_localized']);
	$pseudo_original=intval($_GET['pseudo_original']);
	$chinese_encoding=intval($_GET['chinese_encoding']);
	$views=intval($_GET['views']);
	$cronArr=array();
	$cronArr=array();
	$cronArr['keyword']=daddslashes($keyword);
	$cronArr['typeid']=$typeid;
	$cronArr['writer']=$writer;
	$cronArr['display_link']=$display_link;
	$cronArr['image_localized']=$image_localized;
	$cronArr['pseudo_original']=$pseudo_original;
	$cronArr['chinese_encoding']=$chinese_encoding;
	$cronArr['views']=$views;
	if(empty($_GET['modify_id']))
	{
		$hzw_insert_sql=hzw_implode($cronArr);
		$hzw_insert_sql='INSERT INTO #@__csdn123zd_cron SET ' . $hzw_insert_sql;
		$db->ExecuteNoneQuery($hzw_insert_sql);
		ShowMsg("添加定时采集关键词成功！","hzw_fixedTime.php");
		exit;
		
	} else {
		
		$hzw_update_sql = hzw_implode($cronArr);
		$hzw_update_sql = "UPDATE #@__csdn123zd_cron SET " . $hzw_update_sql . " WHERE ID=" . $_GET['modify_id'];
		$db->ExecuteNoneQuery($hzw_update_sql);
		ShowMsg("修改定时采集关键词成功！","hzw_fixedTime.php");
		exit;
	}
	
}else {
	
	$hzw_dedeadmin=basename(DEDEADMIN);
	$cronUrl = $cfg_basehost . $cfg_cmspath . '/' . $hzw_dedeadmin . '/hzw_send_archives.php?auto=yes';
	$db->Execute('hzw',"SELECT * FROM #@__csdn123zd_cron ORDER BY ID DESC");
	$pendingRs = $dsql->GetOne('SELECT count(*) as num FROM `#@__csdn123zd_news` WHERE arcID=0 AND del=0');
	$pendingCount = $pendingRs['num'];
	$autoSendRs = $dsql->GetOne("SELECT count(*) as num FROM `#@__csdn123zd_news` WHERE catch_way='auto' AND arcID>0");
	$autoSendCount = $autoSendRs['num'];	
	include DedeInclude("hzw/templets/hzw_fixedTime.htm");	
	
}

	
?>