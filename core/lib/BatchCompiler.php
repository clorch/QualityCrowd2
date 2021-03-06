<?php
namespace Clho\QualityCrowd;

class BatchCompiler extends Base
{
	private $batchId;
	private $files;

	public static $syntax = array(
		// special commands
		'meta' => array(
			'isBlock' => false,
			'needsBlock' => false,
			'minArguments' => 1,
			'arguments' => array('key', 'value'),
			'description' => '',
			'keys' => array(
				'title' 		=> '',
				'description'	=> '',
				'workers'		=> -1,
				'timeout'		=> 600,
				),
			),
		'var' => array(
			'isBlock' => false,
			'needsBlock' => false,
			'minArguments' => 2,
			'arguments' => array('variable', 'value'),
			'description' => 'Sets an internal variable to `<value>`. To use this variable for example in a `set` command, use the following syntax: `set title $titlevar`',
			),
		'set' => array(
			'isBlock' => false,
			'needsBlock' => false,
			'minArguments' => 1,
			'arguments' => array('property', 'value'),
			'description' => 'The `set` command sets a property defined by the `<property>`-argument to the value specified by `<value>`. This property can be used by all further commands and its value will be set until a matching `unset`-command is processed.',
			),
		'unset' => array(
			'isBlock' => false,
			'needsBlock' => false,
			'minArguments' => 1,
			'arguments' => array('property'),
			'description' => 'Unsets the property with the passed `<property>`. If `all` is passed all properties will be unset.',
			),
		'end' => array(
			'isBlock' => false,
			'needsBlock' => false,
			'minArguments' => 1,
			'arguments' => array('block'),
			'description' => 'TODO',
			),

		// blocks
		'group' => array(
			'isBlock' => true,
			'needsBlock' => true,
			'minArguments' => 0, 
			'arguments' => array('name'),
			'properties' => array(
				'random' 		 => array(
					'default' => false, 
					'values' => 'true, false',
					'description' => 'Todo'),
				),
			'description' => 'TODO',
			),
		'step' => array(
			'isBlock' => true,
			'needsBlock' => false,
			'minArguments' => 0, 
			'arguments' => array('name'),
			'properties' => array(
				'delay' 		 => array(
					'default' => 0, 
					'values' => 'number of seconds',
					'description' => 'Todo'),
				'skipvalidation' => array(
					'default' => false, 
					'values' => 'true, false',
					'description' => 'Disables the validation of the answer values. The main purpose of this flag is to allow quick testing of the script during development.'),
				),
			'description' => 'TODO',
			),

		// commands inside blocks, step elements
		'title' => array(
			'isBlock' => false,
			'needsBlock' => true,
			'minArguments' => 1, 
			'arguments' => array('title'),
			'properties' => array(),
			'description' => 'Todo',
			),
		'text' => array(
			'isBlock' => false,
			'needsBlock' => true,
			'minArguments' => 1, 
			'arguments' => array('text'),
			'properties' => array(),
			'description' => 'Displays some text. For longer texts it is recommended to use the `include()`-macro: (e.g. `text include(welcome.html)`)',
			),
		'video' => array(
			'isBlock' => false,
			'needsBlock' => true,
			'minArguments' => 1, 
			'arguments' => array('video1', 'video2'),
			'properties' => array(
				'mediaurl' 		 => array(
					'default' => MEDIA_URL,
					'values' => 'URL', 
					'description' => 'Todo'),
				'videowidth' 	 => array(
					'default' => 352, 
					'values' => 'number of pixels',
					'description' => 'Todo'),
				'videoheight' 	 => array(
					'default' => 288, 
					'values' => 'number of pixels',
					'description' => 'Todo'),
				),
			'description' => '',
			),
		'image' => array(
			'isBlock' => false,
			'needsBlock' => true,
			'minArguments' => 1,
			'arguments' => array('image'),
			'properties' => array(	
				'mediaurl' 		 => array(
					'default' => MEDIA_URL,
					'values' => 'URL',  
					'description' => 'Todo'),
				),
			'description' => '',
			),
		'question' => array(
			'isBlock' => false,
			'needsBlock' => true,
			'minArguments' => 1,
			'arguments' => array('question'),
			'properties'   => array(
				'answermode'	 => array(
					'default' => 'discrete', 
					'values' => 'continous, discrete, strings, input, text, decisions',
					'description' => 'Todo'),
				'answers'		 => array(
					'default' => '1: First answer; 2: Second answer; 3: Third answer', 
					'values' => 'key value list',
					'description' => 'Todo'),
				'width'			 => array(
					'default' => -1, 
					'values' => 'number',
					'description' => 'Todo'),
				),
			'description' => '',
			),
		'qualification' => array(
			'isBlock' => false,
			'needsBlock' => true,
			'minArguments' => 1,
			'arguments' => array('qualification-batch'),
			'properties'   => array(),
			'description' => '',
			),
		);

	public function __construct($batchId) 
	{
		$this->batchId = $batchId;
		$this->files = new BatchFiles($batchId);
	}

	public function exists()
	{
		return $this->files->sourceExists();
	}

	public function create()
	{
		$defaultQCS = <<<EOT
meta title "$this->batchId"
meta description "New batch description"

group
	step
		title "New Batch"
		text "Hello World"
	end step
end group

EOT;
		$path = BATCH_PATH . $this->batchId;
		$file = $path . DS . 'definition.qcs';

		mkdir($path);
		chmod($path, $this->getConfig('dirPermissions'));

		file_put_contents($file, $defaultQCS); 
		chmod($file, $this->getConfig('filePermissions'));
	}

	public function getBatch()
	{
		if (!$this->exists()) {
			throw new \Exception('Batch with id "' . $this->batchId . '" not found');
		}

		$myBatch = null;
		$cacheFile = TMP_PATH.'batch-cache'.DS.$this->batchId.'.txt';

		if (!file_exists($cacheFile) or 
			$this->files->getLatestMTime() >= filemtime($cacheFile)) // || true)
		{
			$source = $this->parse();
			$bb = new BatchBuilder($this->batchId, $source);
			$myBatch = $bb->getBatch();
			$myBatch2 = clone $myBatch;
			file_put_contents($cacheFile, serialize($myBatch2));
			chmod($cacheFile, $this->getConfig('filePermissions'));
		} else {
			$myBatch = file_get_contents($cacheFile);
			$myBatch = unserialize($myBatch);
		}

		return $myBatch;
	}

	private function parse() 
	{
		$data = array();
		$source = $this->files->readSource();
		$source = $this->normalize($source);
		$source = $this->resolveMacros($source);
		$source = $this->resolveForLoops($source);

		$source = $this->normalize($source);

		// parse source file
		$lines = explode("\n", $source);
		foreach($lines as $line)
		{
			$words = explode(' ', $line);
			$words = str_getcsv($line, ' ', '"');
			
			if (!isset(self::$syntax[$words[0]]))
			{
				throw new \Exception ($this->batchId . ': unknown command "' . $words[0] . '"');
			}
			$cmd = self::$syntax[$words[0]];

			if (count($words) < $cmd['minArguments'] + 1) 
			{
				throw new \Exception($this->batchId . ': ' .
					'"' . $words[0] . '" requires at least ' . 
					$cmd['minArguments'] . ' arguments');
			}

			if (count($words) > count($cmd['arguments']) + 1) 
			{
				throw new \Exception($this->batchId . ': ' .
					'"' . $words[0] . '" accepts a maximum of ' . 
					count($cmd['arguments']) . ' arguments');
			}

			$data[] = array(
				'command' => $words[0],
				'arguments' => array_slice($words, 1),
				);
		}

		return $data;
	}

	private function resolveMacros($source) 
	{
		// find macro definitions
		$macros = $this->extractBlock($source, 'macro');
	
		// replace macro references
		foreach($macros as $macro) {
			$content = implode("\n", $macro['content']);
			$source = str_replace('$' . $macro['arguments'][0], $content, $source);
		}
		
		return $source;
	}

	private function resolveForLoops($source)
	{
		// find list definitions
		$lists = $this->extractBlock($source, 'list');
		
		// find for loops
		$forloops = $this->extractBlock($source, 'for');
		//header("Content-Type: text/plain; charset=utf8");

		// expand for loops
		$lines = explode("\n", $source);

		foreach($forloops as $loop) {
			// find matching list
			$myList = null;
			foreach($lists as $list) {
				if ($list['arguments'][0] == $loop['arguments'][2]) {
					$myList = $list['content'];
					break;
				}
			}
			if ($myList === null) {
				throw new \Exception("List with name '{$loop['arguments'][2]}' not found.");
			}

			// expanding
			$loopContent = array();
			foreach($myList as $listItem) {
				$content = implode("\n", $loop['content']);	
				$content = str_replace('$' . $loop['arguments'][0], $listItem, $content);
				$loopContent[] = $content;
			}

			// replacing
			$loopContent = implode("\n", $loopContent);	
			$lines[$loop['start']] = $loopContent;
		}
		
		$source = implode("\n", $lines);

		return $source;
	}

	private function extractBlock(&$source, $keyword)
	{
		$blocks = array();

		$insideBlock = false;
		$lines = explode("\n", $source);
		foreach($lines as $li => $line)
		{
			$words = explode(' ', $line);
			$words = str_getcsv($line, ' ', '"');

			if ($words[0] == $keyword) {
				array_shift($words);
				$block = array(
					'start' => $li,
					'arguments'  => $words,
					'content' => array(),
					);
				$insideBlock = true;

			} elseif ($words[0] == 'end' && $words[1] == $keyword) {
				$block['length'] = $li - $block['start'] + 1;
				$blocks[] = $block;
				$insideBlock = false; 
			} else {
				if ($insideBlock) {
					$block['content'][] = $line;
				}
			}
		}

		// remove block definition
		foreach($blocks as $block) {
			$replacement = array_fill(0, $block['length'], '');
			array_splice($lines, $block['start'], $block['length'], $replacement);	
		}

		$source = implode("\n", $lines);

		return $blocks;
	}

	private function normalize($source)
	{
		// remove comments
		$source = preg_replace("/\s*#.*$/m", '', $source);

		// replace tabs with spaces
		$source = str_replace("\t", ' ', $source);

		// clean up line endings
		$source = str_replace("\r\n", "\n", $source);

		// remove empty lines
		$source = preg_replace('/^\s*$/m', '', $source);
		$source = str_replace("\n\n", "\n", $source);
		$source = preg_replace('/^\n/', "", $source);
		$source = preg_replace('/\n$/', "", $source);

		// remove multiple spaces
		$source = preg_replace("/\ {2,}/", ' ', $source);

		// remove spaces at line beginnings
		$source = preg_replace("/^\ /m", "", $source);

		// remove spaces at line endings
		$source = preg_replace('/\ *\n/', "\n", $source);

		return $source;
	}
}