<?php
// Create new PHPExcel object
$objPHPExcel = new \PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("QualityCrowd 2")
							 ->setTitle("$batchId - results")
							 ->setDescription("QualityCrowd 2 results for $batchId");

// fill in results
outputResults($objPHPExcel->getSheet(0), $columns, $workers);
$objPHPExcel->getSheet(0)->setTitle('results');

// fill in durations
$objPHPExcel->createSheet();
outputDurations($objPHPExcel->getSheet(1), $columns, $workers);
$objPHPExcel->getSheet(1)->setTitle('durations');

// fill in durations
$objPHPExcel->createSheet();
outputStepMaps($objPHPExcel->getSheet(2), $columns, $workers);
$objPHPExcel->getSheet(2)->setTitle('step maps');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// redirect output to client browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $batchId . '.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');


function outputResults($sheet, $columns, $workers)
{
	// Header
	$sheet->setCellValue('A1', 'Worker ID')
          ->setCellValue('B1', 'Finished');
	$c = 2;

	foreach($columns as $stepId => $cols) {
		$sheet->setCellValueByColumnAndRow($c, 1, 'Step ' . ($stepId + 1));
		$sheet->setCellValueByColumnAndRow($c, 2, 'Step Id');
    	$c++;

		foreach($cols as $col) {
			$sheet->setCellValueByColumnAndRow($c, 1, 'Step ' . ($stepId + 1));
			$sheet->setCellValueByColumnAndRow($c, 2, $col);
	    	$c++;
		}
	}

	// Data
	$r = 4;
	foreach($workers as $worker) {
		$sheet->setCellValueExplicitByColumnAndRow(0, $r, $worker['workerId'],
			PHPExcel_Cell_DataType::TYPE_STRING);
	    $sheet->setCellValueByColumnAndRow(1, $r, ($worker['finished'] ? 'Yes' : 'No'));

		if (!is_array($worker['results'])) {
			continue;
		}

		$stepMap = $worker['stepMap'];

		$c = 2;
		foreach($worker['results'] as $stepNum => $result) {
			array_shift($result); // step number
			array_shift($result); // timestamp

			// step id
			$sheet->setCellValueByColumnAndRow($c, $r, $stepMap[$stepNum] + 1);
			$c++;

			foreach($result as $value) {
				$sheet->setCellValueByColumnAndRow($c, $r, $value);
				$c++;
			}
		}

		$r++;
	}
}

function outputDurations($sheet, $columns, $workers)
{
	// Header
	$sheet->setCellValue('A1', 'Worker ID')
          ->setCellValue('B1', 'Finished');

	$c = 2;
	foreach($columns as $stepId => $cols) {
		$sheet->setCellValueByColumnAndRow($c, 1, 'Step ' . ($stepId + 1));
    	$c++;
	}

	// Data
	$r = 3;
	foreach($workers as $worker) {
		$sheet->setCellValueExplicitByColumnAndRow(0, $r, $worker['workerId'],
			PHPExcel_Cell_DataType::TYPE_STRING);
	    $sheet->setCellValueByColumnAndRow(1, $r, ($worker['finished'] ? 'Yes' : 'No'));

		if (is_array($worker['durations'])) {
			$c = 2;
			foreach($worker['durations'] as $stepId => $duration) {
				$sheet->setCellValueByColumnAndRow($c, $r, $duration);
				$c++;
			}
		}

		$r++;
	}
}

function outputStepMaps($sheet, $columns, $workers)
{
	// Header
	$sheet->setCellValue('A1', 'Worker ID')
          ->setCellValue('B1', 'Finished');

	$c = 2;
	foreach($columns as $stepId => $cols) {
		$sheet->setCellValueByColumnAndRow($c, 1, 'Step ' . ($stepId + 1));
    	$c++;
	}

	// Data
	$r = 3;
	foreach($workers as $worker) {
		$sheet->setCellValueExplicitByColumnAndRow(0, $r, $worker['workerId'],
			PHPExcel_Cell_DataType::TYPE_STRING);
	    $sheet->setCellValueByColumnAndRow(1, $r, ($worker['finished'] ? 'Yes' : 'No'));

		if (is_array($worker['stepMap'])) {
			$c = 2;
			foreach($worker['stepMap'] as $stepNum => $stepId) {
				$sheet->setCellValueByColumnAndRow($c, $r, $stepId + 1);
				$c++;
			}
		}

		$r++;
	}
}

