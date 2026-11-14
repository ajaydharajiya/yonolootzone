(function(){
  var script = document.currentScript || (function(){
    var scripts = document.getElementsByTagName('script');
    for (var i = 0; i < scripts.length; i++) {
      if (scripts[i].src && scripts[i].src.indexOf('site-config.js') !== -1) {
        return scripts[i];
      }
    }
    return null;
  })();

  var baseUrl = script && script.src ? script.src.replace(/assets\/site-config\.js(?:\?.*)?$/, '') : '';
  function resolveData(file) {
    return (baseUrl || '') + 'data/' + file;
  }

  Promise.all([
    fetch(resolveData('site-settings.json'), {cache:'no-store'}).then(function(r){ return r.ok ? r.json() : null; }),
    fetch(resolveData('homepage.json'), {cache:'no-store'}).then(function(r){ return r.ok ? r.json() : null; }),
    fetch(resolveData('notifications.json'), {cache:'no-store'}).then(function(r){ return r.ok ? r.json() : null; }),
    fetch(resolveData('seo-settings.json'), {cache:'no-store'}).then(function(r){ return r.ok ? r.json() : null; })
  ]).then(function(res){
    var s = res[0], h = res[1], n = res[2], seo = res[3];
    if(s){
      document.querySelectorAll('a[href*="t.me/"]').forEach(function(a){ if(s.telegramUrl) a.href=s.telegramUrl; });
      if(s.siteName) document.querySelectorAll('[data-site-name]').forEach(function(el){ el.textContent=s.siteName; });
      if(s.accentColor) document.documentElement.style.setProperty('--site-accent', s.accentColor);
    }
    if(h){
      var hero = document.querySelector('#home h1');
      if(hero && h.heroTitle) hero.textContent = h.heroTitle;
      var hp = document.querySelector('#home p');
      if(hp && h.heroText) hp.textContent = h.heroText;
      var hb = document.querySelector('#home a[href="#games"]');
      if(hb){
        if(h.heroButtonText) hb.textContent = h.heroButtonText;
        if(h.heroButtonUrl) hb.href = h.heroButtonUrl;
      }
    }
    var isHome = location.pathname === '/' ||
                 location.pathname.endsWith('/index.html') ||
                 location.pathname.replace(/\/$/, '').endsWith('/yonolootzone');
    if(seo && isHome){
      if(seo.title) document.title = seo.title;
      var m = document.querySelector('meta[name="description"]');
      if(m && seo.description) m.content = seo.description;
    }
  }).catch(function(){});
})();
