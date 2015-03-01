<?php
namespace Clho\QualityCrowd;

class Step extends Base
{
	private $tpl;
	
	private $batch;
	private $workerId;
	private $stepId;
	private $elements;
	private $properties;

	public function __construct($stepArray, Batch $batch, $workerId, $stepId)
	{
		parent::__construct();

		$this->batch = $batch;
		$this->workerId = $workerId;
		$this->stepId = $stepId;
		$this->elements = $stepArray['elements'];
		$this->properties = $stepArray['properties'];
		
		$this->tpl = new Template('step', $this->batch->id());
	}

	public function batch()
	{
		return $this->batch;
	}

	public function workerId()
	{
		return $this->workerId;
	}

	private function getElement($element, $ek)
	{
		$uid = hash("crc32b", $this->batch->id() . '-' . $this->stepId . '-' . $ek);
		$class = 'Clho\QualityCrowd\Element' . ucfirst($element['command']);
		return new $class($element, $this, $uid);
	}

	// return true if this step should be skipped
	public function skip()
	{
		foreach($this->elements as $ek => $element) {
			$elementObject = $this->getElement($element, $ek);
			if (! $elementObject->skip()) return false;
		}

		return true;
	}

	public function render()
	{
		if (is_array($this->properties)) {
			$this->tpl->setArray($this->properties);
		}

		$elementRenderings = array();
		
		foreach($this->elements as $ek => $element) {
			$elementObject = $this->getElement($element, $ek);
			$elementRenderings[] = $elementObject->render();
		}

		$this->tpl->set('elements', $elementRenderings);
		return $this->tpl->render();
	}

	public function validate(&$data) 
	{
		if ($this->properties['skipvalidation']) return true;

		$msgs = array();

		foreach($this->elements as $ek => $element) 
		{
			$elementObject = $this->getElement($element, $ek);
			$msg = $elementObject->validate($data);

			if ($msg === false) {
				return false;
			} elseif (is_array($msg)) {
				$msgs = array_merge($msgs, $msg);
			}
		}

		if (count($msgs) > 0) return $msgs;
		return true;
	} 

	public function save($data)
	{
		$results = array(
				'stepId' => $this->stepId, 
				'timestamp' => time());

		foreach($this->elements as $ek => $element) {
			$elementObject = $this->getElement($element, $ek);
			$results = array_merge($results, $elementObject->getResults($data));
		}

		$this->store->writeWorkerCSV('results', array($results), $this->batch->id(), $this->workerId);
	}

	public function getColumns()
	{
		$columns = array();

		foreach($this->elements as $ek => $element) {
			$elementObject = $this->getElement($element, $ek);
			$columns = array_merge($columns, $elementObject->getColumns());
		}

		return $columns;
	}
}
