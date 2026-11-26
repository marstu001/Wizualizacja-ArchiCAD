<?php declare(strict_types=1);
require_once 'k_lib.php';
[$uid, $login, $admin] = wymagaj_logowania();
$tytul = 'Wyślij plik';
require 'gora.php';
$link = db_polacz();
$uzytkownicy = $link ? kom_uzytkownicy($link, $login) : [];
foreach (flash_pobierz() as [$typ, $tekst]) { echo '<div class="alert alert-' . e($typ) . '" role="alert">' . e($tekst) . '</div>' . "\n"; }
?>
<form action="k_upload.php" method="post" enctype="multipart/form-data" class="mb-3" style="max-width:560px;">
	<?php echo csrf_pole(); ?>
	<div class="mb-3">
		<label for="adresat" class="form-label">Adresat</label>
		<select class="form-select" id="adresat" name="recipient" required<?php echo $uzytkownicy ? '' : ' disabled'; ?>>
			<option value="" selected disabled><?php echo $uzytkownicy ? 'Wybierz użytkownika' : 'Brak innych użytkowników'; ?></option>
<?php foreach ($uzytkownicy as $u): ?>
			<option value="<?php echo e($u['username']); ?>"><?php echo e($u['username']); ?></option>
<?php endforeach; ?>
		</select>
	</div>
	<label for="fileToUpload" class="form-label">Select file to upload</label>
	<input class="form-control mb-3" type="file" name="plik[]" id="fileToUpload" multiple required>
	<button class="btn btn-primary" type="submit" name="submit" value="Upload"<?php echo $uzytkownicy ? '' : ' disabled'; ?>><i class="bi bi-send"></i> Upload</button>
	<a class="btn btn-outline-secondary" href="k_index1.php">Wróć do komunikatora</a>
</form>
<?php require 'dol.php'; ?>
