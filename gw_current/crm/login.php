<?php
require __DIR__.'/lib.php'; gw_start_session();
if(!gw_has_admin()){header('Location: setup.php');exit;}
if(gw_is_logged_in()){header('Location: index.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $u=trim($_POST['username']??'');$p=(string)($_POST['password']??'');
 $st=gw_db()->prepare('SELECT * FROM gw_users WHERE username=?');$st->execute([$u]);$row=$st->fetch();
 if($row && password_verify($p,$row['password_hash'])){session_regenerate_id(true);$_SESSION['gw_user_id']=$row['id'];$_SESSION['gw_username']=$row['username'];header('Location: index.php');exit;}
 $error='Usuário ou senha inválidos.';
}
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Entrar | GREEN WORLD CRM</title><link rel="stylesheet" href="style.css"></head><body class="auth"><main class="auth-card"><div class="brand-dot">GW</div><h1>GREEN WORLD CRM</h1><p>Painel interno de inteligência comercial.</p><?php if(isset($_GET['created'])):?><div class="success">Acesso criado. Faça login.</div><?php endif;?><?php if($error):?><div class="alert"><?=gw_e($error)?></div><?php endif;?><form method="post"><label>Usuário<input name="username" autocomplete="username" required></label><label>Senha<input type="password" name="password" autocomplete="current-password" required></label><button>Entrar no CRM</button></form></main></body></html>
