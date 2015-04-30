<?php
header('Content-type: text/csv');
header('Content-disposition: attachment;filename=' . $batchId . '-normalized.csv');

// Header
echo $T->csvEscape('Worker ID').',';
echo $T->csvEscape('Finished').',';
echo $T->csvEscape('Step Number').',';
echo $T->csvEscape('Step Id').',';
echo $T->csvEscape('Duration').',';
echo "\n";

// Results
foreach($workers as $worker)
{
	if (!is_array($worker['results'])) {
		continue;
	}

	$stepMap = $worker['stepMap'];

	foreach($worker['results'] as $stepNum => $result)
	{
		echo $T->csvEscape($worker['workerId']).',';
		echo $T->csvEscape($worker['finished'] ? 'Yes' : 'No').',';
		echo strval($stepNum + 1).',';
		echo strval($stepMap[$stepNum] + 1).',';
		echo $worker['durations'][$stepMap[$stepNum]];

		array_shift($result); // step num
		array_shift($result); // duration

		foreach($result as $value) {
			echo ',' . $T->csvEscape($value); 
		}

		echo "\n";
	}
}
