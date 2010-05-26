<?php
App::import('Core', 'HttpSocket');
App::import('Vendor', 'Mobile.simple_html_dom', array('file' => 'simple_html_dom.php'));

class InlineCssComponent extends Object {

	function shutdown(&$controller) {
		$controller->output = $this->inlineCss($controller->output);
	}

	function inlineCss($html) {
		$css = $this->__getCss($html);
		$styles = $this->__parseCss($css);
		$html = $this->__inlineHead($html, $styles);
		$html = $this->__inlineBody($html, $styles);
		return $html;
	}

	function __getCss($html) {
		// headからCSSファイルを取り出す
		$html = preg_replace('!\s+!', ' ', trim($html));
		preg_match('/<head>.*<\/head>/s', $html, $match);
		
		$dom = str_get_html($matches[0]);
		$css = '';
		$elements = $dom->find('link[type=text/css]');
		foreach ($elements as $element) {
			if (is_object($element)) {
				if (strpos($element->href, 'http://') === 0) {
					$url = $element->href;
				} else {
					$url = Router::url($element->href, true);
				}
				$css .= HttpSocket::get($url);
			}
		}
		$dom->clear();
		
		return $css;
	}

	function __parseCss($css) {
		$css = preg_replace('!\s+!', ' ', $css);
		$css = preg_replace('/\/\*(?:(?!\*\/).)*\*\//', '', $css);
		$css = trim($css);
		$css = preg_replace('!\s*{\s*!', '{', $css);
		$css = preg_replace('!\s*:\s*!', ':', $css);
		$css = preg_replace('!\s*;\s*!', ';', $css);
		$css = preg_replace('!\s*}\s*!', '}', $css);
		preg_match_all('/([^{]+){([^}]+)}/', $css, $matchs);
		$styles = array();
		foreach ($matchs[0] as $key => $match) {
			$styles[$matchs[1][$key]] = $matchs[2][$key];
		}
		return $styles;
	}

	function __inlineHead($html, $styles) {
		// a:*をヘッダ内に格納
		$css = array();
		$css[] = '<style type="text/css"> <![CDATA[';
		$links = array('a:link', 'a:hover', 'a:focus', 'a:visited');
		foreach ($links as $link) {
			if (isset($styles[$link])) {
				$css[] = $link . '{' . $styles[$link]. '}';
				unset($styles[$link]);
			}
		}
		$css[] = ']]> </style>';
		
		$html = preg_replace('/<\/head>/s', implode("\n", $css).'</head>', $html);
		return $html;
	}

	function __inlineBody($html, $styles) {
		preg_match('/<body[^>]*>.*<\/body>/s', $html, $matches);
		$dom = str_get_html($matches[0]);
		// インライン化
		foreach ($styles as $path => $style) {
			$elements = $dom->find($path);
			foreach ($elements as $element) {
				if (is_object($element)) {
					$style .= $element->getAttribute('style');
					$element->setAttribute('style', $style);
				}
			}
		}
		// html再構成
		$body = $dom->save();
		$dom->clear();
		
		$html = preg_replace('/<body[^>]*>.*<\/body>/s', $body, $html);
		$html = preg_replace('/> /', ">\n", $html);
		$html = preg_replace('/ </', "\n<", $html);
		return $html;
	}
}
?>