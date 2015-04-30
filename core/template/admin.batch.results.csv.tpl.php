<?php
header('Content-type: text/csv');
header('Content-disposition: attachment;filename=' . $batchId . '.csv');

// Header
echo $T->csvEscape('Worker ID').','.$T->csvEscape('Finished');
foreach($columns as $stepId => $cols) {
	echo ',' . $T->csvEscape('Step ' . ($stepId + 1) . ' - id');
	echo ',' . $T->csvEscape('Step ' . ($stepId + 1) . ' - duration');
	foreach($cols as $col) {
		echo ',' . $T->csvEscape('Step ' . ($stepId + 1) . ' - ' . $col);
	}
}
echo "\n";

// Results
foreach($workers as $worker)
{
	echo $T->csvEscape($worker['workerId']).',';
	echo $T->csvEscape($worker['finished'] ? 'Yes' : 'No');

	if (!is_array($worker['results'])) {
		echo "\n";
		continue;
	}

	$stepMap = $worker['stepMap'];

	foreach($worker['results'] as $stepNum => $result)
	{
		array_shift($result); // step num
		array_shift($result); // timestamp

		echo ',' . strval(intval($stepMap[$stepNum]) + 1);
		echo ',' . $worker['durations'][$stepMap[$stepNum]];

		foreach($result as $value) {
			echo ',' . $T->csvEscape($value); 
		}
	}

	echo "\n";
}
