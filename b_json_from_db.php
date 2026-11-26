<?php declare(strict_types=1);
require_once 'b_sse.php';
sse_strumien(function (array $x): string {
	return json_encode(['x1' => (float)$x[0], 'x2' => (float)$x[1], 'x3' => (float)$x[2], 'x4' => (float)$x[3], 'x5' => (float)$x[4]]);
});
