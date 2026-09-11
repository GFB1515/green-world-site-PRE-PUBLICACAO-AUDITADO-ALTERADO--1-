<?php
require __DIR__.'/lib.php';gw_start_session();$error='';$configured=gw_configured();
if($configured){try{if(gw_has_admin()){header('Location: login.php');exit;}}catch(Throwable $e){$error=$e->getMessage();}}
if($_SERVER['REQUEST_METHOD']==='POST'){
 $dbHost=trim($_POST['db_host']??'localhost');$dbPort=trim($_POST['db_port']??'3306');$dbName=trim($_POST['db_name']??'');$dbUser=trim($_POST['db_user']??'');$dbPass=(string)($_POST['db_pass']??'');
 $user=trim($_POST['username']??'');$pass=(string)($_POST['password']??'');$pass2=(string)($_POST['password2']??'');
 if(!$dbName||!$dbUser)$error='Informe o banco de dados e o usuário MySQL.';elseif(strlen($user)<3)$error='Use um usuário do CRM com pelo menos 3 caracteres.';elseif(strlen($pass)<10)$error='Use uma senha do CRM com pelo menos 10 caracteres.';elseif($pass!==$pass2)$error='As senhas não conferem.';
 else{
   try{
     if(!extension_loaded('pdo_mysql'))throw new RuntimeException('PDO_MYSQL não está habilitado no servidor.');
     $dsn='mysql:host='.$dbHost.';port='.$dbPort.';dbname='.$dbName.';charset=utf8mb4';$test=new PDO($dsn,$dbUser,$dbPass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
     $salt=bin2hex(random_bytes(32));$local="<?php\nreturn ".var_export(['db_host'=>$dbHost,'db_port'=>$dbPort,'db_name'=>$dbName,'db_user'=>$dbUser,'db_pass'=>$dbPass,'ip_salt'=>$salt],true).";\n";
     if(file_put_contents(__DIR__.'/config.local.php',$local,LOCK_EX)===false)throw new RuntimeException('Não foi possível gravar crm/config.local.php. Verifique a permissão da pasta crm.');
     $_SESSION['gw_setup_user']=$user; $_SESSION['gw_setup_pass']=$pass;
     header('Location: setup.php?finish=1');exit;
   }catch(Throwable $e){$error='Não foi possível conectar/configurar o MySQL: '.$e->getMessage();}
 }
}
if(isset($_GET['finish'])&&gw_configured()){
 try{$db=gw_db();if(!gw_has_admin()){ $user=(string)($_SESSION['gw_setup_user']??'');$pass=(string)($_SESSION['gw_setup_pass']??''); unset($_SESSION['gw_setup_user'],$_SESSION['gw_setup_pass']); if(strlen($user)<3||strlen($pass)<10)throw new RuntimeException('Dados de configuração expirados. Refaça a configuração.');$st=$db->prepare('INSERT INTO gw_users(username,password_hash,created_at) VALUES(?,?,?)');$st->execute([$user,password_hash($pass,PASSWORD_DEFAULT),gw_now()]);}header('Location: login.php?created=1');exit;}catch(Throwable $e){$error=$e->getMessage();}
}
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Configurar GREEN WORLD CRM</title><link rel="stylesheet" href="style.css"></head><body class="auth"><main class="auth-card setup-card"><div class="brand-dot">GW</div><h1>Configurar CRM</h1><p>Use os dados do banco MySQL criado no painel da sua hospedagem. Depois crie seu acesso administrativo.</p><?php if($error):?><div class="alert"><?=gw_e($error)?></div><?php endif;?><form method="post"><div class="two"><label>Host MySQL<input name="db_host" value="localhost" required></label><label>Porta<input name="db_port" value="3306" required></label></div><label>Nome do banco<input name="db_name" placeholder="ex.: usuario_greenworld" required></label><label>Usuário MySQL<input name="db_user" placeholder="ex.: usuario_crm" required></label><label>Senha MySQL<input type="password" name="db_pass" autocomplete="off"></label><hr><label>Usuário do CRM<input name="username" value="admin" autocomplete="username" required></label><label>Senha do CRM<input type="password" name="password" autocomplete="new-password" required></label><label>Confirmar senha<input type="password" name="password2" autocomplete="new-password" required></label><button>Configurar e criar CRM</button></form></main></body></html>
