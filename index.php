<?php declare(strict_types=1);
require_once 'p_lib.php';
$s = p_sesja();
$link = p_polacz();
$liczba = p_zapytanie($link, "SELECT COUNT(*) AS n FROM srodek WHERE status = 'aktywny'")[0]['n'] ?? 0;
$prac = p_zapytanie($link, 'SELECT COUNT(*) AS n FROM pracownik WHERE stan NOT IN (0, 6, 7, 8)')[0]['n'] ?? 0;
$tytul = 'Zadanie 10 – Wizualizacja datacenter';
require 'gora.php';
p_menu('index.php');
?>
<div class="row g-3">
<?php if ($s): ?>
	<div class="col-md-6 col-xl-4"><a class="p-kafel" href="p_wizualizacja.php"><i class="bi bi-map"></i><h3>Wizualizacja budynku</h3><p>Plan i widok quasi-3D z rozmieszczonymi środkami trwałymi i pracownikami.</p></a></div>
	<div class="col-md-6 col-xl-4"><a class="p-kafel jasny" href="p_tabela.php"><i class="bi bi-table"></i><h3>Tabela środków</h3><p><?php echo (int)$liczba; ?> aktywnych środków trwałych i wyposażenia.</p></a></div>
	<div class="col-md-6 col-xl-4"><a class="p-kafel ciemny" href="p_stany.php"><i class="bi bi-people"></i><h3>Stany pracowników</h3><p><?php echo (int)$prac; ?> osób jest teraz w pracy.</p></a></div>
	<div class="col-md-6 col-xl-4"><a class="p-kafel" href="p_srodek.php"><i class="bi bi-plus-circle"></i><h3>Dodaj środek</h3><p>Zarejestruj nowy środek trwały lub wyposażenie.</p></a></div>
<?php else: ?>
	<div class="col-md-6 col-xl-4"><a class="p-kafel ciemny" href="logowanie.php"><i class="bi bi-box-arrow-in-right"></i><h3>Zaloguj się</h3><p>Pracownicy mogą dodawać środki i zmieniać swój stan.</p></a></div>
<?php endif; ?>
<?php if ($s && $s['admin']): ?>
	<div class="col-md-6 col-xl-4"><a class="p-kafel jasny" href="p_podklady.php"><i class="bi bi-layers"></i><h3>Podkłady budowlane</h3><p>Dodawanie i usuwanie podkładów oraz warstw.</p></a></div>
	<div class="col-md-6 col-xl-4"><a class="p-kafel ciemny" href="p_pomieszczenia.php"><i class="bi bi-bounding-box"></i><h3>Pomieszczenia</h3><p>Nazwy i granice pomieszczeń na podkładzie.</p></a></div>
<?php endif; ?>
</div>
<?php require 'dol.php'; ?>
