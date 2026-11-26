<?php declare(strict_types=1);
require_once 'lib.php';
wymagaj_logowania();
$tytul = 'Nowa SCADA – podkład budowlany (SSE, CSV)';
require 'gora.php';
?>
<style>
	.scada-pasek { display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; margin-bottom: .75rem; }
	.scada-dioda { width: .9rem; height: .9rem; border-radius: 50%; background: #dc3545; display: inline-block; }
	.scada-dioda.on { background: #198754; box-shadow: 0 0 6px #198754; }
	.scada-alarm { display: none; }
	.scada-alarm.on { display: block; animation: miganie 1s infinite; }
	@keyframes miganie { 50% { opacity: .35; } }
	#scada { width: 100%; max-width: 52rem; height: auto; background: #fff; border-radius: .5rem; box-shadow: 0 1px 6px rgba(0,0,0,.15); }
	.pok-tlo { transition: fill .6s; }
	.pok-nazwa { font: 600 20px sans-serif; fill: #212529; }
	.pok-temp { font: 700 44px sans-serif; fill: #212529; }
	.pok-stan { font: 600 14px sans-serif; fill: #212529; }
	.pok-min { font: 12px sans-serif; fill: #343a40; }
	.mebel { fill: rgba(0, 0, 0, .13); stroke: rgba(0, 0, 0, .25); stroke-width: 1.5; }
	.sciana { stroke: #343a40; stroke-width: 8; fill: none; stroke-linecap: square; }
	.okno { stroke: #0d6efd; stroke-width: 6; }
	.drzwi { stroke: #6c757d; stroke-width: 1.5; fill: none; stroke-dasharray: 4 3; }
	.drzwi-skrzydlo { stroke: #6c757d; stroke-width: 3; }
	.skala { height: .7rem; border-radius: .35rem; background: linear-gradient(90deg, hsl(210,55%,70%), hsl(120,55%,70%), hsl(0,55%,70%)); max-width: 22rem; }
</style>

<div class="scada-pasek">
	<span><span class="scada-dioda" id="dioda"></span> <span id="stan">Łączenie…</span></span>
	<span>Ostatni pomiar: <strong id="czas">&ndash;</strong></span>
	<span>Średnia: <strong id="srednia">&ndash;</strong></span>
	<span>Pomiarów w historii: <strong id="ile">0</strong></span>
	<a class="btn btn-sm btn-primary ms-auto" href="b_formularz.php?scada=1">FORMULARZ SCADA</a>
</div>
<div class="alert alert-danger scada-alarm" id="alarm"></div>
<div class="alert alert-secondary d-none" id="brak">Brak pomiarów. Dodaj pierwszy pomiar formularzem SCADA.</div>

<svg id="scada" viewBox="0 0 780 600" role="img" aria-label="Rzut mieszkania"></svg>

<div class="d-flex align-items-center gap-2 mt-3 small text-muted">
	<span>14&nbsp;&deg;C</span><div class="skala flex-grow-1"></div><span>32&nbsp;&deg;C</span>
	<span class="ms-3">Alarm: poniżej 10 &deg;C lub powyżej 30 &deg;C</span>
</div>

<script>
(function ()
{
	var NS = 'http://www.w3.org/2000/svg';
	var HISTORIA = 30;
	var pokoje = [
		{ nazwa: 'Salon',     x: 170, y: 20,  w: 350, h: 280 },
		{ nazwa: 'Kuchnia',   x: 520, y: 20,  w: 240, h: 280 },
		{ nazwa: 'Sypialnia', x: 170, y: 300, w: 290, h: 240 },
		{ nazwa: 'Łazienka',  x: 460, y: 300, w: 300, h: 240 },
		{ nazwa: 'Korytarz',  x: 20,  y: 20,  w: 150, h: 520 }
	];
	var dane = [[], [], [], [], []];
	var svg = document.getElementById('scada');

	function el(nazwa, atr, rodzic, tekst)
	{
		var e = document.createElementNS(NS, nazwa);
		for (var k in atr) { e.setAttribute(k, atr[k]); }
		if (tekst !== undefined) { e.textContent = tekst; }
		(rodzic || svg).appendChild(e);
		return e;
	}

	function kolor(v)
	{
		var t = Math.max(0, Math.min(1, (v - 14) / 18));
		return 'hsl(' + Math.round(210 - 210 * t) + ',55%,70%)';
	}

	function stan(v)
	{
		return v < 18 ? 'ZIMNO' : v > 26 ? 'GORĄCO' : 'KOMFORT';
	}

	pokoje.forEach(function (p, i)
	{
		p.tlo = el('rect', { 'class': 'pok-tlo', x: p.x, y: p.y, width: p.w, height: p.h, fill: '#e9ecef' });
	});

	function mebel(x, y, w, h, r) { el('rect', { 'class': 'mebel', x: x, y: y, width: w, height: h, rx: r || 6 }); }
	function kolo(cx, cy, r) { el('circle', { 'class': 'mebel', cx: cx, cy: cy, r: r }); }
	mebel(236, 240, 120, 44, 10); mebel(236, 240, 26, 44, 10); mebel(330, 240, 26, 44, 10);
	mebel(360, 34, 100, 14, 3);
	kolo(420, 140, 30);
	mebel(704, 34, 36, 130, 4); kolo(722, 66, 10); kolo(722, 104, 10); mebel(704, 172, 36, 44, 4);
	mebel(580, 190, 100, 46, 6);
	mebel(330, 380, 110, 70, 8); mebel(330, 380, 110, 16, 8);
	mebel(620, 318, 120, 50, 12); kolo(680, 430, 14); mebel(540, 498, 54, 24, 4);
	mebel(35, 430, 70, 24, 4);

	pokoje.forEach(function (p)
	{
		var tx = p.x + (p.w < 200 ? 14 : 20);
		p.tNazwa = el('text', { 'class': 'pok-nazwa', x: tx, y: p.y + 32 }, null, p.nazwa);
		p.tTemp = el('text', { 'class': 'pok-temp', x: tx, y: p.y + 90 }, null, '–');
		p.tStan = el('text', { 'class': 'pok-stan', x: tx + 2, y: p.y + 112 }, null, '');
		p.tMin = el('text', { 'class': 'pok-min', x: tx + 2, y: p.y + 130 }, null, '');
		p.sw = 130; p.sh = 34;
		p.sx = p.w < 200 ? p.x + 10 : p.x + p.w - 150;
		p.sy = p.y + p.h - 52;
		p.ramka = el('rect', { x: p.sx, y: p.sy, width: p.sw, height: p.sh, fill: 'rgba(255,255,255,.55)', rx: 4, visibility: 'hidden' });
		p.linia = el('polyline', { fill: 'none', stroke: '#212529', 'stroke-width': 2, points: '' });
	});

	function sciana(x1, y1, x2, y2) { el('line', { 'class': 'sciana', x1: x1, y1: y1, x2: x2, y2: y2 }); }
	function drzwi(zawiasX, zawiasY, dx, dy, ex, ey)
	{
		el('line', { 'class': 'drzwi-skrzydlo', x1: zawiasX, y1: zawiasY, x2: dx, y2: dy });
		el('path', { 'class': 'drzwi', d: 'M' + dx + ' ' + dy + ' A50 50 0 0 ' + (((dx - zawiasX) * (ey - zawiasY) - (dy - zawiasY) * (ex - zawiasX)) > 0 ? 1 : 0) + ' ' + ex + ' ' + ey });
	}

	sciana(20, 20, 760, 20); sciana(760, 20, 760, 540); sciana(760, 540, 120, 540); sciana(70, 540, 20, 540); sciana(20, 540, 20, 20);
	sciana(170, 20, 170, 180); sciana(170, 230, 170, 450); sciana(170, 500, 170, 540);
	sciana(520, 20, 520, 180); sciana(520, 230, 520, 300);
	sciana(170, 300, 330, 300); sciana(380, 300, 760, 300);
	sciana(460, 300, 460, 440); sciana(460, 490, 460, 540);
	drzwi(170, 180, 220, 180, 170, 230);
	drzwi(170, 450, 220, 450, 170, 500);
	drzwi(520, 180, 570, 180, 520, 230);
	drzwi(330, 300, 330, 350, 380, 300);
	drzwi(460, 440, 510, 440, 460, 490);
	drzwi(70, 540, 70, 590, 120, 540);
	[[210, 20, 310, 20], [20, 330, 20, 480], [760, 60, 760, 170], [230, 540, 400, 540], [540, 540, 640, 540], [760, 350, 760, 430]].forEach(function (o)
	{
		el('line', { 'class': 'okno', x1: o[0], y1: o[1], x2: o[2], y2: o[3] });
	});
	el('text', { x: 135, y: 574, 'font-size': 13, fill: '#6c757d' }, null, 'wejście');

	function odswiez()
	{
		var ostatnie = [];
		pokoje.forEach(function (p, i)
		{
			var h = dane[i];
			if (!h.length) { return; }
			var v = h[h.length - 1];
			ostatnie.push(v);
			p.tlo.setAttribute('fill', kolor(v));
			p.tTemp.textContent = v.toFixed(1).replace('.0', '') + ' °C';
			p.tStan.textContent = stan(v);
			var mn = Math.min.apply(null, h), mx = Math.max.apply(null, h);
			p.tMin.textContent = 'min ' + mn + ' · max ' + mx;
			var zakres = (mx - mn) || 1;
			var pts = h.map(function (w, k)
			{
				var px = p.sx + 4 + (h.length === 1 ? 0 : k * (p.sw - 8) / (h.length - 1));
				var py = p.sy + p.sh - 4 - (w - mn) / zakres * (p.sh - 8);
				return px.toFixed(1) + ',' + py.toFixed(1);
			});
			p.linia.setAttribute('points', h.length > 1 ? pts.join(' ') : '');
			p.ramka.setAttribute('visibility', h.length > 1 ? 'visible' : 'hidden');
		});
		if (ostatnie.length)
		{
			var s = ostatnie.reduce(function (a, b) { return a + b; }, 0) / ostatnie.length;
			document.getElementById('srednia').textContent = s.toFixed(1) + ' °C';
			var alarmy = [];
			pokoje.forEach(function (p, i)
			{
				var h = dane[i], v = h[h.length - 1];
				if (v < 10 || v > 30) { alarmy.push(p.nazwa + ': ' + v + ' °C'); }
			});
			var a = document.getElementById('alarm');
			a.textContent = alarmy.length ? 'ALARM – ' + alarmy.join(', ') : '';
			a.classList.toggle('on', alarmy.length > 0);
		}
		document.getElementById('ile').textContent = dane[0].length;
	}

	var zrodlo;
	function polacz()
	{
		zrodlo = new EventSource('b_scada_from_db.php');
		zrodlo.onopen = function ()
		{
			document.getElementById('dioda').classList.add('on');
			document.getElementById('stan').textContent = 'Połączono';
		};
		zrodlo.onmessage = function (e)
		{
			if (e.data === 'BRAK') { document.getElementById('brak').classList.remove('d-none'); return; }
			document.getElementById('brak').classList.add('d-none');
			var pola = e.data.split("\t");
			for (var i = 0; i < 5; i++)
			{
				dane[i].push(parseFloat(pola[i + 1]));
				if (dane[i].length > HISTORIA) { dane[i].shift(); }
			}
			document.getElementById('czas').textContent = pola[6];
			odswiez();
		};
		zrodlo.onerror = function ()
		{
			zrodlo.close();
			console.log('Done!');
			document.getElementById('dioda').classList.remove('on');
			document.getElementById('stan').textContent = 'Rozłączono, ponawiam…';
			dane = [[], [], [], [], []];
			setTimeout(polacz, 3000);
		};
	}
	polacz();
})();
</script>
<?php require 'dol.php'; ?>
