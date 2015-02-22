<?php

class ElementVideo extends StepElement
{
	protected function init() 
	{
		
	}

	public function validate($data)
	{
		$msg = array();
		
		if (!isset($data['watched-' . $this->uid]) || $data['watched-' . $this->uid] <> true) {
			$msg[] = 'You have to watch the whole video.';
		}

		if (count($msg) == 0) {
			return true;
		} else {
			return $msg;
		}
	}

	public function getResults($data)
	{
		$results = array();
		$i = 0;
		foreach ($this->arguments as $video) {
			$results['video' . $i . '-' . $this->uid] = $video;
			$i++;
		}
		return $results;
	}

	public function getColumns()
	{
		$cols = array();
		$i = 0;
		foreach ($this->arguments as $video) {
			$cols[] = 'video' . $i . '-' . $this->uid;
			$i++;
		}
		return $cols;
	}

	protected function prepareRender()
	{
		// prerender video players
		$videos = array();

		foreach ($this->arguments as $video)
		{
			$tpl = new Template('player', $this->step->batch()->id());
			$tpl->set('file', $this->properties['mediaurl'] . $video);
			$tpl->set('filename', $video);
			$tpl->set('width',  $this->properties['videowidth']);
			$tpl->set('height', $this->properties['videoheight']);
			$videos[$video] = $tpl->render();
		}

		$this->tpl->set('videos', $videos);
	}
}
