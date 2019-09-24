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
		$num=$db->ExecuteNoneQuery2("UPDATE #@__csdn123zd_news SET del=1 WHERE ID IN(" . $ids . ")");
		ShowMsg("成功删除" . $num . "条，删除的内容已经放入回收站！","hzw_pending.php?page=" . $_GET["page"]);
		exit;
	}
	if (!empty($_GET['selimport'])) {
		if (empty($_GET['num'])) {
			$num = 0;
		} else {
			$num = $_GET['num'];
			$num = intval($num);
		}
		if (is_array($_GET['idarray'])) {
			$idarray = $_GET['idarray'];
			$count_num = count($idarray);
			$idstr = implode(',', $_GET['idarray']);
		} else {
			$idstr = $_GET['idarray'];
			$idarray = explode(',', $idstr);
			$count_num = count($idarray);
		}
		$ID = $idarray[$num];
		if (is_numeric($ID) == false) {
			ShowMsg("全部发布成功！！","hzw_released.php");
			exit;
		}
		$_GET['batrun']='yes';
		$_GET['id']=$ID;
		ob_start;
		require_once("hzw_send_archives.php");
		$recode = ob_get_contents();
		ob_end_clean();
		$recode = preg_replace('/\s+/','',$recode);
		if ($recode == 'ok') {
			$num++;
			$ID = $idarray[$num];
			if (is_numeric($ID) == false) {
				ShowMsg("全部发布成功！！","hzw_released.php");
				exit;
			}
			$nextCatchUrl = 'hzw_pending.php?run=yes&selimport=yes&idarray=' . $idstr . '&num=' . $num;
			$statusStr = "正在执行批量发布，需要等待一段时间，一共需要发布count条，正在发布第num条，请耐心等待一下……";
			$statusStr = str_replace('count', $count_num, $statusStr);
			$statusStr = str_replace('num', $num, $statusStr);
			echo '<div style="font-size:20px;margin-top:64px;text-align:center;color:red">' . $statusStr . '</div>';
			echo '<script>setTimeout(function(){ window.location.href="' . $nextCatchUrl . '" },10000);</script>';
		} else {
			$num++;
			$ID = $idarray[$num];
			if (is_numeric($ID) == false) {
				ShowMsg("全部发布成功！！","hzw_released.php");
				exit;
			}
			$nextCatchUrl = 'hzw_pending.php?run=yes&selimport=yes&idarray=' . $idstr . '&num=' . $num;
			$statusStr = "第num条采集失败！！继续采集下一条，请耐心等待一下……";
			$statusStr = str_replace('num', $num, $statusStr);
			echo '<div style="font-size:20px;margin-top:64px;text-align:center;color:green">' . $statusStr . '</div>';
			echo '<script>setTimeout(function(){ window.location.href="' . $nextCatchUrl . '" },5000);</script>';
		}
	}
	
} elseif (!empty($_GET["clears_all"]) && $_GET["clears_all"]=="yes"){
	
	$num=$db->ExecuteNoneQuery2('DELETE FROM #@__csdn123zd_news WHERE (artUrl="0" or artUrl="-1") AND arcID=0 AND del=0');
	ShowMsg("成功清空" . $num . "条","hzw_pending.php");
	exit;

} elseif (!empty($_GET["sendfail"]) && $_GET["sendfail"]=="yes"){
	
	$num=$db->ExecuteNoneQuery2('DELETE FROM #@__csdn123zd_news WHERE artUrl="-1" AND arcID=0 AND del=0');
	ShowMsg("成功清除" . $num . "条采集失败的内容","hzw_pending.php");
	exit;
	
} elseif (!empty($_GET["smt_update"]) && $_GET["smt_update"]=="yes"){
	
	$_GET = array_merge($_GET,$_POST);
	$update_news=array();
	$update_news["title"] = $_GET["title"];
	$update_news["typeid"] = $_GET["typeid"];
	$update_news["writer"] = $_GET["writer"];
	$update_news["display_link"] = $_GET["display_link"];
	$update_news["image_localized"] = $_GET["image_localized"];
	$update_news["pseudo_original"] = $_GET["pseudo_original"];
	$update_news["chinese_encoding"] = $_GET["chinese_encoding"];
	$update_news["views"] = $_GET["views"];
	$release_time=$_GET['release_time'];
	$release_time=strtotime($release_time);
	if(is_numeric($release_time)==FALSE || $release_time<10000)
	{
		$release_time_start=time() - 3600;
		$release_time=rand($release_time_start,time());
	}
	$update_news["release_time"] = $release_time;
	$id = $_GET["update_id"];
	$update_sql = hzw_implode($update_news);
	$update_sql = "UPDATE #@__csdn123zd_news SET " . $update_sql . " WHERE ID=" . $id;
	$db->ExecuteNoneQuery($update_sql);
	ShowMsg("修改成功！","hzw_pending.php");
	exit;
	
} elseif(!empty($_GET["update_id"]) && is_numeric($_GET["update_id"])) {	
	
	$id = $_GET["update_id"];
	$arr = $db->GetOne("SELECT * FROM #@__csdn123zd_news WHERE ID =" . $id);
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
    $maxWright = $dsql->GetOne("SELECT COUNT(*) AS cc FROM #@__archives");
	include DedeInclude("hzw/templets/hzw_pending_update.htm");
	
} else {
	
	if(empty($_GET['page']) || is_numeric($_GET['page'])==false)
	{
		$page=1;
	} else {
		$page=$_GET['page'];
	}
	$page=max(1,$page);
	$newsCount = $db->GetOne('SELECT count(*) as num FROM #@__csdn123zd_news WHERE (artUrl="0" or artUrl="-1") AND arcID=0 AND del=0');
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
		$homePage = $server_url . '&page=1';
		$nextPage = $server_url . '&page=' . ($page + 1);
		$prePage = $server_url . '&page=' . ($page - 1);
		$endPage = $server_url . '&page=' . $TotalPageNumber;	
		$news_list = $db->Execute('hzw','SELECT * FROM #@__csdn123zd_news WHERE (artUrl="0" or artUrl="-1") AND arcID=0 AND del=0 ORDER BY ID DESC LIMIT ' . $startNum . ',20');
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
				$showPage=$showPage . '<a href="?' . $server_url . '&page=' . $i . '">' . $i . '</a>';
			}
		}
	}
	include DedeInclude("hzw/templets/hzw_pending.htm");	
	
}

	
?>