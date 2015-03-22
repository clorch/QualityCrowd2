<input type="hidden" name="answered-<?= $uid ?>" value="0">

<input type="text" name="text-<?= $uid ?>">

<script type="text/javascript">
	$('input[name=text-<?= $uid ?>]').keyup( function() {
		var text = $('input[name=text-<?= $uid ?>]').val();
		$('input[name=answered-<?= $uid ?>]').val(text.length > 0);
	});
</script>

