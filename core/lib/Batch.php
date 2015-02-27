<?php

class Batch extends Base
{
	private $batchId;
	private $groups;
	private $meta;

	private $state;

	public function __construct($batchId, $meta, $groups) 
	{
		parent::__construct();

		$this->batchId = $batchId;
		$this->meta = $meta;
		$this->groups = $groups;

		$this->state = $this->store->readBatch('state', 'edit', $this->batchId);
	}

	public function __sleep() 
	{
		parent::__sleep();
		return array('batchId', 'groups', 'meta');
	}

	public function __wakeup() 
	{
		parent::__wakeup();
		$this->state = $this->store->readBatch('state', 'edit', $this->batchId);
	}

	public function id()
	{
		return $this->batchId;
	}

	public static function readableState($state) 
	{
		switch($state)
		{
			case 'edit':   return 'Edit';
			case 'active': return 'Active';
			case 'post':   return 'Complete';
		}
	}

	public function init($workerId)
	{
		// collect and write meta data
		$meta = array(
			'workerId' 		=> $workerId,
			'timestamp' 	=> time(),
			'remoteaddr' 	=> md5($_SERVER['REMOTE_ADDR']),
			'useragent' 	=> $_SERVER['HTTP_USER_AGENT'],
		);

		$this->store->writeWorker('meta', $meta, $this->batchId, $workerId);
	}

	public function countSteps()
	{
		return count($this->steps());
	}

	public function meta($key = null)
	{
		if ($key == null) {
			return $this->meta;
		} else {
			return $this->meta[$key];
		}
	}

	public function groups()
	{
		return $this->groups;
	}

	public function steps()
	{
		$steps = array();
		foreach($this->groups as $group)
		{
			foreach($group['steps'] as $sk => $step) {
				$steps[$sk] = $step;
			}
		}
		return $steps;
	}

	public function lockingUpdate($workerId)
	{
		// read table
		$lockingTable = $this->store->readBatch('locking', array(), $this->batchId);

		// clean table
		foreach($lockingTable as $wid => $value)
		{
			if ($value == 'finished') continue;
			if ($value < (time() - $this->meta['timeout'])) unset($lockingTable[$wid]);
		}

		// check new worker
		if (!array_key_exists($workerId, $lockingTable)) {
			if ($this->meta['workers'] > 0 && count($lockingTable) >= $this->meta['workers']) {
				return false;
			}
		} else {
			if ($lockingTable[$workerId] == 'finished') return true;
		}

		// set lock
		$lockingTable[$workerId] = time();
		
		// write table
		$this->store->writeBatch('locking', $lockingTable, $this->batchId, $workerId);
		return true;
	}

	public function lockingFinish($workerId)
	{
		// read table
		$lockingTable = $this->store->readBatch('locking', array(), $this->batchId);

		// update table
		if (array_key_exists($workerId, $lockingTable)) {
			$lockingTable[$workerId] = 'finished';	
		} else {
			return false;
		}
		
		// write table back
		$this->store->writeBatch('locking', $lockingTable, $this->batchId, $workerId);
		return true;
	}

	public function state()
	{
		return $this->state;
	}

	public function setState($state)
	{
		if ($state <> 'edit' 
			&& $state <> 'active' 
			&& $state <> 'post') return false;

		// delete all data when changing from edit to active state
		if ($this->state == 'edit' && $state == 'active') {
			$this->store->deleteAllWorkers($this->batchId);
		}

		$this->state = $state;
		$this->store->writeBatch('state', $state, $this->batchId);
	}

	public function getWorker($wid)
	{
		$meta = $this->store->readWorker('meta', null, $this->batchId, $wid);
		if (is_array($meta)) {
    		$meta['stepNum'] = $this->store->readWorker('stepNum', null, $this->batchId, $wid);
    		$meta['stepId'] = $this->translateStepNum($meta['stepNum'], $wid);
    		$meta['finished'] = ($meta['stepId'] == $this->countSteps() - 1);
    	}

    	return $meta;
	}

	public function workers($includeResults = false)
	{
		$workers = array();

		$path = DATA_PATH . $this->batchId . DS . 'workers' . DS;
		$files = glob($path . '*', GLOB_MARK);

	    foreach ($files as $file) 
	    {
	    	$file = preg_replace('#^' . preg_quote($path) . '#', '', $file);
	    	$wid = preg_replace('#'.DSX.'$#', '', $file);

	    	$meta = $this->store->readWorker('meta', null, $this->batchId, $wid);
	    	$meta['stepNum'] = $this->store->readWorker('stepNum', null, $this->batchId, $wid);
	    	$meta['stepId'] = $this->translateStepNum($meta['stepNum'], $wid);
    		$meta['finished'] = ($meta['stepId'] == $this->countSteps() - 1);
    		$workers[$wid] = $meta;

	    	if ($includeResults) {
	    		$results = $this->store->readWorkerCSV('results', $this->batchId, $wid);

	    		// calculate durations
	    		$durations = array();
	    		if (is_array($results))
	    		{
	    			$lastTimestamp = $meta['timestamp'];
					foreach($results as $stepNum => &$stepResults)
					{
						$stepId = $stepResults[0];
						$durations[$stepId] = $stepResults[1] - $lastTimestamp;
						$lastTimestamp = $stepResults[1];
					}
				}

				// sort step id
				if (is_array($results)) {
					usort($results, function($a, $b) {
					    return $a[0] - $b[0];
					});
				} else {
					$results = array();
				}

				$workers[$wid]['durations'] = $durations;
				$workers[$wid]['results'] = $results;
	    	}
	    }

	    return $workers;
	}

	public function resultsPerStep()
	{
		$workers = $this->workers(true);
		$steps = [];
		$columns = $this->getColumns();

		foreach($workers as $wid => $w)
		{	
			// skip workers without result data
			if (!is_array($w['results'])) continue;

			foreach($w['results'] as $stepResults)
			{			
				$stepId = $stepResults[0];
				array_shift($stepResults); // step id
				array_shift($stepResults); // timestamp
				$colId = 0;
				foreach($columns[$stepId] as $col) {
					if (strpos($col, 'value') === 0) {
						$steps[$stepId]['results'][$col][$wid]= $stepResults[$colId];
					}
					$colId++;
				}
			}

			foreach($w['durations'] as $stepId => $duration)
			{
				$steps[$stepId]['durations'][$wid] = $duration;
			}
		}

		// process durations
		foreach($steps as $stepId => &$step)
		{
			$sum = 0;
			$max = 0;
			$min = time();

			foreach($step['durations'] as $wid => $duration)
			{
				if ($duration > $max) $max = $duration;
				if ($duration < $min) $min = $duration;
				$sum += $duration;
			}

			$step['duration-stats']['mean'] = $sum / count($step['durations']);
			$step['duration-stats']['max'] = $max;
			$step['duration-stats']['min'] = $min;

			if(!isset($step['results'])) {
				$step['results'] = [];
			}
		}

		// consolidate results part 1 - average, min, max
		foreach($steps as $stepId => &$step)
		{
			$sum = [];
			$max = [];
			$min = [];
			$cnt = [];

			foreach($step['results'] as $key => $result)
			{
				if (count($result) == 0) continue;

				foreach($result as $wid => $value) {
					if (!is_numeric($value)) continue;
				
					if (!isset($sum[$key])) {
						$sum[$key] = 0;
						$max[$key] = -0xffffffff;
						$min[$key] = 0xffffffff;
						$cnt[$key] = 0;
					}

					if ($value > $max[$key]) $max[$key] = $value;
					if ($value < $min[$key]) $min[$key] = $value;
					$sum[$key] += $value;
					$cnt[$key] ++;
				}
			}

			foreach($sum as $key => $_) {
				if ($cnt > 0) {
					$step['result-stats']['mean'][$key] = $sum[$key] / $cnt[$key];
					$step['result-stats']['max'][$key] = $max[$key];
					$step['result-stats']['min'][$key] = $min[$key];
				} else {
					$step['result-stats']['mean'][$key] = null;
					$step['result-stats']['max'][$key] = null;
					$step['result-stats']['min'][$key] = null;
				}
			}

			$step['workers'] = count($step['durations']);
		}

		// consolidate results part 2 - standard deviation
		foreach($steps as $stepId => &$step)
		{
			$sd = [];

			foreach($step['results'] as $key => $result)
			{
				if (count($result) == 0) continue;

				foreach($result as $wid => $value) {
					$mean = $step['result-stats']['mean'][$key];
					if (!isset($sd[$key])) {
						$sd[$key] = 0;
					}
					$sd[$key] += ($mean - $value) * ($mean - $value);
				}
			}

			foreach($sd as $key => $s) {
				if ($step['workers'] > 1) {
					$step['result-stats']['sd'][$key] = sqrt($s / ($step['workers'] - 1));
				} else {
					$step['result-stats']['sd'][$key] = 0;
				}
			}
		}

		ksort($steps);

		return $steps;
	}

	public function getStepObject($stepNum, $workerId)
	{
		$stepId = $this->translateStepNum($stepNum, $workerId);
		$step = $this->steps()[$stepId];
		$stepObject = new Step($step, $this, $workerId, $stepId);
		return $stepObject;
	}

	public function translateStepNum($stepNum, $workerId)
	{
		$map = $this->store->readWorker('stepMap', null, $this->batchId, $workerId);

		if (is_null($map)) {
			$map = $this->createStepMap();
			$this->store->writeWorker('stepMap', $map, $this->batchId, $workerId);
		}

		$stepId = $map[$stepNum];
		return $stepId;
	}

	private function createStepMap()
	{
		$map = array();
		foreach($this->groups as $gid => $group) {
			if ($group['properties']['random']) {
				$gmap = array();
				foreach($group['steps'] as $sk => $step) {
					$gmap[] = $sk;
				}
				shuffle($gmap);
				foreach($gmap as $sk) {
					$map[] = $sk;
				} 
			} else {
				foreach($group['steps'] as $sk => $step) {
					$map[] = $sk;
				}
			}
		}
		return $map;
	}

	public function getColumns()
	{
		$columns = array();
		foreach($this->steps() as $sk => $step) {
			$step = $this->steps()[$sk];
			$stepObject = new Step($step, $this, 'WID', $sk);
			$columns[$sk] = $stepObject->getColumns();
		}
		return $columns;
	}
}