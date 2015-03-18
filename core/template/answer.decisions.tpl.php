<input type="hidden" name="answered-<?= $uid ?>" value="0">

<?php 
$i = 0;
foreach($answers as $answer): ?>
	<input type="hidden" name="decision-<?= $i ?>-<?= $uid ?>" value="">
	<input type="hidden" name="timing-<?= $i ?>-<?= $uid ?>" value="">
	<?php $i++;
endforeach; ?>

<div style="text-align:center;">
	<button type="button" class="decision" id="button-start-<?= $uid ?>"><em>Start</em></button>
	<button type="button" class="decision" style="display:none;" id="button-left-<?= $uid ?>"></button>
	<button type="button" class="decision" style="display:none;" id="button-right-<?= $uid ?>"></button>
</div>

<script type="text/javascript">
	var currentRound_<?= $uid ?> = -1;
	var maxRounds_<?= $uid ?> = <?= count($answers) ?>;
	var startTime_<?= $uid ?> = 0;

	var words_<?= $uid ?> = [
	<?php 
	foreach($answers as $a):
		if (rand(0,1) == 0) {
			echo "['".addslashes($a['value'])."', '".addslashes($a['text'])."'],\n";
		} else {
			echo "['".addslashes($a['text'])."', '".addslashes($a['value'])."'],\n";
		}
	endforeach; ?>
	];

	function nextRound_<?= $uid ?>(selectedString)
	{
		if (currentRound_<?= $uid ?> < 0) {
			$('#button-start-<?= $uid ?>').hide();
		} else {
			var end = new Date().getTime();
			$('input[name=timing-'+currentRound_<?= $uid ?>+'-<?= $uid ?>]').val(end - startTime_<?= $uid ?>);
			$('input[name=decision-'+currentRound_<?= $uid ?>+'-<?= $uid ?>]').val(selectedString);
		}

		startTime_<?= $uid ?> = new Date().getTime();
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
			$('#button-left-<?= $uid ?>').show();
			if (words_<?= $uid ?>[currentRound_<?= $uid ?>][0] == words_<?= $uid ?>[currentRound_<?= $uid ?>][1]) {
				$('#button-right-<?= $uid ?>').hide();
			} else {
				$('#button-right-<?= $uid ?>').show();
			}
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
		nextRound_<?= $uid ?>('');
		return false; // prevent default action
	});
</script>
