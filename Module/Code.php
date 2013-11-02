<?php
/**
 * @author Artjom Kurapov
 * @since 20.07.12 19:56
 */

namespace Gratheon\CMS\Module;
use Gratheon\CMS;
use Gratheon\Core;

class Code extends \Gratheon\CMS\ContentModule implements \Gratheon\CMS\Module\Behaviour\Embeddable {
	public $name = 'code';

	protected $supportedLanguages = array(
		'shell', 'php', 'java', 'javascript', 'css', 'mysql', 'clojure', 'coffeescript', 'erlang', 'go',
		'groovy', 'haskell', 'less', 'lua', 'pascal', 'perl', 'plsql', 'python', 'rpm',
		'ruby', 'scheme', 'smalltalk', 'assign', 'sparql', 'yaml',
	);


	public function edit($recMenu = null) {
		$content_code = $this->model('content_code');
		$this->assign('bHideContainer', true);
		$parentID = $recMenu->ID;
		if($parentID) {
			$recElement = $content_code->obj('parentID=' . $parentID);
			if(!$recElement) {
				throw new \Exception('Code entry for editing was not found');
			}

			if($recElement->language == 'php') {
				$recElement->content = "<?php\n" . $recElement->content;
			}

			$this->assign('recElement', $recElement);
		}

		$this->assign('supportedLanguages', $this->supportedLanguages);

		$this->add_css('/vendor/codemirror/codemirror/lib/codemirror.css', false);
		$this->add_css('/vendor/codemirror/codemirror/theme/ambiance.css', false);
//        $this->add_css('/vendor/codemirror/codemirror/doc/docs.css',false);

		$this->add_js('/vendor/codemirror/codemirror/lib/codemirror.js', false);
		$this->add_js('/vendor/codemirror/codemirror/lib/util/loadmode.js', false);
		$this->add_js('/vendor/codemirror/codemirror/lib/util/formatting.js', false);

		$this->add_js('/vendor/codemirror/codemirror/mode/xml/xml.js', false);
		$this->add_js('/vendor/codemirror/codemirror/mode/javascript/javascript.js', false);
		$this->add_js('/vendor/codemirror/codemirror/mode/css/css.js', false);
		$this->add_js('/vendor/codemirror/codemirror/mode/clike/clike.js', false);

		/*
				$this->add_js('/vendor/codemirror/codemirror/mode/htmlmixed/htmlmixed.js',false);*/


		if($parentID && $recElement) {
			$this->add_js('/vendor/codemirror/codemirror/mode/' . $recElement->language . '/' . $recElement->language . '.js', false);
		}

		$this->add_js('modules/code/edit.js');
	}


	public function update($parentID) {
		$content_code         = $this->model('content_code');
		$recElement           = $content_code->obj('parentID=' . $parentID);
		$recElement->language = $this->controller->in->post['language'];
		$recElement->content  = trim($this->controller->in->post['content']);


		if($recElement->language == 'php') {
			$recElement->content = substr($recElement->content, 7);
		}

		$content_code->update($recElement);
	}


	public function insert($parentID) {
		$content_code = $this->model('content_code');

		$recElement           = new \Gratheon\Core\Record();
		$recElement->parentID = $parentID;
		$recElement->language = $this->controller->in->post['language'];
		$recElement->content  = trim($this->controller->in->post['content']);
		if($recElement->language == 'php' && substr($recElement->content, 2, 3) != 'php') {
			$recElement->content = substr($recElement->content, 7);
		}
		$content_code->insert($recElement);
	}


	public function delete($parentID) {
		$content_code = $this->model('content_code');
		$content_code->delete("parentID=" . $parentID);
	}


	//Embeddable
	public function getPlaceholder($menu) {
		$parentID = $menu->ID;
		$ID       = $menu->elementID;

		$content_code = $this->model('content_code');
		$record       = $content_code->obj('parentID=' . $parentID);
		return str_replace("\n", '<br />', (htmlentities($record->content, null, 'UTF-8')));
	}


	public function decodeEmbeddable($menu) {
		$parentID = $menu->ID;
		$ID       = $menu->elementID;

		$content_code = $this->model('content_code');
		$record       = $content_code->obj("parentID=" . $parentID);

		$record->content = '<pre><code class="' . $record->language . '">' . htmlentities($record->content, null, 'UTF-8') . '</code></pre>';

//		$this->add_css('/vendor/Gratheon/CMS/assets/css/modules/code/default.min.css', false);
//		$this->add_css('/vendor/Gratheon/CMS/assets/css/modules/code/tomorrow-night.css', false);
		$this->add_js('modules/code/highlight.min.js', false);

		return $record->content;
	}

//
//	public function addCodeHightlight($str) {
//		preg_match_all('/<code class=["\']+php["\']+>(.*)<\/code>/Uis', $str, $matches);
//
//		require_once 'ext/hyperlight/hyperlight.php';
//
//		foreach($matches[1] as $match) {
//			//$code = hyperlight(html_entity_decode($match),'php');
//			$highlighter = new HyperLight('iphp');
//			$code        = $highlighter->render(htmlspecialchars_decode(str_replace(
//				array('<br />', '&nbsp;', "<br>", '<span class="Apple-tab-span" style="white-space:pre">  </span>'),
//				array("\n", ' ', "\n", "\t"),
//				$match
//			)));
//
//			//$code = ((php_highlight(html_entity_decode($match))));
//			//pre($code);
//			$str = str_replace($match, $code, $str);
//		}
//
//		return $str; //$parser->HighlightPHP($str);
//	}
}