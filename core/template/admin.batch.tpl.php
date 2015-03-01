<h2>Batch "<?= $id ?>"</h2>

<ul class="submenu">
	<li <?= ($subpage == '' ? 'class="active"' : '') ?>>
		<?= $T->link('View', 'admin/batch/'.$id) ?>
	</li>
	<li <?= ($subpage == 'edit' ? 'class="active"' : '') ?>>
		<?= $T->link('Edit', 'admin/batch/'.$id.'/edit') ?>
	</li>
	<li <?= ($subpage == 'validate' ? 'class="active"' : '') ?>>
		<?= $T->link('Validate Worker', 'admin/batch/'.$id.'/validate') ?>
	</li>
	<li <?= ($subpage == 'results' ? 'class="active"' : '') ?>>
		<?= $T->link('Results', 'admin/batch/'.$id.'/results') ?>
	</li>
	<li <?= ($subpage == 'browsers' ? 'class="active"' : '') ?>>
		<?= $T->link('Browsers', 'admin/batch/'.$id.'/browsers') ?>
	</li>
</ul>

<?= $content ?>
