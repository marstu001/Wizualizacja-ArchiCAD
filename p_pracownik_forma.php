<?php declare(strict_types=1);
$foto_pokaz = $pr && $pr['foto'] !== '' ? $pr['foto'] : P_FOTO_KATALOG . '/domyslny.png';
?>
<div class="row g-3">
	<div class="col-lg-5">
		<div class="p-karta-ui p-3 d-grid gap-3">
<?php if (!empty($pole_login)): ?>
			<div><label class="form-label" for="login">Login</label><input class="form-control" type="text" name="login" id="login" maxlength="20" required pattern="[A-Za-z0-9_-]{1,20}" value="<?php echo e((string)($wart['login'] ?? '')); ?>"></div>
			<div><label class="form-label" for="haslo">Hasło</label><input class="form-control" type="password" name="haslo" id="haslo" minlength="1" required></div>
<?php endif; ?>
			<div><label class="form-label" for="nazwisko">Imię i nazwisko</label><input class="form-control" type="text" name="nazwisko" id="nazwisko" maxlength="100" value="<?php echo e((string)($wart['nazwisko'] ?? '')); ?>"></div>
			<div>
				<label class="form-label" for="foto">Zdjęcie</label>
				<div class="d-flex gap-3 align-items-center">
					<img id="p-foto-pod" alt="" src="<?php echo e($foto_pokaz); ?>" style="width:72px;height:72px;border-radius:50%;object-fit:cover">
					<input class="form-control" type="file" name="foto" id="foto" accept="image/png,image/jpeg,image/gif,image/webp">
				</div>
			</div>
			<div>
				<label class="form-label" for="stan">Stan</label>
				<select class="form-select" name="stan" id="stan">
<?php foreach (P_STANY as $k => $v): ?>
					<option value="<?php echo $k; ?>"<?php echo (int)($wart['stan'] ?? 1) === $k ? ' selected' : ''; ?>><?php echo e(($v['ikona'] !== '' ? $v['ikona'] . ' ' : '') . $v['nazwa']); ?></option>
<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>
	<div class="col-lg-7">
		<div class="p-karta-ui p-3 d-grid gap-3">
			<div class="row g-3 align-items-end">
				<div class="col-sm-5"><label class="form-label" for="idpod">Podkład</label>
					<select class="form-select" name="idpod" id="idpod">
<?php foreach ($plany as $p): ?>
						<option value="<?php echo $p['idpod']; ?>"<?php echo (int)($wart['idpod'] ?? 0) === $p['idpod'] ? ' selected' : ''; ?>><?php echo e($p['nazwa']); ?></option>
<?php endforeach; ?>
					</select></div>
				<div class="col-6 col-sm-3"><label class="form-label" for="x">X</label><input class="form-control" type="number" min="0" name="x" id="x" value="<?php echo e((string)($wart['x'] ?? '')); ?>"></div>
				<div class="col-6 col-sm-3"><label class="form-label" for="y">Y</label><input class="form-control" type="number" min="0" name="y" id="y" value="<?php echo e((string)($wart['y'] ?? '')); ?>"></div>
			</div>
			<div class="fw-semibold">Pomieszczenie: <span class="text-primary" id="p-pom-wyj">—</span></div>
			<div id="p-wybor"></div>
		</div>
	</div>
</div>
<script src="<?php echo p_plik_wersja('p_wybor.js'); ?>"></script>
<script>
(function ()
{
	PWybor(document.getElementById('p-wybor'), { plany: <?php echo json_encode($plany, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>, pomieszczenia: <?php echo json_encode($pomieszczenia, JSON_UNESCAPED_UNICODE); ?>, wejX: '#x', wejY: '#y', wejPlan: '#idpod', pomWyj: '#p-pom-wyj', rozmiar: 90, elementy: <?php echo json_encode(p_elementy_wyboru($link, 0, $pr ? (int)$pr['idp'] : 0), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?> });
	var f = document.getElementById('foto');
	f.addEventListener('change', function ()
	{
		if (f.files && f.files[0]) { document.getElementById('p-foto-pod').src = URL.createObjectURL(f.files[0]); }
	});
})();
</script>
