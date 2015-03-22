<h3>Files</h3>
<table class="meta">
	<tr>
		<th>Filename</th>
		<th>Size</th>
		<th>Last modified</th>
	</tr>
	<?php foreach($files as $file): ?>
	<tr>
		<td><a href="<?= BASE_URL ?>admin/batch/<?= $id ?>/edit/<?=$file['name']?>"><?=$file['name']?></a></td>
		<td><?=$T->formatFileSize($file['size'])?></td>
		<td><?=date ("d.m.Y, H:i:s",$file['mtime'])?></td>
	</tr>
	<?php endforeach; ?>
</table>

<h3>Editor</h3>

<form action="<?= BASE_URL ?>admin/batch/<?= $id ?>/edit/<?= $filename ?>" method="post" id="qcsform">
	<?php if ($state == 'error'): ?>
		<div class="errormessage">
			<?= $error ?>
		</div>
	<?php endif; ?>

	<fieldset>
		<legend><?= $filename ?> <?php if ($readonly): ?> (read only)<?php endif; ?></legend>
		<textarea id="filecontents" name="filecontents"><?= $filecontents ?></textarea>
	</fieldset>
	
	<?php if (!$readonly): ?>
		<button id="button_save">Save</button>
	<?php endif; ?>
</form>

<script>
	var editor = CodeMirror.fromTextArea(document.getElementById("filecontents"), {
		<?php if($T->endswith($filename, '.html')): ?>
		mode: 'text/html',
		htmlMode: true,
		<?php elseif($T->endswith($filename, '.qcs')): ?>
		mode: 'text/x-qc-script',
		<?php else: ?>
		mode: 'text/plain',
		<?php endif; ?>
		lineNumbers: true,
		theme: 'ambiance',
		lineWrapping: true,
		viewportMargin: Infinity,
		<?php if ($readonly): ?>
		readOnly: true,
		<?php endif; ?>
	});
</script>
