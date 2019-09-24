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
if(!empty($_GET["autoimport"]) && $_GET["autoimport"]=="yes")
{
	$_GET=array_merge($_GET,$_POST);
	if(empty($_GET['n']) || is_numeric($_GET['n'])==false)
	{
		$n=1;
		
	} else {
		
		$n=$_GET['n'];
		
	}
	$autoImportDataUrl = "http://www.csdn123.net/zd_version/zd9_3/original_data/{$n}.php?" . urlencode($_G['siteurl']);
	$autoImportData = csdn123_dfsockopen($autoImportDataUrl);
	if (strlen($autoImportData) < 100) {
		$autoImportData = csdn123_dfsockopen($autoImportDataUrl, 0, '', '', FALSE, '', 15, TRUE, 'URLENCODE', FALSE);
	}
	$autoImportData = preg_replace('/\s+/','',$autoImportData);
	$autoImportData = base64_decode($autoImportData);
	$autoImportDataArr = dunserialize($autoImportData);
	if(is_array($autoImportDataArr)==false)
	{
		ShowMsg("数据格式错误！","-1");
		exit;
	}
	$word_charset=$autoImportDataArr['charset'];
	foreach($autoImportDataArr['word'] as $word_value)
	{
		$word1=diconv($word_value['word1'],$word_charset,$cfg_soft_lang);
		$word2=diconv($word_value['word2'],$word_charset,$cfg_soft_lang);
		$wordSql="REPLACE INTO #@__csdn123zd_weiyanchang(word1,word2) VALUES('" . $word1 . "','" . $word2 . "')";
		$dsql->ExecuteNoneQuery($wordSql);
	}
	if($n>=6)
	{
		ShowMsg("恭喜您！！全部伪原创词库导入成功！！","hzw_original.php");
		exit;
	} else {
		$n = $n + 1;
	}
	$next_original_url = 'hzw_original.php?autoimport=yes&&n=' . $n;
	ShowMsg("<strong>请稍等，正在导入中……</strong><br>正在执行第" . $n . "批的伪原创词库导入<br>",$next_original_url);
	exit;
	
} elseif(!empty($_GET["weiyanchang_clear"]) && $_GET["weiyanchang_clear"]=="yes") {

	$num=$db->ExecuteNoneQuery2('DELETE FROM #@__csdn123zd_weiyanchang');
	ShowMsg("成功清除" . $num . "条伪原创同义词","hzw_original.php");
	exit;

} elseif(!empty($_GET["weiyanchang_output"]) && $_GET["weiyanchang_output"]=="yes") {
	
	$weiyanchang_Data=array();
	$weiyanchang_Data['charset']=$cfg_soft_lang;
	$weiyanchang_Rs_SQL="SELECT word1,word2 FROM #@__csdn123zd_weiyanchang";
	$db->Execute('hzw',$weiyanchang_Rs_SQL);
	while($arr = $db->GetArray('hzw'))
	{
		$weiyanchang_Data['word'][]=$arr;
	}
	$weiyanchang_output=serialize($weiyanchang_Data);
	$weiyanchang_output=base64_encode($weiyanchang_output);
	include DedeInclude("hzw/templets/hzw_original_output.htm");
	
} elseif(!empty($_GET["weiyanchang_import"]) && $_GET["weiyanchang_import"]=="show") {
	
	include DedeInclude("hzw/templets/hzw_original_import.htm");

} elseif(!empty($_GET["import_word"]) && $_GET["import_word"]=="yes") {
	
	$_GET=array_merge($_GET,$_POST);
	$word_data=$_GET['word_data'];
	$word_data=preg_replace('/\s+/','',$word_data);
	if(strpos($word_data,',')!=false && strpos($word_data,'=')!=false)
	{		
		$word_data_arr=explode(',',$word_data);
		foreach($word_data_arr as $word_value)
		{
			$word_value_arr=explode('=',$word_value);
			$word1=daddslashes($word_value_arr[0]);
			$word2=daddslashes($word_value_arr[1]);
			$wordSql="REPLACE INTO #@__csdn123zd_weiyanchang(word1,word2) VALUES('" . $word1 . "','" . $word2 . "')";
			$db->ExecuteNoneQuery($wordSql);			
			
		}
		ShowMsg('伪原创词库导入成功！！', 'hzw_original.php');
		exit;
	}
	$word_data=base64_decode($word_data);
	$word_arr=dunserialize($word_data);
	if(is_array($word_arr)==false)
	{
		ShowMsg('数据格式错误！', '-1');
		exit;
	}
	$word_charset=$word_arr['charset'];
	foreach($word_arr['word'] as $word_value)
	{
		$word1=diconv($word_value['word1'],$word_charset,$cfg_soft_lang);
		$word2=diconv($word_value['word2'],$word_charset,$cfg_soft_lang);
		$wordSql="REPLACE INTO #@__csdn123zd_weiyanchang(word1,word2) VALUES('" . $word1 . "','" . $word2 . "')";
		$db->ExecuteNoneQuery($wordSql);
	}
	ShowMsg('伪原创词库导入成功！！', 'hzw_original.php');
	exit;
	
} elseif(!empty($_GET["weiyanchang_add"]) && $_GET["weiyanchang_add"]=="show") {
	
	include DedeInclude("hzw/templets/hzw_original_add.htm");

} elseif(!empty($_GET["addword"]) && $_GET["addword"]=="yes") {
	
	$_GET=array_merge($_GET,$_POST);
	if(empty($_GET['word1']) || empty($_GET['word2']))
	{
		ShowMsg('关键词不能为空！', '-1');
		exit;
	}
	$word1=daddslashes($_GET['word1']);
	$word2=daddslashes($_GET['word2']);	
	$wordSql="REPLACE INTO #@__csdn123zd_weiyanchang(word1,word2) VALUES('" . $word1 . "','" . $word2 . "')";
	$db->ExecuteNoneQuery($wordSql);
	ShowMsg('添加同义词库成功！！', 'hzw_original.php');
	exit;

} elseif(!empty($_GET["modify_submit"]) && $_GET["modify_submit"]=='yes') {
	
	$_GET=array_merge($_GET,$_POST);
	if(empty($_GET['word1']) || empty($_GET['word2']))
	{
		ShowMsg('请填写替换词库！', '-1');
		exit;
	}
	$ID=intval($_GET['ID']);
	$word1=daddslashes($_GET['word1']);
	$word2=daddslashes($_GET['word2']);
	$wordArr=array();
	$wordArr['word1']=$word1;
	$wordArr['word2']=$word2;
	$updateSql=hzw_implode($wordArr);
	$updateSql='UPDATE #@__csdn123zd_weiyanchang SET ' . $updateSql . '  WHERE ID=' . $ID;
	$db->ExecuteNoneQuery($updateSql);
	ShowMsg('修改同义词库成功！！', 'hzw_original.php');
	
} elseif(!empty($_GET["modify_id"]) && is_numeric($_GET["modify_id"])) {
	
	$modify_rs=$db->GetOne("SELECT * FROM #@__csdn123zd_weiyanchang WHERE ID=" . $_GET['modify_id']);
	include DedeInclude("hzw/templets/hzw_original_update.htm");
	
} elseif(!empty($_GET["delword"]) && $_GET["delword"]=="yes") {
	
	$_GET=array_merge($_GET,$_POST);
	if(empty($_GET["idarray"]))
	{
		ShowMsg("请点击左边的复选框打上勾，选择至少一条记录","-1");
		exit;
	}
	$ids=implode(",",$_GET["idarray"]);
	$num=$db->ExecuteNoneQuery2("DELETE FROM #@__csdn123zd_weiyanchang WHERE ID IN(" . $ids . ")");
	ShowMsg("成功删除" . $num . "条伪原创同义词","hzw_original.php");
	exit;
	
} else {
	
	if(empty($_GET['page']) || is_numeric($_GET['page'])==false)
	{
		$page=1;
	} else {
		$page=$_GET['page'];
	}
	$page=max(1,$page);
	$weiyanchangCount = $db->GetOne('SELECT count(*) as num FROM #@__csdn123zd_weiyanchang');
	$weiyanchangCount = $weiyanchangCount["num"];
	if($weiyanchangCount>0)
	{
		$server_url='?';
		$TotalNumber = "总共x页，有y条，当前第z页";
		$TotalPageNumber = $weiyanchangCount/20;
		$TotalPageNumber = @ceil($TotalPageNumber);
		$TotalNumber = str_replace('x',$TotalPageNumber,$TotalNumber);
		$TotalNumber = str_replace('y',$weiyanchangCount,$TotalNumber);
		$TotalNumber = str_replace('z',$page,$TotalNumber);
		$page = min($TotalPageNumber,$page);
		$startNum = ($page - 1) * 20;
		$homePage = $server_url . '&page=1';
		$nextPage = $server_url . '&page=' . ($page + 1);
		$prePage = $server_url . '&page=' . ($page - 1);
		$endPage = $server_url . '&page=' . $TotalPageNumber;	
		$weiyanchang_list =  $db->Execute('hzw','SELECT * FROM #@__csdn123zd_weiyanchang ORDER BY ID DESC LIMIT ' . $startNum . ',20');
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
	include DedeInclude("hzw/templets/hzw_original.htm");
	
}
	
?>