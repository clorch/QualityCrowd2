<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>QualityCrowd 2</title>

		<?= $T->css('core/files/css/style.css') ?>
		<?= $T->css('core/files/css/admin.css') ?>

		<?= $T->js('core/files/js/swfobject.js') ?>
		<?= $T->js('core/files/js/flashver.js') ?>
		<?= $T->js('core/files/js/jquery.js') ?>

		<?= $T->css('vendor/codemirror/CodeMirror/lib/codemirror.css') ?>
		<?= $T->css('vendor/codemirror/CodeMirror/theme/ambiance.css') ?>
		<?= $T->js('vendor/codemirror/CodeMirror/lib/codemirror.js') ?>
		<?= $T->js('core/files/js/qc-script.js') ?>
    </head>
	<body>
		<div class="header">
			<h1>QualityCrowd</h1>
			<ul class="menu">
				<li <?= ($page == 'batches' || $page == 'batch' ? 'class="active"' : '') ?>>
					<?= $T->link('Batches', 'admin/batches') ?>
				</li>
				<li <?= ($page == 'doc' ? 'class="active"' : '') ?>> 
					<?= $T->link('Documentation', 'admin/doc') ?>
				</li>
				<li <?= ($page == 'maintenance' ? 'class="active"' : '') ?>> 
					<?= $T->link('Maintenance', 'admin/maintenance') ?>
				</li>
			</ul>
		</div>

		<?= $content ?>

		<div class="footer">
			User: <?= $username ?>
		</div>
	</body>
</html>