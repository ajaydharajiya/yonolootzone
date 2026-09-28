<?php
// =====================================================
// YONOLOOTZONE - ALL GAMES DIRECTORY (MODULAR PHP)
// =====================================================
$page_title = 'All Yono Games Directory 2026 - Complete 72+ App List & APK Download | YonoLootZone';
$page_desc = 'Browse the complete verified directory of all 72 Yono game apps in 2026. Compare bonuses, ratings, file sizes, and download official APK files directly.';
$page_keywords = 'all yono games list, yono games directory, all yono rummy apps, 72 yono games, yono games apk download 2026';
$page_canonical = 'https://yonolootzone.com/all-games.html';

include_once(__DIR__ . '/part/header.php');
include_once(__DIR__ . '/part/navbar.php');
include_once(__DIR__ . '/part/announcement.php');
?>

<div class="hero-banner">
  <div class="hero-card">
    <h1>All Yono Games Directory 2026 – <span>Complete 72+ App List</span></h1>
    <p>Compare sign-up bonuses, file sizes, ratings, and download official APK files directly from trusted CDN servers.</p>
    <div class="hero-badges">
      <span class="hero-badge">🎮 72 Verified Games</span>
      <span class="hero-badge">🎁 ₹550 Free Bonus</span>
      <span class="hero-badge">⚡ Instant UPI Withdrawal</span>
    </div>
  </div>
</div>

<div class="search-wrap">
  <input type="text" id="dirSearch" class="search-input" placeholder="🔍 Search any of the 72+ Yono games..." onkeyup="filterDirectory(this.value)" />
</div>

<main class="main-container">
  <div class="games-grid" id="dirGrid">
    <?php foreach ($all_games as $index => $game): ?>
      <?php 
        $rank = $index + 1;
        $bonus = !empty($game['bonus']) ? $game['bonus'] : '₹550 Bonus';
        $rating = !empty($game['rating']) ? $game['rating'] : '4.8';
        $icon = !empty($game['icon']) ? $game['icon'] : '/assets/default-game.webp';
        $page = !empty($game['page']) ? $game['page'] : '/all-games.html';
      ?>
      <article class="game-card" data-name="<?php echo htmlspecialchars(strtolower($game['name'])); ?>">
        <div class="game-icon-wrap">
          <img src="<?php echo htmlspecialchars($icon); ?>" alt="<?php echo htmlspecialchars($game['name']); ?> APK" class="game-icon" loading="lazy" onerror="this.src='/assets/default-game.webp'" />
          <span class="game-rank"><?php echo $rank; ?></span>
        </div>
        <div class="game-info">
          <h3 class="game-name"><a href="<?php echo htmlspecialchars($page); ?>"><?php echo htmlspecialchars($game['name']); ?></a></h3>
          <div class="game-meta">
            <span class="game-bonus"><?php echo htmlspecialchars($bonus); ?></span>
            <span class="game-stars">★ <?php echo htmlspecialchars($rating); ?></span>
          </div>
        </div>
        <div class="game-actions">
          <a href="<?php echo htmlspecialchars($page); ?>" class="btn-download">Download</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</main>

<script>
function filterDirectory(query) {
  var q = query.toLowerCase().trim();
  var cards = document.querySelectorAll('#dirGrid .game-card');
  cards.forEach(function(card){
    var name = card.getAttribute('data-name') || '';
    card.style.display = name.includes(q) ? 'flex' : 'none';
  });
}
</script>

<?php
include_once(__DIR__ . '/part/footer.php');
?>
