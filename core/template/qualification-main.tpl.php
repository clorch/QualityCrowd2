<div class="header header-qualification">
	<span class="step">Qualification: step <?= ($stepNum + 1) ?> of <?= $stepCount ?></span>

	<?php if($state == 'edit'): ?>
	<span class="debugmessage">PREVIEW MODE, all data will be deleted</span>
	<?php endif; ?>
</div>

<input type="hidden" name="stepNum-<?= $scope ?>" value="<?= $stepNum ?>">

<?php if (isset($msg) && is_array($msg)):?>
	<ul class="errormessage">
		<?php foreach($msg as $m): ?>
		<li><?= $m ?></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<?= $content ?>
