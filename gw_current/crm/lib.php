<?php
$config = require __DIR__ . '/config.php';
date_default_timezone_set($config['timezone'] ?? 'America/Sao_Paulo');

function gw_config(): array { global $config; return $config; }
function gw_configured(): bool {
    $c=gw_config();
    return !empty($c['db_host']) && !empty($c['db_name']) && !empty($c['db_user']);
}
function gw_db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $cfg = gw_config();
    if (!gw_configured()) throw new RuntimeException('CRM ainda não configurado.');
    if (!extension_loaded('pdo_mysql')) throw new RuntimeException('PDO_MYSQL não está habilitado neste servidor.');
    $dsn='mysql:host='.$cfg['db_host'].';port='.($cfg['db_port']??'3306').';dbname='.$cfg['db_name'].';charset=utf8mb4';
    $pdo = new PDO($dsn,$cfg['db_user'],$cfg['db_pass']??'',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
    gw_schema($pdo);
    return $pdo;
}
function gw_schema(PDO $db): void {
    $db->exec("CREATE TABLE IF NOT EXISTS gw_users (
      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      username VARCHAR(120) NOT NULL UNIQUE,
      password_hash VARCHAR(255) NOT NULL,
      created_at DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->exec("CREATE TABLE IF NOT EXISTS gw_visitors (
      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      visitor_key VARCHAR(100) NOT NULL UNIQUE,
      first_seen DATETIME NOT NULL,
      last_seen DATETIME NOT NULL,
      first_path VARCHAR(500) NULL,
      last_path VARCHAR(500) NULL,
      first_referrer VARCHAR(1000) NULL,
      source VARCHAR(100) NULL,
      medium VARCHAR(100) NULL,
      campaign VARCHAR(150) NULL,
      ip_hash CHAR(64) NULL,
      user_agent VARCHAR(500) NULL,
      score INT NOT NULL DEFAULT 0,
      visit_count INT NOT NULL DEFAULT 0,
      event_count INT NOT NULL DEFAULT 0,
      name VARCHAR(180) NULL,
      company VARCHAR(180) NULL,
      email VARCHAR(220) NULL,
      phone VARCHAR(80) NULL,
      status VARCHAR(80) NOT NULL DEFAULT 'Novo visitante',
      notes TEXT NULL,
      INDEX idx_gw_visitors_last_seen(last_seen),
      INDEX idx_gw_visitors_score(score)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->exec("CREATE TABLE IF NOT EXISTS gw_events (
      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      visitor_id BIGINT UNSIGNED NOT NULL,
      event_type VARCHAR(50) NOT NULL,
      path VARCHAR(500) NULL,
      product_code VARCHAR(100) NULL,
      product_family VARCHAR(150) NULL,
      label VARCHAR(250) NULL,
      metadata_json JSON NULL,
      created_at DATETIME NOT NULL,
      INDEX idx_gw_events_visitor(visitor_id,created_at),
      INDEX idx_gw_events_type(event_type,created_at),
      CONSTRAINT fk_gw_events_visitor FOREIGN KEY(visitor_id) REFERENCES gw_visitors(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}
function gw_now(): string { return date('Y-m-d H:i:s'); }
function gw_start_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $cfg = gw_config(); session_name($cfg['session_name']);
    session_set_cookie_params(['httponly'=>true,'secure'=>(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off'),'samesite'=>'Lax','path'=>'/']);
    session_start();
}
function gw_has_admin(): bool { return (int)gw_db()->query('SELECT COUNT(*) FROM gw_users')->fetchColumn() > 0; }
function gw_is_logged_in(): bool { gw_start_session(); return !empty($_SESSION['gw_user_id']); }
function gw_require_login(): void {
    if (!gw_configured()) { header('Location: setup.php'); exit; }
    if (!gw_has_admin()) { header('Location: setup.php'); exit; }
    if (!gw_is_logged_in()) { header('Location: login.php'); exit; }
}
function gw_csrf(): string { gw_start_session(); if(empty($_SESSION['gw_csrf']))$_SESSION['gw_csrf']=bin2hex(random_bytes(24)); return $_SESSION['gw_csrf']; }
function gw_check_csrf(string $token): bool { gw_start_session(); return isset($_SESSION['gw_csrf']) && hash_equals($_SESSION['gw_csrf'],$token); }
function gw_e(?string $v): string { return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); }
function gw_client_ip_hash(): string { $cfg=gw_config(); return hash('sha256',($cfg['ip_salt']??'').'|'.($_SERVER['REMOTE_ADDR']??'')); }
function gw_score_for(string $event): int { return ['page_view'=>1,'products_view'=>5,'product_open'=>10,'whatsapp_click'=>20,'quote_click'=>30,'docs_click'=>20,'sample_click'=>40,'email_click'=>15][$event]??0; }
function gw_statuses(): array { return ['Novo visitante','Em observação','Lead identificado','Cotação','Negociação','Cliente','Descartado']; }
