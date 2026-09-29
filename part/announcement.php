<?php
// =====================================================
// YONOLOOTZONE - ANNOUNCEMENT POPUP (SafeBetIn Style)
// =====================================================
?>
<style>
#ann-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.8);z-index:99999;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(6px);animation:annFadeIn .25s ease}
@keyframes annFadeIn{from{opacity:0}to{opacity:1}}
#ann-box{background:linear-gradient(180deg,#16162d 0%,#0e0e1f 100%);border-radius:22px;width:100%;max-width:440px;overflow:hidden;box-shadow:0 25px 70px rgba(0,0,0,0.8);border:2px solid rgba(255,39,140,0.4);animation:annSlideUp .3s ease;max-height:92vh;overflow-y:auto}
@keyframes annSlideUp{from{transform:translateY(25px);opacity:0}to{transform:translateY(0);opacity:1}}
.ann-head{background:linear-gradient(135deg,#1f1a3a,#0f3460);padding:12px 18px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,39,140,0.2)}
.ann-head h3{color:#fff;font-size:0.95rem;font-weight:900}
.ann-close-btn{background:rgba(255,255,255,0.1);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center}
.ann-body{padding:20px 18px 16px;text-align:center}
.ann-title{font-size:1.6rem;font-weight:900;color:#ffd700;text-shadow:0 0 20px rgba(255,215,0,0.5);letter-spacing:.05em;margin-bottom:4px}
.ann-sub{color:#fff;font-size:1rem;font-weight:800;margin-bottom:14px}
.ann-codes{background:rgba(0,0,0,0.4);border:1px solid rgba(255,215,0,0.3);border-radius:14px;padding:14px;text-align:left;margin-bottom:16px}
.ann-codes-title{color:#ffd700;font-size:0.75rem;font-weight:800;text-transform:uppercase;margin-bottom:8px}
.ann-code-row{display:flex;justify-content:space-between;align-items:center;background:rgba(255,255,255,0.06);border-radius:8px;padding:8px 10px;margin-bottom:6px}
.ann-code-name{color:#b0b8d1;font-size:0.8rem;font-weight:600}
.ann-code-btn{background:linear-gradient(90deg,#ff278c,#e00068);color:#fff;border-radius:6px;padding:3px 10px;font-size:0.78rem;font-weight:900;cursor:pointer}
.ann-tg-action{display:flex;align-items:center;justify-content:center;gap:8px;background:linear-gradient(90deg,#0088cc,#006aad);color:#fff!important;padding:12px;border-radius:12px;font-weight:800;font-size:0.95rem;margin-bottom:8px}
.ann-all-action{display:flex;align-items:center;justify-content:center;background:linear-gradient(90deg,#ff278c,#e00068);color:#fff!important;padding:10px;border-radius:12px;font-weight:800;font-size:0.88rem}
</style>

<div id="ann-overlay">
  <div id="ann-box">
    <div class="ann-head">
      <h3>📢 Official Announcement</h3>
      <button class="ann-close-btn" onclick="closeAnnPopup()">✕</button>
    </div>
    <div class="ann-body">
      <div class="ann-title">🔥 FREE PROMO CODES</div>
      <div class="ann-sub">🎁 Claim ₹550 Free Bonus on 72+ Games</div>
      <div class="ann-codes">
        <div class="ann-codes-title">🏷️ Active Bonus Codes</div>
        <div class="ann-code-row">
          <span class="ann-code-name">🃏 Rummy 91</span>
          <span class="ann-code-btn" onclick="copyAnnCode(this,'YONO550')">YONO550</span>
        </div>
        <div class="ann-code-row">
          <span class="ann-code-name">🎰 789 Jackpots</span>
          <span class="ann-code-btn" onclick="copyAnnCode(this,'JACKPOT550')">JACKPOT550</span>
        </div>
        <div class="ann-code-row">
          <span class="ann-code-name">💰 Yono Rummy</span>
          <span class="ann-code-btn" onclick="copyAnnCode(this,'YONORUMMY550')">YONORUMMY550</span>
        </div>
        <div class="ann-code-row">
          <span class="ann-code-name">🎮 All Games Universal</span>
          <span class="ann-code-btn" onclick="copyAnnCode(this,'YONO2026')">YONO2026</span>
        </div>
      </div>
      <a href="<?php echo defined('TELEGRAM_URL') ? TELEGRAM_URL : 'https://t.me/+jMDOLURgOQc1ODU1'; ?>" target="_blank" class="ann-tg-action" onclick="closeAnnPopup()">
        ✈ Join Telegram — Get Daily Free Codes
      </a>
      <a href="/all-games.html" class="ann-all-action" onclick="closeAnnPopup()">
        🎮 Explore All 72+ Games APK
      </a>
    </div>
  </div>
</div>

<script>
var isAnnClosed = false;
setTimeout(function(){
  if(!isAnnClosed){
    var overlay = document.getElementById('ann-overlay');
    if(overlay) overlay.style.display = 'flex';
  }
}, 1200);

function closeAnnPopup(){
  isAnnClosed = true;
  var overlay = document.getElementById('ann-overlay');
  if(overlay) overlay.style.display = 'none';
}

function copyAnnCode(el, code){
  if(navigator.clipboard) navigator.clipboard.writeText(code);
  var orig = el.textContent;
  el.textContent = '✅ Copied!';
  setTimeout(function(){ el.textContent = orig; }, 1800);
}

document.getElementById('ann-overlay').addEventListener('click', function(e){
  if(e.target === this) closeAnnPopup();
});
</script>
