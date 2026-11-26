import * as THREE from './p_three/three.module.js';

var PX = 0.032016;
var TYPY = [
	{ re: /serwerow/, rodzaj: 'plytki', k: '#d9dde1', l: '#aeb5bd', s: 600 },
	{ re: /korytarz/, rodzaj: 'plytki', k: '#d6d1c7', l: '#b8b2a5', s: 800 },
	{ re: /toalet|szatni/, rodzaj: 'plytki', k: '#d0dce1', l: '#a8bbc5', s: 300 },
	{ re: /socjaln/, rodzaj: 'parkiet', k: '#b98a54', l: '#94693a', s: 1200 },
	{ re: /konferenc/, rodzaj: 'parkiet', k: '#8a6038', l: '#68431f', s: 1200 },
	{ re: /sekretariat|biuro|portiern/, rodzaj: 'parkiet', k: '#a8794a', l: '#84582d', s: 1200 },
	{ re: /poczekaln/, rodzaj: 'dywan', k: '#6f7f93', l: '#5f6f82', s: 1000 },
	{ re: /akumulator|rozdzieln/, rodzaj: 'beton', k: '#adb1b5', l: '#8f949a', s: 2000 },
	{ re: /agregat|transformator/, rodzaj: 'beton', k: '#8c8f93', l: '#6f7378', s: 3000 },
	{ re: /./, rodzaj: 'beton', k: '#9ea1a5', l: '#82868b', s: 2000 }
];

var cache = {};

function los(i)
{
	var x = Math.sin(i * 127.1) * 43758.5453;
	return x - Math.floor(x);
}

function tekstura(t)
{
	var klucz = t.rodzaj + t.k;
	if (cache[klucz]) { return cache[klucz]; }
	var c = document.createElement('canvas');
	c.width = c.height = 256;
	var g = c.getContext('2d');
	g.fillStyle = t.k;
	g.fillRect(0, 0, 256, 256);
	var i;
	if (t.rodzaj === 'plytki') {
		g.strokeStyle = t.l;
		g.lineWidth = 6;
		g.strokeRect(0, 0, 256, 256);
		for (i = 0; i < 260; i++) {
			g.fillStyle = 'rgba(255,255,255,' + (0.05 * los(i)) + ')';
			g.fillRect(los(i + 7) * 250, los(i + 13) * 250, 6, 6);
		}
	} else if (t.rodzaj === 'parkiet') {
		g.strokeStyle = t.l;
		g.lineWidth = 3;
		for (i = 0; i < 8; i++) {
			var y = i * 32;
			g.beginPath(); g.moveTo(0, y); g.lineTo(256, y); g.stroke();
			var x = (i % 2) * 128 + 64;
			g.beginPath(); g.moveTo(x, y); g.lineTo(x, y + 32); g.stroke();
			g.fillStyle = 'rgba(0,0,0,' + (0.06 * los(i)) + ')';
			g.fillRect(0, y, 256, 32);
		}
	} else if (t.rodzaj === 'dywan') {
		for (i = 0; i < 2500; i++) {
			g.fillStyle = 'rgba(' + (los(i) > 0.5 ? '255,255,255' : '0,0,0') + ',0.07)';
			g.fillRect(los(i + 3) * 256, los(i + 5) * 256, 2, 2);
		}
	} else {
		for (i = 0; i < 1800; i++) {
			g.fillStyle = 'rgba(' + (los(i) > 0.5 ? '255,255,255' : '0,0,0') + ',0.06)';
			g.fillRect(los(i + 3) * 256, los(i + 5) * 256, 3, 3);
		}
		g.strokeStyle = t.l;
		g.lineWidth = 3;
		g.strokeRect(0, 0, 256, 256);
	}
	var tex = new THREE.CanvasTexture(c);
	tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
	tex.colorSpace = THREE.SRGBColorSpace;
	tex.anisotropy = 8;
	cache[klucz] = tex;
	return tex;
}

export function typPodlogi(nazwa)
{
	var n = String(nazwa).toLowerCase();
	for (var i = 0; i < TYPY.length; i++) { if (TYPY[i].re.test(n)) { return TYPY[i]; } }
	return TYPY[TYPY.length - 1];
}

export function podlogi(lista, y)
{
	var grupa = new THREE.Group();
	lista.forEach(function (p)
	{
		var t = typPodlogi(p.nazwa);
		var w = p.x2 - p.x1, h = p.y2 - p.y1;
		var geo = new THREE.PlaneGeometry(w, h);
		var uv = geo.attributes.uv;
		var rx = w / (t.s * PX), ry = h / (t.s * PX);
		for (var i = 0; i < uv.count; i++) { uv.setXY(i, uv.getX(i) * rx, uv.getY(i) * ry); }
		var m = new THREE.Mesh(geo, new THREE.MeshLambertMaterial({ map: tekstura(t) }));
		m.rotation.x = -Math.PI / 2;
		m.position.set((p.x1 + p.x2) / 2, y, (p.y1 + p.y2) / 2);
		grupa.add(m);
	});
	return grupa;
}
