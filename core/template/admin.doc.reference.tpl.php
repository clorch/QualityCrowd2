<?php foreach($syntax as $cmd => $cmdDef): ?>
	<h2><?= $cmd ?></h2>

	<?php 
	$cmdStr = $cmd;
	$i = 1;
	foreach ($cmdDef['arguments'] as $arg) {
		if ($i > $cmdDef['minArguments']) {
			$cmdStr .= ' [&lt;' . $arg .'&gt;]';
		} else {
			$cmdStr .= ' &lt;' . $arg .'&gt;';
		}
		$i++;
	}
	if ($cmdDef['isBlock']) {
		$cmdStr .= "\n   ...\nend " . $cmd;
	}
	?>

	<pre><?= $cmdStr ?></pre>

	<?= \Michelf\Markdown::defaultTransform($cmdDef['description']) ?>

	<?php if(isset($cmdDef['properties']) && count($cmdDef['properties']) > 0): ?>
	<h3>Properties</h3>
	<table class="meta">
		<tr>
			<th>Property</th>
			<th>Default value</th>
			<th>Allowed values</th>
			<th>Description</th>
		</tr>
		<?php foreach($cmdDef['properties'] as $propertyKey => $property): ?>
		<tr>
			<td><?= $propertyKey ?></td>
			<td><?= $T->formatPropertyValue($property['default']) ?></td>
			<td><?= $T->formatPropertyValue($property['values']) ?></td>
			<td><?= $property['description'] ?></td>
		</tr>
		<?php endforeach; ?>
	<table>
	<?php endif; ?>
<?php endforeach; ?>
