<li class="batchrow">
	<a href="<?= BASE_URL ?>admin/batch/<?= $id ?>">
	<?php if(isset($error)): ?>
		<div class="infofloat finished">&nbsp;</div>
		<div class="infofloat workers">&nbsp;</div>
		<div class="infofloat steps">&nbsp;</div>
		<div class="infofloat state" title="<?= Batch::readableState($state) ?>">
			<svg><use xlink:href="#icon-<?= $state ?>" /></svg>
		</div>
		<div class="id"><?= $id ?></div>
		<div class="error"><?= $error ?></div>
	<?php else: ?>
		<div class="infofloat finished"><?= $finished ?></div>
		<div class="infofloat workers"><?= $workers ?></div>
		<div class="infofloat steps"><?= $steps ?></div>
		<div class="infofloat state" title="<?= Batch::readableState($state) ?>">
			<svg><use xlink:href="#icon-<?= $state ?>" /></svg>
		</div>
		<div class="id"><?= $id ?></div>
		<div class="title"><?= $title ?></div>
	<?php endif; ?>
	</a>
</li>
