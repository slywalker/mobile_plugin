<?php
App::import('Controller', 'Controller', false);
App::import('Component', array('Mobile.MobileRequestHandler'));

class MobileRequestHandlerTestController extends Controller {

/**
 * name property
 *
 * @var string
 * @access public
 */
	var $name = 'MobileRequestHandlerTest';

/**
 * uses property
 *
 * @var mixed null
 * @access public
 */
	var $uses = null;

/**
 * construct method
 *
 * @param array $params
 * @access private
 * @return void
 */
	function __construct($params = array()) {
		foreach ($params as $key => $val) {
			$this->{$key} = $val;
		}
		parent::__construct();
	}

/**
 * test method for ajax redirection
 *
 * @return void
 */
	function destination() {
		$this->viewPath = 'posts';
		$this->render('index');
	}
/**
 * test method for ajax redirection + parameter parsing
 *
 * @return void
 */
	function param_method($one = null, $two = null) {
		echo "one: $one two: $two";
		$this->autoRender = false;
	}
}

class MobileRequestHandlerComponentTest extends CakeTestCase {

/**
 * Controller property
 *
 * @var MobileRequestHandlerTestController
 * @access public
 */
	var $Controller;

/**
 * MobileRequestHandler property
 *
 * @var MobileRequestHandlerComponent
 * @access public
 */
	var $MobileRequestHandler;

/**
 * startTest method
 *
 * @access public
 * @return void
 */
	function startTest() {
		$this->_init();
	}

/**
 * init method
 *
 * @access protected
 * @return void
 */
	function _init() {
		$this->Controller = new MobileRequestHandlerTestController(array('components' => array('MobileRequestHandler')));
		$this->Controller->constructClasses();
		$this->MobileRequestHandler =& $this->Controller->MobileRequestHandler;
	}

/**
 * endTest method
 *
 * @access public
 * @return void
 */
	function endTest() {
		unset($this->MobileRequestHandler);
		unset($this->Controller);
		if (!headers_sent()) {
			header('Content-type: text/html'); //reset content type.
		}
		App::build();
	}

/**
 * testInitializeCallback method
 *
 * @access public
 * @return void
 */
	function testInitializeCallback() {
		$this->assertNull($this->MobileRequestHandler->carrier);

		$this->_init();
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$this->MobileRequestHandler->initialize($this->Controller);
		$this->assertFalse($this->MobileRequestHandler->isMobile());
		$this->assertEqual($this->MobileRequestHandler->carrier, null);

		$this->_init();
		$_SERVER['HTTP_USER_AGENT'] = 'DoCoMo/2.0 P903i';
		$this->MobileRequestHandler->initialize($this->Controller);
		$this->assertTrue($this->MobileRequestHandler->isMobile());
		$this->assertEqual($this->MobileRequestHandler->carrier, 'docomo');
		$result = $this->Controller->params['resolution'];
		$expected = array('width' => 240, 'height' => 320);
		$this->assertEqual($result, $expected);

		$this->_init();
		$_SERVER['HTTP_USER_AGENT'] = 'KDDI-SA31 UP.Browser/6.2.0.7.3.129 (GUI) MMP/2.0';
		$_SERVER['HTTP_X_UP_DEVCAP_SCREENPIXELS'] = '100,200';
		$this->MobileRequestHandler->initialize($this->Controller);
		$this->assertTrue($this->MobileRequestHandler->isMobile());
		$this->assertEqual($this->MobileRequestHandler->carrier, 'kddi');
		$result = $this->Controller->params['resolution'];
		$expected = array('width' => 100, 'height' => 200);
		$this->assertEqual($result, $expected);

		$this->_init();
		$_SERVER['HTTP_USER_AGENT'] = 'Vodafone/1.0/V903SH/SHJ001[/Serial] Browser/UP.Browser/7.0.2.1 Profile/MIDP-2.0';
		$_SERVER['HTTP_X_JPHONE_DISPLAY'] = '100*200';
		$this->MobileRequestHandler->initialize($this->Controller);
		$this->assertTrue($this->MobileRequestHandler->isMobile());
		$this->assertEqual($this->MobileRequestHandler->carrier, 'softbank');
		$result = $this->Controller->params['resolution'];
		$expected = array('width' => 100, 'height' => 200);
		$this->assertEqual($result, $expected);

		$this->_init();
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_0 like Mac OS X; ja-jp) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5A347 Safari/525.20';
		$this->MobileRequestHandler->initialize($this->Controller);
		$this->assertTrue($this->MobileRequestHandler->isMobile());
		$this->assertEqual($this->MobileRequestHandler->carrier, 'iphone');
		$result = $this->Controller->params['resolution'];
		$expected = array('width' => 240, 'height' => 320);
		$this->assertEqual($result, $expected);

		$this->_init();
		$_SERVER['HTTP_USER_AGENT'] = 'IE';
		$this->MobileRequestHandler->initialize($this->Controller);
		$this->assertFalse($this->MobileRequestHandler->isMobile());
		$this->assertEqual($this->MobileRequestHandler->carrier, null);
	}
}
?>