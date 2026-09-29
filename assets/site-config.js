(function(){
Promise.all([
 fetch('/data/site-settings.json',{cache:'no-store'}).then(r=>r.ok?r.json():null),
 fetch('/data/homepage.json',{cache:'no-store'}).then(r=>r.ok?r.json():null),
 fetch('/data/notifications.json',{cache:'no-store'}).then(r=>r.ok?r.json():null),
 fetch('/data/seo-settings.json',{cache:'no-store'}).then(r=>r.ok?r.json():null)
]).then(([s,h,n,seo])=>{
 if(s){document.querySelectorAll('a[href*="t.me/"]').forEach(a=>{if(s.telegramUrl)a.href=s.telegramUrl;});if(s.siteName)document.querySelectorAll('[data-site-name]').forEach(el=>el.textContent=s.siteName);if(s.accentColor)document.documentElement.style.setProperty('--site-accent',s.accentColor);}
 if(h){const hero=document.querySelector('#home h1');if(hero&&h.heroTitle)hero.textContent=h.heroTitle;const hp=document.querySelector('#home p');if(hp&&h.heroText)hp.textContent=h.heroText;const hb=document.querySelector('#home a[href="#games"]');if(hb){if(h.heroButtonText)hb.textContent=h.heroButtonText;if(h.heroButtonUrl)hb.href=h.heroButtonUrl;}}
 if(seo && location.pathname==='/' || seo && location.pathname.endsWith('/index.html')){if(seo.title)document.title=seo.title;const m=document.querySelector('meta[name="description"]');if(m&&seo.description)m.content=seo.description;}
}).catch(()=>{});
})();