SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
	id         INT AUTO_INCREMENT PRIMARY KEY,
	username   VARCHAR(128) NOT NULL,
	password   VARCHAR(255) NOT NULL,
	avatar     VARCHAR(255) NULL DEFAULT NULL,
	worker     TINYINT(1) NOT NULL DEFAULT 0,
	bledne     INT NOT NULL DEFAULT 0,
	blokada_do DATETIME NULL DEFAULT NULL,
	utworzono  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	UNIQUE KEY uq_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

CREATE TABLE IF NOT EXISTS goscieportalu (
	id           INT AUTO_INCREMENT PRIMARY KEY,
	ipaddress    VARCHAR(45)  NOT NULL,
	`datetime`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	przegladarka TEXT         NULL,
	ekran        VARCHAR(20)  NULL,
	okno         VARCHAR(20)  NULL,
	kolory       TINYINT UNSIGNED NULL,
	ciasteczka   TINYINT(1)   NULL,
	java         TINYINT(1)   NULL,
	jezyk        VARCHAR(20)  NULL,
	login        VARCHAR(128) NULL,
	wynik        VARCHAR(20)  NULL,
	KEY idx_login (login)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS break_ins (
	id        INT AUTO_INCREMENT PRIMARY KEY,
	`datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	ipaddress VARCHAR(45) NOT NULL,
	login     VARCHAR(128) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages (
	idk        INT AUTO_INCREMENT PRIMARY KEY,
	`datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	message    TEXT NOT NULL,
	user       VARCHAR(128) NOT NULL,
	recipient  VARCHAR(128) NOT NULL DEFAULT '',
	KEY idx_user (user),
	KEY idx_recipient (recipient)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

CREATE TABLE IF NOT EXISTS ajax_from_db (
	id         INT AUTO_INCREMENT PRIMARY KEY,
	text1      VARCHAR(255) NOT NULL,
	`datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

CREATE TABLE IF NOT EXISTS pomiary (
	id         INT AUTO_INCREMENT PRIMARY KEY,
	x1 DOUBLE NULL, x2 DOUBLE NULL, x3 DOUBLE NULL, x4 DOUBLE NULL, x5 DOUBLE NULL,
	`datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS domeny (
	id           INT AUTO_INCREMENT PRIMARY KEY,
	host         VARCHAR(255) NOT NULL,
	port         INT NOT NULL,
	user_id      INT NULL,
	worker_id    INT NULL,
	typ          VARCHAR(30) NOT NULL DEFAULT '',
	status       TINYINT NOT NULL DEFAULT 1,
	errors       INT NOT NULL DEFAULT 0,
	total_errors INT NOT NULL DEFAULT 0,
	failure      DATETIME NULL DEFAULT NULL,
	last_check   DATETIME NULL DEFAULT NULL,
	last_error   VARCHAR(255) NULL DEFAULT NULL,
	KEY idx_user (user_id),
	KEY idx_worker (worker_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

CREATE TABLE IF NOT EXISTS udostepnienia (
	id            INT AUTO_INCREMENT PRIMARY KEY,
	wlasciciel_id INT NOT NULL,
	odbiorca_id   INT NOT NULL,
	sciezka       VARCHAR(500) NOT NULL,
	utworzono     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	UNIQUE KEY uq_udo (wlasciciel_id, odbiorca_id, sciezka(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

CREATE TABLE IF NOT EXISTS podklad (
	idpod       INT AUTO_INCREMENT PRIMARY KEY,
	nazwa       VARCHAR(100) NOT NULL,
	nazwa_pliku VARCHAR(255) NOT NULL,
	widok       ENUM('2d','3d') NOT NULL DEFAULT '2d',
	idpod_plan  INT NULL,
	szer        INT NOT NULL DEFAULT 0,
	wys         INT NOT NULL DEFAULT 0,
	ma DOUBLE NULL, mb DOUBLE NULL, mc DOUBLE NULL, md DOUBLE NULL, me DOUBLE NULL, mf DOUBLE NULL,
	kolejnosc   INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

CREATE TABLE IF NOT EXISTS warstwa (
	idw         INT AUTO_INCREMENT PRIMARY KEY,
	idpod       INT NOT NULL,
	nazwa       VARCHAR(100) NOT NULL,
	nazwa_pliku VARCHAR(255) NOT NULL,
	kolejnosc   INT NOT NULL DEFAULT 0,
	KEY idx_pod (idpod)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

CREATE TABLE IF NOT EXISTS pomieszczenie (
	idpom INT AUTO_INCREMENT PRIMARY KEY,
	idpod INT NOT NULL,
	nazwa VARCHAR(100) NOT NULL,
	x1    INT NOT NULL DEFAULT 0,
	y1    INT NOT NULL DEFAULT 0,
	x2    INT NOT NULL DEFAULT 0,
	y2    INT NOT NULL DEFAULT 0,
	KEY idx_pod (idpod)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

CREATE TABLE IF NOT EXISTS typ_srodka (
	idtyp INT AUTO_INCREMENT PRIMARY KEY,
	nazwa VARCHAR(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

CREATE TABLE IF NOT EXISTS nazwa_srodka (
	idnazwa     INT AUTO_INCREMENT PRIMARY KEY,
	nazwa       VARCHAR(100) NOT NULL,
	nazwa_pliku VARCHAR(255) NOT NULL,
	prefiks     VARCHAR(10) NOT NULL DEFAULT 'x',
	rozmiar     INT NOT NULL DEFAULT 80,
	idtyp       INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

CREATE TABLE IF NOT EXISTS pracownik (
	idp                INT AUTO_INCREMENT PRIMARY KEY,
	idu                INT NOT NULL,
	idpod              INT NULL,
	nazwisko           VARCHAR(100) NOT NULL,
	haslo              VARCHAR(255) NOT NULL DEFAULT '',
	stan               TINYINT NOT NULL DEFAULT 1,
	X_pracownika       INT NULL,
	Y_pracownika       INT NULL,
	nazwa_pliku_fotki  VARCHAR(255) NOT NULL DEFAULT '',
	UNIQUE KEY uq_idu (idu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

CREATE TABLE IF NOT EXISTS srodek (
	ids           INT AUTO_INCREMENT PRIMARY KEY,
	idp           INT NULL,
	idpod         INT NULL,
	idpom         INT NULL,
	idtyp         INT NOT NULL,
	idnazwa       INT NOT NULL,
	nazwa         VARCHAR(150) NOT NULL,
	identyfikator VARCHAR(40) NOT NULL,
	koszt         DECIMAL(12,2) NOT NULL DEFAULT 0,
	`datatime`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	X_srodka      INT NULL,
	Y_srodka      INT NULL,
	uwagi         TEXT NULL,
	status        ENUM('aktywny','w magazynie','zlikwidowany','zniszczony','skradziony') NOT NULL DEFAULT 'aktywny',
	stawka_amort  DECIMAL(5,2) NOT NULL DEFAULT 20.00,
	UNIQUE KEY uq_ident (identyfikator),
	KEY idx_pod (idpod),
	KEY idx_pom (idpom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

CREATE TABLE IF NOT EXISTS zdarzenie (
	idz        INT AUTO_INCREMENT PRIMARY KEY,
	ids        INT NOT NULL,
	idp        INT NULL,
	rodzaj     ENUM('odpis','przeniesienie','likwidacja','zniszczenie','kradziez','inne') NOT NULL,
	kwota      DECIMAL(12,2) NULL,
	opis       VARCHAR(500) NOT NULL DEFAULT '',
	`datatime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	KEY idx_ids (ids)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;


INSERT IGNORE INTO podklad (idpod, nazwa, nazwa_pliku, widok, idpod_plan, szer, wys, ma, mb, mc, md, me, mf, kolejnosc) VALUES
(1, 'Parter', 'parter_podklad.png', '2d', NULL, 2263, 2792, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(2, 'Parter quasi-3D', 'parter_quasi3d.png', '3d', 1, 2795, 2225, 0.5658573067491668, -0.5658573067491667, 1541.232383897632, 0.43348536716847613, 0.43348536716847613, 46.204267762691515, 2);
UPDATE podklad SET nazwa_pliku='parter_quasi3d.png', szer=2795, wys=2225, ma=0.5658573067491668, mb=-0.5658573067491667, mc=1541.232383897632, md=0.43348536716847613, me=0.43348536716847613, mf=46.204267762691515 WHERE idpod=2;
INSERT IGNORE INTO warstwa (idw, idpod, nazwa, nazwa_pliku, kolejnosc) VALUES (1, 1, 'Chłodzenie', 'warstwa_chlodzenie.png', 1), (2, 2, 'Chłodzenie', 'warstwa_chlodzenie_quasi3d.png', 1), (5, 1, 'SSP', 'warstwa_ssp.png', 5), (6, 2, 'SSP', 'warstwa_ssp_quasi3d.png', 5), (11, 1, 'Szafy rackowe', 'warstwa_szafy.png', 2), (12, 2, 'Szafy rackowe', 'warstwa_szafy_quasi3d.png', 2);
INSERT INTO warstwa (idw, idpod, nazwa, nazwa_pliku, kolejnosc) VALUES (17, 1, 'LAN / WAN', 'warstwa_lan_wan.png', 9), (18, 2, 'LAN / WAN', 'warstwa_lan_wan_quasi3d.png', 9), (27, 1, 'Umeblowanie', 'warstwa_umeblowanie.png', 0), (25, 1, 'Elektryka, zasilanie awaryjne i rozdzielnie', 'warstwa_elektryka_zasilanie.png', 3), (26, 2, 'Elektryka, zasilanie awaryjne i rozdzielnie', 'warstwa_elektryka_zasilanie_quasi3d.png', 3) ON DUPLICATE KEY UPDATE nazwa=VALUES(nazwa), nazwa_pliku=VALUES(nazwa_pliku), kolejnosc=VALUES(kolejnosc);
DELETE FROM warstwa WHERE idw IN (3, 4, 7, 8, 9, 10, 13, 14, 15, 16, 19, 20, 21, 22, 23, 24, 28, 30);
INSERT IGNORE INTO typ_srodka (idtyp, nazwa) VALUES (1, 'środek trwały'), (2, 'wyposażenie');
INSERT INTO nazwa_srodka (idnazwa, nazwa, nazwa_pliku, prefiks, rozmiar, idtyp) VALUES
(1,'Komputer','komputer.svg','k',38,2),
(2,'Telefon IP','ip_phone.svg','tel',34,2),
(3,'Drukarka','drukarka.svg','dr',36,2),
(4,'Switch LAN','switch_lan.svg','sw',60,1),
(5,'Szafa rakowa','szafa_rack.svg','sz',24,1),
(6,'Serwer','serwer.svg','srv',60,1),
(7,'Zasilacz UPS','ups.svg','ups',24,1),
(8,'Klimatyzator precyzyjny (CRAH)','klimatyzator.svg','kl',46,1),
(9,'Biurko','biurko.svg','b',70,2),
(10,'Fotel obrotowy','fotel.svg','f',24,2),
(11,'Transformator','trafo.svg','tr',64,1),
(12,'Kamera IP','kamera.svg','kam',40,1),
(13,'Access point','access_point.svg','ap',34,1),
(14,'Ekspres do kawy','ekspres.svg','ek',28,2),
(15,'Akumulator UPS','akumulator.svg','bat',24,1),
(16,'Agregat prądotwórczy','agregat.svg','ag',64,1),
(17,'Rozdzielnica SZR','szr.svg','szr',40,1),
(18,'Rozdzielnica RNR','rnr.svg','rnr',40,1),
(19,'Rozdzielnica RNG','rng.svg','rng',40,1),
(20,'Rozdzielnica główna RG','rg.svg','rg',40,1)
ON DUPLICATE KEY UPDATE nazwa=VALUES(nazwa), nazwa_pliku=VALUES(nazwa_pliku), prefiks=VALUES(prefiks), rozmiar=VALUES(rozmiar), idtyp=VALUES(idtyp);
INSERT INTO pomieszczenie (idpom, idpod, nazwa, x1, y1, x2, y2) VALUES
(1,1,'Agregat prądotwórczy 1',134,45,655,515),
(2,1,'Agregat prądotwórczy 2',655,45,1176,515),
(3,1,'Agregat prądotwórczy 3',1176,45,1696,515),
(4,1,'Agregat prądotwórczy 4',1696,45,2217,515),
(5,1,'Chłodzenie 1',44,515,214,2050),
(6,1,'Korytarz',214,515,1890,619),
(7,1,'Transformator 1',1890,515,2217,848),
(8,1,'Transformator 2',1890,848,2217,1182),
(9,1,'Akumulatornia UPS 1',927,619,1609,935),
(10,1,'Akumulatornia UPS 2',927,935,1609,1251),
(11,1,'Sala serwerowa',214,619,760,1682),
(12,1,'Chłodzenie 2',760,619,927,1682),
(13,1,'Rozdzielnia 1',1609,619,1784,935),
(14,1,'Rozdzielnia 2',1609,935,1784,1251),
(15,1,'Pomieszczenie techniczne',214,1682,760,1946),
(16,1,'Pomieszczenie pomocnicze',760,1682,927,1946),
(17,1,'Szatnia',134,2050,238,2218),
(18,1,'Pomieszczenie socjalne',238,2050,399,2218),
(19,1,'Toaleta 1',399,2050,558,2218),
(20,1,'Toaleta 2',558,2050,716,2218),
(21,1,'Sala konferencyjna',822,2050,1087,2378),
(22,1,'Korytarz',716,2050,822,2378),
(23,1,'Sekretariat',134,2322,398,2524),
(24,1,'Biuro prezesa',398,2322,550,2524),
(25,1,'Portiernia',550,2322,716,2524),
(26,1,'Poczekalnia',716,2378,1087,2724),
(27,1,'Korytarz',134,2218,716,2322),
(28,1,'Korytarz',1784,619,1890,1251),
(29,1,'Korytarz',927,1251,1890,1356),
(30,1,'Korytarz',214,1946,927,2050),
(31,1,'Korytarz',927,1356,1033,2050)
ON DUPLICATE KEY UPDATE nazwa=VALUES(nazwa), x1=VALUES(x1), y1=VALUES(y1), x2=VALUES(x2), y2=VALUES(y2);
DELETE FROM pomieszczenie WHERE idpod=1 AND idpom>31;
DELETE FROM zdarzenie WHERE ids IN (1,2,3,4,5,6,7,8,9,29,30,31,10,11,12,13,14,15,16,17,18,19,26,27,28,34,35) OR ids BETWEEN 101 AND 400;
DELETE FROM srodek WHERE ids IN (1,2,3,4,5,6,7,8,9,29,30,31,10,11,12,13,14,15,16,17,18,19,26,27,28,34,35) OR ids BETWEEN 101 AND 400;
INSERT IGNORE INTO srodek (ids, idp, idpod, idpom, idtyp, idnazwa, nazwa, identyfikator, koszt, `datatime`, X_srodka, Y_srodka, uwagi, status, stawka_amort) VALUES
(20,1,1,23,2,1,'Komputer 1','k1',3500.00,'2025-06-01 09:00:00',203,2408,'','aktywny',20.00),
(21,1,1,23,2,1,'Komputer 2','k2',3500.00,'2025-06-01 09:00:00',250,2408,'','aktywny',20.00),
(22,1,1,23,2,1,'Komputer 3','k3',3500.00,'2025-06-01 09:00:00',297,2408,'','aktywny',20.00),
(23,1,1,24,2,1,'Komputer 4','k4',3500.00,'2025-06-01 09:00:00',471,2413,'','aktywny',20.00),
(24,1,1,25,2,1,'Komputer 5','k5',3500.00,'2025-06-01 09:00:00',679,2361,'','aktywny',20.00),
(25,1,1,25,2,1,'Komputer 6','k6',3500.00,'2025-06-01 09:00:00',679,2408,'','aktywny',20.00),
(32,1,1,25,2,2,'Telefon IP 1','tel1',400.00,'2025-06-01 09:00:00',600,2338,'','aktywny',20.00),
(33,1,1,23,2,3,'Drukarka 1','dr1',1800.00,'2025-06-01 09:00:00',154,2326,'','aktywny',20.00),
(101,1,1,11,1,5,'Szafa rakowa 1','sz1',9800.00,'2024-03-12 09:00:00',334,1138,'','aktywny',20.00),
(102,1,1,11,1,5,'Szafa rakowa 2','sz2',9800.00,'2024-03-12 09:00:00',359,1138,'','aktywny',20.00),
(103,1,1,11,1,5,'Szafa rakowa 3','sz3',9800.00,'2024-03-12 09:00:00',385,1138,'','aktywny',20.00),
(104,1,1,11,1,5,'Szafa rakowa 4','sz4',9800.00,'2024-03-12 09:00:00',410,1138,'','aktywny',20.00),
(105,1,1,11,1,5,'Szafa rakowa 5','sz5',9800.00,'2024-03-12 09:00:00',436,1138,'','aktywny',20.00),
(106,1,1,11,1,5,'Szafa rakowa 6','sz6',9800.00,'2024-03-12 09:00:00',462,1138,'','aktywny',20.00),
(107,1,1,11,1,5,'Szafa rakowa 7','sz7',9800.00,'2024-03-12 09:00:00',487,1138,'','aktywny',20.00),
(108,1,1,11,1,5,'Szafa rakowa 8','sz8',9800.00,'2024-03-12 09:00:00',513,1138,'','aktywny',20.00),
(109,1,1,11,1,5,'Szafa rakowa 9','sz9',9800.00,'2024-03-12 09:00:00',539,1138,'','aktywny',20.00),
(110,1,1,11,1,5,'Szafa rakowa 10','sz10',9800.00,'2024-03-12 09:00:00',564,1138,'','aktywny',20.00),
(111,1,1,11,1,5,'Szafa rakowa 11','sz11',9800.00,'2024-03-12 09:00:00',590,1138,'','aktywny',20.00),
(112,1,1,11,1,5,'Szafa rakowa 12','sz12',9800.00,'2024-03-12 09:00:00',615,1138,'','aktywny',20.00),
(113,1,1,11,1,5,'Szafa rakowa 13','sz13',9800.00,'2024-03-12 09:00:00',334,1273,'','aktywny',20.00),
(114,1,1,11,1,5,'Szafa rakowa 14','sz14',9800.00,'2024-03-12 09:00:00',359,1273,'','aktywny',20.00),
(115,1,1,11,1,5,'Szafa rakowa 15','sz15',9800.00,'2024-03-12 09:00:00',385,1273,'','aktywny',20.00),
(116,1,1,11,1,5,'Szafa rakowa 16','sz16',9800.00,'2024-03-12 09:00:00',410,1273,'','aktywny',20.00),
(117,1,1,11,1,5,'Szafa rakowa 17','sz17',9800.00,'2024-03-12 09:00:00',436,1273,'','aktywny',20.00),
(118,1,1,11,1,5,'Szafa rakowa 18','sz18',9800.00,'2024-03-12 09:00:00',462,1273,'','aktywny',20.00),
(119,1,1,11,1,5,'Szafa rakowa 19','sz19',9800.00,'2024-03-12 09:00:00',487,1273,'','aktywny',20.00),
(120,1,1,11,1,5,'Szafa rakowa 20','sz20',9800.00,'2024-03-12 09:00:00',513,1273,'','aktywny',20.00),
(121,1,1,11,1,5,'Szafa rakowa 21','sz21',9800.00,'2024-03-12 09:00:00',539,1273,'','aktywny',20.00),
(122,1,1,11,1,5,'Szafa rakowa 22','sz22',9800.00,'2024-03-12 09:00:00',564,1273,'','aktywny',20.00),
(123,1,1,11,1,5,'Szafa rakowa 23','sz23',9800.00,'2024-03-12 09:00:00',590,1273,'','aktywny',20.00),
(124,1,1,11,1,5,'Szafa rakowa 24','sz24',9800.00,'2024-03-12 09:00:00',615,1273,'','aktywny',20.00),
(125,1,1,11,1,5,'Szafa rakowa 25','sz25',9800.00,'2024-03-12 09:00:00',334,1407,'','aktywny',20.00),
(126,1,1,11,1,5,'Szafa rakowa 26','sz26',9800.00,'2024-03-12 09:00:00',359,1407,'','aktywny',20.00),
(127,1,1,11,1,5,'Szafa rakowa 27','sz27',9800.00,'2024-03-12 09:00:00',385,1407,'','aktywny',20.00),
(128,1,1,11,1,5,'Szafa rakowa 28','sz28',9800.00,'2024-03-12 09:00:00',410,1407,'','aktywny',20.00),
(129,1,1,11,1,5,'Szafa rakowa 29','sz29',9800.00,'2024-03-12 09:00:00',436,1407,'','aktywny',20.00),
(130,1,1,11,1,5,'Szafa rakowa 30','sz30',9800.00,'2024-03-12 09:00:00',462,1407,'','aktywny',20.00),
(131,1,1,11,1,5,'Szafa rakowa 31','sz31',9800.00,'2024-03-12 09:00:00',487,1407,'','aktywny',20.00),
(132,1,1,11,1,5,'Szafa rakowa 32','sz32',9800.00,'2024-03-12 09:00:00',513,1407,'','aktywny',20.00),
(133,1,1,11,1,5,'Szafa rakowa 33','sz33',9800.00,'2024-03-12 09:00:00',539,1407,'','aktywny',20.00),
(134,1,1,11,1,5,'Szafa rakowa 34','sz34',9800.00,'2024-03-12 09:00:00',564,1407,'','aktywny',20.00),
(135,1,1,11,1,5,'Szafa rakowa 35','sz35',9800.00,'2024-03-12 09:00:00',590,1407,'','aktywny',20.00),
(136,1,1,11,1,5,'Szafa rakowa 36','sz36',9800.00,'2024-03-12 09:00:00',615,1407,'','aktywny',20.00),
(137,1,1,11,1,5,'Szafa rakowa 37','sz37',9800.00,'2024-03-12 09:00:00',334,1542,'','aktywny',20.00),
(138,1,1,11,1,5,'Szafa rakowa 38','sz38',9800.00,'2024-03-12 09:00:00',359,1542,'','aktywny',20.00),
(139,1,1,11,1,5,'Szafa rakowa 39','sz39',9800.00,'2024-03-12 09:00:00',385,1542,'','aktywny',20.00),
(140,1,1,11,1,5,'Szafa rakowa 40','sz40',9800.00,'2024-03-12 09:00:00',410,1542,'','aktywny',20.00),
(141,1,1,11,1,5,'Szafa rakowa 41','sz41',9800.00,'2024-03-12 09:00:00',436,1542,'','aktywny',20.00),
(142,1,1,11,1,5,'Szafa rakowa 42','sz42',9800.00,'2024-03-12 09:00:00',462,1542,'','aktywny',20.00),
(143,1,1,11,1,5,'Szafa rakowa 43','sz43',9800.00,'2024-03-12 09:00:00',487,1542,'','aktywny',20.00),
(144,1,1,11,1,5,'Szafa rakowa 44','sz44',9800.00,'2024-03-12 09:00:00',513,1542,'','aktywny',20.00),
(145,1,1,11,1,5,'Szafa rakowa 45','sz45',9800.00,'2024-03-12 09:00:00',539,1542,'','aktywny',20.00),
(146,1,1,11,1,5,'Szafa rakowa 46','sz46',9800.00,'2024-03-12 09:00:00',564,1542,'','aktywny',20.00),
(147,1,1,11,1,5,'Szafa rakowa 47','sz47',9800.00,'2024-03-12 09:00:00',590,1542,'','aktywny',20.00),
(148,1,1,11,1,5,'Szafa rakowa 48','sz48',9800.00,'2024-03-12 09:00:00',615,1542,'','aktywny',20.00),
(149,1,1,15,1,5,'Szafa rakowa 49','sz49',9800.00,'2024-03-12 09:00:00',715,1720,'','aktywny',20.00),
(150,1,1,15,1,5,'Szafa rakowa 50','sz50',9800.00,'2024-03-12 09:00:00',715,1751,'','aktywny',20.00),
(151,1,1,9,1,7,'Zasilacz UPS 1','ups1',12700.00,'2024-02-05 09:00:00',1480,700,'','aktywny',20.00),
(152,1,1,9,1,7,'Zasilacz UPS 2','ups2',12700.00,'2024-02-05 09:00:00',1480,743,'','aktywny',20.00),
(153,1,1,9,1,7,'Zasilacz UPS 3','ups3',12700.00,'2024-02-05 09:00:00',1480,786,'','aktywny',20.00),
(154,1,1,9,1,7,'Zasilacz UPS 4','ups4',12700.00,'2024-02-05 09:00:00',1480,830,'','aktywny',20.00),
(155,1,1,10,1,7,'Zasilacz UPS 5','ups5',12700.00,'2024-02-05 09:00:00',1480,1018,'','aktywny',20.00),
(156,1,1,10,1,7,'Zasilacz UPS 6','ups6',12700.00,'2024-02-05 09:00:00',1480,1061,'','aktywny',20.00),
(157,1,1,10,1,7,'Zasilacz UPS 7','ups7',12700.00,'2024-02-05 09:00:00',1480,1105,'','aktywny',20.00),
(158,1,1,10,1,7,'Zasilacz UPS 8','ups8',12700.00,'2024-02-05 09:00:00',1480,1148,'','aktywny',20.00),
(159,1,1,9,1,15,'Akumulator UPS 1','bat1',4800.00,'2024-02-05 09:00:00',1028,687,'Akumulator 12 V','aktywny',20.00),
(160,1,1,9,1,15,'Akumulator UPS 2','bat2',4800.00,'2024-02-05 09:00:00',1028,710,'Akumulator 12 V','aktywny',20.00),
(161,1,1,9,1,15,'Akumulator UPS 3','bat3',4800.00,'2024-02-05 09:00:00',1028,732,'Akumulator 12 V','aktywny',20.00),
(162,1,1,9,1,15,'Akumulator UPS 4','bat4',4800.00,'2024-02-05 09:00:00',1028,754,'Akumulator 12 V','aktywny',20.00),
(163,1,1,9,1,15,'Akumulator UPS 5','bat5',4800.00,'2024-02-05 09:00:00',1028,776,'Akumulator 12 V','aktywny',20.00),
(164,1,1,9,1,15,'Akumulator UPS 6','bat6',4800.00,'2024-02-05 09:00:00',1028,798,'Akumulator 12 V','aktywny',20.00),
(165,1,1,9,1,15,'Akumulator UPS 7','bat7',4800.00,'2024-02-05 09:00:00',1028,820,'Akumulator 12 V','aktywny',20.00),
(166,1,1,9,1,15,'Akumulator UPS 8','bat8',4800.00,'2024-02-05 09:00:00',1028,842,'Akumulator 12 V','aktywny',20.00),
(167,1,1,10,1,15,'Akumulator UPS 9','bat9',4800.00,'2024-02-05 09:00:00',1028,1004,'Akumulator 12 V','aktywny',20.00),
(168,1,1,10,1,15,'Akumulator UPS 10','bat10',4800.00,'2024-02-05 09:00:00',1028,1026,'Akumulator 12 V','aktywny',20.00),
(169,1,1,10,1,15,'Akumulator UPS 11','bat11',4800.00,'2024-02-05 09:00:00',1028,1048,'Akumulator 12 V','aktywny',20.00),
(170,1,1,10,1,15,'Akumulator UPS 12','bat12',4800.00,'2024-02-05 09:00:00',1028,1070,'Akumulator 12 V','aktywny',20.00),
(171,1,1,10,1,15,'Akumulator UPS 13','bat13',4800.00,'2024-02-05 09:00:00',1028,1092,'Akumulator 12 V','aktywny',20.00),
(172,1,1,10,1,15,'Akumulator UPS 14','bat14',4800.00,'2024-02-05 09:00:00',1028,1114,'Akumulator 12 V','aktywny',20.00),
(173,1,1,10,1,15,'Akumulator UPS 15','bat15',4800.00,'2024-02-05 09:00:00',1028,1136,'Akumulator 12 V','aktywny',20.00),
(174,1,1,10,1,15,'Akumulator UPS 16','bat16',4800.00,'2024-02-05 09:00:00',1028,1158,'Akumulator 12 V','aktywny',20.00),
(175,1,1,9,1,15,'Akumulator UPS 17','bat17',4800.00,'2024-02-05 09:00:00',1118,687,'Akumulator 12 V','aktywny',20.00),
(176,1,1,9,1,15,'Akumulator UPS 18','bat18',4800.00,'2024-02-05 09:00:00',1118,710,'Akumulator 12 V','aktywny',20.00),
(177,1,1,9,1,15,'Akumulator UPS 19','bat19',4800.00,'2024-02-05 09:00:00',1118,732,'Akumulator 12 V','aktywny',20.00),
(178,1,1,9,1,15,'Akumulator UPS 20','bat20',4800.00,'2024-02-05 09:00:00',1118,754,'Akumulator 12 V','aktywny',20.00),
(179,1,1,9,1,15,'Akumulator UPS 21','bat21',4800.00,'2024-02-05 09:00:00',1118,776,'Akumulator 12 V','aktywny',20.00),
(180,1,1,9,1,15,'Akumulator UPS 22','bat22',4800.00,'2024-02-05 09:00:00',1118,798,'Akumulator 12 V','aktywny',20.00),
(181,1,1,9,1,15,'Akumulator UPS 23','bat23',4800.00,'2024-02-05 09:00:00',1118,820,'Akumulator 12 V','aktywny',20.00),
(182,1,1,9,1,15,'Akumulator UPS 24','bat24',4800.00,'2024-02-05 09:00:00',1118,842,'Akumulator 12 V','aktywny',20.00),
(183,1,1,10,1,15,'Akumulator UPS 25','bat25',4800.00,'2024-02-05 09:00:00',1118,1003,'Akumulator 12 V','aktywny',20.00),
(184,1,1,10,1,15,'Akumulator UPS 26','bat26',4800.00,'2024-02-05 09:00:00',1118,1025,'Akumulator 12 V','aktywny',20.00),
(185,1,1,10,1,15,'Akumulator UPS 27','bat27',4800.00,'2024-02-05 09:00:00',1118,1048,'Akumulator 12 V','aktywny',20.00),
(186,1,1,10,1,15,'Akumulator UPS 28','bat28',4800.00,'2024-02-05 09:00:00',1118,1070,'Akumulator 12 V','aktywny',20.00),
(187,1,1,10,1,15,'Akumulator UPS 29','bat29',4800.00,'2024-02-05 09:00:00',1118,1092,'Akumulator 12 V','aktywny',20.00),
(188,1,1,10,1,15,'Akumulator UPS 30','bat30',4800.00,'2024-02-05 09:00:00',1118,1114,'Akumulator 12 V','aktywny',20.00),
(189,1,1,10,1,15,'Akumulator UPS 31','bat31',4800.00,'2024-02-05 09:00:00',1118,1136,'Akumulator 12 V','aktywny',20.00),
(190,1,1,10,1,15,'Akumulator UPS 32','bat32',4800.00,'2024-02-05 09:00:00',1118,1158,'Akumulator 12 V','aktywny',20.00),
(191,1,1,9,1,15,'Akumulator UPS 33','bat33',4800.00,'2024-02-05 09:00:00',1207,687,'Akumulator 12 V','aktywny',20.00),
(192,1,1,9,1,15,'Akumulator UPS 34','bat34',4800.00,'2024-02-05 09:00:00',1207,710,'Akumulator 12 V','aktywny',20.00),
(193,1,1,9,1,15,'Akumulator UPS 35','bat35',4800.00,'2024-02-05 09:00:00',1207,732,'Akumulator 12 V','aktywny',20.00),
(194,1,1,9,1,15,'Akumulator UPS 36','bat36',4800.00,'2024-02-05 09:00:00',1207,754,'Akumulator 12 V','aktywny',20.00),
(195,1,1,9,1,15,'Akumulator UPS 37','bat37',4800.00,'2024-02-05 09:00:00',1207,776,'Akumulator 12 V','aktywny',20.00),
(196,1,1,9,1,15,'Akumulator UPS 38','bat38',4800.00,'2024-02-05 09:00:00',1207,798,'Akumulator 12 V','aktywny',20.00),
(197,1,1,9,1,15,'Akumulator UPS 39','bat39',4800.00,'2024-02-05 09:00:00',1207,820,'Akumulator 12 V','aktywny',20.00),
(198,1,1,9,1,15,'Akumulator UPS 40','bat40',4800.00,'2024-02-05 09:00:00',1207,842,'Akumulator 12 V','aktywny',20.00),
(199,1,1,10,1,15,'Akumulator UPS 41','bat41',4800.00,'2024-02-05 09:00:00',1207,1003,'Akumulator 12 V','aktywny',20.00),
(200,1,1,10,1,15,'Akumulator UPS 42','bat42',4800.00,'2024-02-05 09:00:00',1207,1025,'Akumulator 12 V','aktywny',20.00),
(201,1,1,10,1,15,'Akumulator UPS 43','bat43',4800.00,'2024-02-05 09:00:00',1207,1047,'Akumulator 12 V','aktywny',20.00),
(202,1,1,10,1,15,'Akumulator UPS 44','bat44',4800.00,'2024-02-05 09:00:00',1207,1069,'Akumulator 12 V','aktywny',20.00),
(203,1,1,10,1,15,'Akumulator UPS 45','bat45',4800.00,'2024-02-05 09:00:00',1207,1091,'Akumulator 12 V','aktywny',20.00),
(204,1,1,10,1,15,'Akumulator UPS 46','bat46',4800.00,'2024-02-05 09:00:00',1207,1113,'Akumulator 12 V','aktywny',20.00),
(205,1,1,10,1,15,'Akumulator UPS 47','bat47',4800.00,'2024-02-05 09:00:00',1207,1135,'Akumulator 12 V','aktywny',20.00),
(206,1,1,10,1,15,'Akumulator UPS 48','bat48',4800.00,'2024-02-05 09:00:00',1207,1157,'Akumulator 12 V','aktywny',20.00),
(207,1,1,9,1,15,'Akumulator UPS 49','bat49',4800.00,'2024-02-05 09:00:00',1296,687,'Akumulator 12 V','aktywny',20.00),
(208,1,1,9,1,15,'Akumulator UPS 50','bat50',4800.00,'2024-02-05 09:00:00',1296,710,'Akumulator 12 V','aktywny',20.00),
(209,1,1,9,1,15,'Akumulator UPS 51','bat51',4800.00,'2024-02-05 09:00:00',1296,732,'Akumulator 12 V','aktywny',20.00),
(210,1,1,9,1,15,'Akumulator UPS 52','bat52',4800.00,'2024-02-05 09:00:00',1296,754,'Akumulator 12 V','aktywny',20.00),
(211,1,1,9,1,15,'Akumulator UPS 53','bat53',4800.00,'2024-02-05 09:00:00',1296,776,'Akumulator 12 V','aktywny',20.00),
(212,1,1,9,1,15,'Akumulator UPS 54','bat54',4800.00,'2024-02-05 09:00:00',1296,798,'Akumulator 12 V','aktywny',20.00),
(213,1,1,9,1,15,'Akumulator UPS 55','bat55',4800.00,'2024-02-05 09:00:00',1296,820,'Akumulator 12 V','aktywny',20.00),
(214,1,1,9,1,15,'Akumulator UPS 56','bat56',4800.00,'2024-02-05 09:00:00',1296,842,'Akumulator 12 V','aktywny',20.00),
(215,1,1,10,1,15,'Akumulator UPS 57','bat57',4800.00,'2024-02-05 09:00:00',1296,1002,'Akumulator 12 V','aktywny',20.00),
(216,1,1,10,1,15,'Akumulator UPS 58','bat58',4800.00,'2024-02-05 09:00:00',1296,1024,'Akumulator 12 V','aktywny',20.00),
(217,1,1,10,1,15,'Akumulator UPS 59','bat59',4800.00,'2024-02-05 09:00:00',1296,1046,'Akumulator 12 V','aktywny',20.00),
(218,1,1,10,1,15,'Akumulator UPS 60','bat60',4800.00,'2024-02-05 09:00:00',1296,1068,'Akumulator 12 V','aktywny',20.00),
(219,1,1,10,1,15,'Akumulator UPS 61','bat61',4800.00,'2024-02-05 09:00:00',1296,1090,'Akumulator 12 V','aktywny',20.00),
(220,1,1,10,1,15,'Akumulator UPS 62','bat62',4800.00,'2024-02-05 09:00:00',1296,1113,'Akumulator 12 V','aktywny',20.00),
(221,1,1,10,1,15,'Akumulator UPS 63','bat63',4800.00,'2024-02-05 09:00:00',1296,1135,'Akumulator 12 V','aktywny',20.00),
(222,1,1,10,1,15,'Akumulator UPS 64','bat64',4800.00,'2024-02-05 09:00:00',1296,1157,'Akumulator 12 V','aktywny',20.00),
(223,1,1,9,1,15,'Akumulator UPS 65','bat65',4800.00,'2024-02-05 09:00:00',1386,687,'Akumulator 12 V','aktywny',20.00),
(224,1,1,9,1,15,'Akumulator UPS 66','bat66',4800.00,'2024-02-05 09:00:00',1386,710,'Akumulator 12 V','aktywny',20.00),
(225,1,1,9,1,15,'Akumulator UPS 67','bat67',4800.00,'2024-02-05 09:00:00',1386,732,'Akumulator 12 V','aktywny',20.00),
(226,1,1,9,1,15,'Akumulator UPS 68','bat68',4800.00,'2024-02-05 09:00:00',1386,754,'Akumulator 12 V','aktywny',20.00),
(227,1,1,9,1,15,'Akumulator UPS 69','bat69',4800.00,'2024-02-05 09:00:00',1386,776,'Akumulator 12 V','aktywny',20.00),
(228,1,1,9,1,15,'Akumulator UPS 70','bat70',4800.00,'2024-02-05 09:00:00',1386,798,'Akumulator 12 V','aktywny',20.00),
(229,1,1,9,1,15,'Akumulator UPS 71','bat71',4800.00,'2024-02-05 09:00:00',1386,820,'Akumulator 12 V','aktywny',20.00),
(230,1,1,9,1,15,'Akumulator UPS 72','bat72',4800.00,'2024-02-05 09:00:00',1386,842,'Akumulator 12 V','aktywny',20.00),
(231,1,1,10,1,15,'Akumulator UPS 73','bat73',4800.00,'2024-02-05 09:00:00',1386,1002,'Akumulator 12 V','aktywny',20.00),
(232,1,1,10,1,15,'Akumulator UPS 74','bat74',4800.00,'2024-02-05 09:00:00',1386,1024,'Akumulator 12 V','aktywny',20.00),
(233,1,1,10,1,15,'Akumulator UPS 75','bat75',4800.00,'2024-02-05 09:00:00',1386,1046,'Akumulator 12 V','aktywny',20.00),
(234,1,1,10,1,15,'Akumulator UPS 76','bat76',4800.00,'2024-02-05 09:00:00',1386,1068,'Akumulator 12 V','aktywny',20.00),
(235,1,1,10,1,15,'Akumulator UPS 77','bat77',4800.00,'2024-02-05 09:00:00',1386,1090,'Akumulator 12 V','aktywny',20.00),
(236,1,1,10,1,15,'Akumulator UPS 78','bat78',4800.00,'2024-02-05 09:00:00',1386,1113,'Akumulator 12 V','aktywny',20.00),
(237,1,1,10,1,15,'Akumulator UPS 79','bat79',4800.00,'2024-02-05 09:00:00',1386,1135,'Akumulator 12 V','aktywny',20.00),
(238,1,1,10,1,15,'Akumulator UPS 80','bat80',4800.00,'2024-02-05 09:00:00',1386,1157,'Akumulator 12 V','aktywny',20.00),
(239,1,1,24,2,14,'Ekspres do kawy 1','ek1',1500.00,'2025-06-01 09:00:00',408,2391,'','aktywny',20.00),
(240,1,1,23,2,14,'Ekspres do kawy 2','ek2',1500.00,'2025-06-01 09:00:00',370,2393,'','aktywny',20.00),
(241,1,1,7,1,11,'Transformator 1','tr1',98000.00,'2024-02-20 09:00:00',2020,649,'','aktywny',20.00),
(242,1,1,8,1,11,'Transformator 2','tr2',98000.00,'2024-02-20 09:00:00',2020,982,'','aktywny',20.00),
(243,1,1,5,1,8,'CRAH 1','kl1',26000.00,'2024-04-02 09:00:00',107,652,'','aktywny',20.00),
(244,1,1,5,1,8,'CRAH 2','kl2',26000.00,'2024-04-02 09:00:00',107,771,'','aktywny',20.00),
(245,1,1,5,1,8,'CRAH 3','kl3',26000.00,'2024-04-02 09:00:00',107,889,'','aktywny',20.00),
(246,1,1,5,1,8,'CRAH 4','kl4',26000.00,'2024-04-02 09:00:00',107,1008,'','aktywny',20.00),
(247,1,1,5,1,8,'CRAH 5','kl5',26000.00,'2024-04-02 09:00:00',107,1126,'','aktywny',20.00),
(248,1,1,5,1,8,'CRAH 6','kl6',26000.00,'2024-04-02 09:00:00',107,1245,'','aktywny',20.00),
(249,1,1,5,1,8,'CRAH 7','kl7',26000.00,'2024-04-02 09:00:00',107,1363,'','aktywny',20.00),
(250,1,1,5,1,8,'CRAH 8','kl8',26000.00,'2024-04-02 09:00:00',107,1482,'','aktywny',20.00),
(251,1,1,5,1,8,'CRAH 9','kl9',26000.00,'2024-04-02 09:00:00',107,1600,'','aktywny',20.00),
(252,1,1,12,1,8,'CRAH 10','kl10',26000.00,'2024-04-02 09:00:00',820,652,'','aktywny',20.00),
(253,1,1,12,1,8,'CRAH 11','kl11',26000.00,'2024-04-02 09:00:00',820,771,'','aktywny',20.00),
(254,1,1,12,1,8,'CRAH 12','kl12',26000.00,'2024-04-02 09:00:00',820,889,'','aktywny',20.00),
(255,1,1,12,1,8,'CRAH 13','kl13',26000.00,'2024-04-02 09:00:00',820,1008,'','aktywny',20.00),
(256,1,1,12,1,8,'CRAH 14','kl14',26000.00,'2024-04-02 09:00:00',820,1126,'','aktywny',20.00),
(257,1,1,12,1,8,'CRAH 15','kl15',26000.00,'2024-04-02 09:00:00',820,1245,'','aktywny',20.00),
(258,1,1,12,1,8,'CRAH 16','kl16',26000.00,'2024-04-02 09:00:00',820,1363,'','aktywny',20.00),
(259,1,1,12,1,8,'CRAH 17','kl17',26000.00,'2024-04-02 09:00:00',820,1482,'','aktywny',20.00),
(260,1,1,12,1,8,'CRAH 18','kl18',26000.00,'2024-04-02 09:00:00',820,1600,'','aktywny',20.00),
(261,1,1,11,1,12,'Kamera IP 1','kam1',850.00,'2025-03-10 09:00:00',230,1496,'','aktywny',20.00),
(262,1,1,11,1,12,'Kamera IP 2','kam2',850.00,'2025-03-10 09:00:00',230,1394,'','aktywny',20.00),
(263,1,1,11,1,12,'Kamera IP 3','kam3',850.00,'2025-03-10 09:00:00',230,1276,'','aktywny',20.00),
(264,1,1,11,1,12,'Kamera IP 4','kam4',850.00,'2025-03-10 09:00:00',230,1138,'','aktywny',20.00),
(265,1,1,11,1,12,'Kamera IP 5','kam5',850.00,'2025-03-10 09:00:00',230,1000,'','aktywny',20.00),
(266,1,1,11,1,12,'Kamera IP 6','kam6',850.00,'2025-03-10 09:00:00',230,862,'','aktywny',20.00),
(267,1,1,11,1,12,'Kamera IP 7','kam7',850.00,'2025-03-10 09:00:00',230,726,'','aktywny',20.00),
(268,1,1,11,1,12,'Kamera IP 8','kam8',850.00,'2025-03-10 09:00:00',230,636,'','aktywny',20.00),
(269,1,1,15,1,12,'Kamera IP 9','kam9',850.00,'2025-03-10 09:00:00',224,1886,'','aktywny',20.00),
(270,1,1,15,1,12,'Kamera IP 10','kam10',850.00,'2025-03-10 09:00:00',496,1886,'','aktywny',20.00),
(271,1,1,5,1,12,'Kamera IP 11','kam11',850.00,'2025-03-10 09:00:00',154,1270,'','aktywny',20.00),
(272,1,1,5,1,12,'Kamera IP 12','kam12',850.00,'2025-03-10 09:00:00',154,1078,'','aktywny',20.00),
(273,1,1,5,1,12,'Kamera IP 13','kam13',850.00,'2025-03-10 09:00:00',154,758,'','aktywny',20.00),
(274,1,1,NULL,1,12,'Kamera IP 14','kam14',850.00,'2025-03-10 09:00:00',-6,1542,'','aktywny',20.00),
(275,1,1,NULL,1,12,'Kamera IP 15','kam15',850.00,'2025-03-10 09:00:00',-6,946,'','aktywny',20.00),
(276,1,1,NULL,1,12,'Kamera IP 16','kam16',850.00,'2025-03-10 09:00:00',-6,544,'','aktywny',20.00),
(277,1,1,NULL,1,12,'Kamera IP 17','kam17',850.00,'2025-03-10 09:00:00',-6,2002,'','aktywny',20.00),
(278,1,1,1,1,12,'Kamera IP 18','kam18',850.00,'2025-03-10 09:00:00',590,455,'','aktywny',20.00),
(279,1,1,2,1,12,'Kamera IP 19','kam19',850.00,'2025-03-10 09:00:00',1100,455,'','aktywny',20.00),
(280,1,1,3,1,12,'Kamera IP 20','kam20',850.00,'2025-03-10 09:00:00',1628,455,'','aktywny',20.00),
(281,1,1,4,1,12,'Kamera IP 21','kam21',850.00,'2025-03-10 09:00:00',2158,455,'','aktywny',20.00),
(282,1,1,6,1,12,'Kamera IP 22','kam22',850.00,'2025-03-10 09:00:00',742,531,'','aktywny',20.00),
(283,1,1,6,1,12,'Kamera IP 23','kam23',850.00,'2025-03-10 09:00:00',1198,531,'','aktywny',20.00),
(284,1,1,6,1,12,'Kamera IP 24','kam24',850.00,'2025-03-10 09:00:00',424,531,'','aktywny',20.00),
(285,1,1,NULL,1,12,'Kamera IP 25','kam25',850.00,'2025-03-10 09:00:00',138,-5,'','aktywny',20.00),
(286,1,1,NULL,1,12,'Kamera IP 26','kam26',850.00,'2025-03-10 09:00:00',676,-5,'','aktywny',20.00),
(287,1,1,NULL,1,12,'Kamera IP 27','kam27',850.00,'2025-03-10 09:00:00',1190,-5,'','aktywny',20.00),
(288,1,1,NULL,1,12,'Kamera IP 28','kam28',850.00,'2025-03-10 09:00:00',1696,-5,'','aktywny',20.00),
(289,1,1,NULL,1,12,'Kamera IP 29','kam29',850.00,'2025-03-10 09:00:00',2168,-5,'','aktywny',20.00),
(290,1,1,NULL,1,12,'Kamera IP 30','kam30',850.00,'2025-03-10 09:00:00',72,56,'','aktywny',20.00),
(291,1,1,NULL,1,12,'Kamera IP 31','kam31',850.00,'2025-03-10 09:00:00',2222,54,'','aktywny',20.00),
(292,1,1,NULL,1,12,'Kamera IP 32','kam32',850.00,'2025-03-10 09:00:00',2222,560,'','aktywny',20.00),
(293,1,1,NULL,1,12,'Kamera IP 33','kam33',850.00,'2025-03-10 09:00:00',2222,1160,'','aktywny',20.00),
(294,1,1,NULL,1,12,'Kamera IP 34','kam34',850.00,'2025-03-10 09:00:00',2162,1200,'','aktywny',20.00),
(295,1,1,7,1,12,'Kamera IP 35','kam35',850.00,'2025-03-10 09:00:00',1904,710,'','aktywny',20.00),
(296,1,1,8,1,12,'Kamera IP 36','kam36',850.00,'2025-03-10 09:00:00',1903,1002,'','aktywny',20.00),
(297,1,1,6,1,12,'Kamera IP 37','kam37',850.00,'2025-03-10 09:00:00',1834,531,'','aktywny',20.00),
(298,1,1,29,1,12,'Kamera IP 38','kam38',850.00,'2025-03-10 09:00:00',1827,1308,'','aktywny',20.00),
(299,1,1,NULL,1,12,'Kamera IP 39','kam39',850.00,'2025-03-10 09:00:00',1844,1373,'','aktywny',20.00),
(300,1,1,10,1,12,'Kamera IP 40','kam40',850.00,'2025-03-10 09:00:00',942,950,'','aktywny',20.00),
(301,1,1,9,1,12,'Kamera IP 41','kam41',850.00,'2025-03-10 09:00:00',1138,874,'','aktywny',20.00),
(302,1,1,9,1,12,'Kamera IP 42','kam42',850.00,'2025-03-10 09:00:00',1462,874,'','aktywny',20.00),
(303,1,1,13,1,12,'Kamera IP 43','kam43',850.00,'2025-03-10 09:00:00',1734,874,'','aktywny',20.00),
(304,1,1,10,1,12,'Kamera IP 44','kam44',850.00,'2025-03-10 09:00:00',1138,1190,'','aktywny',20.00),
(305,1,1,10,1,12,'Kamera IP 45','kam45',850.00,'2025-03-10 09:00:00',1466,1190,'','aktywny',20.00),
(306,1,1,14,1,12,'Kamera IP 46','kam46',850.00,'2025-03-10 09:00:00',1732,1190,'','aktywny',20.00),
(307,1,1,22,1,12,'Kamera IP 47','kam47',850.00,'2025-03-10 09:00:00',732,2090,'','aktywny',20.00),
(308,1,1,26,1,12,'Kamera IP 48','kam48',850.00,'2025-03-10 09:00:00',718,2662,'','aktywny',20.00),
(309,1,1,26,1,12,'Kamera IP 49','kam49',850.00,'2025-03-10 09:00:00',1034,2662,'','aktywny',20.00),
(310,1,1,NULL,1,12,'Kamera IP 50','kam50',850.00,'2025-03-10 09:00:00',1034,2741,'','aktywny',20.00),
(311,1,1,NULL,1,12,'Kamera IP 51','kam51',850.00,'2025-03-10 09:00:00',655,2546,'','aktywny',20.00),
(312,1,1,NULL,1,12,'Kamera IP 52','kam52',850.00,'2025-03-10 09:00:00',72,2498,'','aktywny',20.00),
(313,1,1,27,1,12,'Kamera IP 53','kam53',850.00,'2025-03-10 09:00:00',151,2224,'','aktywny',20.00),
(314,1,1,27,1,12,'Kamera IP 54','kam54',850.00,'2025-03-10 09:00:00',678,2233,'','aktywny',20.00),
(315,1,1,31,1,12,'Kamera IP 55','kam55',850.00,'2025-03-10 09:00:00',970,1996,'','aktywny',20.00),
(316,1,1,NULL,1,12,'Kamera IP 56','kam56',850.00,'2025-03-10 09:00:00',1064,1988,'','aktywny',20.00),
(317,1,1,NULL,1,12,'Kamera IP 57','kam57',850.00,'2025-03-10 09:00:00',1064,1373,'','aktywny',20.00),
(318,1,1,30,1,13,'Access point 1','ap1',1200.00,'2025-03-10 09:00:00',642,1964,'','aktywny',20.00),
(319,1,1,30,1,13,'Access point 2','ap2',1200.00,'2025-03-10 09:00:00',260,1992,'','aktywny',20.00),
(320,1,1,31,1,13,'Access point 3','ap3',1200.00,'2025-03-10 09:00:00',942,1826,'','aktywny',20.00),
(321,1,1,31,1,13,'Access point 4','ap4',1200.00,'2025-03-10 09:00:00',942,1466,'','aktywny',20.00),
(322,1,1,27,1,13,'Access point 5','ap5',1200.00,'2025-03-10 09:00:00',608,2274,'','aktywny',20.00),
(323,1,1,26,1,13,'Access point 6','ap6',1200.00,'2025-03-10 09:00:00',750,2496,'','aktywny',20.00),
(324,1,1,29,1,13,'Access point 7','ap7',1200.00,'2025-03-10 09:00:00',1182,1308,'','aktywny',20.00),
(325,1,1,27,1,13,'Access point 8','ap8',1200.00,'2025-03-10 09:00:00',234,2274,'','aktywny',20.00),
(326,1,1,29,1,13,'Access point 9','ap9',1200.00,'2025-03-10 09:00:00',1628,1308,'','aktywny',20.00),
(327,1,1,10,1,13,'Access point 10','ap10',1200.00,'2025-03-10 09:00:00',1094,944,'','aktywny',20.00),
(328,1,1,28,1,13,'Access point 11','ap11',1200.00,'2025-03-10 09:00:00',1814,1182,'','aktywny',20.00),
(329,1,1,14,1,13,'Access point 12','ap12',1200.00,'2025-03-10 09:00:00',1704,944,'','aktywny',20.00),
(330,1,1,6,1,13,'Access point 13','ap13',1200.00,'2025-03-10 09:00:00',1116,552,'','aktywny',20.00),
(331,1,1,6,1,13,'Access point 14','ap14',1200.00,'2025-03-10 09:00:00',1652,552,'','aktywny',20.00),
(332,1,1,15,2,2,'Telefon IP 2','tel2',400.00,'2025-06-01 09:00:00',711,1812,'','aktywny',20.00),
(333,1,1,23,2,2,'Telefon IP 3','tel3',400.00,'2025-06-01 09:00:00',200,2474,'','aktywny',20.00),
(334,1,1,23,2,2,'Telefon IP 4','tel4',400.00,'2025-06-01 09:00:00',248,2474,'','aktywny',20.00),
(335,1,1,23,2,2,'Telefon IP 5','tel5',400.00,'2025-06-01 09:00:00',296,2474,'','aktywny',20.00),
(336,1,1,24,2,2,'Telefon IP 6','tel6',400.00,'2025-06-01 09:00:00',464,2474,'','aktywny',20.00),
(337,1,1,25,2,2,'Telefon IP 7','tel7',400.00,'2025-06-01 09:00:00',616,2474,'','aktywny',20.00),
(338,1,1,25,2,2,'Telefon IP 8','tel8',400.00,'2025-06-01 09:00:00',668,2464,'','aktywny',20.00),
(339,1,1,1,1,16,'Agregat prądotwórczy 1','ag1',185000.00,'2024-01-15 09:00:00',363,248,'','aktywny',20.00),
(340,1,1,2,1,16,'Agregat prądotwórczy 2','ag2',185000.00,'2024-01-15 09:00:00',883,248,'','aktywny',20.00),
(341,1,1,3,1,16,'Agregat prądotwórczy 3','ag3',185000.00,'2024-01-15 09:00:00',1404,248,'','aktywny',20.00),
(342,1,1,4,1,16,'Agregat prądotwórczy 4','ag4',185000.00,'2024-01-15 09:00:00',1924,248,'','aktywny',20.00),
(343,1,1,13,1,17,'Rozdzielnica SZR 1','szr1',42000.00,'2024-02-20 09:00:00',1612,651,'','aktywny',20.00),
(344,1,1,14,1,17,'Rozdzielnica SZR 2','szr2',42000.00,'2024-02-20 09:00:00',1612,967,'','aktywny',20.00),
(345,1,1,13,1,18,'Rozdzielnica RNR 1','rnr1',36000.00,'2024-02-20 09:00:00',1612,739,'','aktywny',20.00),
(346,1,1,13,1,18,'Rozdzielnica RNR 2','rnr2',36000.00,'2024-02-20 09:00:00',1612,803,'','aktywny',20.00),
(347,1,1,14,1,18,'Rozdzielnica RNR 3','rnr3',36000.00,'2024-02-20 09:00:00',1612,1055,'','aktywny',20.00),
(348,1,1,14,1,18,'Rozdzielnica RNR 4','rnr4',36000.00,'2024-02-20 09:00:00',1612,1119,'','aktywny',20.00),
(349,1,1,9,1,19,'Rozdzielnica RNG 1','rng1',38000.00,'2024-02-20 09:00:00',1566,627,'RNG Rack A','aktywny',20.00),
(350,1,1,10,1,19,'Rozdzielnica RNG 2','rng2',38000.00,'2024-02-20 09:00:00',1566,943,'RNG Rack B','aktywny',20.00),
(351,1,1,15,1,19,'Rozdzielnica RNG 3','rng3',38000.00,'2024-02-20 09:00:00',716,1882,'','aktywny',20.00),
(352,1,1,7,1,20,'Rozdzielnica główna RG 1','rg1',120000.00,'2024-02-20 09:00:00',2130,813,'','aktywny',20.00),
(353,1,1,8,1,20,'Rozdzielnica główna RG 2','rg2',120000.00,'2024-02-20 09:00:00',2130,1146,'','aktywny',20.00),
(354,1,1,21,2,10,'Fotel obrotowy 1','f1',650.00,'2024-05-10 09:00:00',901,2152,'','aktywny',20.00),
(355,1,1,21,2,10,'Fotel obrotowy 2','f2',650.00,'2024-05-10 09:00:00',983,2152,'','aktywny',20.00),
(356,1,1,21,2,10,'Fotel obrotowy 3','f3',650.00,'2024-05-10 09:00:00',901,2179,'','aktywny',20.00),
(357,1,1,21,2,10,'Fotel obrotowy 4','f4',650.00,'2024-05-10 09:00:00',983,2179,'','aktywny',20.00),
(358,1,1,21,2,10,'Fotel obrotowy 5','f5',650.00,'2024-05-10 09:00:00',901,2207,'','aktywny',20.00),
(359,1,1,21,2,10,'Fotel obrotowy 6','f6',650.00,'2024-05-10 09:00:00',983,2207,'','aktywny',20.00),
(360,1,1,21,2,10,'Fotel obrotowy 7','f7',650.00,'2024-05-10 09:00:00',901,2234,'','aktywny',20.00),
(361,1,1,21,2,10,'Fotel obrotowy 8','f8',650.00,'2024-05-10 09:00:00',983,2234,'','aktywny',20.00),
(362,1,1,21,2,10,'Fotel obrotowy 9','f9',650.00,'2024-05-10 09:00:00',901,2262,'','aktywny',20.00),
(363,1,1,21,2,10,'Fotel obrotowy 10','f10',650.00,'2024-05-10 09:00:00',983,2262,'','aktywny',20.00),
(364,1,1,21,2,10,'Fotel obrotowy 11','f11',650.00,'2024-05-10 09:00:00',901,2289,'','aktywny',20.00),
(365,1,1,21,2,10,'Fotel obrotowy 12','f12',650.00,'2024-05-10 09:00:00',983,2289,'','aktywny',20.00),
(366,1,1,21,2,10,'Fotel obrotowy 13','f13',650.00,'2024-05-10 09:00:00',901,2316,'','aktywny',20.00),
(367,1,1,21,2,10,'Fotel obrotowy 14','f14',650.00,'2024-05-10 09:00:00',983,2316,'','aktywny',20.00),
(368,1,1,21,2,10,'Fotel obrotowy 15','f15',650.00,'2024-05-10 09:00:00',926,2130,'','aktywny',20.00),
(369,1,1,21,2,10,'Fotel obrotowy 16','f16',650.00,'2024-05-10 09:00:00',958,2130,'','aktywny',20.00),
(370,1,1,21,2,10,'Fotel obrotowy 17','f17',650.00,'2024-05-10 09:00:00',926,2339,'','aktywny',20.00),
(371,1,1,21,2,10,'Fotel obrotowy 18','f18',650.00,'2024-05-10 09:00:00',958,2339,'','aktywny',20.00);
UPDATE srodek SET idpom=23, X_srodka=203, Y_srodka=2408 WHERE identyfikator='k1' AND idpod=1;
UPDATE srodek SET idpom=23, X_srodka=250, Y_srodka=2408 WHERE identyfikator='k2' AND idpod=1;
UPDATE srodek SET idpom=23, X_srodka=297, Y_srodka=2408 WHERE identyfikator='k3' AND idpod=1;
UPDATE srodek SET idpom=24, X_srodka=471, Y_srodka=2413 WHERE identyfikator='k4' AND idpod=1;
UPDATE srodek SET idpom=25, X_srodka=679, Y_srodka=2361 WHERE identyfikator='k5' AND idpod=1;
UPDATE srodek SET idpom=25, X_srodka=679, Y_srodka=2408 WHERE identyfikator='k6' AND idpod=1;
UPDATE srodek SET idpom=25, X_srodka=600, Y_srodka=2338 WHERE identyfikator='tel1' AND idpod=1;
UPDATE srodek SET idpom=23, X_srodka=154, Y_srodka=2326 WHERE identyfikator='dr1' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=334, Y_srodka=1138 WHERE identyfikator='sz1' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=359, Y_srodka=1138 WHERE identyfikator='sz2' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=385, Y_srodka=1138 WHERE identyfikator='sz3' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=410, Y_srodka=1138 WHERE identyfikator='sz4' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=436, Y_srodka=1138 WHERE identyfikator='sz5' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=462, Y_srodka=1138 WHERE identyfikator='sz6' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=487, Y_srodka=1138 WHERE identyfikator='sz7' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=513, Y_srodka=1138 WHERE identyfikator='sz8' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=539, Y_srodka=1138 WHERE identyfikator='sz9' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=564, Y_srodka=1138 WHERE identyfikator='sz10' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=590, Y_srodka=1138 WHERE identyfikator='sz11' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=615, Y_srodka=1138 WHERE identyfikator='sz12' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=334, Y_srodka=1273 WHERE identyfikator='sz13' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=359, Y_srodka=1273 WHERE identyfikator='sz14' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=385, Y_srodka=1273 WHERE identyfikator='sz15' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=410, Y_srodka=1273 WHERE identyfikator='sz16' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=436, Y_srodka=1273 WHERE identyfikator='sz17' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=462, Y_srodka=1273 WHERE identyfikator='sz18' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=487, Y_srodka=1273 WHERE identyfikator='sz19' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=513, Y_srodka=1273 WHERE identyfikator='sz20' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=539, Y_srodka=1273 WHERE identyfikator='sz21' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=564, Y_srodka=1273 WHERE identyfikator='sz22' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=590, Y_srodka=1273 WHERE identyfikator='sz23' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=615, Y_srodka=1273 WHERE identyfikator='sz24' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=334, Y_srodka=1407 WHERE identyfikator='sz25' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=359, Y_srodka=1407 WHERE identyfikator='sz26' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=385, Y_srodka=1407 WHERE identyfikator='sz27' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=410, Y_srodka=1407 WHERE identyfikator='sz28' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=436, Y_srodka=1407 WHERE identyfikator='sz29' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=462, Y_srodka=1407 WHERE identyfikator='sz30' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=487, Y_srodka=1407 WHERE identyfikator='sz31' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=513, Y_srodka=1407 WHERE identyfikator='sz32' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=539, Y_srodka=1407 WHERE identyfikator='sz33' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=564, Y_srodka=1407 WHERE identyfikator='sz34' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=590, Y_srodka=1407 WHERE identyfikator='sz35' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=615, Y_srodka=1407 WHERE identyfikator='sz36' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=334, Y_srodka=1542 WHERE identyfikator='sz37' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=359, Y_srodka=1542 WHERE identyfikator='sz38' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=385, Y_srodka=1542 WHERE identyfikator='sz39' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=410, Y_srodka=1542 WHERE identyfikator='sz40' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=436, Y_srodka=1542 WHERE identyfikator='sz41' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=462, Y_srodka=1542 WHERE identyfikator='sz42' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=487, Y_srodka=1542 WHERE identyfikator='sz43' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=513, Y_srodka=1542 WHERE identyfikator='sz44' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=539, Y_srodka=1542 WHERE identyfikator='sz45' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=564, Y_srodka=1542 WHERE identyfikator='sz46' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=590, Y_srodka=1542 WHERE identyfikator='sz47' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=615, Y_srodka=1542 WHERE identyfikator='sz48' AND idpod=1;
UPDATE srodek SET idpom=15, X_srodka=715, Y_srodka=1720 WHERE identyfikator='sz49' AND idpod=1;
UPDATE srodek SET idpom=15, X_srodka=715, Y_srodka=1751 WHERE identyfikator='sz50' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1480, Y_srodka=700 WHERE identyfikator='ups1' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1480, Y_srodka=743 WHERE identyfikator='ups2' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1480, Y_srodka=786 WHERE identyfikator='ups3' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1480, Y_srodka=830 WHERE identyfikator='ups4' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1480, Y_srodka=1018 WHERE identyfikator='ups5' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1480, Y_srodka=1061 WHERE identyfikator='ups6' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1480, Y_srodka=1105 WHERE identyfikator='ups7' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1480, Y_srodka=1148 WHERE identyfikator='ups8' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1028, Y_srodka=687 WHERE identyfikator='bat1' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1028, Y_srodka=710 WHERE identyfikator='bat2' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1028, Y_srodka=732 WHERE identyfikator='bat3' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1028, Y_srodka=754 WHERE identyfikator='bat4' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1028, Y_srodka=776 WHERE identyfikator='bat5' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1028, Y_srodka=798 WHERE identyfikator='bat6' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1028, Y_srodka=820 WHERE identyfikator='bat7' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1028, Y_srodka=842 WHERE identyfikator='bat8' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1028, Y_srodka=1004 WHERE identyfikator='bat9' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1028, Y_srodka=1026 WHERE identyfikator='bat10' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1028, Y_srodka=1048 WHERE identyfikator='bat11' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1028, Y_srodka=1070 WHERE identyfikator='bat12' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1028, Y_srodka=1092 WHERE identyfikator='bat13' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1028, Y_srodka=1114 WHERE identyfikator='bat14' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1028, Y_srodka=1136 WHERE identyfikator='bat15' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1028, Y_srodka=1158 WHERE identyfikator='bat16' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1118, Y_srodka=687 WHERE identyfikator='bat17' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1118, Y_srodka=710 WHERE identyfikator='bat18' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1118, Y_srodka=732 WHERE identyfikator='bat19' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1118, Y_srodka=754 WHERE identyfikator='bat20' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1118, Y_srodka=776 WHERE identyfikator='bat21' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1118, Y_srodka=798 WHERE identyfikator='bat22' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1118, Y_srodka=820 WHERE identyfikator='bat23' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1118, Y_srodka=842 WHERE identyfikator='bat24' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1118, Y_srodka=1003 WHERE identyfikator='bat25' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1118, Y_srodka=1025 WHERE identyfikator='bat26' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1118, Y_srodka=1048 WHERE identyfikator='bat27' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1118, Y_srodka=1070 WHERE identyfikator='bat28' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1118, Y_srodka=1092 WHERE identyfikator='bat29' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1118, Y_srodka=1114 WHERE identyfikator='bat30' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1118, Y_srodka=1136 WHERE identyfikator='bat31' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1118, Y_srodka=1158 WHERE identyfikator='bat32' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1207, Y_srodka=687 WHERE identyfikator='bat33' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1207, Y_srodka=710 WHERE identyfikator='bat34' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1207, Y_srodka=732 WHERE identyfikator='bat35' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1207, Y_srodka=754 WHERE identyfikator='bat36' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1207, Y_srodka=776 WHERE identyfikator='bat37' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1207, Y_srodka=798 WHERE identyfikator='bat38' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1207, Y_srodka=820 WHERE identyfikator='bat39' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1207, Y_srodka=842 WHERE identyfikator='bat40' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1207, Y_srodka=1003 WHERE identyfikator='bat41' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1207, Y_srodka=1025 WHERE identyfikator='bat42' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1207, Y_srodka=1047 WHERE identyfikator='bat43' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1207, Y_srodka=1069 WHERE identyfikator='bat44' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1207, Y_srodka=1091 WHERE identyfikator='bat45' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1207, Y_srodka=1113 WHERE identyfikator='bat46' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1207, Y_srodka=1135 WHERE identyfikator='bat47' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1207, Y_srodka=1157 WHERE identyfikator='bat48' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1296, Y_srodka=687 WHERE identyfikator='bat49' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1296, Y_srodka=710 WHERE identyfikator='bat50' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1296, Y_srodka=732 WHERE identyfikator='bat51' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1296, Y_srodka=754 WHERE identyfikator='bat52' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1296, Y_srodka=776 WHERE identyfikator='bat53' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1296, Y_srodka=798 WHERE identyfikator='bat54' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1296, Y_srodka=820 WHERE identyfikator='bat55' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1296, Y_srodka=842 WHERE identyfikator='bat56' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1296, Y_srodka=1002 WHERE identyfikator='bat57' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1296, Y_srodka=1024 WHERE identyfikator='bat58' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1296, Y_srodka=1046 WHERE identyfikator='bat59' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1296, Y_srodka=1068 WHERE identyfikator='bat60' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1296, Y_srodka=1090 WHERE identyfikator='bat61' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1296, Y_srodka=1113 WHERE identyfikator='bat62' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1296, Y_srodka=1135 WHERE identyfikator='bat63' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1296, Y_srodka=1157 WHERE identyfikator='bat64' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1386, Y_srodka=687 WHERE identyfikator='bat65' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1386, Y_srodka=710 WHERE identyfikator='bat66' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1386, Y_srodka=732 WHERE identyfikator='bat67' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1386, Y_srodka=754 WHERE identyfikator='bat68' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1386, Y_srodka=776 WHERE identyfikator='bat69' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1386, Y_srodka=798 WHERE identyfikator='bat70' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1386, Y_srodka=820 WHERE identyfikator='bat71' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1386, Y_srodka=842 WHERE identyfikator='bat72' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1386, Y_srodka=1002 WHERE identyfikator='bat73' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1386, Y_srodka=1024 WHERE identyfikator='bat74' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1386, Y_srodka=1046 WHERE identyfikator='bat75' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1386, Y_srodka=1068 WHERE identyfikator='bat76' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1386, Y_srodka=1090 WHERE identyfikator='bat77' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1386, Y_srodka=1113 WHERE identyfikator='bat78' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1386, Y_srodka=1135 WHERE identyfikator='bat79' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1386, Y_srodka=1157 WHERE identyfikator='bat80' AND idpod=1;
UPDATE srodek SET idpom=24, X_srodka=408, Y_srodka=2391 WHERE identyfikator='ek1' AND idpod=1;
UPDATE srodek SET idpom=23, X_srodka=370, Y_srodka=2393 WHERE identyfikator='ek2' AND idpod=1;
UPDATE srodek SET idpom=7, X_srodka=2020, Y_srodka=649 WHERE identyfikator='tr1' AND idpod=1;
UPDATE srodek SET idpom=8, X_srodka=2020, Y_srodka=982 WHERE identyfikator='tr2' AND idpod=1;
UPDATE srodek SET idpom=5, X_srodka=107, Y_srodka=652 WHERE identyfikator='kl1' AND idpod=1;
UPDATE srodek SET idpom=5, X_srodka=107, Y_srodka=771 WHERE identyfikator='kl2' AND idpod=1;
UPDATE srodek SET idpom=5, X_srodka=107, Y_srodka=889 WHERE identyfikator='kl3' AND idpod=1;
UPDATE srodek SET idpom=5, X_srodka=107, Y_srodka=1008 WHERE identyfikator='kl4' AND idpod=1;
UPDATE srodek SET idpom=5, X_srodka=107, Y_srodka=1126 WHERE identyfikator='kl5' AND idpod=1;
UPDATE srodek SET idpom=5, X_srodka=107, Y_srodka=1245 WHERE identyfikator='kl6' AND idpod=1;
UPDATE srodek SET idpom=5, X_srodka=107, Y_srodka=1363 WHERE identyfikator='kl7' AND idpod=1;
UPDATE srodek SET idpom=5, X_srodka=107, Y_srodka=1482 WHERE identyfikator='kl8' AND idpod=1;
UPDATE srodek SET idpom=5, X_srodka=107, Y_srodka=1600 WHERE identyfikator='kl9' AND idpod=1;
UPDATE srodek SET idpom=12, X_srodka=820, Y_srodka=652 WHERE identyfikator='kl10' AND idpod=1;
UPDATE srodek SET idpom=12, X_srodka=820, Y_srodka=771 WHERE identyfikator='kl11' AND idpod=1;
UPDATE srodek SET idpom=12, X_srodka=820, Y_srodka=889 WHERE identyfikator='kl12' AND idpod=1;
UPDATE srodek SET idpom=12, X_srodka=820, Y_srodka=1008 WHERE identyfikator='kl13' AND idpod=1;
UPDATE srodek SET idpom=12, X_srodka=820, Y_srodka=1126 WHERE identyfikator='kl14' AND idpod=1;
UPDATE srodek SET idpom=12, X_srodka=820, Y_srodka=1245 WHERE identyfikator='kl15' AND idpod=1;
UPDATE srodek SET idpom=12, X_srodka=820, Y_srodka=1363 WHERE identyfikator='kl16' AND idpod=1;
UPDATE srodek SET idpom=12, X_srodka=820, Y_srodka=1482 WHERE identyfikator='kl17' AND idpod=1;
UPDATE srodek SET idpom=12, X_srodka=820, Y_srodka=1600 WHERE identyfikator='kl18' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=230, Y_srodka=1496 WHERE identyfikator='kam1' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=230, Y_srodka=1394 WHERE identyfikator='kam2' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=230, Y_srodka=1276 WHERE identyfikator='kam3' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=230, Y_srodka=1138 WHERE identyfikator='kam4' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=230, Y_srodka=1000 WHERE identyfikator='kam5' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=230, Y_srodka=862 WHERE identyfikator='kam6' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=230, Y_srodka=726 WHERE identyfikator='kam7' AND idpod=1;
UPDATE srodek SET idpom=11, X_srodka=230, Y_srodka=636 WHERE identyfikator='kam8' AND idpod=1;
UPDATE srodek SET idpom=15, X_srodka=224, Y_srodka=1886 WHERE identyfikator='kam9' AND idpod=1;
UPDATE srodek SET idpom=15, X_srodka=496, Y_srodka=1886 WHERE identyfikator='kam10' AND idpod=1;
UPDATE srodek SET idpom=5, X_srodka=154, Y_srodka=1270 WHERE identyfikator='kam11' AND idpod=1;
UPDATE srodek SET idpom=5, X_srodka=154, Y_srodka=1078 WHERE identyfikator='kam12' AND idpod=1;
UPDATE srodek SET idpom=5, X_srodka=154, Y_srodka=758 WHERE identyfikator='kam13' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=-6, Y_srodka=1542 WHERE identyfikator='kam14' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=-6, Y_srodka=946 WHERE identyfikator='kam15' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=-6, Y_srodka=544 WHERE identyfikator='kam16' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=-6, Y_srodka=2002 WHERE identyfikator='kam17' AND idpod=1;
UPDATE srodek SET idpom=1, X_srodka=590, Y_srodka=455 WHERE identyfikator='kam18' AND idpod=1;
UPDATE srodek SET idpom=2, X_srodka=1100, Y_srodka=455 WHERE identyfikator='kam19' AND idpod=1;
UPDATE srodek SET idpom=3, X_srodka=1628, Y_srodka=455 WHERE identyfikator='kam20' AND idpod=1;
UPDATE srodek SET idpom=4, X_srodka=2158, Y_srodka=455 WHERE identyfikator='kam21' AND idpod=1;
UPDATE srodek SET idpom=6, X_srodka=742, Y_srodka=531 WHERE identyfikator='kam22' AND idpod=1;
UPDATE srodek SET idpom=6, X_srodka=1198, Y_srodka=531 WHERE identyfikator='kam23' AND idpod=1;
UPDATE srodek SET idpom=6, X_srodka=424, Y_srodka=531 WHERE identyfikator='kam24' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=138, Y_srodka=-5 WHERE identyfikator='kam25' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=676, Y_srodka=-5 WHERE identyfikator='kam26' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=1190, Y_srodka=-5 WHERE identyfikator='kam27' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=1696, Y_srodka=-5 WHERE identyfikator='kam28' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=2168, Y_srodka=-5 WHERE identyfikator='kam29' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=72, Y_srodka=56 WHERE identyfikator='kam30' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=2222, Y_srodka=54 WHERE identyfikator='kam31' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=2222, Y_srodka=560 WHERE identyfikator='kam32' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=2222, Y_srodka=1160 WHERE identyfikator='kam33' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=2162, Y_srodka=1200 WHERE identyfikator='kam34' AND idpod=1;
UPDATE srodek SET idpom=7, X_srodka=1904, Y_srodka=710 WHERE identyfikator='kam35' AND idpod=1;
UPDATE srodek SET idpom=8, X_srodka=1903, Y_srodka=1002 WHERE identyfikator='kam36' AND idpod=1;
UPDATE srodek SET idpom=6, X_srodka=1834, Y_srodka=531 WHERE identyfikator='kam37' AND idpod=1;
UPDATE srodek SET idpom=29, X_srodka=1827, Y_srodka=1308 WHERE identyfikator='kam38' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=1844, Y_srodka=1373 WHERE identyfikator='kam39' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=942, Y_srodka=950 WHERE identyfikator='kam40' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1138, Y_srodka=874 WHERE identyfikator='kam41' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1462, Y_srodka=874 WHERE identyfikator='kam42' AND idpod=1;
UPDATE srodek SET idpom=13, X_srodka=1734, Y_srodka=874 WHERE identyfikator='kam43' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1138, Y_srodka=1190 WHERE identyfikator='kam44' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1466, Y_srodka=1190 WHERE identyfikator='kam45' AND idpod=1;
UPDATE srodek SET idpom=14, X_srodka=1732, Y_srodka=1190 WHERE identyfikator='kam46' AND idpod=1;
UPDATE srodek SET idpom=22, X_srodka=732, Y_srodka=2090 WHERE identyfikator='kam47' AND idpod=1;
UPDATE srodek SET idpom=26, X_srodka=718, Y_srodka=2662 WHERE identyfikator='kam48' AND idpod=1;
UPDATE srodek SET idpom=26, X_srodka=1034, Y_srodka=2662 WHERE identyfikator='kam49' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=1034, Y_srodka=2741 WHERE identyfikator='kam50' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=655, Y_srodka=2546 WHERE identyfikator='kam51' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=72, Y_srodka=2498 WHERE identyfikator='kam52' AND idpod=1;
UPDATE srodek SET idpom=27, X_srodka=151, Y_srodka=2224 WHERE identyfikator='kam53' AND idpod=1;
UPDATE srodek SET idpom=27, X_srodka=678, Y_srodka=2233 WHERE identyfikator='kam54' AND idpod=1;
UPDATE srodek SET idpom=31, X_srodka=970, Y_srodka=1996 WHERE identyfikator='kam55' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=1064, Y_srodka=1988 WHERE identyfikator='kam56' AND idpod=1;
UPDATE srodek SET idpom=NULL, X_srodka=1064, Y_srodka=1373 WHERE identyfikator='kam57' AND idpod=1;
UPDATE srodek SET idpom=30, X_srodka=642, Y_srodka=1964 WHERE identyfikator='ap1' AND idpod=1;
UPDATE srodek SET idpom=30, X_srodka=260, Y_srodka=1992 WHERE identyfikator='ap2' AND idpod=1;
UPDATE srodek SET idpom=31, X_srodka=942, Y_srodka=1826 WHERE identyfikator='ap3' AND idpod=1;
UPDATE srodek SET idpom=31, X_srodka=942, Y_srodka=1466 WHERE identyfikator='ap4' AND idpod=1;
UPDATE srodek SET idpom=27, X_srodka=608, Y_srodka=2274 WHERE identyfikator='ap5' AND idpod=1;
UPDATE srodek SET idpom=26, X_srodka=750, Y_srodka=2496 WHERE identyfikator='ap6' AND idpod=1;
UPDATE srodek SET idpom=29, X_srodka=1182, Y_srodka=1308 WHERE identyfikator='ap7' AND idpod=1;
UPDATE srodek SET idpom=27, X_srodka=234, Y_srodka=2274 WHERE identyfikator='ap8' AND idpod=1;
UPDATE srodek SET idpom=29, X_srodka=1628, Y_srodka=1308 WHERE identyfikator='ap9' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1094, Y_srodka=944 WHERE identyfikator='ap10' AND idpod=1;
UPDATE srodek SET idpom=28, X_srodka=1814, Y_srodka=1182 WHERE identyfikator='ap11' AND idpod=1;
UPDATE srodek SET idpom=14, X_srodka=1704, Y_srodka=944 WHERE identyfikator='ap12' AND idpod=1;
UPDATE srodek SET idpom=6, X_srodka=1116, Y_srodka=552 WHERE identyfikator='ap13' AND idpod=1;
UPDATE srodek SET idpom=6, X_srodka=1652, Y_srodka=552 WHERE identyfikator='ap14' AND idpod=1;
UPDATE srodek SET idpom=15, X_srodka=711, Y_srodka=1812 WHERE identyfikator='tel2' AND idpod=1;
UPDATE srodek SET idpom=23, X_srodka=200, Y_srodka=2474 WHERE identyfikator='tel3' AND idpod=1;
UPDATE srodek SET idpom=23, X_srodka=248, Y_srodka=2474 WHERE identyfikator='tel4' AND idpod=1;
UPDATE srodek SET idpom=23, X_srodka=296, Y_srodka=2474 WHERE identyfikator='tel5' AND idpod=1;
UPDATE srodek SET idpom=24, X_srodka=464, Y_srodka=2474 WHERE identyfikator='tel6' AND idpod=1;
UPDATE srodek SET idpom=25, X_srodka=616, Y_srodka=2474 WHERE identyfikator='tel7' AND idpod=1;
UPDATE srodek SET idpom=25, X_srodka=668, Y_srodka=2464 WHERE identyfikator='tel8' AND idpod=1;
UPDATE srodek SET idpom=1, X_srodka=363, Y_srodka=248 WHERE identyfikator='ag1' AND idpod=1;
UPDATE srodek SET idpom=2, X_srodka=883, Y_srodka=248 WHERE identyfikator='ag2' AND idpod=1;
UPDATE srodek SET idpom=3, X_srodka=1404, Y_srodka=248 WHERE identyfikator='ag3' AND idpod=1;
UPDATE srodek SET idpom=4, X_srodka=1924, Y_srodka=248 WHERE identyfikator='ag4' AND idpod=1;
UPDATE srodek SET idpom=13, X_srodka=1612, Y_srodka=651 WHERE identyfikator='szr1' AND idpod=1;
UPDATE srodek SET idpom=14, X_srodka=1612, Y_srodka=967 WHERE identyfikator='szr2' AND idpod=1;
UPDATE srodek SET idpom=13, X_srodka=1612, Y_srodka=739 WHERE identyfikator='rnr1' AND idpod=1;
UPDATE srodek SET idpom=13, X_srodka=1612, Y_srodka=803 WHERE identyfikator='rnr2' AND idpod=1;
UPDATE srodek SET idpom=14, X_srodka=1612, Y_srodka=1055 WHERE identyfikator='rnr3' AND idpod=1;
UPDATE srodek SET idpom=14, X_srodka=1612, Y_srodka=1119 WHERE identyfikator='rnr4' AND idpod=1;
UPDATE srodek SET idpom=9, X_srodka=1566, Y_srodka=627 WHERE identyfikator='rng1' AND idpod=1;
UPDATE srodek SET idpom=10, X_srodka=1566, Y_srodka=943 WHERE identyfikator='rng2' AND idpod=1;
UPDATE srodek SET idpom=15, X_srodka=716, Y_srodka=1882 WHERE identyfikator='rng3' AND idpod=1;
UPDATE srodek SET idpom=7, X_srodka=2130, Y_srodka=813 WHERE identyfikator='rg1' AND idpod=1;
UPDATE srodek SET idpom=8, X_srodka=2130, Y_srodka=1146 WHERE identyfikator='rg2' AND idpod=1;
UPDATE srodek SET idpom=21, X_srodka=901, Y_srodka=2152 WHERE identyfikator='f1' AND idpod=1;
UPDATE srodek SET idpom=21, X_srodka=983, Y_srodka=2152 WHERE identyfikator='f2' AND idpod=1;
UPDATE srodek SET idpom=21, X_srodka=901, Y_srodka=2179 WHERE identyfikator='f3' AND idpod=1;
UPDATE srodek SET idpom=21, X_srodka=983, Y_srodka=2179 WHERE identyfikator='f4' AND idpod=1;
UPDATE srodek SET idpom=21, X_srodka=901, Y_srodka=2207 WHERE identyfikator='f5' AND idpod=1;
UPDATE srodek SET idpom=21, X_srodka=983, Y_srodka=2207 WHERE identyfikator='f6' AND idpod=1;
UPDATE srodek SET idpom=21, X_srodka=901, Y_srodka=2234 WHERE identyfikator='f7' AND idpod=1;
UPDATE srodek SET idpom=21, X_srodka=983, Y_srodka=2234 WHERE identyfikator='f8' AND idpod=1;
UPDATE srodek SET idpom=21, X_srodka=901, Y_srodka=2262 WHERE identyfikator='f9' AND idpod=1;
UPDATE srodek SET idpom=21, X_srodka=983, Y_srodka=2262 WHERE identyfikator='f10' AND idpod=1;
UPDATE srodek SET idpom=21, X_srodka=901, Y_srodka=2289 WHERE identyfikator='f11' AND idpod=1;
UPDATE srodek SET idpom=21, X_srodka=983, Y_srodka=2289 WHERE identyfikator='f12' AND idpod=1;
UPDATE srodek SET idpom=21, X_srodka=901, Y_srodka=2316 WHERE identyfikator='f13' AND idpod=1;
UPDATE srodek SET idpom=21, X_srodka=983, Y_srodka=2316 WHERE identyfikator='f14' AND idpod=1;
UPDATE srodek SET idpom=21, X_srodka=926, Y_srodka=2130 WHERE identyfikator='f15' AND idpod=1;
UPDATE srodek SET idpom=21, X_srodka=958, Y_srodka=2130 WHERE identyfikator='f16' AND idpod=1;
UPDATE srodek SET idpom=21, X_srodka=926, Y_srodka=2339 WHERE identyfikator='f17' AND idpod=1;
UPDATE srodek SET idpom=21, X_srodka=958, Y_srodka=2339 WHERE identyfikator='f18' AND idpod=1;
DELETE FROM nazwa_srodka WHERE prefiks='ak' AND idnazwa>=14;
