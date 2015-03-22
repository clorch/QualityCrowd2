<input type="hidden" name="answered-<?= $uid ?>" value="0">

<table>
<?php foreach($answers as $row): ?>
	<tr>
		<td>
			<label for="value-<?= $row['value'] ?>-<?= $uid ?>" id="label-<?= $row['value'] ?>-<?= $uid ?>"><?= $row['text'] ?></label>
		</td>
		<td>
			<input type="text" name="text-<?= $row['value'] ?>-<?= $uid ?>" id="value-<?= $row['value'] ?>-<?= $uid ?>" value="">
		</td>
	</tr>
<?php endforeach; ?>
</table>

<script type="text/javascript">
	$('input[type=text][name$=<?= $uid ?>]').keyup( function() {
		var answered = 0;
		$('input[type=text][name$=<?= $uid ?>]').each(function(i, item) {
			if($(item).val().length > 0) {
				answered = 1;
			}
		});
		
		$('input[name=answered-<?= $uid ?>]').val(answered);
	});
</script>
