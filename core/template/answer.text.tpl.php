<input type="hidden" name="answered-<?= $uid ?>" value="0">
<input type="hidden" name="length-<?= $uid ?>" value="">

<textarea name="text-<?= $uid ?>"></textarea>

<script type="text/javascript">

	$('textarea[name=text-<?= $uid ?>]').keyup( function() {
		var text = $('textarea[name=text-<?= $uid ?>]').val();
		$('input[name=length-<?= $uid ?>]').val(text.length);
		$('input[name=answered-<?= $uid ?>]').val(text.length > 0);
	});

</script>

