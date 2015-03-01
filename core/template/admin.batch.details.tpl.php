<h3>Info</h3>
<table class="meta">
<?php foreach($properties as $k => $v): ?>
	<tr>
		<th><?= ucfirst($k) ?></th>
		<td><?php if ($k == 'timeout') {
				echo $T->formatTime($v);
			} else if ($k == 'workers') {
				echo ($v == -1 ? '∞' : $v);
			} else {
				echo $v; 
			}?></td>
	</tr>
<?php endforeach; ?>
	<tr>
		<th>State</th>
		<td><?= Clho\QualityCrowd\Batch::readableState($state) ?></td>
	</tr>
	<tr>
		<th>Worker URL</th>
		<td><?= (BASE_URL . $id . '/&lt;worker id&gt;') ?></td>
	</tr>
</table>

<h3>Steps</h3>
<table class="steps">
<?php $groupStarted = false; ?>
<?php foreach($groups as $gk => $group): ?>
	<?php if(count($group['steps']) > 1): ?>
		<?php $groupStarted = true; ?>
		<tr class="group">
			<td class="name" colspan="4"><?= $T->ifset($group['arguments']['name']) ?></td>
		</tr>
		<?php if (isset($group['properties'])):
			ksort($group['properties']);
			foreach($group['properties'] as $pk => $pv): ?>
				<tr class="property">
					<td>&nbsp;</td>
					<td class="property-key"><?= $pk ?></td>
					<td class="property-value"><?= $T->formatPropertyValue($pv) ?></td>
					<td></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	<?php else: ?>
		<?php if($groupStarted): ?>
			<tr class="group">
				<td class="name" colspan="4"></td>
			</tr>
		<?php endif; ?>
		<?php $groupStarted = false; ?>
	<?php endif; ?>
	<?php foreach($group['steps'] as $sk => $step):
		$o = '';
		$rows = count($step['properties']) + 1;
		foreach($step['elements'] as $ek => $e) {
			$rows += 1;
			$rows += count($e['properties']);

			$o .= '<tr class="element">';
			$o .= '<td class="command">' . $e['command'] . '</td>';
			$o .= '<td colspan="2">' . $T->trimText(implode(' &nbsp; &nbsp; ', $e['arguments']), 70) . '</td>';
			$o .= '</tr>';

			if (isset($e['properties'])) {
				ksort($e['properties']);
				foreach($e['properties'] as $pk => $pv) {
					$o .= '<tr class="property">';
					$o .= '<td class="property-key">' . $pk . '</td>';
					$o .= '<td class="property-value" colspan="2">'. $T->formatPropertyValue($pv) . '</td>';
					$o .= '</tr>';
				}
			}
		}
		?>
		<tr class="step">
			<td class="number" rowspan="<?= $rows ?>"><?= ($sk + 1) ?></td>
			<td class="command" colspan="2"><?= $T->ifset($step['arguments']['name']) ?></td>
			<td class="preview">
				<?= $T->link('Preview', 'admin/batch/'.$id.'/'.$sk) ?>
			</td>
		</tr>
		<?php if (isset($step['properties'])):
			ksort($step['properties']);
			foreach($step['properties'] as $pk => $pv): ?>
				<tr class="property">
					<td class="property-key"><?= $pk ?></td>
					<td class="property-value" colspan="1"><?= $T->formatPropertyValue($pv) ?></td>
					<td></td>
				</tr>
			<?php endforeach; 
		endif;?>
		<?= $o ?>
	<?php endforeach; ?>
<?php endforeach; ?>
</table>

