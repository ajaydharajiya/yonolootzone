<?php
// =====================================================
// YONOLOOTZONE - COMMON HEADER
// =====================================================
if (!defined('SITE_URL')) {
    include_once(__DIR__ . '/config.php');
}

$page_title = isset($page_title) ? $page_title : 'All Yono Games 2026 - Download 72+ Yono Game APK, Yono Rummy & Claim ₹550 Bonus';
$page_desc = isset($page_desc) ? $page_desc : 'Download 72+ All Yono Games APK list 2026. Get ₹550 free sign-up bonus, instant UPI withdrawal proof, and download links for Yono Rummy, Rummy 91, 789 Jackpots on YonoLootZone.';
$page_keywords = isset($page_keywords) ? $page_keywords : 'all yono games, yono rummy, rummy 91, 789 jackpots, yono vip, yono 777, yono game apk download, yono games list 2026';
$page_canonical = isset($page_canonical) ? $page_canonical : SITE_URL . '/';
$page_image = isset($page_image) ? $page_image : SITE_URL . '/assets/all-yono-header.webp';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0" />
<title><?php echo htmlspecialchars($page_title); ?></title>
<meta name="description" content="<?php echo htmlspecialchars($page_desc); ?>" />
<meta name="keywords" content="<?php echo htmlspecialchars($page_keywords); ?>" />
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
<link rel="canonical" href="<?php echo htmlspecialchars($page_canonical); ?>" />

<!-- Open Graph / Social -->
<meta property="og:type" content="website" />
<meta property="og:site_name" content="YonoLootZone" />
<meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>" />
<meta property="og:description" content="<?php echo htmlspecialchars($page_desc); ?>" />
<meta property="og:url" content="<?php echo htmlspecialchars($page_canonical); ?>" />
<meta property="og:image" content="<?php echo htmlspecialchars($page_image); ?>" />

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>" />
<meta name="twitter:description" content="<?php echo htmlspecialchars($page_desc); ?>" />
<meta name="twitter:image" content="<?php echo htmlspecialchars($page_image); ?>" />

<link rel="icon" href="/assets/favicon.ico" sizes="any" />
<link rel="manifest" href="/manifest.json" />
<meta name="theme-color" content="#1a1a2e" />

<style>
/* Reset & Core Styling */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;background:#0d0d1a;color:#e6e6f0;line-height:1.5;scroll-behavior:smooth}
body{min-height:100vh;display:flex;flex-direction:column;background:linear-gradient(180deg,#0d0d1a 0%,#131326 100%)}
a{color:inherit;text-decoration:none}
img{max-width:100%;height:auto;display:block}

/* Header & Navbar */
.site-header{position:sticky;top:0;z-index:1000;background:rgba(13,13,26,0.95);backdrop-filter:blur(10px);border-bottom:1px solid rgba(255,255,255,0.08)}
.nav-container{max-width:1100px;margin:0 auto;padding:10px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px}
.brand{display:flex;align-items:center;gap:10px;font-weight:900;font-size:1.15rem;color:#fff}
.brand span{background:linear-gradient(90deg,#ff278c,#e00068);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.brand-badge{background:linear-gradient(90deg,#ff278c,#e00068);color:#fff;font-size:0.7rem;font-weight:800;padding:2px 8px;border-radius:20px}
.nav-links{display:flex;align-items:center;gap:16px;font-size:0.9rem;font-weight:600}
.nav-links a:hover{color:#ff278c}
.nav-tg-btn{background:linear-gradient(90deg,#0088cc,#006aad);color:#fff!important;padding:6px 14px;border-radius:20px;font-size:0.82rem;font-weight:700;display:flex;align-items:center;gap:6px}

/* Mobile Search Bar */
.search-wrap{max-width:1100px;margin:0 auto;padding:8px 16px 12px}
.search-input{width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.15);border-radius:12px;padding:12px 16px;color:#fff;font-size:0.95rem;outline:none;transition:border-color .2s}
.search-input:focus{border-color:#ff278c;background:rgba(255,255,255,0.1)}

/* Hero Banner */
.hero-banner{max-width:1100px;margin:12px auto;padding:0 16px}
.hero-card{background:linear-gradient(135deg,#1f1a3a 0%,#2a1638 50%,#181a2e 100%);border-radius:20px;padding:24px 20px;border:1px solid rgba(255,39,140,0.3);position:relative;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.4)}
.hero-card h1{font-size:clamp(1.3rem,4vw,2rem);font-weight:900;color:#fff;margin-bottom:8px;line-height:1.25}
.hero-card h1 span{background:linear-gradient(90deg,#ffd700,#ff9100);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.hero-card p{color:#b0b8d1;font-size:0.9rem;max-width:700px;margin-bottom:16px}
.hero-badges{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.hero-badge{background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);color:#ffd700;font-size:0.78rem;font-weight:700;padding:4px 12px;border-radius:20px}

/* Category Quick Navigation */
.cat-scroll{display:flex;gap:10px;overflow-x:auto;padding:4px 16px 14px;max-width:1100px;margin:0 auto;-webkit-overflow-scrolling:touch;scrollbar-width:none}
.cat-scroll::-webkit-scrollbar{display:none}
.cat-pill{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.12);padding:8px 16px;border-radius:25px;font-size:0.85rem;font-weight:700;white-space:nowrap;display:flex;align-items:center;gap:6px;transition:all .2s}
.cat-pill:hover,.cat-pill.active{background:linear-gradient(90deg,#ff278c,#e00068);border-color:#ff278c;color:#fff}

/* Game Cards Container */
.main-container{max-width:1100px;margin:0 auto;padding:12px 16px 40px;width:100%;flex:1}
.section-title-wrap{display:flex;justify-content:space-between;align-items:center;margin:20px 0 14px}
.section-title{font-size:1.25rem;font-weight:900;color:#fff;display:flex;align-items:center;gap:8px}
.section-title::before{content:"";width:4px;height:18px;background:linear-gradient(180deg,#ff278c,#e00068);border-radius:4px;display:inline-block}

/* Mobile & Desktop Cards */
.games-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:12px}
.game-card{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:16px;padding:12px 14px;display:flex;align-items:center;justify-content:space-between;gap:12px;transition:transform .2s,border-color .2s,box-shadow .2s}
.game-card:hover{transform:translateY(-2px);border-color:rgba(255,39,140,0.4);background:rgba(255,255,255,0.07);box-shadow:0 8px 20px rgba(0,0,0,0.3)}
.game-icon-wrap{position:relative;width:56px;height:56px;flex-shrink:0}
.game-icon{width:56px;height:56px;border-radius:12px;object-fit:cover;background:#1a1a2e}
.game-rank{position:absolute;top:-4px;left:-4px;background:linear-gradient(90deg,#ffd700,#ff9100);color:#000;font-size:0.65rem;font-weight:900;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center}
.game-info{flex:1;min-width:0}
.game-name{font-size:0.95rem;font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:3px}
.game-meta{display:flex;align-items:center;gap:8px;font-size:0.75rem;color:#8f98b2}
.game-bonus{color:#00e676;font-weight:800;background:rgba(0,230,118,0.1);padding:1px 6px;border-radius:4px}
.game-stars{color:#ffb300;font-size:0.75rem}
.game-actions{display:flex;align-items:center;gap:6px;flex-shrink:0}
.btn-download{background:linear-gradient(90deg,#ff278c,#e00068);color:#fff!important;font-size:0.82rem;font-weight:800;padding:8px 16px;border-radius:10px;text-align:center;box-shadow:0 4px 12px rgba(255,39,140,0.3);transition:opacity .2s}
.btn-download:hover{opacity:0.9}

/* Leaderboard Table */
.top-table-wrap{background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:16px;overflow:hidden;margin:24px 0}
.top-table{width:100%;border-collapse:collapse;font-size:0.88rem;text-align:left}
.top-table th{background:rgba(255,255,255,0.06);padding:12px 14px;color:#ffd700;font-weight:800}
.top-table td{padding:10px 14px;border-bottom:1px solid rgba(255,255,255,0.05)}
.top-table tr:hover{background:rgba(255,255,255,0.04)}

/* FAQ Accordion */
.faq-wrap{margin:28px 0}
.faq-item{background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:12px;margin-bottom:10px;overflow:hidden}
.faq-item summary{padding:14px 18px;font-weight:700;font-size:0.92rem;color:#fff;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center}
.faq-item summary::after{content:"+";font-size:1.2rem;color:#ff278c;font-weight:800}
.faq-item[open] summary::after{content:"−"}
.faq-ans{padding:0 18px 14px;color:#a0a8c2;font-size:0.88rem;line-height:1.6}

/* Footer */
.site-footer{background:#080811;border-top:1px solid rgba(255,255,255,0.06);padding:32px 16px 24px;color:#7a829e;font-size:0.82rem;margin-top:auto}
.footer-container{max-width:1100px;margin:0 auto}
.footer-links{display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px}
.footer-links a:hover{color:#ff278c}
.footer-disclaimer{line-height:1.6;font-size:0.76rem;color:#5a617a;margin-top:12px}

@media(max-width:600px){
  .nav-links{display:none}
  .games-grid{grid-template-columns:1fr}
  .hero-card{padding:18px 14px}
}
</style>
</head>
<body>
