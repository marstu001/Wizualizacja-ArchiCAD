<?php declare(strict_types=1);
require_once 'k_lib.php';
[$uid, $login, $admin] = wymagaj_logowania();
session_write_close();

$link = db_polacz();
if (!$link) { kom_json(['ok' => false], 500); }
$r = json_decode((string)($_GET['r'] ?? ''), true);
$przeczytane = is_array($r) ? array_map('intval', $r) : [];
kom_json(['ok' => true, 'czaty' => kom_czaty($link, $login, $przeczytane)]);
