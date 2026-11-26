<?php declare(strict_types=1);
require_once 'k_lib.php';
[$uid, $login, $admin] = wymagaj_logowania();
$tytul = 'Komunikator';
require 'gora.php';

$link = db_polacz();
$uzytkownicy = $link ? kom_uzytkownicy($link, $login) : [];
$ostatni = 0;
if ($link) {
	$r = mysqli_query($link, 'SELECT COALESCE(MAX(idk), 0) FROM messages');
	$ostatni = $r ? (int)mysqli_fetch_row($r)[0] : 0;
}
?>
<link rel="stylesheet" href="k_komunikator.css?v=16">
<div id="kom-app" data-ostatni="<?php echo $ostatni; ?>" data-csrf="<?php echo e(csrf_token()); ?>" data-login="<?php echo e($login); ?>" data-limit="<?php echo e((string)ini_get('upload_max_filesize')); ?>">

	<aside id="kom-bok">
		<div class="kom-bok-gora">
			<h2>Czaty</h2>
			<span class="badge rounded-pill text-bg-secondary" id="kom-stan"><i class="bi bi-wifi-off"></i></span>
		</div>
		<div class="kom-szukaj"><i class="bi bi-search"></i><input type="search" id="kom-szukaj" placeholder="Szukaj rozmowy" autocomplete="off" aria-label="Szukaj rozmowy"></div>
		<div id="kom-rozmowy" class="kom-rozmowy"></div>
	</aside>

	<section id="kom-panel">
		<div id="kom-brak-czatu"><i class="bi bi-chat-dots"></i><div>Wybierz rozmowę</div></div>

		<div id="kom-czat" class="d-none">
			<header class="kom-czat-gora">
				<button type="button" class="kom-ikona kom-wstecz" id="kom-wstecz" title="Wróć do listy"><i class="bi bi-arrow-left"></i></button>
				<span id="kom-czat-awatar"></span>
				<div class="kom-czat-nazwa"><strong id="kom-czat-nazwa"></strong><small id="kom-czat-opis"></small></div>
				<div class="kom-narzedzia">
					<button type="button" class="kom-ikona" id="kom-motyw" title="Motyw rozmowy"><i class="bi bi-palette"></i></button>
					<button type="button" class="kom-ikona" id="kom-dzwiek" title="Dźwięk powiadomień"><i class="bi bi-bell"></i></button>
					<button type="button" class="kom-ikona" id="kom-glos" title="Czytaj nowe wiadomości na głos"><i class="bi bi-volume-up"></i></button>
					<button type="button" class="kom-ikona" id="kom-kolejnosc" title="Najnowsze u góry"><i class="bi bi-sort-down"></i></button>
				</div>
			</header>

			<div class="kom-motyw-okno d-none" id="kom-motyw-okno"></div>

			<div id="kom-wiadomosci">
				<div id="kom-starsze-blok" class="d-none"><button type="button" class="btn btn-sm btn-light" id="kom-starsze"><i class="bi bi-clock-history"></i> Starsze wiadomości</button></div>
				<div id="kom-lista"></div>
				<div id="kom-pusto" class="d-none"><i class="bi bi-chat-heart"></i><div>Brak wiadomości</div></div>
			</div>
			<button type="button" id="kom-nowe" class="d-none"><i class="bi bi-arrow-down"></i> <span></span></button>

			<footer class="kom-pisz">
				<div class="progress d-none" id="kom-postep" style="height:1.2rem;"><div class="progress-bar" style="width:0%"></div></div>
				<div class="alert alert-danger d-none py-1 px-2 mb-2" id="kom-blad" role="alert"></div>
				<form id="kom-forma" autocomplete="off">
					<select class="form-select form-select-sm d-none" id="kom-adresat" name="recipient" aria-label="Adresat">
						<option value="" selected disabled>Adresat</option>
<?php foreach ($uzytkownicy as $u): ?>
						<option value="<?php echo e($u['username']); ?>"><?php echo e($u['username']); ?></option>
<?php endforeach; ?>
					</select>
					<div class="kom-taca d-none" id="kom-taca"></div>
					<div class="kom-pasek">
						<div class="kom-emoji-okno d-none" id="kom-emoji-okno"></div>
						<button type="button" class="kom-ikona" id="kom-emoji" title="Emotikony"><i class="bi bi-emoji-smile"></i></button>
						<button type="button" class="kom-ikona" id="kom-zalacznik" title="Dołącz plik"><i class="bi bi-paperclip"></i></button>
						<input type="text" id="kom-tekst" name="post" maxlength="<?php echo KOM_TEKST_MAX; ?>" placeholder="Aa" aria-label="Wiadomość">
						<button type="button" class="kom-ikona" id="kom-mikrofon" title="Dyktuj wiadomość" style="display:none"><i class="bi bi-mic"></i></button>
						<button type="submit" class="kom-ikona kom-wyslij" id="kom-wyslij" title="Wyślij"><i class="bi bi-send-fill"></i></button>
					</div>
					<input type="file" id="kom-plik" multiple hidden>
				</form>
			</footer>
		</div>
	</section>
</div>
<div id="kom-lightbox" class="d-none"><img alt=""></div>
<script src="k_komunikator.js?v=16"></script>
<?php require 'dol.php'; ?>
