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

$remoteUrl = array();
$remoteUrl['zhiwu'] = $_COOKIE['zhiwu'];;
$remoteUrl['SiteUrl'] = $cfg_basehost;
$remoteUrl['ClientUrl'] = $cfg_basehost;
$remoteUrl['SiteUrl2'] = $cfg_basehost;
$remoteUrl['ip'] = $_SERVER['REMOTE_ADDR'];
$remoteUrl['siteur1'] = 'http://' . $_SERVER['HTTP_HOST'];
$fetchUrl = "http://www.csdn123.net/zd_version/zd9_3/check.php";
$htmlcode = csdn123_dfsockopen($fetchUrl, 0, $remoteUrl);
$csdn123_arr = preg_replace('/^\s+|\s+$/','',$htmlcode);
$csdn123_arr = base64_decode($csdn123_arr);
$csdn123_arr = dunserialize($csdn123_arr);
$csdn123_arr['info']=diconv($csdn123_arr['info'],"UTF-8",$cfg_soft_lang);
include DedeInclude("hzw/templets/hzw_help.htm");	
	


	
?>