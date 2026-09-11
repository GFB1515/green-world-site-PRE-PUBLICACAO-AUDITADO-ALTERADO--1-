<?php
header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store');
require __DIR__ . '/../crm/lib.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false]); exit; }
if(!gw_configured()){http_response_code(503);echo json_encode(['ok'=>false]);exit;}
$origin=$_SERVER['HTTP_ORIGIN']??'';
if($origin){$host=$_SERVER['HTTP_HOST']??'';$oh=parse_url($origin,PHP_URL_HOST);if(!$oh||strcasecmp((string)$oh,preg_replace('/:\d+$/','',$host))!==0){http_response_code(403);echo json_encode(['ok'=>false]);exit;}}

try {
    $in=json_decode(file_get_contents('php://input')?:'{}',true);if(!is_array($in))$in=[];
    $visitorKey=preg_replace('/[^a-zA-Z0-9_-]/','',(string)($in['visitor_id']??''));
    if(strlen($visitorKey)<12||strlen($visitorKey)>80){http_response_code(400);echo json_encode(['ok'=>false]);exit;}
    $type=substr((string)($in['event']??'page_view'),0,50);$allowed=['page_view','products_view','product_open','whatsapp_click','quote_click','docs_click','sample_click','email_click'];if(!in_array($type,$allowed,true)){http_response_code(400);echo json_encode(['ok'=>false]);exit;}$path=substr((string)($in['path']??''),0,500);$referrer=substr((string)($in['referrer']??''),0,1000);
    $productCode=substr((string)($in['product_code']??''),0,100);$productFamily=substr((string)($in['product_family']??''),0,150);$label=substr((string)($in['label']??''),0,250);
    $source=substr((string)($in['utm_source']??''),0,100);$medium=substr((string)($in['utm_medium']??''),0,100);$campaign=substr((string)($in['utm_campaign']??''),0,150);
    $metadata=$in['metadata']??[];if(!is_array($metadata))$metadata=[];$metadataJson=json_encode($metadata,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);if(strlen((string)$metadataJson)>4000)$metadataJson='{}';$now=gw_now();$db=gw_db();$db->beginTransaction();
    $st=$db->prepare('SELECT * FROM gw_visitors WHERE visitor_key=?');$st->execute([$visitorKey]);$v=$st->fetch();$incVisit=in_array($type,['page_view','products_view'],true)?1:0;
    if(!$v){$ins=$db->prepare('INSERT INTO gw_visitors(visitor_key,first_seen,last_seen,first_path,last_path,first_referrer,source,medium,campaign,ip_hash,user_agent,score,visit_count,event_count) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)');$ins->execute([$visitorKey,$now,$now,$path,$path,$referrer,$source,$medium,$campaign,gw_client_ip_hash(),substr($_SERVER['HTTP_USER_AGENT']??'',0,500),gw_score_for($type),$incVisit,1]);$visitorId=(int)$db->lastInsertId();}
    else{$visitorId=(int)$v['id'];$up=$db->prepare("UPDATE gw_visitors SET last_seen=?,last_path=?,score=score+?,visit_count=visit_count+?,event_count=event_count+1,source=CASE WHEN source IS NULL OR source='' THEN ? ELSE source END,medium=CASE WHEN medium IS NULL OR medium='' THEN ? ELSE medium END,campaign=CASE WHEN campaign IS NULL OR campaign='' THEN ? ELSE campaign END WHERE id=?");$up->execute([$now,$path,gw_score_for($type),$incVisit,$source,$medium,$campaign,$visitorId]);}
    $ev=$db->prepare('INSERT INTO gw_events(visitor_id,event_type,path,product_code,product_family,label,metadata_json,created_at) VALUES(?,?,?,?,?,?,?,?)');$ev->execute([$visitorId,$type,$path,$productCode,$productFamily,$label,$metadataJson,$now]);$db->commit();echo json_encode(['ok'=>true]);
} catch(Throwable $e){if(isset($db)&&$db instanceof PDO&&$db->inTransaction())$db->rollBack();http_response_code(500);echo json_encode(['ok'=>false]);}
