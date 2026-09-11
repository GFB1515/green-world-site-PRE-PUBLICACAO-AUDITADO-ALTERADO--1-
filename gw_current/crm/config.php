<?php
$base = [
    'session_name' => 'GWCRMSESSID',
    'site_name' => 'GREEN WORLD CRM',
    'timezone' => 'America/Sao_Paulo',
    'ip_salt' => 'gw-change-this-before-production-2026',
];
$localFile = __DIR__ . '/config.local.php';
if (is_file($localFile)) {
    $local = require $localFile;
    if (is_array($local)) $base = array_merge($base, $local);
}
return $base;
