<?php
declare(strict_types=1);
session_start();
define('CMS_ROOT', __DIR__);
define('SITE_ROOT', dirname(__DIR__));
define('SITE_URL', 'https://yonolootzone.com');
define('CMS_DATA', CMS_ROOT . '/data');
$authFile = CMS_DATA . '/auth.json';
function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function slugify(string $v): string {
  $v = strtolower(trim($v));
  $v = preg_replace('/[^a-z0-9]+/', '-', $v) ?? '';
  return trim($v, '-') ?: 'item-' . time();
}
function read_json(string $file, $default = []) { if (!is_file($file)) return $default; $d=json_decode((string)file_get_contents($file), true); return is_array($d)?$d:$default; }
function write_json(string $file, $data): void { file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE), LOCK_EX); }
function csrf(): string { if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function check_csrf(): void { if(!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) die('Invalid request.'); }
function logged_in(): bool { return !empty($_SESSION['cms_auth']); }
function require_login(): void { if(!logged_in()){ header('Location: setup.php'); exit; } }
function safe_asset(string $url): string { return trim($url); }
function game_page_url(array $g): string {
  $page = trim((string)($g['page_link'] ?? ''));
  if ($page === '') $page = 'games/' . slugify((string)($g['slug'] ?? $g['name'] ?? 'game')) . '/index.html';
  return SITE_URL . '/' . ltrim($page, '/');
}
function update_sitemap(string $url): void {
  $file=SITE_ROOT.'/sitemap.xml'; $xml=is_file($file)?file_get_contents($file):'<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
  $loc=h($url); if(strpos($xml, '<loc>'. $loc .'</loc>') !== false) return;
  $entry='  <url><loc>'.$loc.'</loc><lastmod>'.date('Y-m-d').'</lastmod><changefreq>weekly</changefreq></url>\n';
  $xml=str_replace('</urlset>', $entry.'</urlset>', $xml); file_put_contents($file,$xml,LOCK_EX);
}
function update_latest_post(array $posts): void {
  if (!$posts) return;
  usort($posts, fn($a,$b)=>strcmp(($b['date']??'').' '.($b['slug']??''), ($a['date']??'').' '.($a['slug']??'')));
  $p=$posts[0];
  $latest=[
    'id'=>($p['slug']??'').'|'.($p['date']??''),
    'title'=>$p['title']??'',
    'slug'=>$p['slug']??'',
    'date'=>$p['date']??'',
    'type'=>$p['type']??'Article',
    'excerpt'=>$p['excerpt']??($p['description']??''),
    'description'=>$p['description']??'',
    'image'=>$p['image']??'',
    'imageAlt'=>$p['imageAlt']??''
  ];
  if(!is_dir(SITE_ROOT.'/posts')) mkdir(SITE_ROOT.'/posts',0755,true);
  write_json(SITE_ROOT.'/posts/latest.json',$latest);
}
function update_posts_index(array $posts): void {
  usort($posts, fn($a,$b)=>strcmp($b['date']??'', $a['date']??''));
  $cards=''; foreach($posts as $p){ $url=SITE_URL.'/posts/'.($p['slug']??'').'/'; $img=$p['image']??''; $cards.='<article class="post-card"><a href="'.h($url).'">'.($img?'<img src="'.h($img).'" alt="'.h($p['imageAlt']??$p['title']??'Post').'" loading="lazy">':'').'<div><small>'.h($p['date']??'').' · '.h($p['type']??'Article').'</small><h2>'.h($p['title']??'').'</h2><p>'.h($p['excerpt']??$p['description']??'').'</p><span>Read update →</span></div></a></article>'; }
  $html='<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>YonoLootZone — Latest Posts & Updates</title><meta name="description" content="Latest Yono Game updates, guides, new game announcements, images and videos from YonoLootZone."><link rel="canonical" href="'.SITE_URL.'/posts/"><link rel="stylesheet" href="../assets/style.css"><style>.posts-wrap{max-width:1100px;margin:30px auto;padding:0 16px}.post-card{background:#fff;border:1px solid #ffd5e2;border-radius:18px;margin:16px 0;overflow:hidden}.post-card a{display:flex;gap:18px;padding:16px;text-decoration:none;color:inherit}.post-card img{width:180px;height:110px;object-fit:cover;border-radius:12px}.post-card h2{margin:6px 0;font-size:21px}.post-card p{color:#475569;line-height:1.6}.post-card small{color:#64748b}@media(max-width:650px){.post-card a{display:block}.post-card img{width:100%;height:190px}}</style></head><body><main class="posts-wrap"><h1>Latest Yono Game Updates & Guides</h1><p>Fresh articles, game updates, guides and announcements published by YonoLootZone.</p>'.$cards.'</main></body></html>';
  if(!is_dir(SITE_ROOT.'/posts')) mkdir(SITE_ROOT.'/posts',0755,true); file_put_contents(SITE_ROOT.'/posts/index.html',$html,LOCK_EX);
}
function render_post(array $p): string {
  $title=$p['title']; $desc=$p['description']??$p['excerpt']??''; $url=SITE_URL.'/posts/'.$p['slug'].'/'; $img=$p['image']??'';
  $body=$p['content']??''; $video=$p['video']??''; $videoHtml='';
  if($video){ $embed=$video; if(preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/shorts/)([A-Za-z0-9_-]{6,})~',$video,$m)) $embed='https://www.youtube.com/embed/'.$m[1]; if(str_ends_with(strtolower(parse_url($video,PHP_URL_PATH)??''),'.mp4')) $videoHtml='<video controls preload="metadata" style="width:100%;border-radius:14px"><source src="'.h($video).'">Your browser does not support video.</video>'; else $videoHtml='<div style="aspect-ratio:16/9"><iframe src="'.h($embed).'" title="'.h($title).' video" loading="lazy" allowfullscreen style="width:100%;height:100%;border:0;border-radius:14px"></iframe></div>'; }
  $gameHtml=''; if(!empty($p['gameName']) && !empty($p['gamePageLink'])){ $gameHtml='<section class="game-box"><h2>'.h($p['gameName']).'</h2>'.(!empty($p['gameImage'])?'<img src="'.h($p['gameImage']).'" alt="'.h($p['gameName']).'" loading="lazy">':'').'<p>Read the dedicated game page for details, screenshots and available information.</p><a class="game-btn" href="'.h($p['gamePageLink']).'">View '.h($p['gameName']).' Game Page →</a></section>'; }
  $promoHtml=''; if(!empty($p['promoEnabled']) && !empty($p['promoCode'])){ $promoHtml='<section class="promo-box"><h2>🎁 '.h($p['gameName']?($p['gameName'].' Promo Code'):'Promo Code Update').'</h2><div class="promo-code">'.h($p['promoCode']).'</div>'.(!empty($p['promoReward'])?'<p><strong>Offer:</strong> '.h($p['promoReward']).'</p>':'').(!empty($p['promoMinDeposit'])?'<p><strong>Minimum Deposit:</strong> '.h($p['promoMinDeposit']).'</p>':'').(!empty($p['promoExpiry'])?'<p><strong>Expiry:</strong> '.h($p['promoExpiry']).'</p>':'').(!empty($p['promoTerms'])?'<p><strong>Terms:</strong> '.nl2br(h($p['promoTerms'])).'</p>':'').'<p class="promo-note">Check the offer inside the game/app before using it. Availability and eligibility may vary by account.</p></section>'; }
  $schema=['@context'=>'https://schema.org','@type'=>'Article','headline'=>$title,'description'=>$desc,'datePublished'=>$p['date']??date('Y-m-d'),'dateModified'=>$p['date']??date('Y-m-d'),'author'=>['@type'=>'Person','name'=>$p['author']??'YonoLootZone'],'publisher'=>['@type'=>'Organization','name'=>'YonoLootZone'],'mainEntityOfPage'=>$url];
  if($img) $schema['image']=SITE_URL.'/'.ltrim($img,'/');
  return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.h($title).'</title><meta name="description" content="'.h($desc).'"><meta name="robots" content="index,follow,max-image-preview:large"><link rel="canonical" href="'.h($url).'"><meta property="og:type" content="article"><meta property="og:title" content="'.h($title).'"><meta property="og:description" content="'.h($desc).'"><meta property="og:url" content="'.h($url).'">'.($img?'<meta property="og:image" content="'.h(SITE_URL.'/'.ltrim($img,'/')).'">':'').'<script type="application/ld+json">'.json_encode($schema,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'</script><link rel="stylesheet" href="../../assets/style.css"><style>.post-wrap{max-width:900px;margin:30px auto;padding:0 16px}.post-wrap article{background:#fff;border:1px solid #ffd5e2;border-radius:20px;padding:28px}.post-wrap img.hero{width:100%;max-height:480px;object-fit:cover;border-radius:16px;margin:15px 0}.post-content{font-size:16px;line-height:1.8;color:#334155}.post-content h2{margin-top:28px}.game-box,.promo-box{margin-top:28px;padding:22px;border-radius:18px;background:#fff7fb;border:1px solid #ffd1e2}.game-box img{width:82px;height:82px;object-fit:cover;border-radius:16px;display:block;margin:10px 0}.game-btn{display:inline-block;background:#e00068;color:#fff!important;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:800}.promo-code{display:inline-block;font-size:24px;font-weight:900;letter-spacing:1px;background:#fff;padding:12px 18px;border:2px dashed #e00068;border-radius:12px;margin:8px 0}.promo-note{font-size:13px;color:#64748b}</style></head><body><main class="post-wrap"><article><p><a href="/posts/">← All posts</a></p><h1>'.h($title).'</h1><p><small>'.h($p['date']??'').' · '.h($p['author']??'YonoLootZone').'</small></p>'.($img?'<img class="hero" src="'.h($img).'" alt="'.h($p['imageAlt']??$title).'">':'').'<p><strong>'.h($p['excerpt']??'').'</strong></p><div class="post-content">'.$body.'</div>'.$gameHtml.$promoHtml.($video?'<section><h2>Video</h2>'.$videoHtml.'</section>':'').'</article></main></body></html>';
}
function render_game(array $g): string {
  $name=$g['name']; $slug=$g['slug']; $url=SITE_URL.'/games/'.$slug.'/'; $desc=$g['metaDescription']??($g['description']??''); $img=$g['image']??''; $cat=$g['category']??'Yono Game'; $link=$g['url']??'#';
  $games=read_json(SITE_ROOT.'/data/games.json',[]); $related=array_values(array_filter($games,fn($x)=>($x['slug']??'')!==$slug)); shuffle($related); $related=array_slice($related,0,6);
  $relatedHtml=''; foreach($related as $r){$rp=$r['page_link']??('games/'.($r['slug']??'').'/index.html'); $rh=str_ends_with($rp,'.html')?'/'.ltrim($rp,'/') : '/'.trim($rp,'/').'/'; $relatedHtml.='<li><a href="'.h($rh).'">'.h($r['name']??'Related game').'</a></li>';}
  $schema=['@context'=>'https://schema.org','@type'=>'SoftwareApplication','name'=>$name,'applicationCategory'=>'GameApplication','description'=>$desc,'url'=>$url]; if($img)$schema['image']=SITE_URL.'/'.ltrim($img,'/');
  return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.h($g['seoTitle']??($name.' APK & App Guide 2026 | YonoLootZone')).'</title><meta name="description" content="'.h($desc).'"><meta name="robots" content="index,follow,max-image-preview:large"><link rel="canonical" href="'.h($url).'"><meta property="og:type" content="website"><meta property="og:title" content="'.h($g['seoTitle']??$name).'">'.($img?'<meta property="og:image" content="'.h(SITE_URL.'/'.ltrim($img,'/')).'">':'').'<script type="application/ld+json">'.json_encode($schema,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'</script><link rel="stylesheet" href="../../assets/style.css"><style>.gwrap{max-width:1000px;margin:30px auto;padding:16px}.gcard{background:#fff;border:1px solid #ffd5e2;border-radius:20px;padding:28px}.ghead{display:flex;gap:20px;align-items:center}.ghead img{width:96px;height:96px;object-fit:cover;border-radius:20px}.gwrap p,.gwrap li{line-height:1.75;color:#334155}.btn{display:inline-block;background:#e00068;color:#fff!important;padding:13px 22px;border-radius:12px;text-decoration:none;font-weight:800;margin:12px 0}</style></head><body><main class="gwrap"><article class="gcard"><div class="ghead">'.($img?'<img src="'.h($img).'" alt="'.h($name).' APK">':'').'<div><h1>'.h($name).' APK</h1><p>'.h($cat).'</p></div></div><p>'.h($g['description']??$desc).'</p><a class="btn" href="'.h($link).'">View / Download</a><h2>About '.h($name).'</h2><p>'.h($desc).'</p><h2>Related Games</h2><ul>'.$relatedHtml.'</ul></article></main></body></html>';
}
