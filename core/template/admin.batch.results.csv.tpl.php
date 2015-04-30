<?php
header('Content-type: text/csv');
header('Content-disposition: attachment;filename=' . $batchId . '.csv');

// Header line 1
echo csvEscape('Worker ID').','.csvEscape('Finished');
foreach($columns as $stepId => $cols) {
	echo ',' . csvEscape('Step ' . ($stepId + 1) . ' - id');
	echo ',' .csvEscape('Step ' . ($stepId + 1) . ' - duration');
	foreach($cols as $col) {
		echo ',' . csvEscape('Step ' . ($stepId + 1) . ' - ' . $col);
	}
}
echo "\n";

// Results
foreach($workers as $worker)
{
	echo $worker['workerId'] . ',';
	echo ($worker['finished'] ? 'Yes' : 'No');

	if (!is_array($worker['results'])) {
		echo "\n";
		continue;
	}

	$stepMap = $worker['stepMap'];
	$rmap = array_flip($stepMap);

	foreach($worker['results'] as $stepNum => $result)
	{
		array_shift($result); // step id
		array_shift($result); // timestamp

		echo ',' . strval(intval($stepMap[$stepNum]) + 1);
		echo ',' . $worker['durations'][$stepMap[$stepNum]];

		foreach($result as $value) {
			echo ',' . csvEscape($value); 
		}
	}

	echo "\n";
}

function csvEscape($value) {
	if (is_numeric($value)) {
		return $value;
	} else {
		return '"'.str_replace('"', '\"', $value).'"';
	}
}