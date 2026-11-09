(function(){
 var d=document,root=d.documentElement,b=d.querySelector('.sk-burger'),n=d.querySelector('.sk-nav');
 if(b&&n){
  b.addEventListener('click',function(){var o=n.classList.toggle('open');b.setAttribute('aria-expanded',o?'true':'false');});
  d.addEventListener('keydown',function(e){if(e.key==='Escape'&&n.classList.contains('open')){n.classList.remove('open');b.setAttribute('aria-expanded','false');b.focus();}});
 }
 function post(x){return fetch(SK.ajax,{method:'POST',body:x,credentials:'same-origin'}).then(function(r){return r.json();});}
 var uid=0;
 function labels(f){uid++;f.querySelectorAll('.f').forEach(function(w,i){var l=w.querySelector('label'),c=w.querySelector('input:not([type=hidden]),select,textarea');if(l&&c&&!l.querySelector('input')){var id='skf'+uid+'_'+i;c.id=id;l.setAttribute('for',id);if(c.required)c.setAttribute('aria-required','true');}});}
 function say(m,t,txt){m.className='sk-msg '+t;m.setAttribute('role',t==='err'?'alert':'status');m.textContent=txt;}

 /* Rezervasyon formları (PT randevusu / salon kiralama) */
 d.querySelectorAll('form.sk-form[data-type]').forEach(function(f){
  var type=f.dataset.type,ref=f.elements.ref_id,date=f.elements.date,slots=f.querySelector('.sk-slots'),start=f.elements.start,msg=f.querySelector('.sk-msg');
  labels(f);
  if(slots){slots.setAttribute('role','group');slots.setAttribute('aria-live','polite');}
  function apply(){
   f.elements.type.value=type;
   f.querySelectorAll('[data-for]').forEach(function(el){var s=el.dataset.for.split(' ').indexOf(type)>-1;el.hidden=!s;el.querySelectorAll('input,select').forEach(function(i){i.disabled=!s;});});
   if(ref&&ref.tagName==='SELECT'){var ok=false;[].forEach.call(ref.options,function(o){if(!o.value)return;var s=o.dataset.type===type;o.disabled=!s;o.hidden=!s;if(s&&o.selected)ok=true;});if(!ok)ref.value='';}
   f.querySelectorAll('.sk-tab').forEach(function(t){var on=t.dataset.type===type;t.classList.toggle('on',on);t.setAttribute('aria-pressed',on?'true':'false');});
   load();
  }
  function load(){
   if(!slots||(type!=='pt'&&type!=='hall'))return;start.value='';
   if(!ref.value||!date.value){slots.textContent='';var p=d.createElement('p');p.className='sk-muted';p.textContent=SK.hint;slots.appendChild(p);return;}
   var q=new FormData();q.append('action','sk_slots');q.append('kind',type);q.append('ref_id',ref.value);q.append('date',date.value);
   post(q).then(function(r){
    slots.textContent='';var a=(r&&r.data)||[];
    if(!a.length){var p=d.createElement('p');p.className='sk-muted';p.textContent=SK.none;slots.appendChild(p);return;}
    a.forEach(function(s){
     var x=d.createElement('button');x.type='button';
     if(s.free){
      x.textContent=s.t;x.setAttribute('aria-pressed','false');
      x.onclick=function(){slots.querySelectorAll('button').forEach(function(z){z.classList.remove('on');z.setAttribute('aria-pressed','false');});x.classList.add('on');x.setAttribute('aria-pressed','true');start.value=s.t;};
     }else{
      x.disabled=true;x.classList.add('full');x.textContent=s.t;var sm=d.createElement('small');sm.textContent=SK.full;x.appendChild(sm);x.setAttribute('aria-label',s.t+' – '+SK.full);
     }
     slots.appendChild(x);
    });
   });
  }
  f.querySelectorAll('.sk-tab').forEach(function(t){t.onclick=function(){type=t.dataset.type;apply();};});
  if(ref)ref.addEventListener('change',load);if(date)date.addEventListener('change',load);
  f.addEventListener('submit',function(e){
   e.preventDefault();
   if((type==='pt'||type==='hall')&&!start.value){say(msg,'err',SK.pick);return;}
   if(!f.checkValidity()){f.reportValidity();return;}
   var q=new FormData(f);q.append('action','sk_book');say(msg,'ok',SK.wait);
   post(q).then(function(r){
    var m=(r.data&&r.data.msg)||'Hata';say(msg,r.success?'ok':'err',m);
    if(r.success){f.reset();apply();say(msg,'ok',m);}else load();
   }).catch(function(){say(msg,'err','Bağlantı hatası.');});
  });
  apply();
 });

 /* Şifre göster / gizle (üye ol ve giriş formları) */
 d.querySelectorAll('.sk-signup input[type=password],.sk-login input[type=password]').forEach(function(i){
  var w=d.createElement('span');w.className='sk-pw';i.parentNode.insertBefore(w,i);w.appendChild(i);
  var t=d.createElement('button');t.type='button';t.textContent=SK.show;t.setAttribute('aria-pressed','false');
  t.addEventListener('click',function(){var s=i.type==='password';i.type=s?'text':'password';t.textContent=s?SK.hide:SK.show;t.setAttribute('aria-pressed',s?'true':'false');});
  w.appendChild(t);
 });

 /* Üye ol formu */
 d.querySelectorAll('form.sk-signup').forEach(function(f){
  labels(f);var m=f.querySelector('.sk-msg');
  f.addEventListener('submit',function(e){
   e.preventDefault();if(!f.checkValidity()){f.reportValidity();return;}
   var q=new FormData(f);q.append('action','sk_signup');say(m,'ok',SK.wait);
   post(q).then(function(r){
    var t=(r.data&&r.data.msg)||'Hata';say(m,r.success?'ok':'err',t);
    if(r.success&&r.data.redirect)setTimeout(function(){location.href=r.data.redirect;},900);
   }).catch(function(){say(m,'err','Bağlantı hatası.');});
  });
 });

 /* Erişilebilirlik araç çubuğu + hareket kontrolü */
 var K='skA11y',st={};try{st=JSON.parse(localStorage.getItem(K)||'{}')||{};}catch(e){}
 var mm=window.matchMedia&&matchMedia('(prefers-reduced-motion: reduce)').matches;
 var vid=d.querySelector('.sk-hero video'),vp=d.querySelector('.sk-vpause'),vpaused=false;
 function vidApply(){if(!vid)return;var stop=vpaused||st.motion||mm;if(stop)vid.pause();else{var p=vid.play();if(p&&p.catch)p.catch(function(){});}if(vp)vp.setAttribute('aria-pressed',stop?'true':'false');}
 function applyA(){
  root.style.fontSize=(100+(st.fs||0)*10)+'%';
  ['contrast','links','font','motion'].forEach(function(k){root.classList.toggle('sk-a-'+k,!!st[k]);});
  d.querySelectorAll('.sk-a11y [data-a]').forEach(function(x){var a=x.dataset.a;if(a==='contrast'||a==='links'||a==='font'||a==='motion')x.setAttribute('aria-pressed',st[a]?'true':'false');});
  vidApply();
  try{localStorage.setItem(K,JSON.stringify(st));}catch(e){}
 }
 var tb=d.querySelector('.sk-a11y-btn'),pn=d.getElementById('sk-a11y-panel');
 if(tb&&pn){
  tb.addEventListener('click',function(){var open=pn.hidden;pn.hidden=!open;tb.setAttribute('aria-expanded',open?'true':'false');if(open)pn.querySelector('button').focus();});
  pn.addEventListener('click',function(e){var x=e.target.closest('[data-a]');if(!x)return;var a=x.dataset.a;
   if(a==='font-up')st.fs=Math.min(3,(st.fs||0)+1);else if(a==='font-down')st.fs=Math.max(-1,(st.fs||0)-1);else if(a==='reset')st={};else st[a]=!st[a];applyA();});
  d.addEventListener('keydown',function(e){if(e.key==='Escape'&&!pn.hidden){pn.hidden=true;tb.setAttribute('aria-expanded','false');tb.focus();}});
 }
 if(vp)vp.addEventListener('click',function(){vpaused=!vpaused;vidApply();});
 applyA();
})();
