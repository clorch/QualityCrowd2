<?php
namespace Clho\QualityCrowd;

class Admin extends AdminPage
{
	private $username;

	public function __construct($username, $path = null)
	{
		parent::__construct(null);
		$this->path = $path;

		$this->username = $username;
		$this->tpl->set('username', $username);	
	}

	public function prepareRender()
	{
		$class = 'Clho\QualityCrowd\Admin' . ucfirst($this->path[0]);
		$pageObject = new $class($this->path);

		$this->tpl->set('page', $this->path[0]);
		$this->tpl->set('content', $pageObject->render());

		return $this->tpl->render();
	}
}
