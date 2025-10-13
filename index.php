<?php
require __DIR__ . '/db.php'; 

function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$err = '';
$ok  = '';