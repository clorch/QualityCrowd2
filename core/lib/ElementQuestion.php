<?php
namespace Clho\QualityCrowd;

// this class handles the step commands "video", "image" and "question"
class ElementQuestion extends StepElement
{
	protected function init() 
	{
		
	}

	public function validate($data) 
	{
		$msg = array();

		if (!isset($data['answered-' . $this->uid]) || $data['answered-' . $this->uid] <> true) {
			$msg[] = 'You have to answer the question.';
		}
		
		if (count($msg) == 0) {
			return true;
		} else  {
			return $msg;
		}
	}

	public function getColumns()
	{
		$cols = array();

		switch($this->properties['answermode']) {
			case 'continous':
			case 'discrete':
			$cols[] = 'value-' . $this->uid;
			$cols[] = 'text-' . $this->uid;
			break;

			case 'text':
			$cols[] = 'length-' . $this->uid;
			$cols[] = 'text-' . $this->uid;
			break;

			case 'strings':
			foreach($this->getAnswers() as $answer) {
				$cols[] = 'text-' . $answer['value'] . '-' . $this->uid;
			}
			break;

			case 'decisions':
			$i = 0;
			foreach($this->getAnswers() as $answer) {
				$cols[] = 'decision-' . $i . '-' . $this->uid;
				$cols[] = 'timing-' . $i . '-' . $this->uid;
				$i++;
			}
			break;

			default:
			throw new \Exception("Invalid answer mode");
			break;
		}
		
		return $cols;
	}

	private function getAnswers()
	{
		$answerStr = $this->properties['answers'];
		$answerStr = explode(';', $answerStr);

		$answers = array();
		foreach ($answerStr as $str)
		{
			$str = explode(':', $str);

			$answers[] = array(
				'value' => trim($str[0]),
				'text' => trim($str[1]),
			);
		}

		return $answers;
	}

	protected function prepareRender()
	{
		$answers = $this->getAnswers();

		// set answer template
		$answermode = $this->properties['answermode'];
		if (!Template::exists('answer.' . $answermode)) {
			$answermode = 'continous';
		}

		$tpl = new Template('answer.' . $answermode, $this->step->batch()->id());
		$tpl->set('answers', $answers);
		$answerform = $tpl->render();
		$this->tpl->set('answerform', $answerform);
	}
}
