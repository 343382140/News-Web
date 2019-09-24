<?php
if (empty($_GET["url"])) {
	$img = "http://img.csdn123.net/csdn123_img_error.jpg";
} else {
	$img = $_GET["url"];
	$img = urldecode($img);
}
if (strpos(strtolower($img), 'gif') !== false) {
	header('Content-Type: image/gif');
} elseif (strpos(strtolower($img), 'png') !== false) {
	header('Content-Type: image/png');
} else {
	header('Content-Type: image/jpeg');
}
$urlinfo = parse_url($img);
$refererUrl = 'http://' . $urlinfo["host"];
if ($refererUrl == 'http://mmbiz.qpic.cn') {
	$refererUrl = 'http://www.qq.com';
}
if ($refererUrl == 'http://mmbiz.qlogo.cn') {
	$refererUrl = 'http://www.qq.com';
}
if (strpos($refererUrl, 'zhihu.com') !== false) {
	$refererUrl = 'http://www.zhihu.com/';
}
if (strpos($refererUrl, 'nipic.com') !== false) {
	$refererUrl = 'http://www.nipic.com/';
}
if (strpos($refererUrl, 'baidu.com') !== false) {
	$refererUrl = 'http://www.baidu.com/';
}
if (strpos($refererUrl, 'inews.gtimg.com') !== false) {
	$refererUrl = '';
}
if (function_exists('curl_init') && function_exists('curl_exec')) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $img);
	if ($refererUrl != '') {
		curl_setopt($ch, CURLOPT_REFERER, $refererUrl);
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
	$return = curl_exec($ch);
	curl_close($ch);
}
?> 