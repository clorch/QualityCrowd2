<?php
header('Content-type: text/csv');
header('Content-disposition: attachment;filename=' . $batchId . '.csv');

// Header line 1
echo 'Worker ID,Finished';
foreach($columns as $stepId => $cols) {
	echo ',Step ' . ($stepId + 1) . ' duration';
	foreach($cols as $col) {
		echo ',Step ' . ($stepId + 1) . ' ' . $col;
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

	foreach($worker['results'] as $sk => $result)
	{
		array_shift($result); // step id
		array_shift($result); // timestamp

		echo ',' . $worker['durations'][$sk];  //duration

		foreach($result as $value) {
			echo ',' . $value; 
		}
	}

	echo "\n";
}
