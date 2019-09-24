<?php
$z[0]=Array("field","",215,237);
$z[0][4]['name']="seotitle";
$z[1]=Array("global","",238,264);
$z[1][4]['name']="cfg_webname";
$z[2]=Array("field","",305,327);
$z[2][4]['name']="keywords";
$z[3]=Array("field","",367,418);
$z[3][4]['name']="description";
$z[3][4]['function']="html2text(@me)";
$z[4]=Array("include","",1194,1231);
$z[4][4]['filename']="head_m.htm";
$z[5]=Array("arclist","\r\n<h3 class=\"headNew\"><a href=\"view.php?aid=[field:id/]\" title=\"[field:title /]\">[field:title /]</a></h3>\r\n<h5 class=\"subNew\">[field:description function=\"cn_substr(@me,60)\"/]...<a href=\"view.php?aid=[field:id/]\">[更多]</a></h5>\r\n",1248,1538);
$z[5][4]['titlelen']="35";
$z[5][4]['row']="1";
$z[5][4]['flag']="c";
$z[6]=Array("arclist","\r\n      <div class=\"image swiper-slide\" style=\"background:url([field:litpic/]) center no-repeat;background-size: cover;\"> <a href=\"view.php?aid=[field:id/]\" target=\"_blank\"></a>\r\n        <div class=\"title\">\r\n          <p>[field:title /]</p>\r\n        </div>\r\n      </div>\r\n      ",1672,2012);
$z[6][4]['titlelen']="45";
$z[6][4]['row']="5";
$z[6][4]['flag']="n,p";
$z[7]=Array("channelartlist","\r\n<!-- 元素开始 -->\r\n<div id=\"tabBox2\" class=\"tabBox\">\r\n  <div class=\"hd\">\r\n    <h3><a href=\"list.php?tid={dede:field name='id'/}\" >{dede:field name='typename'/}</a></h3>\r\n    <ul>\r\n      <li><a href=\"list.php?tid={dede:field name='id'/}\">更多</a></li>\r\n    </ul>\r\n  </div>\r\n  <div class=\"bd\">\r\n      <ul>\r\n        {dede:arclist titlelen='40' row='7'}\r\n        <li><a href=\"view.php?aid=[field:id/]\" title=\"[field:title /]\">[field:title /]</a></li>\r\n         {/dede:arclist}\r\n      </ul>\r\n  </div>\r\n</div>\r\n\r\n",2075,2635);
$z[7][4]['row']="5";
$z[8]=Array("include","",2637,2674);
$z[8][4]['filename']="foot_m.htm";

?>