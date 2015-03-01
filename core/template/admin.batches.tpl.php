<svg display="none" width="0" height="0" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
	<defs>
		<symbol id="icon-post" viewBox="0 0 1024 1024">
			<title><?= Clho\QualityCrowd\Batch::readableState('post') ?></title>
			<path class="path1" d="M384 689.92l-177.92-177.92-60.373 60.373 238.293 238.293 512-512-60.373-60.373z"></path>
		</symbol>
		<symbol id="icon-error" viewBox="0 0 1024 1024">
			<title><?= Clho\QualityCrowd\Batch::readableState('error') ?></title>
			<path class="path1" d="M42.667 896h938.667l-469.333-810.667-469.333 810.667zM554.667 768h-85.333v-85.333h85.333v85.333zM554.667 597.333h-85.333v-170.667h85.333v170.667z"></path>
		</symbol>
		<symbol id="icon-edit" viewBox="0 0 1024 1024">
			<title><?= Clho\QualityCrowd\Batch::readableState('edit') ?></title>
			<path class="path1" d="M128 736v160h160l472.107-472.107-160-160-472.107 472.107zM883.413 300.587c16.64-16.64 16.64-43.733 0-60.373l-99.627-99.627c-16.64-16.64-43.733-16.64-60.373 0l-78.080 78.080 160 160 78.080-78.080z"></path>
		</symbol>
		<symbol id="icon-active" viewBox="0 0 1024 1024">
			<title><?= Clho\QualityCrowd\Batch::readableState('active') ?></title>
			<path class="path1" d="M192 128l640 384-640 384z"></path>
		</symbol>
	</defs>
</svg>

<h2>Batches</h2>
<ul class="batchlist">
	<?= $batchlist ?>
</ul>

<button id="button_new">New Batch</button>

<script>
	$('#button_new').click(function() 
	{
		var batchId = prompt("Enter a name for the new batch:", "");
		var url = '<?= BASE_URL ?>admin/batch/' + batchId + '/new';
		window.location.href = url;
	});
</script>

