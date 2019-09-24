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
	require_once(DEDEADMIN.'/hzw/common.fun.php');	
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
	$release_time=$_GET['release_time'];
	$release_time=strtotime($release_time);
	if(is_numeric($release_time)==FALSE || $release_time<10000)
	{
		$release_time_start=time() - 3600;
		$release_time=rand($release_time_start,time());
	}	
	$api_server="http://www.csdn123.net/zd_version/zd9_3/server_batch.php?";
	$api_server_parameter=array();
	$api_server_parameter['zhiwu'] = $_COOKIE['zhiwu'];
	$api_server_parameter['zw0lO1'] = $cfg_basehost;
	$api_server_parameter['zwOl01'] = $_SERVER['HTTP_REFERER'];
	$api_server_parameter['zwl0lO'] = 'http://' . $_SERVER['SERVER_NAME'];
	$api_server_parameter['zw01O1'] = 'http://' . $_SERVER['HTTP_HOST'];
	$api_server_parameter['ip'] = $_SERVER['REMOTE_ADDR'];
	$keyword = diconv($keyword,$cfg_soft_lang,'UTF-8');
	$api_server_parameter['query'] = $keyword;
	$api_server_parameter['number_collected'] = $_GET['number_collected'];	
	$api_server = $api_server . http_build_query($api_server_parameter);
	$htmlcode = csdn123_dfsockopen($api_server);
	if (strlen($htmlcode) < 50) {
		$htmlcode = csdn123_dfsockopen($api_server, 0, '', '', FALSE, '', 15, TRUE, 'URLENCODE', FALSE);
	}
	if (strlen($htmlcode) < 50) {
		ShowMsg("抱歉！采集结果为空，请尝试更换另外一个关键词或者其它设置。","-1");
	}
	$resultLink=dunserialize($htmlcode);
	if(is_array($resultLink)==FALSE)
	{
		ShowMsg("抱歉！采集结果为空，请尝试更换另外一个关键词或者其它设置。","-1");
	}	
	$display_link=intval($_GET['display_link']);
	$image_localized=intval($_GET['image_localized']);
	$pseudo_original=intval($_GET['pseudo_original']);
	$chinese_encoding=intval($_GET['chinese_encoding']);
	$group_fid=intval($_GET['group_fid']);
	$views=intval($_GET['views']);
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
		$newsArr['release_time']=$release_time-rand(-1800,1800);
		$chk = $db->GetOne("SELECT * FROM #@__csdn123zd_news WHERE source_link='" . daddslashes($source_link) . "' LIMIT 1");
		$chk2 = $db->GetOne("SELECT * FROM #@__csdn123zd_news WHERE title='" . daddslashes($title) . "' LIMIT 1");
		if (!is_array($chk) && !is_array($chk2)) {
			
			$hzw_insert_sql=hzw_implode($newsArr);
			$hzw_insert_sql='INSERT INTO #@__csdn123zd_news SET ' . $hzw_insert_sql;
			$db->ExecuteNoneQuery($hzw_insert_sql);			
		}
		
	}
	ShowMsg("批量采集成功！！","hzw_pending.php");	
	
}else {

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
	$server_url = "?";
	$preKeywordPage=$server_url . "&keyword_page=" . ($keyword_page - 1);
	$nextKeywordPage=$server_url . "&keyword_page=" . ($keyword_page + 1);
	include DedeInclude("hzw/templets/hzw_batch.htm");	
	
}

	
?>