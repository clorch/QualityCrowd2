<?php
namespace Clho\QualityCrowd;

class AdminDoc extends AdminPage
{
	protected function prepareRender()
	{
		$subpage = (isset($this->path[1]) ? $this->path[1] : '');
		$this->tpl->set('subpage', $subpage);

		switch($subpage)
		{
			default:
			case '':
				$md = file_get_contents(ROOT_PATH . 'core'.DS.'doc'.DS.'qc-script.md');
				$html = \Michelf\Markdown::defaultTransform($md);
				$this->tpl->set('content', $html);
				break;

			case 'reference':
				$myTpl = new Template('admin.doc.reference');
				$myTpl->set('syntax', BatchCompiler::$syntax);
				$this->tpl->set('content', $myTpl->render());
				break; 
		}
	}
}
