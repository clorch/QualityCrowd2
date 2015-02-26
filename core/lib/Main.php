<?php

class Main extends Base
{
	private $batch;
	private $tpl;
	private $scope;

	private $batchId;
	private $workerId;
	private $lastStepNum;
	private $refreshStep = false;

	public function __construct($batchId, $workerId, $scope = 'main', $restart = false)
	{
		parent::__construct();
		$this->store = new DataStore();

		$this->batchId = $batchId;		
		$this->workerId = $workerId;
		$this->scope = $scope;

		$this->tpl = new Template($this->scope, $this->batchId);
		$this->tpl->set('workerId', $this->workerId);
		$this->tpl->set('batchId', $this->batchId);
		$this->tpl->set('scope', $this->scope);

		// handle manual restart
		if ($restart) $this->store->deleteWorker($this->batchId, $this->workerId);

		// compile the batch script
		$myBatchCompiler = new BatchCompiler($this->batchId);
		$this->batch = $myBatchCompiler->getBatch();
	}

	public function getBatch()
	{
		return $this->batch;
	}

	public function render()
	{
		// read last step id
		$this->lastStepNum = $this->store->readWorker('stepNum', -1, $this->batchId, $this->workerId);

		// fresh start
		if ($this->lastStepNum == -1) {
			$this->batch->init($this->workerId);
			$this->lastStepNum = 0;
		}

		// process submitted post data
		$errorMessages = $this->handlePostData();

		// display error messages
		if (is_array($errorMessages)) {
			$this->tpl->set('msg', $errorMessages);
		}

		// calculate current step id
		$stepNum = $this->lastStepNum;
		if (!$this->refreshStep) $stepNum++;

		while($stepNum < $this->batch->countSteps()) {
			$step = $this->batch->getStepObject($stepNum, $this->workerId);
			if ($step->skip()) {
				$stepNum++;
			} else {
				break;
			}
		}
	
		if ($stepNum < 0) $stepNum = 0;
		if ($stepNum >= $this->batch->countSteps()) $stepNum = $this->batch->countSteps() - 1;

		$this->store->writeWorker('stepNum', $stepNum, $this->batchId, $this->workerId);

		// handle last step
		if ($stepNum == $this->batch->countSteps() - 1) {
			$this->batch->lockingFinish($this->workerId);
			$this->store->writeWorker('done', true, $this->batchId, $this->workerId);
		}

		// set variables
		$this->tpl->set('stepNum', $stepNum);
		$this->tpl->set('stepCount', $this->batch->countSteps());
		$this->tpl->set('state', $this->batch->state());
		$this->tpl->set('isLocked', $this->batch->lockingUpdate($this->workerId));
		$meta = $this->batch->meta();
		$this->tpl->set('timeout', $meta['timeout']);

		// render step
		$this->tpl->set('content', $step->render());

		return $this->tpl->render();
	}

	private function handlePostData()
	{
		$msg = '';
		
		if (!isset($_POST['stepNum-' . $this->scope]))
		{
			if ($this->lastStepNum < 0) return;
			// user hit "reload" in his browser, changed the browser, ...
			$this->refreshStep = true;

		} else {
			if (!is_numeric($_POST['stepNum-' . $this->scope]))
			{
				$msg = array('invalid form data submitted');
				$this->refreshStep = true;
			}

			$stepNum = $_POST['stepNum-' . $this->scope];
			settype($stepNum, 'int');

			$data = $_POST;
			unset($data['stepNum-' . $this->scope]);

			if ($stepNum <> $this->lastStepNum) {
				// user hit "reload" in his browser and sent the post data again
				$this->refreshStep = true;
			} else {
				// validate answer data
				$step = $this->batch->getStepObject($stepNum, $this->workerId);
				$msg = $step->validate($data);

				// save answer data if valid
				if ($msg === true) {
					$step->save($data);
				} else {
					$this->refreshStep = true;
				}
			}
		}
		
		return $msg;
	}
}
