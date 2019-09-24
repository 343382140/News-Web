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
if(!empty($_GET["add"]) && $_GET["add"]=="yes")
{
	$_GET=array_merge($_GET,$_POST);
	if(empty($_GET['column_url']) || stripos($_GET['column_url'],'http')===false)
	{
		ShowMsg("栏目页的地址不能为空或者输入不是一个网址","-1");
		exit;
		
	} else {
		$column_url=$_GET['column_url'];
		$column_url=trim($column_url);
	}
	if(empty($_GET['needstr']))
	{
		ShowMsg("请输入网址必须包含的字符，如：http,htm等","-1");
		exit;
	}
	$needstr=trim($_GET['needstr']);
	if($_GET['model_catch']==2 && $_GET['rule_id']==0)
	{
		ShowMsg("请选择一个指定规则","-1");
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
	$htmlcode = csdn123_dfsockopen($column_url);
	if (strlen($htmlcode) < 50) {
		$htmlcode = csdn123_dfsockopen($column_url, 0, '', '', FALSE, '', 15, TRUE, 'URLENCODE', FALSE);
	}
	if (strlen($htmlcode) < 50) {
		ShowMsg("抱歉！采集结果为空，请尝试更换另外一个关键词或者其它设置。","-1");
		exit;
	}
	$api_server="http://www.csdn123.net/zd_version/zd9_3/url.php";
	$api_server_parameter=array();
	$api_server_parameter['zhiwu'] = $_COOKIE['zhiwu'];
	$api_server_parameter['zw0lO1'] = $cfg_basehost;
	$api_server_parameter['zwOl01'] = 'http://' . $_SERVER['SERVER_NAME'];
	$api_server_parameter['SlteUrl'] = 'http://' . $_SERVER['HTTP_HOST'];
	$api_server_parameter['ip'] = $_SERVER['REMOTE_ADDR'];
	$api_server_parameter['zwl0lO'] = $_SERVER['HTTP_REFERER'];
	$api_server_parameter['zw01O1'] = 'http://' . $_SERVER['HTTP_HOST'];
	$api_server_parameter['htmlcode'] = base64_encode($htmlcode);
	$api_server_parameter['needstr'] = $needstr;
	$api_server_parameter['fromurl'] = $column_url;
	$htmlcode = csdn123_dfsockopen($api_server,0,$api_server_parameter);
	if (strlen($htmlcode) < 50) {
		$htmlcode = csdn123_dfsockopen($api_server, 0, $api_server_parameter, '', FALSE, '', 15, TRUE, 'URLENCODE', FALSE);
	}
	if (strlen($htmlcode) < 50) {
		ShowMsg("抱歉！采集结果为空，请尝试更换另外一个关键词或者其它设置。","-1");
		exit;
	}	
	$htmlcode = base64_decode($htmlcode);
	$resultLink = dunserialize($htmlcode);
	if(is_array($resultLink)==FALSE)
	{
		ShowMsg("抱歉！采集结果为空，请尝试更换另外一个关键词或者其它设置。","-1");
		exit;
	}
	$display_link=intval($_GET['display_link']);
	$image_localized=intval($_GET['image_localized']);
	$pseudo_original=intval($_GET['pseudo_original']);
	$chinese_encoding=intval($_GET['chinese_encoding']);
	$views=intval($_GET['views']);
	$model_catch=intval($_GET['model_catch']);
	$rule_id=intval($_GET['rule_id']);
	$webCodeCorrection=daddslashes($_GET['webCodeCorrection']);
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
		$newsArr['model_catch']=$model_catch;
		$newsArr['rule_id']=$rule_id;
		$newsArr['release_time']=$release_time-rand(-1800,1800);
		$chk = $db->GetOne("SELECT * FROM #@__csdn123zd_news WHERE source_link='" . daddslashes($source_link) . "' LIMIT 1");
		$chk2 = $db->GetOne("SELECT * FROM #@__csdn123zd_news WHERE title='" . daddslashes($title) . "' LIMIT 1");
		if (!is_array($chk) && !is_array($chk2) && strlen($newsArr['title'])>10) {
			
			$hzw_insert_sql=hzw_implode($newsArr);			
			$hzw_insert_sql='INSERT INTO #@__csdn123zd_news SET ' . $hzw_insert_sql;
			$db->ExecuteNoneQuery($hzw_insert_sql);			
		}
		
	}
	ShowMsg("网址采集成功！！","hzw_pending.php");	
	
} elseif(!empty($_GET['deleteRuleId']) && is_numeric($_GET['deleteRuleId'])) {
	
	$deleteRuleId = $_GET['deleteRuleId'];
	$db->ExecuteNoneQuery('DELETE FROM #@__csdn123zd_rule WHERE ID=' . $deleteRuleId);
	ShowMsg("删除成功！！","hzw_url.php?admin_rule=yes");
	
} elseif(!empty($_GET['admin_rule']) && $_GET['admin_rule']=='yes') {
	
	$rule_list = $db->Execute('hzw','SELECT * FROM #@__csdn123zd_rule ORDER BY ID DESC');
	include DedeInclude("hzw/templets/hzw_url_AdminRule.htm");
	
	
} elseif(!empty($_GET['submitImportRule']) && $_GET['submitImportRule']=='yes') {
	
	$_GET=array_merge($_GET,$_POST);
	if(empty($_GET['get_ruleData']))
	{	
		$rule_data=$_GET['rule_data'];
	} else {
		$rule_data=csdn123_dfsockopen("http://www.csdn123.net/zd_version/zd9_3/ruleData.php");
	}
	$rule_data=preg_replace('/\s+/','',$rule_data);
	$rule_data=base64_decode($rule_data);
	$rule_arr=dunserialize($rule_data);
	if(!is_array($rule_arr))
	{
		ShowMsg("失败！！","");
	}
	$rule_charset=$rule_arr['charset'];
	foreach($rule_arr['ruleData'] as $rule_value)
	{
		$rule_name=diconv($rule_value['rule_name'],$rule_charset,$cfg_soft_lang);
		$title01=diconv($rule_value['title01'],$rule_charset,$cfg_soft_lang);
		$title02=diconv($rule_value['title02'],$rule_charset,$cfg_soft_lang);
		$title03=diconv($rule_value['title03'],$rule_charset,$cfg_soft_lang);
		$content01=diconv($rule_value['content01'],$rule_charset,$cfg_soft_lang);
		$content02=diconv($rule_value['content02'],$rule_charset,$cfg_soft_lang);
		$content03=diconv($rule_value['content03'],$rule_charset,$cfg_soft_lang);
		$replace01=diconv($rule_value['replace01'],$rule_charset,$cfg_soft_lang);
		$replace02=diconv($rule_value['replace02'],$rule_charset,$cfg_soft_lang);
		$replace03=diconv($rule_value['replace03'],$rule_charset,$cfg_soft_lang);		
		$rule_remark=diconv($rule_value['rule_remark'],$rule_charset,$cfg_soft_lang);
		$addRuleArr=array();
		$addRuleArr['rule_name'] = $rule_name;
		$addRuleArr['title01'] =  $title01;
		$addRuleArr['title02'] =  $title02;
		$addRuleArr['title03'] =  $title03;
		$addRuleArr['content01'] =  $content01;
		$addRuleArr['content02'] =  $content02;
		$addRuleArr['content03'] =  $content03;
		$addRuleArr['replace01'] =  $replace01;
		$addRuleArr['replace02'] =  $replace02;
		$addRuleArr['replace03'] =  $replace03;		
		$addRuleArr['rule_remark'] =  $rule_remark;
		$hzw_insert_sql=hzw_implode($addRuleArr);
		$hzw_insert_sql='INSERT INTO #@__csdn123zd_rule SET ' . $hzw_insert_sql;
		$db->ExecuteNoneQuery($hzw_insert_sql);	
	}
	ShowMsg("成功！！","hzw_url.php?admin_rule=yes");
	
} elseif(!empty($_GET['import_rule']) && $_GET['import_rule']=='yes') {
	
	include DedeInclude("hzw/templets/hzw_url_RuleImport.htm");
	
} elseif(!empty($_GET['export_rule']) && $_GET['export_rule']=='yes') {
	
	$ruleRs=$db->Execute('hzw','SELECT * FROM #@__csdn123zd_rule ORDER BY ID DESC');
	$exportRuleArr=array();
	$exportRuleArr['charset']=$cfg_soft_lang;
	while($ruleItem = $db->GetArray("hzw"))
	{
		$exportRuleArr['ruleData'][]=$ruleItem;
	}
	$exportRuleStr=serialize($exportRuleArr);
	$exportRuleStr=base64_encode($exportRuleStr);	
	include DedeInclude("hzw/templets/hzw_url_RuleOutput.htm");
	
} elseif(!empty($_GET['clears_all_rule']) && $_GET['clears_all_rule']=='yes') {
	
	$db->ExecuteNoneQuery('DELETE FROM #@__csdn123zd_rule');
	ShowMsg("清空成功！！","hzw_url.php?admin_rule=yes");
	
} elseif(!empty($_GET['smtAddRule']) && $_GET['smtAddRule']=='yes') {
	
	$_GET=array_merge($_GET,$_POST);
	$ruleArr=array();
	$ruleArr['rule_name'] = daddslashes($_GET['rule_name']);
	$ruleArr['rule_remark'] = daddslashes($_GET['rule_remark']);
	$ruleArr['title01'] = daddslashes($_GET['title01_start']) . '#hzw#' . daddslashes($_GET['title01_end']);	
	$ruleArr['title02'] = daddslashes($_GET['title02_start']) . '#hzw#' . daddslashes($_GET['title02_end']);	
	$ruleArr['title03'] = daddslashes($_GET['title03_start']) . '#hzw#' . daddslashes($_GET['title03_end']);	
	$ruleArr['content01'] = daddslashes($_GET['content01_start']) . '#hzw#' . daddslashes($_GET['content01_end']);
	$ruleArr['content02'] = daddslashes($_GET['content02_start']) . '#hzw#' . daddslashes($_GET['content02_end']);
	$ruleArr['content03'] = daddslashes($_GET['content03_start']) . '#hzw#' . daddslashes($_GET['content03_end']);
	$ruleArr['replace01'] = daddslashes($_GET['replace01_start']) . '#hzw#' . daddslashes($_GET['replace01_end']);
	$ruleArr['replace02'] = daddslashes($_GET['replace02_start']) . '#hzw#' . daddslashes($_GET['replace02_end']);
	$ruleArr['replace03'] = daddslashes($_GET['replace03_start']) . '#hzw#' . daddslashes($_GET['replace03_end']);	
	if(empty($_GET['url_update_id']))
	{	
		$csdn123zd_rule_sql=hzw_implode($ruleArr);
		$csdn123zd_rule_sql='INSERT INTO #@__csdn123zd_rule SET ' . $csdn123zd_rule_sql;
		$db->ExecuteNoneQuery($csdn123zd_rule_sql);
		ShowMsg("添加成功！","hzw_url.php?admin_rule=yes");
	} else {
		$csdn123zd_rule_sql = hzw_implode($ruleArr);
		$csdn123zd_rule_sql = "UPDATE #@__csdn123zd_rule SET " . $csdn123zd_rule_sql . " WHERE ID=" . $_GET['url_update_id'];
		$db->ExecuteNoneQuery($csdn123zd_rule_sql);
		ShowMsg("修改成功！","hzw_url.php?admin_rule=yes");
	}
	
} elseif(!empty($_GET['modifyRuleId']) && is_numeric($_GET['modifyRuleId'])) {
	
	$modifyRuleId = $_GET['modifyRuleId'];
	$ruleRs = $dsql->GetOne("Select * From  #@__csdn123zd_rule where ID=" . $modifyRuleId);
	$title01=explode('#hzw#',$ruleRs['title01']);
	$title02=explode('#hzw#',$ruleRs['title02']);
	$title03=explode('#hzw#',$ruleRs['title03']);
	$content01=explode('#hzw#',$ruleRs['content01']);
	$content02=explode('#hzw#',$ruleRs['content02']);
	$content03=explode('#hzw#',$ruleRs['content03']);
	$replace01=explode('#hzw#',$ruleRs['replace01']);
	$replace02=explode('#hzw#',$ruleRs['replace02']);
	$replace03=explode('#hzw#',$ruleRs['replace03']);	
	$title01[0] = stripcslashes($title01[0]);
	$title01[1] = stripcslashes($title01[1]);
	$title02[0] = stripcslashes($title02[0]);
	$title02[1] = stripcslashes($title02[1]);
	$title03[0] = stripcslashes($title03[0]);
	$title03[1] = stripcslashes($title03[1]);
	$title01[0] = str_replace('"','&quot;',$title01[0]);
	$title01[0] = str_replace("'",'&apos;',$title01[0]);	
	$title01[1] = str_replace('"','&quot;',$title01[1]);
	$title01[1] = str_replace("'",'&apos;',$title01[1]);	
	$title02[0] = str_replace('"','&quot;',$title02[0]);
	$title02[0] = str_replace("'",'&apos;',$title02[0]);	
	$title02[1] = str_replace('"','&quot;',$title02[1]);
	$title02[1] = str_replace("'",'&apos;',$title02[1]);	
	$title03[0] = str_replace('"','&quot;',$title03[0]);
	$title03[0] = str_replace("'",'&apos;',$title03[0]);	
	$title03[1] = str_replace('"','&quot;',$title03[1]);
	$title03[1] = str_replace("'",'&apos;',$title03[1]);	
	$content01[0] = stripcslashes($content01[0]);
	$content01[1] = stripcslashes($content01[1]);
	$content02[0] = stripcslashes($content02[0]);
	$content02[1] = stripcslashes($content02[1]);
	$content03[0] = stripcslashes($content03[0]);
	$content03[1] = stripcslashes($content03[1]);
	$content01[0] = str_replace('"','&quot;',$content01[0]);
	$content01[0] = str_replace("'",'&apos;',$content01[0]);	
	$content01[1] = str_replace('"','&quot;',$content01[1]);
	$content01[1] = str_replace("'",'&apos;',$content01[1]);	
	$content02[0] = str_replace('"','&quot;',$content02[0]);
	$content02[0] = str_replace("'",'&apos;',$content02[0]);	
	$content02[1] = str_replace('"','&quot;',$content02[1]);
	$content02[1] = str_replace("'",'&apos;',$content02[1]);	
	$content03[0] = str_replace('"','&quot;',$content03[0]);
	$content03[0] = str_replace("'",'&apos;',$content03[0]);	
	$content03[1] = str_replace('"','&quot;',$content03[1]);
	$content03[1] = str_replace("'",'&apos;',$content03[1]);	
	$replace01[0] = stripcslashes($replace01[0]);
	$replace01[1] = stripcslashes($replace01[1]);
	$replace02[0] = stripcslashes($replace02[0]);
	$replace02[1] = stripcslashes($replace02[1]);
	$replace03[0] = stripcslashes($replace03[0]);
	$replace03[1] = stripcslashes($replace03[1]);
	$replace01[0] = str_replace('"','&quot;',$replace01[0]);
	$replace01[0] = str_replace("'",'&apos;',$replace01[0]);	
	$replace01[1] = str_replace('"','&quot;',$replace01[1]);
	$replace01[1] = str_replace("'",'&apos;',$replace01[1]);	
	$replace02[0] = str_replace('"','&quot;',$replace02[0]);
	$replace02[0] = str_replace("'",'&apos;',$replace02[0]);	
	$replace02[1] = str_replace('"','&quot;',$replace02[1]);
	$replace02[1] = str_replace("'",'&apos;',$replace02[1]);	
	$replace03[0] = str_replace('"','&quot;',$replace03[0]);
	$replace03[0] = str_replace("'",'&apos;',$replace03[0]);	
	$replace03[1] = str_replace('"','&quot;',$replace03[1]);
	$replace03[1] = str_replace("'",'&apos;',$replace03[1]);
	include DedeInclude("hzw/templets/hzw_url_ModifyRule.htm");
	
} elseif(!empty($_GET['add_rule']) && $_GET['add_rule']=='yes') {
	
	include DedeInclude("hzw/templets/hzw_url_AddRule.htm");
	
} elseif(!empty($_GET['sendid']) && $_GET['sendid']=='zero') {
	
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

    $cInfos = $dsql->GetOne("Select * From  `#@__channeltype` where id='$channelid' ");    
    $maxWright = $dsql->GetOne("SELECT COUNT(*) AS cc FROM #@__archives");
	if(empty($_SESSION['dede_admin_name']))
	{
		$writer="admin";
	}else{
		$writer=$_SESSION['dede_admin_name'];
	}
	$ruleRs=$db->Execute('hzw','SELECT ID,rule_name FROM #@__csdn123zd_rule ORDER BY ID DESC');
	include DedeInclude("hzw/templets/hzw_url_single.htm");

} elseif(!empty($_GET['single_page_content']) && $_GET['single_page_content']=='yes') {
	
	$_GET=array_merge($_GET,$_POST);
	if(empty($_GET['url']) || stripos($_GET['url'],'http')===false)
	{
		ShowMsg("内容网址为空或者格式不正确！！","-1");
		exit;
	} else {
		$url=$_GET['url'];
		$url=trim($url);
	}	
	$release_time=$_GET['release_time'];
	$release_time=strtotime($release_time);
	if(is_numeric($release_time)==FALSE || $release_time<10000)
	{
		$release_time=time();
	}
	if($_GET['model_catch']==2 && $_GET['rule_id']==0)
	{
		ShowMsg("请选择一个指定规则","-1");
		exit;
	}
	$model_catch=$_GET['model_catch'];
	$typeid=$_GET['typeid'];
	$writer=$_GET['writer'];
	$display_link=$_GET['display_link'];
	$image_localized=$_GET['image_localized'];
	$pseudo_original=$_GET['pseudo_original'];
	$chinese_encoding=$_GET['chinese_encoding'];
	$views=$_GET['views'];
	$model_catch=$_GET['model_catch'];
	$rule_id=$_GET['rule_id'];
	$newsArr=array();
	$title='Tentative temporary title ' . md5($url);
	$newsArr['title']=$title;
	$newsArr['source_link']=daddslashes($url);
	$newsArr['typeid']=$typeid;
	$newsArr['writer']=$writer;
	$newsArr['display_link']=$display_link;
	$newsArr['image_localized']=$image_localized;
	$newsArr['pseudo_original']=$pseudo_original;
	$newsArr['chinese_encoding']=$chinese_encoding;
	$newsArr['views']=$views;
	$newsArr['model_catch']=$model_catch;
	$newsArr['rule_id']=$rule_id;
	$newsArr['release_time']=$release_time;
	$chk = $db->GetOne("SELECT * FROM #@__csdn123zd_news WHERE source_link='" . daddslashes($source_link) . "' LIMIT 1");
	$chk2 = $db->GetOne("SELECT * FROM #@__csdn123zd_news WHERE title='" . daddslashes($title) . "' LIMIT 1");
	if (!is_array($chk) && !is_array($chk2)) {
			
		$hzw_insert_sql=hzw_implode($newsArr);
		$hzw_insert_sql='INSERT INTO #@__csdn123zd_news SET ' . $hzw_insert_sql;
		$db->ExecuteNoneQuery($hzw_insert_sql);			
	} else {
		ShowMsg("已经存在该内容！","-1");
		exit;
	}
	$id = $db->GetLastID();
	header("Location:hzw_send_archives.php?id=" . $id);
	exit;
	
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
	$ruleRs=$db->Execute('hzw','SELECT ID,rule_name FROM #@__csdn123zd_rule ORDER BY ID DESC');
	include DedeInclude("hzw/templets/hzw_url.htm");	
	
}

	
?>