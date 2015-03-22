<form action="<?= BASE_URL ?>admin/batch/<?= $id ?>/settings" method="post" id="qcsform">
	<?php if ($state == 'error'): ?>
		<div class="errormessage">
			<?= $error ?>
		</div>
	<?php else: ?>
		<fieldset>
			<legend>Settings</legend>
			<label for="state">State</label>
			<select name="state">
				<?php if ($state == 'edit'): ?>
				<option value="edit" <?= ($state == 'edit' ? 'selected="selected"' : '') ?>>
					<?= Clho\QualityCrowd\Batch::readableState('edit') ?>
				</option>
				<?php endif; ?>
				<option value="active" <?= ($state == 'active' ? 'selected="selected"' : '') ?>>
					<?= Clho\QualityCrowd\Batch::readableState('active') ?>
				</option>
				<?php if ($state == 'active' || $state == 'post'): ?>
				<option value="post" <?= ($state == 'post' ? 'selected="selected"' : '') ?>>
					<?= Clho\QualityCrowd\Batch::readableState('post') ?>
				</option>
				<?php endif; ?>
			</select>
			<?php if ($state == 'edit'): ?>
				<p>Changing from "<?= Clho\QualityCrowd\Batch::readableState('edit') ?>" 
					to "<?= Clho\QualityCrowd\Batch::readableState('active') ?>" deletes all result data!</p>
			<?php endif; ?>
		</fieldset>

		<button id="button_save">Save</button>

	<?php endif; ?>
</form>
