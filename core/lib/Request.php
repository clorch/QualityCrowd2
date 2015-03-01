<?php
namespace Clho\QualityCrowd;

class Request extends Base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function process()
	{
		$path = $_GET['path'];
		$path = explode('/', $path);
		if ($path[count($path) - 1] == '') array_pop($path);

		if (count($path) < 1) {
			header('Location: ' . BASE_URL . 'admin');
			exit;
		}

		if ($path[0] == 'setup') {
			header('Location: ' . BASE_URL . 'admin');
			exit;
		}

		if ($path[0] == 'admin') {
			$username = $this->login();

			array_shift($path);
			if (count($path) == 0) $path[] = 'batches';

			try {

				$admin = new Admin($username, $path);
				echo $admin->render();
			} catch (\Exception $e) {
				return $this->renderException($e);
			}
		} else {
			$this->processMain($path);
		}
	}

	private function processMain($path)
	{
		// extract batch id
		$batchId = preg_replace("/[^a-zA-Z0-9-]/", "", $path[0]);
		if ($batchId == '') die('invalid URL');

		if (count($path) == 1) {
			// extract worker id
			$workerId = uniqid();
			header('Location: '.BASE_URL.$path[0].'/'.$workerId);
		}

		// check worker id
		$workerId = $path[1];	
		if(preg_match('/[^a-zA-Z0-9]/i', $workerId) || 
		   $workerId == '' || 
		   strlen($workerId) > 64) 
		{
			die('invalid worker id');
		}

		// handle manual restart
		$restart = false;
		if (isset($_GET['restart'])) {
			$restart = true;
		}

		try {
			$myPage = new Main($batchId, $workerId, 'main', $restart);
			echo $myPage->render();
		} catch (\Exception $e) {
			return $this->renderException($e);
		}
	}

	private function login()
	{
		if (isset($_SERVER['PHP_AUTH_USER'])) {
		    $username = $_SERVER['PHP_AUTH_USER'];
		    $password = $_SERVER['PHP_AUTH_PW'];

		    $username = $this->auth($username, $password);
		    if ($username !== false) {
		    	return $username;
		    } else {
		    	sleep(1);
		    }
		}

		header('WWW-Authenticate: Basic realm="QualityCrowd"');
	    header('HTTP/1.0 401 Unauthorized');
	    echo 'Unauthorized';
	    exit;
	}

	private function auth($username, $password)
	{
		$users = $this->getConfig('adminUsers');
		$hash = $users[$username];

		if ($hash == crypt($password, $hash)) {
			return $username;
		} else {
			return false;
		}
	}

	private function renderException(\Exception $e)
	{
		$errorTpl = new Template('error');
		$errorTpl->set('message', $e->getMessage());

		if ($this->getConfig('debug')) {
			$errorTpl->set('trace', $e->getTraceAsString());
		}

		echo $errorTpl->render();
	}
}
