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

if(!empty($_GET["run"]) && $_GET["run"]=="yes")
{
	$_GET = array_merge($_GET,$_POST);
	if(empty($_GET["idarray"]))
	{
		ShowMsg("请点击左边的复选框打上勾，选择至少一条记录","-1");
		exit;
	}
	if(!empty($_GET["seldelete"]) && $_GET["seldelete"]=="全部删除")
	{
		$ids=implode(",",$_GET["idarray"]);
		$num=$db->ExecuteNoneQuery2("DELETE FROM #@__csdn123zd_news WHERE ID IN(" . $ids . ")");
		ShowMsg("成功删除" . $num . "条！","hzw_released.php?page=" . $_GET["page"]);
		exit;
	}	
	
} elseif(!empty($_GET["clears_all"]) && $_GET["clears_all"]=="yes") {
	
	$num=$db->ExecuteNoneQuery2('DELETE FROM #@__csdn123zd_news WHERE artUrl!="0" AND artUrl!="-1" AND arcID>0 AND del=0');
	ShowMsg("全部清空成功，一共" . $num . "条。","hzw_released.php");
	exit;

} else {	

	if(empty($_GET['page']) || is_numeric($_GET['page'])==false)
	{
		$page=1;
	} else {
		$page=$_GET['page'];
	}
	$page=max(1,$page);
	if(!empty($_GET['catch_way']) && $_GET['catch_way']=='auto')
	{	
		$newsCount = $db->GetOne('SELECT count(*) as num FROM #@__csdn123zd_news WHERE artUrl!="0" AND artUrl!="-1" AND arcID>0 AND del=0 AND catch_way="auto"');
	} else {
		$newsCount = $db->GetOne('SELECT count(*) as num FROM #@__csdn123zd_news WHERE artUrl!="0" AND artUrl!="-1" AND arcID>0 AND del=0');
	}
	$newsCount = $newsCount['num'];
	if($newsCount>0)
	{
		$server_url='?';
		$TotalNumber = "总共x页，有y条，当前第z页";
		$TotalPageNumber = $newsCount/20;
		$TotalPageNumber = @ceil($TotalPageNumber);
		$TotalNumber = str_replace('x',$TotalPageNumber,$TotalNumber);
		$TotalNumber = str_replace('y',$newsCount,$TotalNumber);
		$TotalNumber = str_replace('z',$page,$TotalNumber);
		$page = min($TotalPageNumber,$page);
		$startNum = ($page - 1) * 20;
		$homePage = $server_url . '&page=1&catch_way=' . $_GET['catch_way'];
		$nextPage = $server_url . '&page=' . ($page + 1) . '&catch_way=' . $_GET['catch_way'];
		$prePage = $server_url . '&page=' . ($page - 1) . '&catch_way=' . $_GET['catch_way'];
		$endPage = $server_url . '&page=' . $TotalPageNumber . '&catch_way=' . $_GET['catch_way'];
		if(!empty($_GET['catch_way']) && $_GET['catch_way']=='auto')
		{	
			$news_list = $db->Execute('hzw','SELECT * FROM #@__csdn123zd_news WHERE artUrl!="0" AND artUrl!="-1" AND arcID>0 AND del=0 AND catch_way="auto" ORDER BY send_datetime DESC LIMIT ' . $startNum . ',20');
		} else {
			$news_list = $db->Execute('hzw','SELECT * FROM #@__csdn123zd_news WHERE artUrl!="0" AND artUrl!="-1" AND arcID>0 AND del=0 ORDER BY send_datetime DESC LIMIT ' . $startNum . ',20');
		}
		$start = $page - 4;		
		$start = min($TotalPageNumber - 8,$start);
		$start = max($start,1);
		$end = $start + 8;
		$end = min($end,$TotalPageNumber);
		$showPage="";
		for($i=$start;$i<=$end;$i++)
		{
			if($i==$page)
			{
				$showPage=$showPage . '<strong>' . $i . '</strong>';
			} else {
				$showPage=$showPage . '<a href="?' . $server_url . '&page=' . $i . '&catch_way=' . $_GET['catch_way'] . '">' . $i . '</a>';
			}
		}
	}
	include DedeInclude("hzw/templets/hzw_released.htm");	
	
}	
?>