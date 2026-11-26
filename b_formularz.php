<?php declare(strict_types=1);
require_once 'lib.php';
wymagaj_logowania();

$link = db_polacz();
$ostatnie = [];
if ($link) {
	$r = mysqli_query($link, 'SELECT id, x1, x2, x3, x4, x5, `datetime` FROM pomiary ORDER BY id DESC LIMIT 10');
	while ($r && ($w = mysqli_fetch_assoc($r))) { $ostatnie[] = $w; }
	mysqli_close($link);
}

$scada = isset($_GET['scada']);
$tytul = $scada ? 'Formularz SCADA – nowy pomiar' : 'Formularz – nowy pomiar';
require 'gora.php';
?>
<?php foreach (flash_pobierz() as [$typ, $tekst]): ?>
<div class="alert alert-<?php echo e($typ); ?>" role="alert"><?php echo e($tekst); ?></div>
<?php endforeach; ?>
<form method="post" action="b_add.php" class="row g-2 align-items-end mb-4" style="max-width: 44rem;">
	<?php echo csrf_pole(); ?>
<?php if ($scada): ?>
	<input type="hidden" name="scada" value="1">
<?php endif; ?>
<?php foreach (['x1', 'x2', 'x3', 'x4', 'x5'] as $n): ?>
	<div class="col">
		<label class="form-label" for="<?php echo $n; ?>"><?php echo $n; ?></label>
		<input type="number" step="any" class="form-control" id="<?php echo $n; ?>" name="<?php echo $n; ?>" required>
	</div>
<?php endforeach; ?>
	<div class="col-auto">
		<button class="btn btn-primary" type="submit">Dodaj</button>
	</div>
</form>

<table class="table table-sm table-striped" style="max-width: 44rem;">
	<thead><tr><th>id</th><th>x1</th><th>x2</th><th>x3</th><th>x4</th><th>x5</th><th>datetime</th></tr></thead>
	<tbody>
<?php foreach ($ostatnie as $w): ?>
		<tr><td><?php echo (int)$w['id']; ?></td><td><?php echo e((string)$w['x1']); ?></td><td><?php echo e((string)$w['x2']); ?></td><td><?php echo e((string)$w['x3']); ?></td><td><?php echo e((string)$w['x4']); ?></td><td><?php echo e((string)$w['x5']); ?></td><td><?php echo e($w['datetime']); ?></td></tr>
<?php endforeach; ?>
	</tbody>
</table>
<div class="mt-3">Podgląd na żywo:
	<a href="b_index1.php">CSV</a> &middot; <a href="b_index2.php">JSON</a> &middot; <a href="b_index3.php">XML</a> &middot; <a href="b_index4.php">NOWA SCADA</a>
</div>
<script>
(function ()
{
	var tbody = document.querySelector('table tbody');
	function odswiez()
	{
		fetch('b_ostatnie.php', { credentials: 'same-origin', cache: 'no-store' })
			.then(function (r) { return r.json(); })
			.then(function (wiersze)
			{
				tbody.textContent = '';
				wiersze.forEach(function (w)
				{
					var tr = document.createElement('tr');
					w.forEach(function (pole)
					{
						var td = document.createElement('td');
						td.textContent = pole;
						tr.appendChild(td);
					});
					tbody.appendChild(tr);
				});
			})
			.catch(function () {});
	}
	setInterval(odswiez, 2000);
})();
</script>
<?php require 'dol.php'; ?>
