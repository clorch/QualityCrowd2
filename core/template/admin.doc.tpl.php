<ul class="submenu">
	<li <?= ($subpage == '' ? 'class="active"' : '') ?>>
		<?= $T->link('Introduction', 'admin/doc') ?>
	</li>
	<li <?= ($subpage == 'reference' ? 'class="active"' : '') ?>>
		<?= $T->link('Command Reference', 'admin/doc/reference') ?>
	</li>
</ul>

<?= $content ?>