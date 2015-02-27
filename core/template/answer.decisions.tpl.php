<input type="hidden" name="answered-<?= $uid ?>" value="0">

<?php 
$i = 0;
foreach($answers as $answer): ?>
	<input type="hidden" name="decision-<?= $i ?>-<?= $uid ?>" value="">
	<input type="hidden" name="timing-<?= $i ?>-<?= $uid ?>" value="">
	<?php $i++;
endforeach; ?>

<div style="text-align:center;">
	<button type="button" class="decision" id="button-start-<?= $uid ?>">Start</button>
	<button type="button" class="decision" style="display:none;" id="button-left-<?= $uid ?>"><?= $answers[0]['value'] ?></button>
	<button type="button" class="decision" style="display:none;" id="button-right-<?= $uid ?>"><?= $answers[0]['text'] ?></button>
</div>

<script type="text/javascript">
	var currentRound_<?= $uid ?> = 0;
	var maxRounds_<?= $uid ?> = <?= count($answers) ?>;
	var startTime_<?= $uid ?> = 0;

	var words_<?= $uid ?> = [
	<?php foreach($answers as $a): ?>
		['<?= $a['value'] ?>', '<?= $a['text'] ?>'],
	<?php endforeach; ?>
	];

	function nextRound_<?= $uid ?>(selectedString)
	{
		var end = new Date().getTime();
		$('input[name=timing-'+currentRound_<?= $uid ?>+'-<?= $uid ?>]').val(end - startTime_<?= $uid ?>);
		startTime_<?= $uid ?> = new Date().getTime();
		$('input[name=decision-'+currentRound_<?= $uid ?>+'-<?= $uid ?>]').val(selectedString);

		currentRound_<?= $uid ?>++;

		if (currentRound_<?= $uid ?> == maxRounds_<?= $uid ?>) {
			$('input[name=answered-<?= $uid ?>]').val(1);
			$('#button-left-<?= $uid ?>').prop("disabled", true);
			$('#button-right-<?= $uid ?>').prop("disabled", true);
			$('#button-left-<?= $uid ?>').hide();
			$('#button-right-<?= $uid ?>').hide();
			$('#button-start-<?= $uid ?>').html('Click <em>Next</em> to continue.');
			$('#button-start-<?= $uid ?>').prop("disabled", true);
			$('#button-start-<?= $uid ?>').show();
		} else {
			$('#button-left-<?= $uid ?>').html(words_<?= $uid ?>[currentRound_<?= $uid ?>][0]);
			$('#button-right-<?= $uid ?>').html(words_<?= $uid ?>[currentRound_<?= $uid ?>][1]);
		}
	}

	$('#button-left-<?= $uid ?>').click( function () {
		nextRound_<?= $uid ?>($('#button-left-<?= $uid ?>').html());
		return false; // prevent default action
	});

	$('#button-right-<?= $uid ?>').click( function () {
		nextRound_<?= $uid ?>($('#button-right-<?= $uid ?>').html());
		return false; // prevent default action
	});

	$('#button-start-<?= $uid ?>').click( function () {
		$('#button-start-<?= $uid ?>').hide();
		$('#button-left-<?= $uid ?>').show();
		$('#button-right-<?= $uid ?>').show();
		startTime_<?= $uid ?> = new Date().getTime();
		return false; // prevent default action
	});
</script>
