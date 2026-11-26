<?php declare(strict_types=1);
require_once 'p_lib.php';
$s = p_wymagaj();
$link = p_polacz();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	csrf_wymagaj();
	$idp = (int)($_POST['idp'] ?? 0);
	$prac = p_pracownicy($link, 'p.idp = ?', 'i', [$idp]);
	if ($prac) {
		p_wykonaj($link, 'UPDATE srodek SET idp = NULL WHERE idp = ?', 'i', [$idp]);
		p_wykonaj($link, 'DELETE FROM pracownik WHERE idp = ?', 'i', [$idp]);
		p_foto_usun(basename((string)$prac[0]['foto']));
		flash('success', 'Usunięto pracownika: ' . $prac[0]['login'] . '.');
	} else { flash('danger', 'Nie można usunąć tego pracownika.'); }
	header('Location: p_pracownicy.php');
	exit();
}
$pracownicy = p_pracownicy($link);
$tytul = 'Pracownicy';
require 'gora.php';
p_menu('p_pracownicy.php');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
	<h2 class="h5 m-0">Pracownicy <span class="badge text-bg-secondary"><?php echo count($pracownicy); ?></span></h2>
	<a class="btn btn-primary" href="p_pracownik_nowy.php"><i class="bi bi-person-plus"></i> Dodaj pracownika</a>
</div>
<div class="table-responsive">
<table class="table table-striped align-middle">
	<thead class="table-dark"><tr><th></th><th>Login</th><th>Imię i nazwisko</th><th>Stan</th><th></th></tr></thead>
	<tbody>
<?php foreach ($pracownicy as $p): ?>
		<tr>
			<td><img alt="" src="<?php echo e($p['foto']); ?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover"></td>
			<td><?php echo e($p['login']); ?></td>
			<td><?php echo e($p['nazwisko']); ?></td>
			<td><?php echo e(($p['stan_nazwa'])); ?></td>
			<td class="text-end">
				<a class="btn btn-sm btn-outline-primary" href="p_profil.php?idp=<?php echo $p['idp']; ?>"><i class="bi bi-pencil"></i> Edytuj</a>
				<form method="post" class="d-inline" onsubmit="return confirm('Usunąć pracownika <?php echo e($p['login']); ?>?');">
					<?php echo csrf_pole(); ?>
					<input type="hidden" name="idp" value="<?php echo $p['idp']; ?>">
					<button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> Usuń</button>
				</form>
			</td>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>
</div>
<?php require 'dol.php'; ?>
