<?php
namespace Clho\QualityCrowd;

class BatchFiles extends Base
{
	private $batchId;

	public function __construct($batchId) 
	{
		parent::__construct();
		$this->batchId = $batchId;
	}

	public function getFiles()
	{
		$dir = BATCH_PATH.$this->batchId;
		$files = scandir($dir);
		array_shift($files); // .
		array_shift($files); // ..

		foreach($files as $id => &$file) {
			if (!is_file($dir.DS.$file) or substr($file, 0, 1) == '.') {
				unset($files[$id]);
				continue;
			}
			$file = ['name'=> $file];
			$file['size'] = @filesize($dir.DS.$file['name']);
			$file['mtime'] = @filemtime($dir.DS.$file['name']);
		}

		return $files;
	}

	public function getLatestMTime()
	{
		$dir = BATCH_PATH.$this->batchId;
		$files = scandir($dir);
		$lastestMTime = 0;

		foreach($files as $id => &$file) {
			if (!is_file($dir.DS.$file) or substr($file, 0, 1) == '.') {
				continue;
			}
			$mtime = filemtime($dir.DS.$file);
			if ($mtime > $lastestMTime) {
				$lastestMTime = $mtime;
			}
		}

		return $lastestMTime;
	}

	public function readFile($filename) 
	{
		$f = BATCH_PATH.$this->batchId.DS.$filename;
		$contents = file_get_contents($f);
		return $contents;
	}

	public function writeFile($filename, $contents)
	{
		$f = BATCH_PATH.$this->batchId.DS.$filename;
		file_put_contents($f, $contents);
		@chmod($f, $this->getConfig('filePermissions'));
	}

	public function readSource()
	{
		return $this->readFile('definition.qcs');
	}

	public function writeSource($contents)
	{
		$this->writeFile('definition.qcs', $contents);
	}

	public function sourceExists()
	{
		return file_exists(BATCH_PATH.$this->batchId.DS.'definition.qcs');
	}
}