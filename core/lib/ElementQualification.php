<?php
namespace Clho\QualityCrowd;

class ElementQualification extends StepElement
{
	private $qualiMain;

	protected function init() 
	{
		$qualiBatch = $this->arguments['qualification-batch'];

		if (!$this->skip()) {
			$this->qualiMain = new Main($qualiBatch, $this->step->workerId(), 'qualification-main');
		}
	}

	public function skip()
	{
		$qualiBatch = $this->arguments['qualification-batch'];
		$done = $this->store->readWorker('done', false, $qualiBatch, $this->step->workerId());
		return $done;
	}

	public function validate($data) 
	{
		if (!isset($this->qualiMain)) return true;
		
		$qualiStepNum = $data['stepNum-qualification-main'];
		$qualiStepId = $this->qualiMain->getBatch()->translateStepNum($qualiStepNum, $this->step->workerId());

		if ($this->qualiMain->getBatch()->countSteps() == $qualiStepId + 1) {
			return true;
		} else {
			return false;
		}
	}

	protected function prepareRender()
	{
		if (isset($this->qualiMain)) {
			$this->tpl->set('content', $this->qualiMain->render());
		} else {
			$this->tpl->set('content', 'NOTHING TO DO');
		}
	}
}
