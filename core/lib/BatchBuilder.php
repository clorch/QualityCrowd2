<?php
namespace Clho\QualityCrowd;

class BatchBuilder extends Base
{
	private $batchId;
	private $groups = array();
	private $meta = array();
	private $properties = array('global' => array(), 'group' => array(), 'step' => array());
	private $variables = array('global' => array(), 'group' => array(), 'step' => array());

	private $scope = 'global';
	private $group;
	private $step;
	private $stepId = 0;
	private $fakeGroup = false;

	public function __construct($batchId, $source) 
	{
		$this->batchId = $batchId;

		foreach($source as $line) {
			$this->addLine($line);
		}
	}

	public function getBatch() 
	{
		// clean up meta properties
		foreach(BatchCompiler::$syntax['meta']['keys'] as $property => $default)
		{
			if (!isset($this->meta[$property])) {
				$this->meta[$property] = $default;
			}
		}

		$myBatch = new Batch($this->batchId, $this->meta, $this->groups);
		return $myBatch;
	}

	public function addLine($line) 
	{
		$method = array($this, "command" . ucfirst(strtolower($line['command'])));

		if (is_callable($method)) {
			call_user_func($method, $line['arguments']);
		} else {
			$this->commandElement($line['command'], $line['arguments']);
		}
	}

	private function commandMeta($args)
	{
		$this->meta[$args[0]] = $this->parseValue($args[1]);
	}

	private function commandVar($args)
	{
		$this->variables[$this->scope][$args[0]] = $this->parseValue($args[1]);
	}

	private function commandSet($args)
	{
		$value = (isset($args[1]) ? $args[1] : true);
		$this->properties[$this->scope][$args[0]] = $this->parseValue($value);
	}

	private function commandUnset($args)
	{
		if ($args[0] == 'all') {
			$this->properties[$this->scope] = array();
		} else
		{
			unset($this->properties[$this->scope][$args[0]]);	
		}
	}

	private function commandGroup($args)
	{
		$this->group = array(
			'arguments' => array(),
			'properties' => array(),
		 	'steps' => array()
		 	);

		$this->setProperties($this->group, 'group');
		$this->setArguments($this->group, 'group', $args);
		$this->narrowScope('group');
	}

	private function commandStep($args)
	{
		// step not inside of group
		if ($this->scope == 'global') {
			$this->commandGroup(array());
			$this->fakeGroup = true;
		}

		$this->step = array(
			'arguments' => array(),
			'properties' => array(),
		 	'elements' => array()
		 	);

		$this->setProperties($this->step, 'step');
		$this->setArguments($this->step, 'step', $args);
		$this->narrowScope('step');
	}

	private function commandElement($command, $args)
	{
		$element = array(
			'command' => $command,
			'arguments' => array(),
			'properties' => array(),
			);

		$this->setProperties($element, $command);
		$this->setArguments($element, $command, $args);

		$this->step['elements'][] = $element;
	}

	private function commandEnd($args)
	{
		switch($this->scope) {
			case 'group':
				$this->groups[] = $this->group;
				$this->scope = 'global';
				break;
			case 'step':
				$this->group['steps'][$this->stepId] = $this->step;
				$this->stepId++;
				$this->scope = 'group';
				if ($this->fakeGroup) {
					$this->commandEnd(array());
					$this->fakeGroup = false;
				}
				break;
			default:
				throw new \Exception($this->batchId . ': invalid "end" command');
		}
	}

	private function narrowScope($scope)
	{
		$parents = array('group' => 'global', 'step' => 'group');

		$this->scope = $scope;
		$this->properties[$this->scope] = $this->properties[$parents[$scope]];
		$this->variables[$this->scope] = $this->variables[$parents[$scope]];
	}

	private function setProperties(&$item, $type)
	{
		foreach(BatchCompiler::$syntax[$type]['properties'] as $propertyKey => $property)
		{
			if (isset($this->properties[$this->scope][$propertyKey])) {
				$item['properties'][$propertyKey] = $this->properties[$this->scope][$propertyKey];
			} else {
				$item['properties'][$propertyKey] = $property['default'];
			}
		}
	}

	private function setArguments(&$item, $type, $args)
	{
		$i = 0;
		foreach($args as $arg) 
		{
			$argumentKey = BatchCompiler::$syntax[$type]['arguments'][$i];
			$item['arguments'][$argumentKey] = $this->parseValue($arg);
			$i++;
		}
	}

	private function parseValue($value)
	{
		// leave ints, bools, etc. untouched
		if (gettype($value) <> 'string') return $value;

		// resolve variables
		foreach($this->variables[$this->scope] as $k => $v)
		{
			$value = str_replace('$' . $k, $v, $value);
		}

		// find and resolve includes
		if (preg_match('/^include\(\s*(.+)\s*\)$/', $value, $matches))
		{
			$inc = $matches[1];
			$inc = str_replace('/', DS, $inc);
			$inc = str_replace('\\', DS, $inc);
			
			$file = BATCH_PATH . $this->batchId . DS . $inc;
			if (file_exists($file))
			{
				$value = file_get_contents($file);
			}
		}

		return $value;	
	}

}