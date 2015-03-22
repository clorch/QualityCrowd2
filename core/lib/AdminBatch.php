<?php
namespace Clho\QualityCrowd;

class AdminBatch extends AdminPage
{
	protected function prepareRender()
	{
		$batchId = $this->path[1];
		$this->tpl->set('id', $batchId);

		if (!isset($this->path[2])) $this->path[2] = '';

		try {
			if ($this->path[2] == 'edit' or $this->path[2] == 'new') {
				$this->edit($batchId);
			} else {
				$this->otherPages($batchId);
			}
		} catch (\Exception $e) {
			$this->emergencyEdit($batchId, $e->getMessage());
		}
	}

	private function otherPages($batchId)
	{
		$myBatchCompiler = new BatchCompiler($batchId);
		$batch = $myBatchCompiler->getBatch();

		$this->tpl->set('subpage', $this->path[2]);

		switch($this->path[2]) {
		case 'settings':
			// save state
			if (isset($_POST['state'])) {
				$batch->setState($_POST['state']);
			}

			$myTpl = new Template('admin.batch.settings');
			$myTpl->set('state', $batch->state());

			if ($batch->state() == 'post' or $batch->state() == 'active') {
				$myTpl->set('readonly', true);
			} else {
				$myTpl->set('readonly', false);
			}
			break;

		case 'validate':
			$myTpl = new Template('admin.batch.validate');

			$workerId = (isset($_GET['workerid']) ? $_GET['workerid'] : '');
			$workerId = preg_replace("/[^a-zA-Z0-9-]/", "", $workerId);
			$result = $batch->getWorker($workerId);
			$myTpl->set('result', $result);
			$myTpl->set('query', $workerId);
			break;

		case 'results':
			$myTpl = new Template('admin.batch.results');
			$myTpl->set('steps', $batch->steps());
			$myTpl->set('columns', $batch->getColumns());
			$myTpl->set('results', $batch->resultsPerStep());
			break;

		case 'results.csv':
			$myTpl = new Template('admin.batch.results.csv');
			$myTpl->set('batchId', $batchId);
			$myTpl->set('columns', $batch->getColumns());
			$myTpl->set('workers', $batch->workers(true));
			echo $myTpl->render();
			exit;
			break;

		case 'results.xlsx':
			$myTpl = new Template('admin.batch.results.xlsx');
			$myTpl->set('batchId', $batchId);
			$myTpl->set('columns', $batch->getColumns());
			$myTpl->set('workers', $batch->workers(true));
			echo $myTpl->render();
			exit;
			break;

		case 'browsers':
			$myTpl = new Template('admin.batch.browsers');
			$myTpl->set('workers', $batch->workers());
			break;

		default:
			if (is_numeric($this->path[2]))
			{
				$myTpl = new Template('admin.batch.preview');
				$stepId = $this->path[2];
				$step = $batch->getStepObjectFromId($stepId, 'WID');
				$myTpl->set('stepid', $stepId);
				$myTpl->set('preview', $step->render());
			}
			break;
		}
		
		if (!isset($myTpl))
		{
			$myTpl = new Template('admin.batch.details');

			$myTpl->set('properties', $batch->meta());
			$myTpl->set('groups', $batch->groups());
			$myTpl->set('state', $batch->state());
		}

		$this->tpl->set('content', $myTpl->render());
	}

	private function edit($batchId)
	{
		$this->tpl->set('subpage', 'edit');

		$files = new BatchFiles($batchId);
		$myTpl = new Template('admin.batch.edit');
		$myTpl->set('files', $files->getFiles());

		// get filename
		if (!isset($this->path[3])) {
			$this->path[3] = 'definition.qcs';
		}
		$filename = preg_replace("/[^a-zA-Z0-9-\.]/", "", $this->path[3]);
		$myTpl->set('filename', $filename);

		// save edited file
		if ($this->path[2] == 'edit' && isset($this->path[3]) 
			&& isset($_POST['filecontents'])) 
		{
			$files->writeFile($filename, $_POST['filecontents']);
		}

		// compile batch
		$myBatchCompiler = new BatchCompiler($batchId);
		if (!$myBatchCompiler->exists() && $this->path[2] == 'new') {
			$myBatchCompiler->create();
		}
		$batch = $myBatchCompiler->getBatch();
		
		$myTpl->set('state', $batch->state());
		$myTpl->set('filecontents', $files->readFile($filename));
		if ($batch->state() == 'post' or $batch->state() == 'active') {
			$myTpl->set('readonly', true);
		} else {
			$myTpl->set('readonly', false);
		}

		$this->tpl->set('content', $myTpl->render());
	}

	private function emergencyEdit($batchId, $message)
	{
		$this->tpl->set('subpage', 'edit');
		$myTpl = new Template('admin.batch.edit');
		$files = new BatchFiles($batchId);
		$myTpl->set('files', $files->getFiles());
		$myTpl->set('filename', 'definition.qcs');
		$myTpl->set('filecontents', $files->readSource());
		$myTpl->set('state', 'error');
		$myTpl->set('error', $message);
		$myTpl->set('readonly', false);
		$this->tpl->set('content', $myTpl->render());
	}
}
