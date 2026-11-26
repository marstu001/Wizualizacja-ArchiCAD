<?php declare(strict_types=1);
require_once 'b_sse.php';
sse_strumien(function (array $x): string {
	return implode("\t", $x);
});
