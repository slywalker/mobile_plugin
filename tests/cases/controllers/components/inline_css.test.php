<?php
App::import('Controller', 'Controller', false);
App::import('Component', array('Mobile.InlineCss'));

class InlineCssTestController extends Controller {

/**
 * name property
 *
 * @var string
 * @access public
 */
	var $name = 'InlineCssTest';

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

class InlineCssComponentTest extends CakeTestCase {

/**
 * Controller property
 *
 * @var InlineCssTestController
 * @access public
 */
	var $Controller;

/**
 * InlineCss property
 *
 * @var InlineCssComponent
 * @access public
 */
	var $InlineCss;

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
		$this->Controller = new InlineCssTestController(array('components' => array('InlineCss')));
		$this->Controller->constructClasses();
		$this->InlineCss =& $this->Controller->InlineCss;
	}

/**
 * endTest method
 *
 * @access public
 * @return void
 */
	function endTest() {
		unset($this->InlineCss);
		unset($this->Controller);
		if (!headers_sent()) {
			header('Content-type: text/html'); //reset content type.
		}
		App::build();
	}


/**
 * testInlineHeadAndInlineBody method
 *
 * @access public
 * @return void
 */
	function testParseCss() {
		$this->_init();
		$css = <<<CSS
body {background-color:#fff;}
a:hover {color:#000;}
h1 span {font-size:xx-small;}
h2 {
	font-weight:normal;
	margin:0 0 0 0;
	padding:0 0 0 0;
	font-size:xx-small;
	color:#fff;
}
#foo {font-size:xx-small;}
.bar {font-size:xx-small;}
CSS;
		$result = $this->InlineCss->__parseCss($css);
		$expected = array(
			'body' => 'background-color:#fff;',
			'a:hover' => 'color:#000;',
			'h1 span' => 'font-size:xx-small;',
			'h2' => 'font-weight:normal;margin:0 0 0 0;padding:0 0 0 0;font-size:xx-small;color:#fff;',
			'#foo' => 'font-size:xx-small;',
			'.bar' => 'font-size:xx-small;',
		);
		$this->assertEqual($result, $expected);
	}

/**
 * testInlineHeadAndInlineBody method
 *
 * @access public
 * @return void
 */
	function testInlineHeadAndInlineBody() {
		$this->_init();
		$styles = array(
			'body' => 'background-color:#fff;',
			'a:hover' => 'color:#000;',
			'h1 span' => 'font-size:xx-small;',
			'h2' => 'font-weight:normal;margin:0 0 0 0;padding:0 0 0 0;font-size:xx-small;color:#fff;',
			'#foo' => 'font-size:xx-small;',
			'.bar' => 'font-size:xx-small;',
		);
		$html = <<<HTML
<?xml version="1.0" encoding="Shift_JIS"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=Shift_JIS" />
	<meta content="width=320, minimum-scale=0.5" name="viewport" />
	<meta name="description" content=""/><meta name="keywords" content=""/>
	<title>page_title</title>
</head>
<body style="color:#000;">
	<div>
		<h1><span>h1_title</span></h1>
		<div>
			<h2><span>h2_title</span></h2>
		</div>
		<hr />
	</div>
	<div id="foo">
		<div>
			<ul>
				<li><a href="#">testLink1</a></li>
				<li><a href="#">testLink1</a></li>
			</ul>
		</div>
	</div>
	<div>
		<hr />
		<span class="bar">Copyrights(C)2009<br />
		someone<br />
		All rights reserved</span>
	</div>
</body>
</html>
HTML;
		$html = $this->InlineCss->__inlineHead($html, $styles);
		$result = $this->InlineCss->__inlineBody($html, $styles);
		$expected = <<<HTML
<?xml version="1.0" encoding="Shift_JIS"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=Shift_JIS" />
	<meta content="width=320, minimum-scale=0.5" name="viewport" />
	<meta name="description" content=""/><meta name="keywords" content=""/>
	<title>page_title</title>
<style type="text/css">
<![CDATA[
a:hover{color:#000;}
]]>
</style></head>
<body style="background-color:#fff;color:#000;">
	<div>
		<h1><span style="font-size:xx-small;">h1_title</span></h1>
		<div>
			<h2 style="font-weight:normal;margin:0 0 0 0;padding:0 0 0 0;font-size:xx-small;color:#fff;"><span>h2_title</span></h2>
		</div>
		<hr />
	</div>
	<div id="foo" style="font-size:xx-small;">
		<div>
			<ul>
				<li><a href="#">testLink1</a></li>
				<li><a href="#">testLink1</a></li>
			</ul>
		</div>
	</div>
	<div>
		<hr />
		<span class="bar" style="font-size:xx-small;">Copyrights(C)2009<br />
		someone<br />
		All rights reserved</span>
	</div>
</body>
</html>
HTML;
		$this->assertEqual($result, $expected);
	}
}
?>