(function ()
{
	'use strict';
	function $(s) { return document.querySelector(s); }

	window.PWybor = function (kontener, opcje)
	{
		var plany = opcje.plany, pomieszczenia = opcje.pomieszczenia || [];
		var wx = $(opcje.wejX), wy = $(opcje.wejY), wp = $(opcje.wejPlan), wyj = opcje.pomWyj ? $(opcje.pomWyj) : null;
		var rozmiar = opcje.rozmiar || 80;
		kontener.classList.add('p-wybor');
		kontener.innerHTML = '<img alt="" draggable="false"><div class="p-znacznik"></div>';
		var warstwy = document.createElement('div'); warstwy.className = 'p-wyb-warstwy'; kontener.insertBefore(warstwy, kontener.querySelector('.p-znacznik'));
		var elementy = document.createElement('div'); elementy.className = 'p-wyb-elementy'; kontener.insertBefore(elementy, kontener.querySelector('.p-znacznik'));
		var pasek = document.createElement('div'); pasek.className = 'p-wyb-pasek d-flex flex-wrap gap-3 mb-2'; kontener.parentNode.insertBefore(pasek, kontener);
		var wl = {};
		function pasekRysuj(p)
		{
			var h = '';
			[['k', 'Konstrukcja'], ['i', 'Ikony'], ['p', 'Pracownicy']].forEach(function (t) { h += '<label class="form-check form-check-inline m-0"><input class="form-check-input" type="checkbox" data-w="' + t[0] + '"' + (wl[t[0]] === false ? '' : ' checked') + '> <span class="form-check-label">' + t[1] + '</span></label>'; });
			(p.warstwy || []).forEach(function (w) { if (w.nazwa === 'Umeblowanie') { return; } h += '<label class="form-check form-check-inline m-0"><input class="form-check-input" type="checkbox" data-w="' + w.idw + '"' + (wl[w.idw] === false ? '' : ' checked') + '> <span class="form-check-label">' + w.nazwa.replace(/</g, '&lt;') + '</span></label>'; });
			pasek.innerHTML = h;
		}
		function zastosuj(p)
		{
			obraz.style.visibility = wl.k === false ? 'hidden' : '';
			Array.prototype.slice.call(warstwy.querySelectorAll('img')).forEach(function (im, n) { var w = (p.warstwy || [])[n]; im.style.display = w && wl[w.idw] === false ? 'none' : ''; });
			Array.prototype.slice.call(elementy.children).forEach(function (d) { d.style.display = (d.classList.contains('prac') ? wl.p : wl.i) === false ? 'none' : ''; });
		}
		pasek.addEventListener('change', function (e) { var c = e.target; if (c.getAttribute('data-w')) { wl[c.getAttribute('data-w')] = c.checked; zastosuj(plan()); } });
		var obraz = kontener.querySelector('img'), znacznik = kontener.querySelector('.p-znacznik');

		function plan() { var id = parseInt(wp ? wp.value : opcje.idpod, 10); return plany.filter(function (p) { return p.idpod === id; })[0] || plany[0]; }

		function pomieszczenie(p, cx, cy)
		{
			var najl = null;
			pomieszczenia.forEach(function (r)
			{
				if (r.idpod === p.idpod && cx >= r.x1 && cx <= r.x2 && cy >= r.y1 && cy <= r.y2) {
					if (!najl || (r.x2 - r.x1) * (r.y2 - r.y1) < (najl.x2 - najl.x1) * (najl.y2 - najl.y1)) { najl = r; }
				}
			});
			return najl;
		}

		function rysuj()
		{
			var p = plan();
			if (!p) { return; }
			if (obraz.getAttribute('data-src') !== p.plik) {
				obraz.src = p.plik; obraz.setAttribute('data-src', p.plik);
				warstwy.innerHTML = '';
				pasekRysuj(p);
				(p.warstwy || []).forEach(function (w) { var im = document.createElement('img'); im.alt = ''; im.draggable = false; im.src = w.plik; warstwy.appendChild(im); });
			}
			elementy.innerHTML = '';
			(opcje.elementy || []).forEach(function (e)
			{
				if (e.idpod !== p.idpod) { return; }
				var d = document.createElement('div'); d.className = 'p-wyb-el' + (e.prac ? ' prac' : '');
				d.style.left = e.x / p.szer * 100 + '%'; d.style.top = e.y / p.wys * 100 + '%'; d.style.width = e.r / p.szer * 100 + '%';
				d.innerHTML = '<img alt="" draggable="false" src="' + e.plik.replace(/"/g, '&quot;') + '">';
				elementy.appendChild(d);
			});
			zastosuj(p);
			var x = parseInt(wx.value, 10), y = parseInt(wy.value, 10);
			if (isNaN(x) || isNaN(y) || wx.value === '' || wy.value === '') { znacznik.style.display = 'none'; if (wyj) { wyj.textContent = '—'; } return; }
			var cx = x + rozmiar / 2, cy = y + rozmiar / 2;
			znacznik.style.display = 'block';
			znacznik.style.left = cx / p.szer * 100 + '%';
			znacznik.style.top = cy / p.wys * 100 + '%';
			znacznik.style.width = rozmiar / p.szer * 100 + '%';
			if (wyj) { var r = pomieszczenie(p, cx, cy); wyj.textContent = r ? r.nazwa : 'poza pomieszczeniami'; wyj.setAttribute('data-idpom', r ? r.idpom : ''); }
		}

		kontener.addEventListener('click', function (e)
		{
			var p = plan(), rect = obraz.getBoundingClientRect();
			var cx = (e.clientX - rect.left) / rect.width * p.szer, cy = (e.clientY - rect.top) / rect.height * p.wys;
			wx.value = Math.max(0, Math.min(Math.round(cx - rozmiar / 2), p.szer - rozmiar));
			wy.value = Math.max(0, Math.min(Math.round(cy - rozmiar / 2), p.wys - rozmiar));
			rysuj();
			wx.dispatchEvent(new Event('change', { bubbles: true }));
		});
		[wx, wy].forEach(function (w) { w.addEventListener('input', rysuj); });
		if (wp) { wp.addEventListener('change', function () { obraz.setAttribute('data-src', ''); rysuj(); }); }
		obraz.addEventListener('load', rysuj);
		rysuj();
		return { odswiez: rysuj, ustawRozmiar: function (r) { rozmiar = r; rysuj(); } };
	};
})();
