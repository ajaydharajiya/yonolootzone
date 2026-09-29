<?php
// =====================================================
// YONOLOOTZONE - GLOBAL CONFIGURATION
// =====================================================

define('SITE_NAME', 'YonoLootZone');
define('SITE_URL', 'https://yonolootzone.com');
define('TELEGRAM_URL', 'https://t.me/+jMDOLURgOQc1ODU1');
define('SITE_VERSION', '2026.3');
define('DEFAULT_BONUS', '₹550');

// Load Games Data
$games_json_file = __DIR__ . '/../data/games.json';
$all_games = [];
if (file_exists($games_json_file)) {
    $all_games = json_decode(file_get_contents($games_json_file), true) ?: [];
}
?>