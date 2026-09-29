(function(){
  const $=id=>document.getElementById(id);
  const KEY='mygame_list_v1';
  const esc=s=>String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  const slug=s=>String(s||'').toLowerCase().trim().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'').slice(0,80);
  function data(){try{return JSON.parse(localStorage.getItem(KEY)||'{}')}catch(e){return {games:[]}}}
  function download(name,text,type='text/html'){const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([text],{type}));a.download=name;a.click();setTimeout(()=>URL.revokeObjectURL(a.href),1000)}
  function faqHtml(raw){return String(raw||'').split('\n').map(line=>{const p=line.split('|');return p.length>=2?`<details><summary>${esc(p[0].trim())}</summary><p>${esc(p.slice(1).join('|').trim())}</p></details>`:''}).filter(Boolean).join('')}
  function collect(){
    const name=$('gameName')?.value.trim(); const url=$('gameUrl')?.value.trim();
    if(!name||!url){alert('Game Name and Game/Download Link are required.');return null}
    const g={name,category:$('gameCategory')?.value||'All Games',bonus:$('bonus')?.value.trim(),withdraw:$('withdraw')?.value.trim(),image:$('gameImage')?.value.trim()||'assets/logo.svg',url,seoTitle:$('gameSeoTitle')?.value.trim()||`${name} Game — Features & Download`,metaDescription:$('gameMetaDescription')?.value.trim()||`Learn about ${name}, available features, category information and the current access link.`,slug:slug($('gameSlug')?.value.trim()||name),ogImage:$('gameOgImage')?.value.trim()||$('gameImage')?.value.trim(),description:$('gameDescription')?.value.trim()||`Information about ${name}, including its category, available features and the access link provided by the site owner.`,faq:$('gameFaq')?.value||'',button:$('buttonText')?.value.trim()||'Download'};
    return g;
  }
  function build(g,related){
    const canonical=`https://yonolootzone.com/games/${g.slug}/`;
    const rel=related.filter(x=>x.slug!==g.slug).slice(0,6).map(x=>`<li><a href="/games/${esc(x.slug)}/">${esc(x.name)}</a></li>`).join('');
    const schema={"@context":"https://schema.org","@type":"WebPage","name":g.seoTitle,"description":g.metaDescription,"url":canonical,"mainEntity":{"@type":"SoftwareApplication","name":g.name,"applicationCategory":"Game","operatingSystem":"Android"}};
    return `<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>${esc(g.seoTitle)}</title><meta name="description" content="${esc(g.metaDescription)}"><meta name="robots" content="index,follow,max-image-preview:large"><link rel="canonical" href="${canonical}"><meta property="og:type" content="website"><meta property="og:title" content="${esc(g.seoTitle)}"><meta property="og:description" content="${esc(g.metaDescription)}">${g.ogImage?`<meta property="og:image" content="${esc(g.ogImage)}">`:''}<script type="application/ld+json">${JSON.stringify(schema)}</script><style>body{font-family:Arial,sans-serif;line-height:1.7;margin:0;background:#f7f7f8;color:#222}.wrap{max-width:920px;margin:auto;padding:24px}.card{background:#fff;border-radius:16px;padding:24px;box-shadow:0 5px 25px #00000012}.hero{display:flex;gap:18px;align-items:center;flex-wrap:wrap}.hero img{width:120px;height:120px;object-fit:cover;border-radius:20px}.meta{color:#666;font-size:14px}.btn{display:inline-block;padding:12px 20px;background:#e11;color:#fff;border-radius:9px;text-decoration:none;font-weight:700}details{padding:12px 0;border-bottom:1px solid #eee}summary{cursor:pointer;font-weight:700}a{color:#c11}</style></head><body><main class="wrap"><article class="card"><div class="hero">${g.image?`<img src="${esc(g.image)}" alt="${esc(g.name)} game icon" loading="eager">`:''}<div><p class="meta">${esc(g.category)} · Updated ${esc(new Date().toISOString().slice(0,10))}</p><h1>${esc(g.name)}</h1><p>${esc(g.description)}</p></div></div><p><a class="btn" href="${esc(g.url)}" target="_blank" rel="nofollow noopener">${esc(g.button)}</a></p><h2>${esc(g.name)} Game Information</h2><p>${esc(g.description)}</p><ul><li>${esc(g.bonus||'Check the current offer on the linked service.')}</li><li>${esc(g.withdraw||'Check the current withdrawal terms before using the service.')}</li><li>Category: ${esc(g.category)}</li></ul>${faqHtml(g.faq)?`<h2>Frequently Asked Questions</h2>${faqHtml(g.faq)}`:''}<h2>Related Games</h2><ul>${rel||'<li><a href="/all-yono-games.html">Browse all Yono games</a></li>'}</ul><hr><p><a href="/">← Back to YonoLootZone</a></p></article></main></body></html>`;
  }
  function entry(g){return `  <url><loc>https://yonolootzone.com/games/${esc(g.slug)}/</loc><lastmod>${new Date().toISOString().slice(0,10)}</lastmod><changefreq>weekly</changefreq></url>`}
  $('generateGameFiles')?.addEventListener('click',()=>{
    const g=collect(); if(!g)return;
    const d=data(); const related=(d.games||[]).map(x=>({...x,slug:x.slug||slug(x.name)}));
    download(g.slug+'-index.html',build(g,related));
    download(g.slug+'-sitemap.xml',entry(g),'application/xml');
    download(g.slug+'-internal-link.json',JSON.stringify({game:g.name,url:`/games/${g.slug}/`,related:related.filter(x=>x.slug!==g.slug).slice(0,6).map(x=>({name:x.name,url:`/games/${x.slug}/`}))},null,2),'application/json');
    alert('3 files generated:\n\n1. '+g.slug+'-index.html → upload as public_html/games/'+g.slug+'/index.html\n2. '+g.slug+'-sitemap.xml → add its <url> entry to sitemap.xml\n3. '+g.slug+'-internal-link.json → reference for internal linking\n\nThen submit/update the sitemap in Google Search Console.');
  });
})();
