<?php
class MobileIp {

	var $url = array(
		'DoCoMo' => 'http://www.nttdocomo.co.jp/service/imode/make/content/ip/index.html',
		'SoftBank' => 'https://creation.mb.softbank.jp/web/web_ip.html',
		'AU' => 'http://www.au.kddi.com/ezfactory/tec/spec/ezsava_ip.html',
		'WILLCOM' => 'http://www.willcom-inc.com/ja/service/contents_service/create/center_info/index.html',
		'Emobile' => 'http://developer.emnet.ne.jp/ipaddress.html',
	);

	function getAllIP() {
		$results = array();
		foreach ($this->url as $ua => $url) {
			$results[$ua] = $this->getIP($url);
		}
		return $results;
	}

	function getIP($url, $targrtEncode = 'SJIS', $internalEncode = 'UTF-8') {
		$htmlText = file_get_contents($url);
		$htmlText = mb_convert_encoding($htmlText, 'UTF-8', $targrtEncode);
		$htmlText = str_replace("\r\n", "\n", $htmlText);
		$htmlText = str_replace("\n", '', $htmlText);
		preg_match_all('/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}(.*?)\/[0-9]{1,3})/', $htmlText, $result);
		foreach ($result[0] as $key => $value) {
			$result[0][$key] = str_replace($result[2][$key], '', $result[1][$key]);
		}
		return array_unique($result[0]);
	}

	function showAllIP() {
		$results = $this->getAllIP();
		var_dump($results);
		echo '<pre>';
		foreach ($results as $ua => $ips) {
			echo "\n{$ua}\n";
			foreach ($ips as $ip) {
				echo "'{$ip}',\n";
			}
		}
		echo '</pre>';
	}
}

// header("Content-Type: text/html; charset=UTF-8");
// $mobile = new MobileIp();
// $mobile->showAllIP();
?>