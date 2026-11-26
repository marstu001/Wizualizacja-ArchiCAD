<?php declare(strict_types=1);
require_once 'p_lib.php';
p_wymagaj_admin();
$link = p_polacz();
$podklady = p_podklady($link);
$plany = array_values(array_filter($podklady, static fn($p) => $p['widok'] === '2d'));
$tytul = 'Podkłady budowlane';
require 'gora.php';
p_menu('p_podklady.php');
p_flash_wypisz();
?>
<div class="row g-3">
	<div class="col-lg-8">
<?php foreach ($podklady as $p): ?>
		<div class="p-karta-ui p-3 mb-3">
			<div class="d-flex gap-3">
				<a href="<?php echo e($p['plik']); ?>" target="_blank" rel="noopener"><img src="<?php echo e($p['plik']); ?>" alt="" style="width:150px;max-height:150px;object-fit:contain;background:#fff;border:1px solid #dee2e6;border-radius:.5rem"></a>
				<div class="flex-grow-1">
					<h2 class="h5 mb-1"><?php echo e($p['nazwa']); ?> <span class="badge text-bg-<?php echo $p['widok'] === '2d' ? 'primary' : 'info'; ?>"><?php echo $p['widok'] === '2d' ? 'plan 2D' : 'quasi-3D'; ?></span></h2>
					<div class="small text-muted"><?php echo $p['szer']; ?> × <?php echo $p['wys']; ?> px<?php echo $p['plan'] ? ', widok planu nr ' . $p['plan'] : ''; ?></div>
<?php if ($p['warstwy']): ?>
					<ul class="list-unstyled small mt-2 mb-2">
<?php foreach ($p['warstwy'] as $w): ?>
						<li class="d-flex align-items-center gap-2"><i class="bi bi-layers"></i> <?php echo e($w['nazwa']); ?>
							<form method="post" action="p_podklad_akcja.php" class="d-inline" onsubmit="return confirm('Usunąć warstwę?');"><?php echo csrf_pole(); ?><input type="hidden" name="akcja" value="usun_warstwe"><input type="hidden" name="idw" value="<?php echo $w['idw']; ?>"><button class="btn btn-sm btn-link text-danger p-0" type="submit" title="Usuń warstwę"><i class="bi bi-trash"></i></button></form></li>
<?php endforeach; ?>
					</ul>
<?php endif; ?>
					<form method="post" action="p_podklad_akcja.php" onsubmit="return confirm('Usunąć podkład? Środki i pracownicy ustawieni na nim wrócą na listę elementów poza planem.');"><?php echo csrf_pole(); ?><input type="hidden" name="akcja" value="usun_podklad"><input type="hidden" name="idpod" value="<?php echo $p['idpod']; ?>"><button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> Usuń podkład</button></form>
				</div>
			</div>
		</div>
<?php endforeach; ?>
<?php if (!$podklady): ?>
		<div class="p-karta-ui p-3 text-muted">Brak podkładów. Dodaj pierwszy po prawej stronie.</div>
<?php endif; ?>
	</div>
	<div class="col-lg-4">
		<form class="p-karta-ui p-3 d-grid gap-2 mb-3" method="post" action="p_podklad_akcja.php" enctype="multipart/form-data" id="p-forma-podklad">
			<?php echo csrf_pole(); ?>
			<input type="hidden" name="akcja" value="dodaj_podklad">
			<h2 class="h6 mb-0">Dodaj podkład</h2>
			<input class="form-control" type="text" name="nazwa" placeholder="Nazwa (np. Parter, Piętro)" maxlength="100" required>
			<select class="form-select" name="widok" id="p-widok-sel"><option value="2d">Plan 2D</option><option value="3d">Widok quasi-3D</option></select>
			<div class="d-none" id="p-3d-pola"><div class="d-grid gap-2">
				<select class="form-select" name="idpod_plan"><?php foreach ($plany as $p): ?><option value="<?php echo $p['idpod']; ?>"><?php echo e($p['nazwa']); ?></option><?php endforeach; ?></select>
				<div class="row g-1">
<?php foreach (['ma', 'mb', 'mc', 'md', 'me', 'mf'] as $k): ?>
					<div class="col-4"><input class="form-control form-control-sm" type="text" name="<?php echo $k; ?>" placeholder="<?php echo $k; ?>"></div>
<?php endforeach; ?>
				</div>
			</div></div>
			<input class="form-control" type="file" name="plik" accept="image/png,image/jpeg,image/gif,image/webp" required>
			<button class="btn btn-primary" type="submit"><i class="bi bi-upload"></i> Wyślij podkład</button>
		</form>
		<form class="p-karta-ui p-3 d-grid gap-2" method="post" action="p_podklad_akcja.php" enctype="multipart/form-data">
			<?php echo csrf_pole(); ?>
			<input type="hidden" name="akcja" value="dodaj_warstwe">
			<h2 class="h6 mb-0">Dodaj warstwę do podkładu</h2>
			<select class="form-select" name="idpod"><?php foreach ($podklady as $p): ?><option value="<?php echo $p['idpod']; ?>"><?php echo e($p['nazwa']); ?></option><?php endforeach; ?></select>
			<input class="form-control" type="text" name="nazwa" placeholder="Nazwa warstwy (np. Zasilanie)" maxlength="100" required>
			<input class="form-control" type="file" name="plik" accept="image/png,image/jpeg,image/gif,image/webp" required>
			<button class="btn btn-primary" type="submit"><i class="bi bi-upload"></i> Wyślij warstwę</button>
		</form>
	</div>
</div>
<script>
(function ()
{
	var s = document.getElementById('p-widok-sel'), p = document.getElementById('p-3d-pola');
	s.addEventListener('change', function () { p.classList.toggle('d-none', s.value !== '3d'); });
})();
</script>
<?php require 'dol.php'; ?>
