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
function normalize_site_url(string $url): string {
  $url = trim($url);
  if ($url === '') return SITE_URL . '/';
  if (str_starts_with($url, '/')) $url = SITE_URL . $url;
  if (!preg_match('~^https?://~i', $url)) $url = SITE_URL . '/' . ltrim($url, '/');
  $parts = parse_url($url);
  if (!$parts || empty($parts['host'])) return SITE_URL . '/';
  $scheme = strtolower($parts['scheme'] ?? 'https');
  $host = strtolower($parts['host']);
  $path = preg_replace('~/+~', '/', $parts['path'] ?? '/');
  $path = preg_replace('~/index\.html$~i', '/', $path);
  if ($path !== '/' && !str_ends_with($path, '/') && !preg_match('~\.[a-z0-9]{2,8}$~i', $path)) $path .= '/';
  $out = $scheme . '://' . $host . $path;
  if (!empty($parts['query'])) $out .= '?' . $parts['query'];
  return $out;
}
function game_content_quality_ok(array $g): bool {
  $desc=trim((string)($g['description']??''));
  $features=array_values(array_filter((array)($g['features']??[])));
  $faq=array_values(array_filter((array)($g['faq']??[])));
  return mb_strlen($desc)>=180 || count($features)>=3 || count($faq)>=2;
}
function game_page_url(array $g): string {
  $slug = slugify((string)($g['slug'] ?? $g['name'] ?? 'game'));
  return SITE_URL . '/games/' . $slug . '/';
}
function canonical_from_file(string $file): ?string {
  $html = @file_get_contents($file);
  if ($html === false) return null;
  if (preg_match('~<meta[^>]+name=["\']robots["\'][^>]+content=["\']([^"\']+)["\']~i', $html, $rm) && preg_match('~\bnoindex\b~i', $rm[1])) return null;
  if (preg_match('~<link[^>]+rel=["\']canonical["\'][^>]+href=["\']([^"\']+)["\']~i', $html, $m)) return normalize_site_url(html_entity_decode($m[1], ENT_QUOTES));
  if (preg_match('~<link[^>]+href=["\']([^"\']+)["\'][^>]+rel=["\']canonical["\']~i', $html, $m)) return normalize_site_url(html_entity_decode($m[1], ENT_QUOTES));
  return null;
}
function rebuild_sitemap(): void {
  $urls = [];
  $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(SITE_ROOT, FilesystemIterator::SKIP_DOTS));
  foreach ($it as $file) {
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'html') continue;
    $path = $file->getPathname();
    $rel = str_replace('\\','/', ltrim(str_replace(SITE_ROOT, '', $path), '/'));
    if (str_starts_with($rel, 'admin/') || str_starts_with($rel, 'cms/') || $rel === '404.html') continue;
    $canonical = canonical_from_file($path);
    if (!$canonical || !str_starts_with($canonical, SITE_URL . '/') && $canonical !== SITE_URL . '/') continue;
    // Only include a page when its canonical points to itself.
    $expected = SITE_URL . '/' . $rel;
    if ($rel === 'index.html') $expected = SITE_URL . '/';
    elseif (str_ends_with($rel, '/index.html')) $expected = SITE_URL . '/' . substr($rel, 0, -strlen('index.html'));
    $expected = normalize_site_url($expected);
    if ($canonical !== $expected) continue;
    $urls[$canonical] = $file->getMTime();
  }
  ksort($urls, SORT_STRING);
  $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
  foreach ($urls as $url=>$mtime) {
    $lastmod = date('Y-m-d', $mtime ?: time());
    $freq = ($url === SITE_URL . '/' || str_contains($url, '/posts/') || str_contains($url, '/news/')) ? 'daily' : 'weekly';
    $xml .= "  <url><loc>" . h($url) . "</loc><lastmod>" . $lastmod . "</lastmod><changefreq>" . $freq . "</changefreq></url>\n";
  }
  $xml .= "</urlset>\n";
  file_put_contents(SITE_ROOT.'/sitemap.xml', $xml, LOCK_EX);
}
function update_sitemap(string $url): void { rebuild_sitemap(); }
function update_latest_post(array $posts): void {
  if (!$posts) { @unlink(SITE_ROOT.'/posts/latest.json'); return; }
  usort($posts, fn($a,$b)=>strcmp(($b['date']??'').' '.($b['slug']??''), ($a['date']??'').' '.($a['slug']??'')));
  $p=$posts[0];
  $latest=['id'=>($p['slug']??'').'|'.($p['date']??''),'title'=>$p['title']??'','slug'=>$p['slug']??'','date'=>$p['date']??'','type'=>$p['type']??'Article','excerpt'=>$p['excerpt']??($p['description']??''),'description'=>$p['description']??'','image'=>$p['image']??'','imageAlt'=>$p['imageAlt']??''];
  if(!is_dir(SITE_ROOT.'/posts')) mkdir(SITE_ROOT.'/posts',0755,true); write_json(SITE_ROOT.'/posts/latest.json',$latest);
}
function update_posts_index(array $posts): void {
  usort($posts, fn($a,$b)=>strcmp($b['date']??'', $a['date']??''));
  $cards=''; foreach($posts as $p){ $url=SITE_URL.'/posts/'.slugify($p['slug']??'').'/'; $img=$p['image']??''; $cards.='<article class="post-card"><a href="'.h($url).'">'.($img?'<img src="'.h($img).'" alt="'.h($p['imageAlt']??$p['title']??'Post').'" loading="lazy">':'').'<div><small>'.h($p['date']??'').' · '.h($p['type']??'Article').'</small><h2>'.h($p['title']??'').'</h2><p>'.h($p['excerpt']??$p['description']??'').'</p><span>Read update →</span></div></a></article>'; }
  $html='<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>YonoLootZone — Latest Posts & Updates</title><meta name="description" content="Latest Yono Game updates, guides, new game announcements, images and videos from YonoLootZone."><meta name="robots" content="index,follow,max-image-preview:large"><link rel="canonical" href="'.SITE_URL.'/posts/"><link rel="stylesheet" href="../assets/style.css"><style>.posts-wrap{max-width:1100px;margin:30px auto;padding:0 16px}.post-card{background:#fff;border:1px solid #ffd5e2;border-radius:18px;margin:16px 0;overflow:hidden}.post-card a{display:flex;gap:18px;padding:16px;text-decoration:none;color:inherit}.post-card img{width:180px;height:110px;object-fit:cover;border-radius:12px}.post-card h2{margin:6px 0;font-size:21px}.post-card p{color:#475569;line-height:1.6}.post-card small{color:#64748b}@media(max-width:650px){.post-card a{display:block}.post-card img{width:100%;height:190px}}</style></head><body><main class="posts-wrap"><h1>Latest Yono Game Updates & Guides</h1><p>Fresh articles, game updates, guides and announcements published by YonoLootZone.</p>'.$cards.'</main></body></html>';
  if(!is_dir(SITE_ROOT.'/posts')) mkdir(SITE_ROOT.'/posts',0755,true); file_put_contents(SITE_ROOT.'/posts/index.html',$html,LOCK_EX);
}
function public_asset_url(string $path): string {
  $path = trim($path);
  if ($path === '') return '';
  if (preg_match('~^https?://~i', $path)) return $path;
  return SITE_URL . '/' . ltrim($path, '/');
}
function render_post(array $p): string {
  $title=$p['title']; $desc=$p['description']??$p['excerpt']??''; $url=SITE_URL.'/posts/'.slugify($p['slug']).'/'; $img=trim((string)($p['image']??'')); $imgUrl=public_asset_url($img); $body=$p['content']??''; $video=$p['video']??''; $videoHtml='';
  if($video){ $embed=$video; if(preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/shorts/)([A-Za-z0-9_-]{6,})~',$video,$m)) $embed='https://www.youtube.com/embed/'.$m[1]; if(str_ends_with(strtolower(parse_url($video,PHP_URL_PATH)??''),'.mp4')) $videoHtml='<video controls preload="metadata" style="width:100%;border-radius:14px"><source src="'.h($video).'">Your browser does not support video.</video>'; else $videoHtml='<div style="aspect-ratio:16/9"><iframe src="'.h($embed).'" title="'.h($title).' video" loading="lazy" allowfullscreen style="width:100%;height:100%;border:0;border-radius:14px"></iframe></div>'; }
  $gameHtml=''; if(!empty($p['gameName']) && !empty($p['gameSlug'])){ $gameSlug=slugify($p['gameSlug']); $gameHtml='<section class="game-box"><h2>'.h($p['gameName']).'</h2>'.(!empty($p['gameImage'])?'<img src="'.h($p['gameImage']).'" alt="'.h($p['gameName']).'" loading="lazy">':'').'<p>Read the dedicated game page for details, screenshots and available information.</p><a class="game-btn" href="'.h(SITE_URL.'/games/'.$gameSlug.'/').'">View '.h($p['gameName']).' Game Page →</a></section>'; }
  $promoHtml=''; if(!empty($p['promoEnabled']) && !empty($p['promoCode'])){ $promoHtml='<section class="promo-box"><h2>🎁 '.h($p['gameName']?($p['gameName'].' Promo Code'):'Promo Code Update').'</h2><div class="promo-code">'.h($p['promoCode']).'</div>'.(!empty($p['promoReward'])?'<p><strong>Offer:</strong> '.h($p['promoReward']).'</p>':'').(!empty($p['promoMinDeposit'])?'<p><strong>Minimum Deposit:</strong> '.h($p['promoMinDeposit']).'</p>':'').(!empty($p['promoExpiry'])?'<p><strong>Expiry:</strong> '.h($p['promoExpiry']).'</p>':'').(!empty($p['promoTerms'])?'<p><strong>Terms:</strong> '.nl2br(h($p['promoTerms'])).'</p>':'').'<p class="promo-note">Check the offer inside the game/app before using it. Availability and eligibility may vary by account.</p></section>'; }
  $schema=['@context'=>'https://schema.org','@type'=>'Article','headline'=>$title,'description'=>$desc,'datePublished'=>$p['date']??date('Y-m-d'),'dateModified'=>$p['date']??date('Y-m-d'),'author'=>['@type'=>'Person','name'=>$p['author']??'YonoLootZone'],'publisher'=>['@type'=>'Organization','name'=>'YonoLootZone','url'=>SITE_URL.'/'],'mainEntityOfPage'=>['@type'=>'WebPage','@id'=>$url]]; if($imgUrl) $schema['image']=$imgUrl;
  return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.h($title).'</title><meta name="description" content="'.h($desc).'"><meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1"><link rel="canonical" href="'.h($url).'">'.($imgUrl?'<meta property="og:image" content="'.h($imgUrl).'">':'').'<meta property="og:type" content="article"><meta property="og:title" content="'.h($title).'"> <meta property="og:description" content="'.h($desc).'"> <meta property="og:url" content="'.h($url).'"> <meta name="twitter:card" content="summary_large_image"><script type="application/ld+json">'.json_encode($schema,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'</script><link rel="stylesheet" href="../../assets/style.css"><style>.post-wrap{max-width:900px;margin:30px auto;padding:0 16px}.post-wrap article{background:#fff;border:1px solid #ffd5e2;border-radius:20px;padding:28px}.post-wrap img.hero{width:100%;max-height:480px;object-fit:cover;border-radius:16px;margin:15px 0}.post-content{font-size:16px;line-height:1.8;color:#334155}.post-content h2{margin-top:28px}.game-box,.promo-box{margin-top:28px;padding:22px;border-radius:18px;background:#fff7fb;border:1px solid #ffd1e2}.game-box img{width:82px;height:82px;object-fit:cover;border-radius:16px;display:block;margin:10px 0}.game-btn{display:inline-block;background:#e00068;color:#fff!important;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:800}.promo-code{display:inline-block;font-size:24px;font-weight:900;letter-spacing:1px;background:#fff;padding:12px 18px;border:2px dashed #e00068;border-radius:12px;margin:8px 0}.promo-note{font-size:13px;color:#64748b}</style></head><body><main class="post-wrap"><article><p><a href="/posts/">← All posts</a></p><h1>'.h($title).'</h1><p><small>'.h($p['date']??'').' · '.h($p['author']??'YonoLootZone').'</small></p>'.($imgUrl?'<img class="hero" src="'.h($imgUrl).'" alt="'.h($p['imageAlt']??$title).'">':'').'<p><strong>'.h($p['excerpt']??'').'</strong></p><div class="post-content">'.$body.'</div>'.$gameHtml.$promoHtml.($video?'<section><h2>Video</h2>'.$videoHtml.'</section>':'').'</article></main></body></html>';
}
function render_game(array $g): string {
  $name=trim((string)($g['name']??'Game')); $slug=slugify((string)($g['slug']??$name)); $url=SITE_URL.'/games/'.$slug.'/';
  $desc=trim((string)($g['description']??'')); $meta=trim((string)($g['metaDescription']??'')); $img=trim((string)($g['image']??'')); $imgUrl=public_asset_url($img);
  $cat=trim((string)($g['category']??'Game')); $link=trim((string)($g['url']??''));
  if($desc==='') $desc=$meta ?: 'Information, access details and app guidance for '. $name .' on YonoLootZone.';
  $title=trim((string)($g['seoTitle']??'')) ?: $name.' APK & App Guide | YonoLootZone';
  $reviewed=trim((string)($g['lastReviewed']??'')); $version=trim((string)($g['appVersion']??'')); $developer=trim((string)($g['developer']??'')); $platform=trim((string)($g['platform']??'')); $os=trim((string)($g['os']??'')); $size=trim((string)($g['size']??'')); $language=trim((string)($g['language']??''));
  $requirements=array_values(array_filter(array_map('trim',(array)($g['requirements']??[]))));
  $features=array_values(array_filter(array_map('trim',(array)($g['features']??[]))));
  $screenshots=array_values(array_filter(array_map('public_asset_url',(array)($g['screenshots']??[]))));
  $faq=array_values(array_filter((array)($g['faq']??[]),fn($x)=>is_array($x)&&trim((string)($x['q']??''))!==''&&trim((string)($x['a']??''))!==''));
  $safety=array_values(array_filter(array_map('trim',(array)($g['safetyNotes']??[]))));
  $sourceNote=trim((string)($g['sourceNote']??''));
  $games=read_json(SITE_ROOT.'/data/games.json',[]); $related=[];
  foreach($games as $r){ if(($r['slug']??'')!==$slug && !empty($r['status']) && (($r['category']??'')===$cat)) $related[]=$r; }
  if(count($related)<6) foreach($games as $r){ if(($r['slug']??'')!==$slug && !empty($r['status']) && !in_array($r,$related,true)) $related[]=$r; }
  usort($related,fn($a,$b)=>strcmp($a['name']??'',$b['name']??'')); $related=array_slice($related,0,8);
  $relatedHtml=''; foreach($related as $r){$rs=slugify($r['slug']??$r['name']??'game'); $relatedHtml.='<li><a href="'.h(SITE_URL.'/games/'.$rs.'/').'">'.h($r['name']??'Related game').'</a></li>';}
  $details=[];
  foreach([['Category',$cat],['Platform',$platform],['Android / OS',$os],['Version',$version],['App size',$size],['Language',$language],['Developer',$developer],['Last reviewed',$reviewed]] as $d){ if($d[1]!=='') $details[]='<tr><th>'.h($d[0]).'</th><td>'.h($d[1]).'</td></tr>'; }
  $detailsHtml=$details?'<section><h2>App details</h2><div class="table-wrap"><table><tbody>'.implode('',$details).'</tbody></table></div></section>':'';
  $featuresHtml=$features?'<section><h2>Key features</h2><ul>'.implode('',array_map(fn($x)=>'<li>'.h($x).'</li>',$features)).'</ul></section>':'';
  $reqHtml=$requirements?'<section><h2>System requirements</h2><ul>'.implode('',array_map(fn($x)=>'<li>'.h($x).'</li>',$requirements)).'</ul></section>':'';
  $shotsHtml=$screenshots?'<section><h2>Screenshots</h2><div class="shots">'.implode('',array_map(fn($x)=>'<img src="'.h($x).'" alt="'.h($name).' screenshot" loading="lazy">',$screenshots)).'</div></section>':'';
  $faqHtml=''; $faqSchema=[];
  foreach($faq as $f){$q=trim((string)$f['q']);$a=trim((string)$f['a']);$faqHtml.='<details><summary>'.h($q).'</summary><p>'.nl2br(h($a)).'</p></details>'; $faqSchema[]=['@type'=>'Question','name'=>$q,'acceptedAnswer'=>['@type'=>'Answer','text'=>$a]];}
  if($faqHtml) $faqHtml='<section><h2>Frequently asked questions</h2>'.$faqHtml.'</section>';
  $safetyHtml=$safety?'<section class="notice"><h2>Safety &amp; verification checklist</h2><ul>'.implode('',array_map(fn($x)=>'<li>'.h($x).'</li>',$safety)).'</ul></section>':'<section class="notice"><h2>Before installing or registering</h2><p>Check the publisher, app permissions, privacy policy, age requirements, payment terms and availability. YonoLootZone does not operate the third-party apps listed here.</p></section>';
  $button=$link!==''?'<a class="btn" href="'.h($link).'" rel="nofollow sponsored noopener" target="_blank">'.h($g['button']??'Visit Game').'</a>':'<p><strong>Access link:</strong> No external link is currently listed.</p>';
  $safeImg=$imgUrl!==''?'<img src="'.h($imgUrl).'" alt="'.h($name).' game icon" loading="eager" width="112" height="112">':'';
  $breadcrumb=['@context'=>'https://schema.org','@type'=>'BreadcrumbList','itemListElement'=>[['@type'=>'ListItem','position'=>1,'name'=>'Home','item'=>SITE_URL.'/'],['@type'=>'ListItem','position'=>2,'name'=>'All Games','item'=>SITE_URL.'/all-yono-games.html'],['@type'=>'ListItem','position'=>3,'name'=>$name,'item'=>$url]]];
  $schema=['@context'=>'https://schema.org','@type'=>'WebPage','name'=>$title,'description'=>$meta?:$desc,'url'=>$url,'dateModified'=>$reviewed?:date('Y-m-d'),'isPartOf'=>['@type'=>'WebSite','name'=>'YonoLootZone','url'=>SITE_URL.'/']];
  $scripts='<script type="application/ld+json">'.json_encode($schema,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'</script><script type="application/ld+json">'.json_encode($breadcrumb,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'</script>';
  if($faqSchema) $scripts.='<script type="application/ld+json">'.json_encode(['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>$faqSchema],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'</script>';
  return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.h($title).'</title><meta name="description" content="'.h($meta ?: $desc).'"> <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1"><link rel="canonical" href="'.h($url).'"> <meta property="og:type" content="website"><meta property="og:site_name" content="YonoLootZone"><meta property="og:title" content="'.h($title).'"> <meta property="og:description" content="'.h($meta ?: $desc).'"> <meta property="og:url" content="'.h($url).'">'.($imgUrl?'<meta property="og:image" content="'.h($imgUrl).'">':'').'<meta name="twitter:card" content="summary_large_image">'.$scripts.'<link rel="stylesheet" href="../../assets/style.css"><style>.gwrap{max-width:1000px;margin:30px auto;padding:16px}.gcard{background:#fff;border:1px solid #ffd5e2;border-radius:20px;padding:28px}.crumbs{font-size:14px;margin-bottom:18px}.ghead{display:flex;gap:20px;align-items:center}.ghead img{width:112px;height:112px;object-fit:cover;border-radius:22px}.gwrap p,.gwrap li{line-height:1.75;color:#334155}.btn{display:inline-block;background:#e00068;color:#fff!important;padding:13px 22px;border-radius:12px;text-decoration:none;font-weight:800;margin:12px 0}.notice{padding:18px;background:#fff7fb;border:1px solid #ffd1e2;border-radius:14px}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:10px 12px;border-bottom:1px solid #f1dce5}th{width:35%;font-weight:800}.shots{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}.shots img{width:100%;aspect-ratio:16/10;object-fit:cover;border-radius:14px;border:1px solid #f1dce5}details{padding:14px 0;border-bottom:1px solid #f1dce5}summary{cursor:pointer;font-weight:800}h2{margin-top:28px}@media(max-width:650px){.ghead{align-items:flex-start}.shots{grid-template-columns:1fr 1fr}}</style></head><body><main class="gwrap"><article class="gcard"><nav class="crumbs"><a href="/">Home</a> → <a href="/all-yono-games.html">All Games</a> → '.h($name).'</nav><div class="ghead">'.$safeImg.'<div><h1>'.h($name).' APK &amp; App Guide</h1><p>'.h($cat).'</p></div></div><p>'.h($desc).'</p>'.$button.$detailsHtml.$featuresHtml.$reqHtml.$shotsHtml.'<section><h2>How to install or access '.h($name).'</h2><ol><li>Review the app information and current terms on this page.</li><li>Use the listed access link only after checking the destination and publisher details.</li><li>If installing an APK, review Android security prompts and requested permissions before continuing.</li><li>Do not share passwords, OTPs, UPI PINs or banking credentials with anyone claiming to provide support.</li></ol></section>'.$safetyHtml.($sourceNote?'<section><h2>Source &amp; editorial note</h2><p>'.h($sourceNote).'</p></section>':'').$faqHtml.'<section><h2>Related '.h($cat).' games</h2><ul>'.$relatedHtml.'</ul></section></article></main></body></html>';
}
