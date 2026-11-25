/**
 * YonoLootZone Core Engine - 2026
 * Multi-Theme, LocalStorage Favorites & Recents, Live Search, Compare, AI Assistant
 */

(function () {
  'use strict';

  // --- 1. STATE & STORAGE KEYS ---
  const STORAGE_KEYS = {
    THEME: 'yono_theme',
    FAVORITES: 'yono_favorites',
    RECENT: 'yono_recent_views',
    SEARCH_HISTORY: 'yono_search_history',
    ANNOUNCEMENT: 'yono_banner_dismissed'
  };

  window.YonoApp = {
    games: [],
    favorites: [],
    recentViews: [],
    compareList: [],
    currentTheme: 'light'
  };

  // --- 2. THEME ENGINE ---
  function initTheme() {
    const savedTheme = localStorage.getItem(STORAGE_KEYS.THEME) || 'light';
    setTheme(savedTheme, false);

    document.querySelectorAll('.theme-toggle-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const nextTheme = YonoApp.currentTheme === 'light' ? 'dark' : (YonoApp.currentTheme === 'dark' ? 'eye' : 'light');
        setTheme(nextTheme, true);
      });
    });
  }

  function setTheme(theme, showToastMsg) {
    if (!['light', 'dark', 'eye'].includes(theme)) theme = 'light';
    YonoApp.currentTheme = theme;
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem(STORAGE_KEYS.THEME, theme);

    // Update icons
    const icons = {
      light: '<i class="fas fa-moon"></i>',
      dark: '<i class="fas fa-glasses"></i>',
      eye: '<i class="fas fa-sun"></i>'
    };
    const labels = {
      light: 'Dark Mode',
      dark: 'Eye Care Mode',
      eye: 'Light Mode'
    };
    document.querySelectorAll('.theme-toggle-btn').forEach(btn => {
      btn.innerHTML = icons[theme] || icons.light;
      btn.setAttribute('title', 'Switch to ' + (labels[theme] || 'theme'));
    });

    if (showToastMsg) {
      showToast(`Switched to ${theme.toUpperCase()} mode`, '🎨');
    }
  }

  // --- 3. TOAST SYSTEM ---
  function showToast(message, icon = '✨') {
    let container = document.getElementById('yono-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'yono-toast-container';
      container.className = 'yono-toast-container';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'yono-toast';
    toast.innerHTML = `<span style="font-size:18px;">${icon}</span> <span>${message}</span>`;
    container.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add('show'));

    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 300);
    }, 2800);
  }

  // --- 4. FAVORITES SYSTEM (localStorage, No Login Required) ---
  function getFavorites() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEYS.FAVORITES)) || [];
    } catch (e) {
      return [];
    }
  }

  function saveFavorites(list) {
    localStorage.setItem(STORAGE_KEYS.FAVORITES, JSON.stringify(list));
    YonoApp.favorites = list;
    updateFavBadges();
    updateHeartIcons();
  }

  function toggleFavorite(slug, name, img, cat) {
    let favs = getFavorites();
    const index = favs.findIndex(item => (typeof item === 'string' ? item === slug : item.slug === slug));

    if (index > -1) {
      favs.splice(index, 1);
      saveFavorites(favs);
      showToast(`Removed "${name || slug}" from Favorites`, '💔');
    } else {
      favs.push({ slug, name, img, cat, addedAt: Date.now() });
      saveFavorites(favs);
      showToast(`Saved "${name || slug}" to Favorites!`, '❤️');
    }
  }

  function isFavorite(slug) {
    return getFavorites().some(item => (typeof item === 'string' ? item === slug : item.slug === slug));
  }

  function updateFavBadges() {
    const count = getFavorites().length;
    document.querySelectorAll('.fav-count-badge').forEach(el => {
      el.textContent = count;
      el.style.display = count > 0 ? 'inline-flex' : 'none';
    });
  }

  function updateHeartIcons() {
    document.querySelectorAll('.fav-btn').forEach(btn => {
      const slug = btn.getAttribute('data-slug');
      if (slug && isFavorite(slug)) {
        btn.classList.add('active');
        btn.innerHTML = '<i class="fas fa-heart"></i>';
      } else {
        btn.classList.remove('active');
        btn.innerHTML = '<i class="far fa-heart"></i>';
      }
    });
  }

  // --- 5. RECENTLY VIEWED (localStorage) ---
  function trackGameView(slug, name, img, cat) {
    if (!slug) return;
    try {
      let recent = JSON.parse(localStorage.getItem(STORAGE_KEYS.RECENT)) || [];
      recent = recent.filter(item => item.slug !== slug);
      recent.unshift({ slug, name, img, cat, viewedAt: Date.now() });
      if (recent.length > 10) recent = recent.slice(0, 10);
      localStorage.setItem(STORAGE_KEYS.RECENT, JSON.stringify(recent));
    } catch (e) {}
  }

  function getRecentViews() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEYS.RECENT)) || [];
    } catch (e) {
      return [];
    }
  }

  function clearRecentViews() {
    localStorage.removeItem(STORAGE_KEYS.RECENT);
    showToast('Recent history cleared', '🧹');
    renderRecentViewsSection();
  }

  function renderRecentViewsSection() {
    const container = document.getElementById('recent-views-container');
    if (!container) return;

    const list = getRecentViews();
    if (!list.length) {
      container.innerHTML = `
        <div style="text-align:center; padding:30px; color:var(--text-muted); font-size:14px; grid-column: 1/-1;">
          <i class="far fa-clock" style="font-size:32px; margin-bottom:8px; display:block; opacity:0.5;"></i>
          No recently viewed games yet. Browse games to see your history here!
        </div>
      `;
      return;
    }

    container.innerHTML = list.map(g => `
      <div class="yono-card" style="padding:12px;">
        <div class="card-thumb-wrap" style="width:64px; height:64px; margin-bottom:8px;">
          <img src="${g.img || 'assets/all-yono-header.webp'}" alt="${g.name}" class="card-thumb" onerror="this.src='assets/all-yono-header.webp'">
        </div>
        <div class="card-title" style="font-size:13px;">${g.name}</div>
        <div class="card-category" style="font-size:10px; margin-bottom:8px;">${g.cat || 'Games'}</div>
        <a href="games/${g.slug}/" class="btn-yono-outline" style="padding:6px 12px; font-size:12px;">View</a>
      </div>
    `).join('');
  }

  // --- 6. GAME COMPARISON DOCK & MODAL ---
  function toggleCompare(game) {
    const index = YonoApp.compareList.findIndex(g => g.slug === game.slug);
    if (index > -1) {
      YonoApp.compareList.splice(index, 1);
      showToast(`Removed from comparison`, '➖');
    } else {
      if (YonoApp.compareList.length >= 3) {
        showToast('You can compare at most 3 games at once!', '⚠️');
        return;
      }
      YonoApp.compareList.push(game);
      showToast(`Added "${game.name}" to Compare (${YonoApp.compareList.length}/3)`, '⚖️');
    }
    updateCompareDock();
    updateCompareCheckboxes();
  }

  function updateCompareCheckboxes() {
    document.querySelectorAll('.compare-check').forEach(input => {
      const slug = input.getAttribute('data-slug');
      input.checked = YonoApp.compareList.some(g => g.slug === slug);
    });
  }

  function updateCompareDock() {
    let dock = document.getElementById('comparison-dock');
    if (!dock) {
      dock = document.createElement('div');
      dock.id = 'comparison-dock';
      dock.className = 'comparison-dock';
      document.body.appendChild(dock);
    }

    const count = YonoApp.compareList.length;
    if (count === 0) {
      dock.classList.remove('visible');
      return;
    }

    dock.classList.add('visible');
    dock.innerHTML = `
      <div style="font-weight:700; font-size:13px; color:var(--text-main); display:flex; align-items:center; gap:8px;">
        <span style="background:var(--primary); color:#fff; width:22px; height:22px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:11px;">${count}</span>
        Compare Games
      </div>
      <button class="btn-yono-primary" style="padding:6px 16px; font-size:12px;" onclick="YonoApp.openCompareModal()">Compare Now</button>
      <button class="btn-icon" style="width:28px; height:28px; border-radius:50%; font-size:12px;" onclick="YonoApp.clearCompare()" title="Clear">✕</button>
    `;
  }

  function openCompareModal() {
    if (!YonoApp.compareList.length) return;

    let modal = document.getElementById('compare-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'compare-modal';
      modal.className = 'search-modal-backdrop';
      document.body.appendChild(modal);
    }

    const games = YonoApp.compareList;
    modal.innerHTML = `
      <div class="search-modal-box" style="max-width:850px; padding:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
          <h3 style="margin:0; font-size:20px; font-weight:800; color:var(--text-main); display:flex; align-items:center; gap:8px;">
            <i class="fas fa-balance-scale" style="color:var(--primary);"></i> Side-by-Side Game Comparison
          </h3>
          <button class="btn-icon" onclick="document.getElementById('compare-modal').classList.remove('open')">✕</button>
        </div>

        <div style="overflow-x:auto;">
          <table style="width:100%; border-collapse:collapse; font-size:13px; color:var(--text-main);">
            <thead>
              <tr style="border-bottom:2px solid var(--border);">
                <th style="padding:12px; text-align:left; width:25%;">Feature</th>
                ${games.map(g => `
                  <th style="padding:12px; text-align:center; width:${75 / games.length}%;">
                    <img src="${g.image || 'assets/all-yono-header.webp'}" style="width:48px; height:48px; border-radius:12px; object-fit:cover; margin-bottom:6px;">
                    <div style="font-weight:700;">${g.name}</div>
                  </th>
                `).join('')}
              </tr>
            </thead>
            <tbody>
              <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:10px; font-weight:600; color:var(--text-muted);">Category</td>
                ${games.map(g => `<td style="padding:10px; text-align:center;"><span class="card-category">${g.category || 'Games'}</span></td>`).join('')}
              </tr>
              <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:10px; font-weight:600; color:var(--text-muted);">Platform</td>
                ${games.map(g => `<td style="padding:10px; text-align:center;">${g.platform || 'Android'}</td>`).join('')}
              </tr>
              <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:10px; font-weight:600; color:var(--text-muted);">Bonus / Highlights</td>
                ${games.map(g => `<td style="padding:10px; text-align:center; color:#16a34a; font-weight:600;">${g.bonus || 'Standard Access'}</td>`).join('')}
              </tr>
              <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:10px; font-weight:600; color:var(--text-muted);">Review Date</td>
                ${games.map(g => `<td style="padding:10px; text-align:center; color:var(--text-muted);">${g.lastReviewed || '2026'}</td>`).join('')}
              </tr>
              <tr>
                <td style="padding:14px 10px; font-weight:600; color:var(--text-muted);">Action</td>
                ${games.map(g => `
                  <td style="padding:14px 10px; text-align:center;">
                    <a href="games/${g.slug}/" class="btn-yono-primary" style="padding:8px 14px; font-size:12px;">Full Details</a>
                  </td>
                `).join('')}
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    `;

    modal.classList.add('open');
    modal.onclick = (e) => {
      if (e.target === modal) modal.classList.remove('open');
    };
  }

  // --- 7. ADVANCED LIVE SEARCH MODAL ---
  function initSearchModal() {
    let modal = document.getElementById('search-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'search-modal';
      modal.className = 'search-modal-backdrop';
      modal.innerHTML = `
        <div class="search-modal-box">
          <div class="search-input-wrap">
            <i class="fas fa-search" style="color:var(--primary); font-size:18px;"></i>
            <input type="text" id="live-search-input" placeholder="Search 73+ games by name, category, features..." autocomplete="off">
            <button class="btn-icon" style="width:32px; height:32px;" onclick="document.getElementById('search-modal').classList.remove('open')">✕</button>
          </div>
          <div style="padding:12px 20px; background:var(--primary-subtle); display:flex; align-items:center; gap:8px; flex-wrap:wrap; font-size:12px;">
            <span style="font-weight:700; color:var(--primary);">Popular:</span>
            <span class="filter-pill" style="padding:2px 10px; font-size:11px;" onclick="YonoApp.triggerSearch('Rummy')">Rummy</span>
            <span class="filter-pill" style="padding:2px 10px; font-size:11px;" onclick="YonoApp.triggerSearch('Jeet Spin')">Jeet Spin</span>
            <span class="filter-pill" style="padding:2px 10px; font-size:11px;" onclick="YonoApp.triggerSearch('101 Z')">101 Z</span>
            <span class="filter-pill" style="padding:2px 10px; font-size:11px;" onclick="YonoApp.triggerSearch('Slots')">Slots</span>
            <span class="filter-pill" style="padding:2px 10px; font-size:11px;" onclick="YonoApp.triggerSearch('New')">New Releases</span>
          </div>
          <div id="search-modal-results" class="search-results-area">
            <div style="text-align:center; padding:30px; color:var(--text-muted); font-size:14px;">
              Type any game name or category above to see instant results.
            </div>
          </div>
        </div>
      `;
      document.body.appendChild(modal);

      modal.onclick = (e) => {
        if (e.target === modal) modal.classList.remove('open');
      };

      const input = document.getElementById('live-search-input');
      input.addEventListener('input', (e) => executeSearch(e.target.value.trim()));
      input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') modal.classList.remove('open');
      });
    }

    document.querySelectorAll('.trigger-search-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        openSearchModal();
      });
    });
  }

  function openSearchModal() {
    const modal = document.getElementById('search-modal');
    if (!modal) return;
    modal.classList.add('open');
    setTimeout(() => {
      const input = document.getElementById('live-search-input');
      if (input) {
        input.value = '';
        input.focus();
        executeSearch('');
      }
    }, 100);
  }

  function executeSearch(query) {
    const resultsContainer = document.getElementById('search-modal-results');
    if (!resultsContainer) return;

    if (!query) {
      resultsContainer.innerHTML = `
        <div style="text-align:center; padding:30px; color:var(--text-muted); font-size:14px;">
          Start typing to explore all 73 verified game listings.
        </div>
      `;
      return;
    }

    const q = query.toLowerCase();
    const matches = YonoApp.games.filter(g => {
      return (
        (g.name && g.name.toLowerCase().includes(q)) ||
        (g.category && g.category.toLowerCase().includes(q)) ||
        (g.description && g.description.toLowerCase().includes(q)) ||
        (g.developer && g.developer.toLowerCase().includes(q))
      );
    });

    if (!matches.length) {
      resultsContainer.innerHTML = `
        <div style="text-align:center; padding:35px 20px; color:var(--text-muted);">
          <i class="fas fa-search-minus" style="font-size:36px; color:var(--text-subtle); margin-bottom:12px; display:block;"></i>
          <div style="font-weight:700; font-size:16px; color:var(--text-main); margin-bottom:6px;">No games found matching "${query}"</div>
          <p style="font-size:13px; max-width:340px; margin:0 auto 16px;">Try checking your spelling, or browse by categories like Rummy, Teen Patti, Slots, or New Games.</p>
          <button class="btn-yono-outline" style="padding:6px 14px; font-size:12px;" onclick="YonoApp.triggerSearch('')">Clear Search</button>
        </div>
      `;
      return;
    }

    resultsContainer.innerHTML = matches.slice(0, 15).map(g => {
      const highlightedName = g.name.replace(new RegExp(`(${query})`, 'gi'), '<mark style="background:#fee2e2; color:#b91c1c; border-radius:2px;">$1</mark>');
      return `
        <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; border-bottom:1px solid var(--border); border-radius:12px; margin-bottom:6px; background:var(--bg-surface);">
          <div style="display:flex; align-items:center; gap:12px;">
            <img src="${g.image || 'assets/all-yono-header.webp'}" style="width:42px; height:42px; border-radius:10px; object-fit:cover;" onerror="this.src='assets/all-yono-header.webp'">
            <div>
              <div style="font-weight:700; font-size:14px; color:var(--text-main);">${highlightedName}</div>
              <div style="font-size:11px; color:var(--primary); font-weight:600;">${g.category || 'Games'} • ${g.platform || 'Android'}</div>
            </div>
          </div>
          <div style="display:flex; gap:8px;">
            <a href="games/${g.slug}/" class="btn-yono-outline" style="padding:6px 12px; font-size:12px;">Guide</a>
            <a href="${g.url || '#'}" target="_blank" rel="nofollow noopener" class="btn-yono-primary" style="padding:6px 14px; font-size:12px;">Play</a>
          </div>
        </div>
      `;
    }).join('');
  }

  // --- 8. AI SEARCH ASSISTANT (Natural Language NLP Filter) ---
  function openAIAssistant() {
    let modal = document.getElementById('ai-assistant-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'ai-assistant-modal';
      modal.className = 'ai-assistant-modal';
      modal.innerHTML = `
        <div class="ai-assistant-box">
          <div style="background:var(--accent-gradient); padding:20px; color:#fff; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:10px;">
              <i class="fas fa-robot" style="font-size:24px;"></i>
              <div>
                <h3 style="margin:0; font-size:17px; font-weight:800;">Yono AI Game Discovery Assistant</h3>
                <div style="font-size:11px; opacity:0.9;">Tell me what kind of game you want to play</div>
              </div>
            </div>
            <button class="btn-icon" style="background:rgba(255,255,255,0.2); border:none; color:#fff;" onclick="document.getElementById('ai-assistant-modal').classList.remove('open')">✕</button>
          </div>
          <div style="padding:20px;">
            <div style="display:flex; gap:8px; margin-bottom:14px;">
              <input type="text" id="ai-query-input" placeholder="e.g. 'Show me fast rummy games' or 'New releases with bonuses'" style="flex:1; padding:12px 16px; border-radius:12px; border:1px solid var(--border-strong); background:var(--bg-surface); color:var(--text-main); font-size:14px; outline:none;">
              <button class="btn-yono-primary" onclick="YonoApp.askAI()">Ask</button>
            </div>
            <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:16px;">
              <span style="font-size:11px; font-weight:600; color:var(--text-muted);">Quick prompts:</span>
              <button class="filter-pill" style="padding:2px 8px; font-size:11px;" onclick="document.getElementById('ai-query-input').value='best card and rummy games'; YonoApp.askAI();">Card & Rummy</button>
              <button class="filter-pill" style="padding:2px 8px; font-size:11px;" onclick="document.getElementById('ai-query-input').value='popular arcade slot games'; YonoApp.askAI();">Arcade & Slots</button>
              <button class="filter-pill" style="padding:2px 8px; font-size:11px;" onclick="document.getElementById('ai-query-input').value='newest games this month'; YonoApp.askAI();">Newest releases</button>
            </div>
            <div id="ai-results-area" style="max-height:300px; overflow-y:auto; font-size:13px; color:var(--text-main);">
              <div style="text-align:center; padding:20px; color:var(--text-muted);">
                Ask any question about games in our directory to get instant recommendations.
              </div>
            </div>
          </div>
        </div>
      `;
      document.body.appendChild(modal);

      modal.onclick = (e) => {
        if (e.target === modal) modal.classList.remove('open');
      };

      document.getElementById('ai-query-input').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') askAI();
      });
    }

    modal.classList.add('open');
    setTimeout(() => {
      document.getElementById('ai-query-input')?.focus();
    }, 100);
  }

  function askAI() {
    const input = document.getElementById('ai-query-input');
    const area = document.getElementById('ai-results-area');
    if (!input || !area) return;

    const query = input.value.trim().toLowerCase();
    if (!query) return;

    area.innerHTML = '<div style="text-align:center; padding:20px;"><i class="fas fa-spinner fa-spin" style="color:var(--primary); font-size:24px;"></i><div style="margin-top:8px; font-size:12px; color:var(--text-muted);">Scanning verified catalog...</div></div>';

    setTimeout(() => {
      // Natural language matching rules
      let matches = [];
      if (query.includes('rummy') || query.includes('card') || query.includes('teen patti')) {
        matches = YonoApp.games.filter(g => (g.name + g.category).toLowerCase().includes('rummy') || (g.name + g.category).toLowerCase().includes('teen patti'));
      } else if (query.includes('slot') || query.includes('spin') || query.includes('arcade')) {
        matches = YonoApp.games.filter(g => (g.name + g.category).toLowerCase().includes('slot') || (g.name + g.category).toLowerCase().includes('spin') || (g.name + g.category).toLowerCase().includes('arcade'));
      } else if (query.includes('new') || query.includes('latest') || query.includes('recent')) {
        matches = YonoApp.games.filter(g => g.new_game || (g.category && g.category.toLowerCase().includes('new')));
      } else if (query.includes('popular') || query.includes('best') || query.includes('top')) {
        matches = YonoApp.games.filter(g => g.popular || g.featured);
      } else {
        matches = YonoApp.games.filter(g => (g.name + g.category + (g.description || '')).toLowerCase().includes(query));
      }

      if (!matches.length) matches = YonoApp.games.slice(0, 5); // Fallback to top editorial picks

      area.innerHTML = `
        <div style="margin-bottom:12px; padding:10px 14px; background:var(--primary-subtle); border-radius:10px; font-size:12px; color:var(--primary); font-weight:600;">
          💡 AI Recommendation for: <em>"${query}"</em> (${matches.length} matching verified games found)
        </div>
        ${matches.slice(0, 5).map(g => `
          <div style="display:flex; align-items:center; justify-content:space-between; padding:10px; border-bottom:1px solid var(--border); margin-bottom:6px;">
            <div style="display:flex; align-items:center; gap:10px;">
              <img src="${g.image || 'assets/all-yono-header.webp'}" style="width:36px; height:36px; border-radius:8px; object-fit:cover;" onerror="this.src='assets/all-yono-header.webp'">
              <div>
                <strong style="font-size:13px;">${g.name}</strong>
                <div style="font-size:11px; color:var(--text-muted);">${g.category || 'Games'} • ${g.bonus || 'Verified'}</div>
              </div>
            </div>
            <a href="games/${g.slug}/" class="btn-yono-primary" style="padding:4px 10px; font-size:11px;">View</a>
          </div>
        `).join('')}
      `;
    }, 300);
  }

  // --- 9. SCROLL TO TOP ---
  function initScrollTop() {
    let btn = document.getElementById('scroll-top-btn');
    if (!btn) {
      btn = document.createElement('button');
      btn.id = 'scroll-top-btn';
      btn.className = 'scroll-top-btn';
      btn.innerHTML = '<i class="fas fa-arrow-up"></i>';
      btn.setAttribute('title', 'Scroll to top');
      document.body.appendChild(btn);

      btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    }

    window.addEventListener('scroll', () => {
      if (window.scrollY > 300) {
        btn.classList.add('visible');
      } else {
        btn.classList.remove('visible');
      }
    });
  }

  // --- 10. ANNOUNCEMENT BANNER DISMISSAL ---
  function initAnnouncement() {
    const banner = document.getElementById('site-announcement-bar');
    if (!banner) return;
    if (localStorage.getItem(STORAGE_KEYS.ANNOUNCEMENT) === '1') {
      banner.style.display = 'none';
    }
  }

  function dismissAnnouncement() {
    const banner = document.getElementById('site-announcement-bar');
    if (banner) {
      banner.style.display = 'none';
      localStorage.setItem(STORAGE_KEYS.ANNOUNCEMENT, '1');
    }
  }

  // --- 11. INITIALIZATION ON DOM READY ---
  document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initSearchModal();
    initScrollTop();
    initAnnouncement();
    updateFavBadges();
    updateHeartIcons();

    // If gamesData array was pre-injected in HTML:
    if (window.gamesData && Array.isArray(window.gamesData)) {
      YonoApp.games = window.gamesData;
    }

    // Bind AI assistant button if present
    document.querySelectorAll('.trigger-ai-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        openAIAssistant();
      });
    });
  });

  // Expose methods to global scope
  window.YonoApp.setTheme = setTheme;
  window.YonoApp.toggleFavorite = toggleFavorite;
  window.YonoApp.isFavorite = isFavorite;
  window.YonoApp.trackGameView = trackGameView;
  window.YonoApp.getRecentViews = getRecentViews;
  window.YonoApp.clearRecentViews = clearRecentViews;
  window.YonoApp.toggleCompare = toggleCompare;
  window.YonoApp.openCompareModal = openCompareModal;
  window.YonoApp.clearCompare = () => {
    YonoApp.compareList = [];
    updateCompareDock();
    updateCompareCheckboxes();
  };
  window.YonoApp.openSearchModal = openSearchModal;
  window.YonoApp.triggerSearch = (tag) => {
    openSearchModal();
    const input = document.getElementById('live-search-input');
    if (input) {
      input.value = tag;
      executeSearch(tag);
    }
  };
  window.YonoApp.openAIAssistant = openAIAssistant;
  window.YonoApp.askAI = askAI;
  window.YonoApp.dismissAnnouncement = dismissAnnouncement;
  window.YonoApp.showToast = showToast;
})();
