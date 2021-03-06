<?php
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;
use Amenadiel\JpGraph\Themes;

if (count($results) == 0) {
	echo "<p>No results available</p>";
	return;
}

if(!function_exists("imageantialias")) {
	function imageantialias($image, $enabled) { return false; }
}

// prepare workers graph
$dataY = array();
$labelsX = array();
$finished = 0;

foreach($results as $stepId => &$step)
{
	$dataY[] = $step['workers'];
	$labelsX[] = ($stepId + 1);
	$finished = $step['workers'];
}

// setup the graph
$graph = new Graph\Graph(760,300);
$graph->SetScale("textint");

$theme_class = new Themes\UniversalTheme;

$graph->SetTheme($theme_class);
$graph->img->SetAntiAliasing(true);
$graph->SetBox(false);
$graph->SetMargin(50,5,5,45);

$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false, false);
$graph->yaxis->SetTitle("Workers", 'center');
$graph->yaxis->SetTitleMargin(40);

$graph->xgrid->Show();
$graph->xgrid->SetLineStyle("solid");
$graph->xaxis->SetTickLabels($labelsX);
$graph->xaxis->SetTitle("Step", 'center');
$graph->xgrid->SetColor('#E3E3E3');

// plot bars
$p1 = new Plot\BarPlot($dataY);
$graph->Add($p1);
$p1->SetColor("olivedrab3");
$p1->SetFillGradient('olivedrab1','olivedrab4',GRAD_VERT);

// plot finished lines
$pF = new Plot\PlotLine(HORIZONTAL, $finished, 'olivedrab4', 1);
$graph->Add($pF);

// output graph to temp file
$graph->Stroke(TMP_PATH.'img-cache'.DS.'workers-'.$id.'.png');


/*
 * render step graphs
 */
foreach($results as $stepId => &$step)
{
	foreach($step['results'] as $key => &$values) {
		if (count($values) == 0) continue;
		// prepare graph
		$dataY = [];

		foreach($values as $wid => &$value)
		{
			if (is_numeric($value)) {
				$dataY[] = $value;
			}
		}

		if (count($dataY) == 0) continue;

		// setup the graph
		$graph = new Graph\Graph(350,120);
		$graph->SetScale("textint");

		$theme_class = new Themes\UniversalTheme;

		$graph->SetTheme($theme_class);
		$graph->img->SetAntiAliasing(true);
		$graph->SetBox(false);
		$graph->SetMargin(50,5,15,15);

		$graph->xaxis->HideLabels();

		$graph->yaxis->HideLine(false);
		$graph->yaxis->HideTicks(false, false);

		// plot bars
		$p1 = new Plot\BarPlot($dataY);
		$graph->Add($p1);
		$p1->SetColor("olivedrab3");
		$p1->SetFillGradient('olivedrab1','olivedrab4',GRAD_VERT);

		$pAvg = new Plot\PlotLine(HORIZONTAL, $step['result-stats']['mean'][$key], '#000000', 1);
		$graph->Add($pAvg);

		$pMin = new Plot\PlotLine(HORIZONTAL, $step['result-stats']['min'][$key], '#008800', 1);
		$graph->Add($pMin);

		$pMax = new Plot\PlotLine(HORIZONTAL, $step['result-stats']['max'][$key], '#ff0000', 1);
		$graph->Add($pMax);

		$pSd1 = new Plot\PlotLine(HORIZONTAL, $step['result-stats']['mean'][$key] + $step['result-stats']['sd'][$key] / 2, '#0000ff', 1);
		$graph->Add($pSd1);

		$pSd2 = new Plot\PlotLine(HORIZONTAL, $step['result-stats']['mean'][$key] - $step['result-stats']['sd'][$key] / 2, '#0000ff', 1);
		$graph->Add($pSd2);

		// output graph to temp file
		$graph->Stroke(TMP_PATH.'img-cache'.DS.'results-'.$id.'-'.$stepId.'-'.$key.'.png');
	}
}

?>

<h3>Download Results</h3>
<ul>
	<li><?= $T->link('Download as XLSX-file', 'admin/batch/'.$id.'/results.xlsx') ?></li>
	<li><?= $T->link('Download as CSV-file', 'admin/batch/'.$id.'/results.csv') ?></li>
	<li><?= $T->link('Download as normalized CSV-file', 'admin/batch/'.$id.'/results-normalized.csv') ?></li>
</ul>

<h3>Workers per Step</h3>

<img src="<?= BASE_URL.'core/tmp/img-cache/workers-'.$id.'.png' ?>">

<h3>Result Statistics</h3>

<table class="steps">

<?php 
$questions = array();
foreach($steps as $stepId => &$step) {
	if (!isset($step['elements'])) continue;
	foreach($step['elements'] as $element) {
		if ($element['command'] != 'question') continue;
		$questions[$stepId][] = $element;
	}
}

foreach($results as $stepId => &$step): 
	$rows = 5;
	foreach($columns[$stepId] as $col) {
		if (strpos($col, 'value') === 0) {
			$rows += 5;
		}
	}
?>
	<tr class="step">
		<td class="number" rowspan="<?= $rows ?>"><?= ($stepId + 1) ?></td>
		<td class="command" colspan="4"><?= $T->ifset($steps[$stepId]['arguments']['name']) ?></td>
	</tr>
	
	<tr class="property">
		<td class="property-key last" colspan="2">workers</td>
		<td class="property-value last"><?= $step['workers'] ?></td>
		<td class="empty"></td>
	</tr>

	<tr class="property">
		<td class="property-key last" rowspan="3">duration</td>
		<td class="property-key2">average</td>
		<td class="property-value"><?= $T->formatTime($step['duration-stats']['mean']) ?></td>
		<td class="empty"></td>
	</tr>
	<tr class="property">
		<td class="property-key2">maximum</td>
		<td class="property-value"><?= $T->formatTime($step['duration-stats']['max']) ?></td>
		<td class="empty"></td>
	</tr>
	<tr class="property">
		<td class="property-key2 last">minimum</td>
		<td class="property-value last"><?= $T->formatTime($step['duration-stats']['min']) ?></td>
		<td class="last"></td>
	</tr>

	<?php if (count($step['results']) > 0): ?>
		<?php 
		$colId = -1;
		foreach($step['result-stats']['mean'] as $col => $_):
			if (strpos($col, 'value') !== 0) continue;
			$colId++;
			?>
			<tr class="property">
				<td class="property-key" rowspan="5">result</td>
				<td class="property-key2">question</td>
				<td class="property-value"><?= $questions[$stepId][$colId]['arguments']['question'] ?></td>
				<td rowspan="5" class="last">
					<img src="<?= BASE_URL.'core/tmp/img-cache/results-'.$id.'-'.$stepId.'-'.$col.'.png' ?>">
				</td>
			</tr>
			<tr class="property">
				<td class="property-key2">average</td>
				<td class="property-value"><?= round($step['result-stats']['mean'][$col], 1) ?></td>
			</tr>
			<tr class="property">
				<td class="property-key2" style="color:blue;">std. dev.</td>
				<td class="property-value"><?= round($step['result-stats']['sd'][$col], 1) ?></td>
			</tr>
			<tr class="property">
				<td class="property-key2" style="color:red;">maximum</td>
				<td class="property-value"><?= $step['result-stats']['max'][$col] ?></td>
			</tr>
			<tr class="property">
				<td class="property-key2 last" style="color:#008800;">minimum</td>
				<td class="property-value last"><?= $step['result-stats']['min'][$col] ?></td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
<?php endforeach; ?>
</table>

