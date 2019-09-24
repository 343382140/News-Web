<?php
function csdn123_dfsockopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE, $encodetype  = 'URLENCODE', $allowcurl = TRUE, $position = 0, $files = array()) {
	$return = '';
	$headers='';
	$matches = parse_url($url);
	$scheme = $matches['scheme'];
	$host = $matches['host'];
	$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
	$port = !empty($matches['port']) ? $matches['port'] : ($scheme == 'http' ? '80' : '');
	$boundary = $encodetype == 'URLENCODE' ? '' : random(40);

	if($post) {
		if(!is_array($post)) {
			parse_str($post, $post);
		}
		csdn123_format_postkey($post, $postnew);
		$post = $postnew;
	}
	if(function_exists('curl_init') && function_exists('curl_exec')) {
		$ch = curl_init();
		$httpheader = array();
		if($ip) {
			$httpheader[] = "Host: ".$host;
		}
		if(stripos($url,'weixin.sogou.com')!==false)
		{	
			$httpheader=array('CLIENT-IP:58.250.125.37','X-FORWARDED-FOR:58.250.125.37');
			curl_setopt($ch, CURLOPT_REFERER, $url);
		}
		if($httpheader) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
		}
		curl_setopt($ch, CURLOPT_URL, $scheme.'://'.($ip ? $ip : $host).($port ? ':'.$port : '').$path);		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		if($post) {
			curl_setopt($ch, CURLOPT_POST, 1);
			if($encodetype == 'URLENCODE') {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			} else {
				foreach($post as $k => $v) {
					if(isset($files[$k])) {
						$post[$k] = '@'.$files[$k];
					}
				}
				foreach($files as $k => $file) {
					if(!isset($post[$k]) && file_exists($file)) {
						$post[$k] = '@'.$file;
					}
				}
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			}
		}
		if($cookie) {
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		$data = curl_exec($ch);
		$status = curl_getinfo($ch);
		$errno = curl_errno($ch);
		curl_close($ch);
		if($errno || $status['http_code'] != 200) {
			return;
		} else {
			$GLOBALS['filesockheader'] = substr($data, 0, $status['header_size']);
			$data = substr($data, $status['header_size']);
			return !$limit ? $data : substr($data, 0, $limit);
		}
	}

	if($post) {
		if($encodetype == 'URLENCODE') {
			$data = http_build_query($post);
		} else {
			$data = '';
			foreach($post as $k => $v) {
				$data .= "--$boundary\r\n";
				$data .= 'Content-Disposition: form-data; name="'.$k.'"'.(isset($files[$k]) ? '; filename="'.basename($files[$k]).'"; Content-Type: application/octet-stream' : '')."\r\n\r\n";
				$data .= $v."\r\n";
			}
			foreach($files as $k => $file) {
				if(!isset($post[$k]) && file_exists($file)) {
					if($fp = @fopen($file, 'r')) {
						$v = fread($fp, filesize($file));
						fclose($fp);
						$data .= "--$boundary\r\n";
						$data .= 'Content-Disposition: form-data; name="'.$k.'"; filename="'.basename($file).'"; Content-Type: application/octet-stream'."\r\n\r\n";
						$data .= $v."\r\n";
					}
				}
			}
			$data .= "--$boundary\r\n";
		}
		$out = "POST $path HTTP/1.0\r\n";
		$header = "Accept: */*\r\n";
		$header .= "Accept-Language: zh-cn\r\n";
		$header .= $encodetype == 'URLENCODE' ? "Content-Type: application/x-www-form-urlencoded\r\n" : "Content-Type: multipart/form-data; boundary=$boundary\r\n";
		$header .= 'Content-Length: '.strlen($data)."\r\n";
		$header .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$header .= "Host: $host:$port\r\n";
		$header .= "Connection: Close\r\n";
		$header .= "Cache-Control: no-cache\r\n";
		$header .= "Cookie: $cookie\r\n\r\n";
		$out .= $header;
		$out .= $data;
	} else {
		$out = "GET $path HTTP/1.0\r\n";
		$header = "Accept: */*\r\n";
		$header .= "Accept-Language: zh-cn\r\n";
		$header .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$header .= "Host: $host:$port\r\n";
		$header .= "Connection: Close\r\n";
		$header .= "Cookie: $cookie\r\n\r\n";
		$out .= $header;
	}


	$fpflag = 0;
	
	if(!$fp = @csdn123_refsocketopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout)) {
		$context = array(
			'http' => array(
				'method' => $post ? 'POST' : 'GET',
				'header' => $header,
				'content' => $post,
				'timeout' => $timeout,
			),
		);
		$context = stream_context_create($context);
		$fp = @fopen($scheme.'://'.($ip ? $ip : $host).':'.$port.$path, 'b', false, $context);
		$fpflag = 1;
	}

	if(!$fp) {
		return '';
	} else {
		stream_set_blocking($fp, $block);
		stream_set_timeout($fp, $timeout);
		@fwrite($fp, $out);
		$status = stream_get_meta_data($fp);
		if(!$status['timed_out']) {
			while (!feof($fp) && !$fpflag) {
				$header = @fgets($fp);
				$headers .= $header;
				if($header && ($header == "\r\n" ||  $header == "\n")) {
					break;
				}
			}
			$GLOBALS['filesockheader'] = $headers;

			if($position) {
				for($i=0; $i<$position; $i++) {
					$char = fgetc($fp);
					if($char == "\n" && $oldchar != "\r") {
						$i++;
					}
					$oldchar = $char;
				}
			}

			if($limit) {
				$return = stream_get_contents($fp, $limit);
			} else {
				$return = stream_get_contents($fp);
			}
		}
		@fclose($fp);
		return $return;
	}
}

function csdn123_format_postkey($post, &$result, $key = '') {
	foreach($post as $k => $v) {
		$_k = $key ? $key.'['.$k.']' : $k;
		if(is_array($v)) {
			csdn123_format_postkey($v, $result, $_k);
		} else {
			$result[$_k] = $v;
		}
	}
}

function csdn123_refsocketopen($hostname, $port = 80, &$errno, &$errstr, $timeout = 15) {
	$fp = '';
	if(function_exists('fsockopen')) {
		$fp = @fsockopen($hostname, $port, $errno, $errstr, $timeout);
	} elseif(function_exists('pfsockopen')) {
		$fp = @pfsockopen($hostname, $port, $errno, $errstr, $timeout);
	} elseif(function_exists('stream_socket_client')) {
		$fp = @stream_socket_client($hostname.':'.$port, $errno, $errstr, $timeout);
	}
	return $fp;
}
function diconv($str, $in_charset, $out_charset = "", $ForceTable = FALSE) {

	$in_charset = strtoupper($in_charset);
	$out_charset = strtoupper($out_charset);
	if(empty($str) || $in_charset == $out_charset) {
		return $str;
	}
	$out = '';
	if(!$ForceTable) {
		if(function_exists('iconv')) {
			$out = iconv($in_charset, $out_charset.'//IGNORE', $str);
		} elseif(function_exists('mb_convert_encoding')) {
			$out = mb_convert_encoding($str, $out_charset, $in_charset);
		}
	}
	return $out;
}
function dunserialize($data) {
	if(($ret = unserialize($data)) === false) {
		$ret = unserialize(stripslashes($data));
	}
	return $ret;
}
function daddslashes($string, $force = 1) {
	if(is_array($string)) {
		$keys = array_keys($string);
		foreach($keys as $key) {
			$val = $string[$key];
			unset($string[$key]);
			$string[addslashes($key)] = daddslashes($val, $force);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}
function hzw_implode($array, $glue = ',') 
{
		$sql = $comma = '';
		$glue = ' ' . trim($glue) . ' ';
		foreach ($array as $k => $v) {
			$sql .= $comma . quote_field($k) . '=' . quote($v);
			$comma = $glue;
		}
		return $sql;
}
function quote_field($field) {
		if (is_array($field)) {
			foreach ($field as $k => $v) {
				$field[$k] = quote_field($v);
			}
		} else {
			if (strpos($field, '`') !== false)
				$field = str_replace('`', '', $field);
			$field = '`' . $field . '`';
		}
		return $field;
	}
function quote($str, $noarray = false) {

		if (is_string($str))
			return '\'' . mysql_escape_string($str) . '\'';

		if (is_int($str) or is_float($str))
			return '\'' . $str . '\'';

		if (is_array($str)) {
			if($noarray === false) {
				foreach ($str as &$v) {
					$v = quote($v, true);
				}
				return $str;
			} else {
				return '\'\'';
			}
		}

		if (is_bool($str))
			return $str ? '1' : '0';

		return '\'\'';
	}	

function catch_data_source($keyword,$datasource=0,$inputcookie='')
{
	global $cfg_soft_lang;
	if(empty($keyword))
	{
		return 'keyword_empty';
	}
	$datasource=intval($datasource);
	if(!is_numeric($datasource) || $datasource<=0 || $datasource>6)
	{
		$datasource=rand(1,6);
	}
	$keyword = diconv($keyword, $cfg_soft_lang, 'UTF-8');
	$ori_keyword = $keyword;
	$keyword = urlencode($keyword);
	switch($datasource)
	{
		case 1:
		$catchUrl = "http://www.toutiao.com/search_content/?offset=0&format=json&keyword=%%keyword%%&autoload=true&count=20&cur_tab=1";
		break;
		
		case 2:
		$catchUrl='http://r.cnews.qq.com/searchMore?mid=6a6ff8ff488fa63b4f07baf5a8a6413180f79f1a&devid=d43db7e1e57177ae&mac=C8%3A94%3ABB%3A56%3A31%3AD4&store=70124&screen_height=1812&apptype=android&origin_imei=864647039546844&hw=HUAWEI_BLN-AL40&appver=24_areading_4.0.95&appversion=4.0.95&uid=d43db7e1e57177ae&screen_width=1080&sceneid=&android_id=d43db7e1e57177ae&adcode=441602&ssid=HZW3&source=&IronThroneBuildTime=1510799982348&omgid=bb71176e02717f48586b411a757244e4ee19001021251c&timeline=1507253599&IronThroneRelBuildTime=690655697&qqnetwork=wifi&secId=2&sid=5737977429392444793&commonGray=1&id=20171006G015VG00&currentTab=kuaibao&is_wap=0&omgbizid=02467906f0ae234296f9750050ba7a6b38a1008021251c&type=0&page=%%page%%&imsi=460016197011678&queryid=8153201510799894&bssid=b8%3Af8%3A83%3A97%3Aa9%3Abf&IronThroneRelExecTime=690655697&query=%%keyword%%&muid=65840668683900868&activefrom=icon&qimei=864647039546844&Cookie=%20lskey%3D%3B%20luin%3D%3B%20skey%3D%3B%20uin%3D%3B%20logintype%3D0%3B&chlid=&IronThroneExecTime=1510799982348&rawQuery=&imsi_history=460016197011678&qn-sig=7835b941a82aaaaa4fe265582082f2d0&qn-rid=fe253e66-c73e-45ad-b7ef-56a438ae6c42';
		break;
		
		case 3:
		$catchUrl = "http://www.yidianzixun.com/home/q/news_list_for_keyword?display=%%keyword%%&word_type=token&cstart=0&cend=9";
		break;

		case 4:
		$catchUrlArr=array();
		$catchUrlArr[] = "http://weixin.sogou.com/weixin?type=2&ie=utf8&query=%%keyword%%&tsn=2&ft=&et=&interation=&wxid=&usip=";
		$catchUrlArr[] = "http://weixin.sogou.com/weixin?type=2&ie=utf8&query=%%keyword%%&tsn=3&ft=&et=&interation=&wxid=&usip=";
		$catchUrlArr[] = "http://weixin.sogou.com/weixin?type=2&ie=utf8&query=%%keyword%%&tsn=4&ft=&et=&interation=&wxid=&usip=";
		$catchUrlArr[] = "http://weixin.sogou.com/weixin?type=2&s_from=input&query=%%keyword%%&ie=utf8&_sug_=n&_sug_type_=";
		$catchUrlArr[] = "http://weixin.sogou.com/weixin?type=2&ie=utf8&query=%%keyword%%&tsn=1&ft=&et=&interation=&wxid=&usip=";
		shuffle($catchUrlArr);
		$catchUrl = $catchUrlArr[0];
		break;
		
		case 5:
		$catchUrl = "http://www.myzaker.com/news/search_new.php?f=myzaker_com&keyword=%%keyword%%";
		break;
		
		case 6:
		$api_server="http://www.csdn123.net/zd_version/zd9_3/server_batch.php?";
		$api_server_parameter=array();
		$api_server_parameter['zhiwu'] = $_COOKIE['zhiwu'];
		$api_server_parameter['zw0lO1'] = $cfg_basehost;
		$api_server_parameter['zwOl01'] =  'http://' . $_SERVER['HTTP_HOST'];
		$api_server_parameter['zwl0lO'] = 'http://' .  $_SERVER['SERVER_NAME'];
		$api_server_parameter['ip'] = $_SERVER['REMOTE_ADDR'];
		$api_server_parameter['query'] = $ori_keyword;
		$api_server_parameter['zw01O1'] = $_SERVER['HTTP_REFERER'];
		$api_server_parameter['number_collected'] = 20;	
		$catchUrl = $api_server . http_build_query($api_server_parameter);
		break;

	}
	$catchUrl = str_replace('%%keyword%%',$keyword,$catchUrl);
	$page_number = rand(-10,5);
	$page_number = max(1,$page_number);
	$catchUrl = str_replace('%%page%%',$page_number,$catchUrl);	
	$htmlcode = ext_dfsockopen($catchUrl,$inputcookie);
	if (strlen($htmlcode) < 200) {		
		$htmlcode = csdn123_dfsockopen($catchUrl);		
	}
	if (strlen($htmlcode) < 200) {		
		$htmlcode = csdn123_dfsockopen($catchUrl, 0, '', '', FALSE, '', 15, TRUE, 'URLENCODE', FALSE);		
	}
	if (strlen($htmlcode) < 200) {
		
		return "htmlcode_empty";
		
	} else {
		
		return array('htmlcode'=>$htmlcode,'catchUrl'=>$catchUrl);
		
	}

}
function show_sourcelink($url1,$url2)
{

	if(strlen($url1)>5 && stripos($url1,'csdn123')==false)
	{
		return $url1;
	} else {
		return $url2;
	}
	
}
function typename($cid)
{
	global $dsql;
	$row = $dsql->GetOne("SELECT typename FROM `#@__arctype` WHERE id='$cid'; ");
	return $row['typename'];
}
function ext_dfsockopen($url,$cookie = '')
{
	if(function_exists('curl_init') && function_exists('curl_exec')) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_REFERER, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);		
		if(strlen($cookie)>5) {
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}		
		$data = curl_exec($ch);
		$status = curl_getinfo($ch);
		$errno = curl_errno($ch);
		curl_close($ch);
		if($errno || $status['http_code'] != 200) {
			return;
		} else {
			return $data;
		}
	} else {
		return 'Not open PHP curl';
	}
	
}
if(!empty($_GET['zhiwu']) && strlen($_GET['zhiwu'])>5)
{
	setcookie('zhiwu',$_GET['zhiwu'],time()+3600*999);
	
}elseif(!empty($_POST['zhiwu']) && strlen($_POST['zhiwu'])>5)
{
	setcookie('zhiwu',$_POST['zhiwu'],time()+3600*999);
}
?>