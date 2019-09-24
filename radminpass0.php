<?php

/**********************
DedeCMS 管理员帐号重设工具.
http://bbs.dedecms.com/
***********************/
//error_reporting(E_ALL || ~E_NOTICE);
require_once(dirname(__FILE__)."/include/common.inc.php");
if(empty($step))
{
	$step = 1;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<title>密码重设工具_GBK</title>
<style type="text/css">
<!--
body {
	font-family: "MS Serif", "New York", serif;
	font-size: 12px;
	color: #000;
}
table {
	border-top-width: 1px;
	border-right-width: 1px;
	border-left-width: 1px;
	border-top-style: dotted;
	border-right-style: dotted;
	border-left-style: dotted;
	border-top-color: #CCC;
	border-right-color: #CCC;
	border-left-color: #CCC;
}
td {
	border-bottom-width: 1px;
	border-bottom-style: dotted;
	border-bottom-color: #CCC;
}
-->
</style>
</head>
<body>
<?php
if($step==1)
{
	$dsql->SetQuery("Select * From `#@__admin` where usertype='10'");
	$dsql->Execute("ut");
?>
<table width="98%" border="0" align="center" cellpadding="3" cellspacing="1">
  <tr>
    <td height="19" bgcolor="#E7E7E7"><table width="96%" border="0" cellspacing="1" cellpadding="1">
      <tr>
        <td width="24%"><b><strong>第一步：选择管理员账号</strong></b></td>
        <td width="76%" align="right">&nbsp;</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td height="215" align="center" valign="top" bgcolor="#FFFFFF"><form action="radminpass.php" method="post" name="form1" id="form1">
      <input type="hidden" name="step" value="2" />
      <table width="98%" border="0" cellspacing="1" cellpadding="1">
<tr>
          <td height="60" colspan="2" align="left">本工具是用于新人忘记管理员密码重设所制作，只需要将radminpass.php文件拷贝到根目录，运行&ldquo;http://yousite/radminpass.php（yousite为网站域名）&rdquo;，按照操作执行就可以。<font color="#FF0000">恢复完成后请及时删除这个文件!</font></td>
          </tr>
        <tr>
          <td width="16%" height="30" align="left">选择超级管理员ID：</td>
          <td width="84%" align="left">
            <select name='id' style='width:150px'>
              <?php
			  	while($myrow = $dsql->GetObject("ut"))
			  	{
			  		echo "<option value='".$myrow->id."'>".$myrow->userid."</option>\r\n";
			  	}
			  	?>
              </select>
            
            </td>
        </tr>
        <tr>
          <td height="60" align="left">&nbsp;</td>
          <td align="left"><input type="submit" name="Submit" value="下一步&gt;" class="coolbg np" /></td>
        </tr>
      </table>
    </form></td>
  </tr>
</table>
<?php
}elseif($step==2){
	$row = $dsql->GetOne("Select * From `#@__admin` where id='$id'");
?>
<table width="98%" border="0" align="center" cellpadding="3" cellspacing="1">
  <tr>
    <td height="19" bgcolor="#E7E7E7"><table width="96%" border="0" cellspacing="1" cellpadding="1">
      <tr>
        <td width="24%"><b><strong>第二步：修改管理员密码</strong></b></td>
        <td width="76%" align="right">&nbsp;</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td height="215" align="center" valign="top" bgcolor="#FFFFFF"><form action="radminpass.php" method="post" name="form1" id="form1">
      <input name="step" type="hidden" id="step" value="3" />
      <input type="hidden" name="id" value="<?php echo $row['id']?>" />
      <table width="98%" border="0" cellspacing="1" cellpadding="1">
        <tr>
          <td width="16%" height="30" align="left">用户登录ID：</td>
          <td width="84%" align="left"><?php echo $row['userid']?></td>
        </tr>
        <tr>
          <td height="30" align="left">用户笔名：</td>
          <td align="left"><input name="uname" type="text" id="uname" size="16" value="<?php echo $row['uname']?>" style="width:200px" />
            &nbsp;（发布文章后显示责任编辑的名字） </td>
        </tr>
        <tr>
          <td height="30" align="left">用户密码：</td>
          <td align="left"><input name="pwd" type="text" id="pwd" size="16" style="width:200px" />
            &nbsp;（留空则不修改，只能用'0-9a-zA-Z.@_-!'以内范围的字符） </td>
        </tr>
        <tr>
          <td height="60" align="left">&nbsp;</td>
          <td align="left"><input type="submit" name="Submit" value="确定修改 " class="coolbg np" /></td>
        </tr>
      </table>
    </form></td>
  </tr>
</table>
<?php
}elseif($step==3){
	$pwdm = '';
	if($pwd!=''){
		$pwdm = ",pwd='".md5($pwd)."'";
		$pwd = ",pwd='".substr(md5($pwd),5,20)."'";
	}
	$query = "Update `#@__admin` set uname='$uname' $pwd where id='$id'";
	$dsql->ExecuteNoneQuery($query);
	$query = "Update `#@__member` set uname='$uname' $pwdm where mid='$id'";
	$dsql->ExecuteNoneQuery($query);
	ShowMsg("成功更改一个帐户！","radminpass.php");	
}
?>
</body>
</html>
