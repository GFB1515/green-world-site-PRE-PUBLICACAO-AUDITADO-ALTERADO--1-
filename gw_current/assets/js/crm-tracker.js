(function(){
  'use strict';
  const ENDPOINT='api/track.php';
  const KEY='gw_visitor_id';
  function uuid(){
    if(window.crypto && crypto.randomUUID) return crypto.randomUUID();
    return 'gw-'+Date.now().toString(36)+'-'+Math.random().toString(36).slice(2)+Math.random().toString(36).slice(2);
  }
  let visitor=localStorage.getItem(KEY);
  if(!visitor){ visitor=uuid(); localStorage.setItem(KEY,visitor); }
  const params=new URLSearchParams(location.search);
  const base={
    visitor_id:visitor,
    path:location.pathname+location.search,
    referrer:document.referrer||'',
    utm_source:params.get('utm_source')||'',
    utm_medium:params.get('utm_medium')||'',
    utm_campaign:params.get('utm_campaign')||''
  };
  function send(event, extra){
    const payload=Object.assign({},base,{event:event},extra||{});
    const body=JSON.stringify(payload);
    try{
      if(navigator.sendBeacon){
        const ok=navigator.sendBeacon(ENDPOINT,new Blob([body],{type:'application/json'}));
        if(ok) return;
      }
      fetch(ENDPOINT,{method:'POST',headers:{'Content-Type':'application/json'},body:body,keepalive:true,credentials:'same-origin'}).catch(function(){});
    }catch(e){}
  }
  const isProducts=/produtos\.html$/i.test(location.pathname) || document.body.classList.contains('products-page');
  send(isProducts?'products_view':'page_view');
  document.addEventListener('click',function(e){
    const a=e.target.closest('a,button'); if(!a) return;
    const text=(a.textContent||a.getAttribute('aria-label')||'').replace(/\s+/g,' ').trim().slice(0,250);
    const href=a.getAttribute('href')||'';
    let type='';
    if(a.classList.contains('whatsapp-sample') || a.classList.contains('sample-btn') || /amostra/i.test(text)) type='sample_click';
    else if(a.classList.contains('whatsapp-quote') || a.classList.contains('quote-action-btn') || /cota[cç][aã]o/i.test(text)) type='quote_click';
    else if(a.classList.contains('docs-btn') || /documenta[cç][aã]o/i.test(text)) type='docs_click';
    else if(/^mailto:/i.test(href)) type='email_click';
    else if(/wa\.me|whatsapp/i.test(href) || a.classList.contains('whatsapp-general')) type='whatsapp_click';
    if(type){
      const card=a.closest('[data-product-code],.family-product-item,.product-card,details');
      const productCode=(card && (card.dataset.productCode || card.querySelector('[data-code]')?.dataset.code)) || extractCode(text+ ' '+href);
      const family=(card && card.dataset.productFamily)||'';
      send(type,{label:text,product_code:productCode,product_family:family,metadata:{href:href.slice(0,500)}});
    }
  },true);
  function extractCode(s){ const m=(s||'').match(/\b(GR?WC[-\s]?[A-Z0-9.-]+|GWC[-\s]?[A-Z0-9.-]+)\b/i); return m?m[0].replace(/\s+/g,''):''; }
  document.addEventListener('toggle',function(e){
    const d=e.target;
    if(!(d instanceof HTMLDetailsElement) || !d.open) return;
    const summary=d.querySelector('summary');
    const label=(summary?.textContent||'Produto').replace(/\s+/g,' ').trim();
    const code=extractCode(label);
    send('product_open',{label:label.slice(0,250),product_code:code,product_family:d.dataset.productFamily||''});
  },true);
  window.GreenWorldCRM={track:send,visitorId:visitor};
})();
