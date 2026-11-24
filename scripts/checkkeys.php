<?php

/*
 * MenuQR — check Arabic/English translation key parity.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

function flat(array $a, string $p = ''): array
{
    $k = [];
    foreach ($a as $x => $v) {
        $f = $p === '' ? (string) $x : $p.'.'.$x;
        if (is_array($v)) {
            $k = array_merge($k, flat($v, $f));
        } else {
            $k[] = $f;
        }
    }

    return $k;
}

$ar = flat(require __DIR__.'/../lang/ar/menuqr.php');
$en = flat(require __DIR__.'/../lang/en/menuqr.php');
sort($ar);
sort($en);
echo 'missing in en: '.implode(', ', array_diff($ar, $en)).PHP_EOL;
echo 'missing in ar: '.implode(', ', array_diff($en, $ar)).PHP_EOL;
echo 'ar keys '.count($ar).' / en keys '.count($en).PHP_EOL;
