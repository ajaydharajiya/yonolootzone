<?php
// =====================================================
// YONOLOOTZONE - NAVBAR COMPONENT
// =====================================================
?>
<header class="site-header">
  <div class="nav-container">
    <a href="/" class="brand">
      <span>YonoLootZone</span>
      <span class="brand-badge">2026</span>
    </a>
    <nav class="nav-links">
      <a href="/">Home</a>
      <a href="/all-games.html">All 72 Games</a>
      <a href="/rummy-games/">Rummy Games</a>
      <a href="/teen-patti-games/">Teen Patti</a>
      <a href="/news/">News & Bonuses</a>
      <a href="<?php echo defined('TELEGRAM_URL') ? TELEGRAM_URL : 'https://t.me/+jMDOLURgOQc1ODU1'; ?>" target="_blank" class="nav-tg-btn">
        ✈ Telegram
      </a>
    </nav>
  </div>
</header>
