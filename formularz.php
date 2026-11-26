<?php declare(strict_types=1);
require_once 'lib.php';
wymagaj_logowania();

$link = db_polacz();
$ostatnie = [];
if ($link) {
	$r = mysqli_query($link, 'SELECT id, text1, `datetime` FROM ajax_from_db ORDER BY id DESC LIMIT 10');
	while ($r && ($w = mysqli_fetch_assoc($r))) { $ostatnie[] = $w; }
	mysqli_close($link);
}

$tytul = 'Formularz – nowy wpis w bazie danych';
require 'gora.php';
?>
<?php foreach (flash_pobierz() as [$typ, $tekst]): ?>
<div class="alert alert-<?php echo e($typ); ?>" role="alert"><?php echo e($tekst); ?></div>
<?php endforeach; ?>
<form method="post" action="dodaj.php" class="row g-2 align-items-center mb-4" style="max-width: 32rem;">
	<?php echo csrf_pole(); ?>
	<div class="col">
		<input type="text" class="form-control" name="text1" maxlength="255" placeholder="text1" required>
	</div>
	<div class="col-auto">
		<button class="btn btn-primary" type="submit">Dodaj</button>
	</div>
</form>

<table class="table table-sm table-striped" style="max-width: 40rem;">
	<thead><tr><th>id</th><th>text1</th><th>datetime</th></tr></thead>
	<tbody>
<?php foreach ($ostatnie as $w): ?>
		<tr><td><?php echo (int)$w['id']; ?></td><td><?php echo e($w['text1']); ?></td><td><?php echo e($w['datetime']); ?></td></tr>
<?php endforeach; ?>
	</tbody>
</table>
<div class="mt-3">Podgląd na żywo: <a href="index2.php">Odczyt z AJAX – baza</a> &middot; <a href="index1.php">Odczyt z AJAX</a></div>
<script>
(function ()
{
	var tbody = document.querySelector('table tbody');
	function odswiez()
	{
		fetch('ostatnie.php', { credentials: 'same-origin', cache: 'no-store' })
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
