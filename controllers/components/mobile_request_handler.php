<?php
class MobileRequestHandlerComponent extends Object {
	
	var $components = array('Session', 'RequestHandler');

	var $crawlerUA = array('Googlebot-Mobile', 'Y!J-SRD', 'Y!J-MBS');

	var $isMobile = null;

	var $userAgent = null;
	
	var $carrier = null;

	var $resolution = array('width' => 240, 'height' => 320);

	var $mobileUA = array(
		'docomo' => array('/^DoCoMo.+$/'),
		'kddi' => array('/^KDDI.+UP.Browser.+$/', '/^UP.Browser.+$/'),
		'softbank' => array('/^(SoftBank|Vodafone|J-PHONE|MOT-C).+$/'),
		'iphone' => array('/^Mozilla.+iPhone.+$/'),
		'willcom' => array('/^Mozilla.+(WILLCOM|DDIPOCKET|MobilePhone).+$/', '/^PDXGW.+$/'),
		'emobile' => array('/^emobile.+$/'),
	);

	var $settings = array(
		'crawler' => true, // add crawler in mobile
		'iphone' => true, // add iPhone in mobile
		'kana' => 'rank', // mb_convert_kana option
		'encoding' => 'Shift_JIS', //mb_convert_encoding option
	);

	function initialize(&$controller, $settings = array()) {
		$this->settings = array_merge($this->settings, $settings);
		$this->userAgent = env('HTTP_USER_AGENT');
		$this->isMobile = $controller->params['isMobile'] = $this->isMobile();
		$controller->params['resolution'] = $this->getResolution();
		
		if ($this->isMobile()) {
			$this->__session($controller);
			$this->__data($controller);
			$this->__params($controller);
			$this->__passedArgs($controller);
		}
	}

	function __session(&$controller) {
		$cookie = Configure::read('Session.cookie');
		if (!empty($controller->params['url'][$cookie])) {
			$this->Session->id($controller->params['url'][$cookie]);
			$this->Session->renew();
		}
	}

	function __data(&$controller) {
		if (!empty($controller->data) && Configure::read('App.encoding') !== $this->settings['encoding']) {
			mb_convert_variables(Configure::read('App.encoding'), $this->settings['encoding'], $controller->data);
		}
	}

	function __params(&$controller) {
		if (!empty($controller->params) && Configure::read('App.encoding') !== $this->settings['encoding']) {
			mb_convert_variables(Configure::read('App.encoding'), $this->settings['encoding'], $controller->params);
		}
	}

	function __passedArgs(&$controller) {
		if (!empty($controller->passedArgs) && Configure::read('App.encoding') !== $this->settings['encoding']) {
			mb_convert_variables(Configure::read('App.encoding'), $this->settings['encoding'], $controller->passedArgs);
		}
	}

	function beforeRedirect(&$controller, $url, $status = null, $exit = true) {
		if ($this->isMobile()) {
			//
		}
		return $url;
	}

	function shutdown(&$controller) {
		if ($this->isMobile()) {
			$this->__convert($controller);
			$this->__header();
		}
	}

	function __convert(&$controller) {
		if ($controller->params['url']['ext'] === 'html') {
			if (!empty($this->settings['kana'])) {
				$controller->output = mb_convert_kana($controller->output, $this->settings['kana'], Configure::read('App.encoding'));
			}
			if (Configure::read('App.encoding') !== $this->settings['encoding']) {
				$controller->output = mb_convert_encoding($controller->output, $this->settings['encoding'], Configure::read('App.encoding'));
			}
		}
	}

	function __header() {
		if ($this->getCarrier() === 'kddi') {
			header("Content-type: text/html; charset={$this->settings['encoding']}");
		} else {
			header("Content-type: application/xhtml+xml; charset={$this->settings['encoding']}");
		}
	}

	function isMobile() {
		if (is_null($this->isMobile)) {
			if (!empty($this->userAgent)) {
				foreach ($this->mobileUA as $carrier => $regix) {
					if ($carrier === 'iphone' && $this->settings['iphone'] === false) {
						continue;
					}
					foreach ($regix as $reg) {
						if (preg_match($reg, $this->userAgent)) {
							$this->carrier = $carrier;
							return true;
						}
					}
				}
				if ($this->settings['crawler']) {
					foreach ($this->crawlerUA as $crawlerUA) {
						if (strpos($this->userAgent, $crawlerUA) !== false) {
							$this->carrier = 'crawler';
							return true;
						}
					}
				}
			}
			return false;
		}
		return $this->isMobile;
	}

	function getCarrier() {
		if ($this->isMobile()) {
			return $this->carrier;
		}
		return false;
	}

	function getUid() {
		if ($carrier = $this->getCarrier()) {
			if ($carrier === 'docomo') {
				return env('HTTP_X_DCMGUID');
			}
			if ($carrier === 'softbank') {
				return env('HTTP_X_JPHONE_UID');
			}
			if ($carrier === 'kddi') {
				return env('HTTP_X_UP_SUBNO');
			}
		}
		return false;
	}

	function getResolution() {
		if ($carrier = $this->getCarrier()) {
			$width = $height = null;
			if ($carrier === 'softbank') {
				list($width, $height) = explode("*", env('HTTP_X_JPHONE_DISPLAY'));
			}
			elseif ($carrier === 'kddi') {
				list($width, $height) = explode(",", env('HTTP_X_UP_DEVCAP_SCREENPIXELS'));
			}
			
			if ((int) $width && (int) $height) {
				return array('width' => (int) $width, 'height' => (int) $height);
			}
		}
		return $this->resolution;
	}
}