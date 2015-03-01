<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>QualityCrowd 2</title>

		<?= $T->css('core/files/css/style.css') ?>
		
		<?= $T->js('core/files/js/jquery.js') ?>
	</head>
	<body>
		<div class="header">
		</div>

		<h1>Error!</h1>
		<p>The following error occured:<p>
		<pre><?= $message ?></pre>
		<?php if(isset($trace)): ?>
			<pre><?= $trace ?></pre>
		<?php endif; ?>
		<p>Restart the test to try again or contact the test supervisor.<p>

		<div class="footer">
			<button id="button_restart">Restart</button>
		</div>

		<script type="text/javascript">
			$('#button_restart').click(function() {
				var href = window.location.href;
				href = href.replace(/\?restart=$/g, '');
				window.location.href = href + '?restart='; 
			});
		</script>
	</body>
</html>
