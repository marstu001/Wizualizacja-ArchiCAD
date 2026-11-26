<?php declare(strict_types=1);
require_once 'p_lib.php';
$s = p_wymagaj();
$tytul = 'Wizualizacja środków trwałych i pracowników';
$publiczna = true;
require 'gora.php';
p_menu('p_wizualizacja.php');
?>
<div class="p-uklad" id="p-wrap">
	<aside class="p-karta-ui p-boczny">
		<h2><i class="bi bi-boxes"></i> Elementy <span class="badge text-bg-secondary" id="p-licznik">0</span></h2>
		<select class="form-select form-select-sm" id="p-typ" aria-label="Typ"></select>
		<input type="search" class="form-control form-control-sm" id="p-szukaj" placeholder="Szukaj (np. k1, serwer, WC)" aria-label="Szukaj" autocomplete="off">
		<div class="p-lista" id="p-lista"></div>
	</aside>

	<section class="p-karta-ui p-glowny">
		<div class="p-pasek">
			<div class="btn-group" id="p-pietra" role="group" aria-label="Piętro"></div>
			<div class="btn-group d-none" id="p-widok" role="group" aria-label="Widok">
				<button type="button" class="btn btn-sm btn-secondary" id="p-w2d"><i class="bi bi-map"></i> Plan 2D</button>
				<button type="button" class="btn btn-sm btn-outline-secondary" id="p-w3d"><i class="bi bi-box"></i> Quasi-3D</button>
				<button type="button" class="btn btn-sm btn-outline-secondary d-none" id="p-wm"><i class="bi bi-badge-3d"></i> Model 3D</button>
			</div>
			<label class="p-wys d-none" id="p-model-opcje">Wysokość ścian <input type="range" class="form-range" id="p-wys" min="0" max="100" value="100"></label>
			<div class="p-warstwy" id="p-warstwy"></div>
			<label class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" id="p-pokaz-prac" checked><span class="form-check-label">Pracownicy</span></label>
			<label class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" id="p-pokaz-et" checked><span class="form-check-label">Identyfikatory</span></label>
<?php if ($s): ?>
			<label class="form-check form-switch m-0 ms-lg-auto"><input class="form-check-input" type="checkbox" id="p-edycja"><span class="form-check-label"><i class="bi bi-arrows-move"></i> Przesuwanie</span></label>
<?php endif; ?>
		</div>
		<div class="alert alert-warning py-1 px-2 d-none" id="p-komunikat" role="alert"></div>
		<div class="position-relative">
			<div class="p-plansza" id="p-plansza"></div>
			<div class="p-info d-none" id="p-info"></div>
		</div>
		<div class="p-legenda" id="p-legenda"></div>
	</section>
</div>
<script src="<?php echo p_plik_wersja('p_wizualizacja.js'); ?>"></script>
<?php require 'dol.php'; ?>
