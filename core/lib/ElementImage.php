<?php

class ElementImage extends StepElement
{
	protected function init() 
	{
		
	}
	
	public function getResults($data)
	{
		return array('image-' . $this->uid => $this->arguments['image']);
	}

	public function getColumns()
	{
		return array('image-' . $this->uid);
	}

	protected function prepareRender()
	{
		$this->tpl->set('image', $this->properties['mediaurl'] . $this->arguments['image']);
	}
}
