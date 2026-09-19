/* ═══════════════════════════════════════════════════════
   app.js — تطبيق المحرّر
   يربط الحقول بالمشروع، ويعيد الرسم بعد تهدئة 150ms،
   ويدير التراجع/الإعادة والاختصارات والحفظ التلقائي.
   ═══════════════════════════════════════════════════════ */

const $  = s => document.querySelector(s);
const $$ = s => Array.from(document.querySelectorAll(s));

let P = null;            // المشروع الحالي
let rc = null;           // محرّك الرسم
let screen = 'hook';     // الشاشة المعروضة
let zoom = 100;
let dirty = false;
let playing = false, playRaf = null, showSafe = false;

/* ── التنبيهات ── */
function toast(msg, kind) {
  const box = $('#toasts');
  if (!box) return;
  const el = document.createElement('div');
  el.className = 'toast' + (kind ? ' ' + kind : '');
  el.textContent = msg;
  box.appendChild(el);
  setTimeout(() => { el.classList.add('out'); setTimeout(() => el.remove(), 260); }, 2600);
}

/* ═══ التراجع/الإعادة — 10 خطوات ═══ */
const HIST = { stack: [], at: -1, max: 10, lock: false };

function snapshot() {
  if (HIST.lock) return;
  const s = JSON.stringify(P);
  if (HIST.at >= 0 && HIST.stack[HIST.at] === s) return;
  HIST.stack = HIST.stack.slice(0, HIST.at + 1);
  HIST.stack.push(s);
  if (HIST.stack.length > HIST.max) HIST.stack.shift();
  HIST.at = HIST.stack.length - 1;
  updateHistBtns();
}

function undo() {
  if (HIST.at <= 0) return;
  HIST.at--;
  P = JSON.parse(HIST.stack[HIST.at]);
  HIST.lock = true; syncAll(); HIST.lock = false;
  updateHistBtns(); markDirty();
}

function redo() {
  if (HIST.at >= HIST.stack.length - 1) return;
  HIST.at++;
  P = JSON.parse(HIST.stack[HIST.at]);
  HIST.lock = true; syncAll(); HIST.lock = false;
  updateHistBtns(); markDirty();
}

function updateHistBtns() {
  const u = $('#btnUndo'), r = $('#btnRedo');
  if (u) u.disabled = HIST.at <= 0;
  if (r) r.disabled = HIST.at >= HIST.stack.length - 1;
}

/* ═══ الرسم مع تهدئة ═══ */
let rafId = null, debTimer = null;

function draw() {
  if (playing) return;                 /* أثناء التشغيل يقود المحرّك الرسم */
  if (rafId) return;
  rafId = requestAnimationFrame(() => {
    rafId = null;
    rc.render(P, screen);
    if (showSafe) drawSafeZones(rc);
    const note = $('#fitNote');
    if (note) {
      note.textContent = (screen === 'content' && rc.lastFit < 0.995)
        ? ` · صُغِّرت النقاط تلقائيًا ${Math.round(rc.lastFit * 100)}% لتتّسع`
        : '';
    }
  });
}

/* لا نعيد الرسم مع كل حرف — ننتظر 150ms بعد آخر ضغطة */
function drawSoon() {
  clearTimeout(debTimer);
  debTimer = setTimeout(draw, 150);
}

function markDirty() {
  dirty = true;
  const s = $('#saveState');
  if (s) s.textContent = 'تغييرات غير محفوظة';
}

function onEdit(instant) {
  markDirty();
  instant ? draw() : drawSoon();
  clearTimeout(onEdit._h);
  onEdit._h = setTimeout(snapshot, 550);   // لقطة واحدة بعد توقّف الكتابة
}

/* ═══ الحفظ ═══ */
function save(silent) {
  P.name = ($('#projName').value || '').trim() || 'ريل بلا عنوان';
  const r = saveProject(P);
  if (!r.ok) { toast(r.msg, 'err'); return false; }
  dirty = false;
  const s = $('#saveState');
  if (s) s.textContent = 'محفوظ · ' + fmtDate(P.updated_at);
  if (!silent) toast('تم الحفظ ✓');
  return true;
}

/* ═══ مزامنة الواجهة مع المشروع ═══ */
function setVal(sel, v) {
  const el = $(sel);
  if (!el) return;
  if (document.activeElement === el) return;   // لا نقاطع المستخدم أثناء الكتابة
  el.value = v == null ? '' : v;
}

function syncAll() {
  const h = P.screens.hook, c = P.screens.content, st = P.settings;

  setVal('#projName', P.name);
  setVal('#fHook',  h.text);  setVal('#fHook2', h.text);
  setVal('#fBadge', h.badge); setVal('#fSub',   h.sub);
  setVal('#fEmoji', c.emoji); setVal('#fTitle', c.title);
  setVal('#fOutro', c.outro); setVal('#fCta',   c.cta);
  setVal('#fBrand', c.brand);

  const hl = (h.text || '').length;
  $('#hookLen').textContent  = hl + ' / 120';
  $('#hookLen2').textContent = hl + ' / 120';
  $('#hookLen').className  = 'hint' + (hl > 110 ? ' warn' : '');
  $('#titleLen').textContent = (c.title || '').length + ' حرفًا';

  setVal('#fFont', st.font);
  setVal('#fMotion', st.motion || 'slide');
  if ($('#motionHint')) $('#motionHint').textContent = (MOTION[st.motion || 'slide'] || MOTION.slide).hint;
  [['#sPt','#vPt','ptFontSize'], ['#sTitle','#vTitle','titleFontSize'],
   ['#sLh','#vLh','lineHeight'], ['#sGh','#vGh','graphicHeight'],
   ['#sRad','#vRad','cornerRadius'], ['#sMar','#vMar','margins'],
   ['#sGap','#vGap','pointGap']].forEach(([s, v, k]) => {
    const el = $(s); if (el) el.value = st[k];
    const lbl = $(v); if (lbl) lbl.textContent = st[k];
  });

  renderPoints();
  renderThemeGrid();
  renderSwatches();
  renderImageBoxes();
  renderHookStyles();
  draw();
}

/* ═══ النقاط ═══ */
function renderPoints() {
  const box = $('#points');
  const pts = P.screens.content.points;
  box.innerHTML = '';

  pts.forEach((pt, i) => {
    const el = document.createElement('div');
    el.className = 'point';
    el.draggable = false;
    el.dataset.i = i;
    el.innerHTML = `
      <div class="point-hd">
        <span class="grip" draggable="true" title="اسحب لإعادة الترتيب">⠿</span>
        <span class="idx">${toArabicDigits(i + 1)}</span>
        <span class="sp"></span>
        <button class="del" type="button" title="حذف النقطة">✕</button>
      </div>
      <input type="text" class="p-head" placeholder="عنوان النقطة" value="">
      <textarea class="p-body" rows="2" placeholder="شرح النقطة"></textarea>
      <div class="len"></div>`;
    el.querySelector('.p-head').value = pt.head || '';
    el.querySelector('.p-body').value = pt.body || '';
    box.appendChild(el);

    const upd = () => {
      const total = (pt.head || '').length + (pt.body || '').length;
      const len = el.querySelector('.len');
      len.textContent = total + ' حرفًا';
      len.className = 'len' + (total > 95 ? ' warn' : '');
    };
    upd();

    el.querySelector('.p-head').addEventListener('input', e => {
      pt.head = e.target.value; upd(); onEdit();
    });
    el.querySelector('.p-body').addEventListener('input', e => {
      pt.body = e.target.value; upd(); onEdit();
    });
    el.querySelector('.del').addEventListener('click', () => {
      if (pts.length <= 1) { toast('لا بدّ من نقطة واحدة على الأقل', 'warn'); return; }
      pts.splice(i, 1); snapshot(); renderPoints(); onEdit(true);
    });

    /* السحب والإفلات لإعادة الترتيب */
    const grip = el.querySelector('.grip');
    grip.addEventListener('dragstart', e => {
      e.dataTransfer.setData('text/plain', String(i));
      e.dataTransfer.effectAllowed = 'move';
      el.classList.add('drag');
    });
    grip.addEventListener('dragend', () => {
      el.classList.remove('drag');
      $$('.point').forEach(p => p.classList.remove('over'));
    });
    el.addEventListener('dragover', e => {
      e.preventDefault(); e.dataTransfer.dropEffect = 'move';
      el.classList.add('over');
    });
    el.addEventListener('dragleave', () => el.classList.remove('over'));
    el.addEventListener('drop', e => {
      e.preventDefault();
      el.classList.remove('over');
      const from = parseInt(e.dataTransfer.getData('text/plain'), 10);
      const to = i;
      if (isNaN(from) || from === to) return;
      const moved = pts.splice(from, 1)[0];
      pts.splice(to, 0, moved);
      snapshot(); renderPoints(); onEdit(true);
    });
  });

  $('#ptCount').textContent = pts.length + ' / 10';
  $('#btnAddPoint').disabled = pts.length >= 10;
}

/* ═══ الثيمات ═══ */
function renderThemeGrid() {
  const g = $('#themeGrid');
  g.innerHTML = '';
  THEME_KEYS.forEach(k => {
    const t = THEMES[k];
    const wrap = document.createElement('div');
    const b = document.createElement('button');
    b.type = 'button';
    b.className = 'theme-dot';
    b.style.setProperty('--d-accent', t.accent);
    b.style.setProperty('--d-bg', t.bg);
    b.setAttribute('aria-pressed', String(P.theme === k));
    b.title = t.name;
    b.addEventListener('click', () => {
      P.theme = k;
      P.colorOverrides = {};
      snapshot(); renderThemeGrid(); renderSwatches(); onEdit(true);
    });
    const n = document.createElement('span');
    n.className = 'theme-name';
    n.textContent = t.name;
    wrap.appendChild(b); wrap.appendChild(n);
    g.appendChild(wrap);
  });
}

function renderSwatches() {
  const box = $('#swatches');
  const th = resolveTheme(P.theme, P.colorOverrides);
  box.innerHTML = '';
  EDITABLE_COLORS.forEach(c => {
    const row = document.createElement('div');
    row.className = 'swatch-row';
    row.innerHTML = `<input type="color" value="${th[c.key]}">
                     <label>${c.label}</label>
                     <span class="hex">${th[c.key]}</span>`;
    row.querySelector('input').addEventListener('input', e => {
      P.colorOverrides = P.colorOverrides || {};
      P.colorOverrides[c.key] = e.target.value;
      row.querySelector('.hex').textContent = e.target.value;
      onEdit();
    });
    box.appendChild(row);
  });
}

/* ═══ الصور ═══ */
/* نصغّر الصورة قبل تخزينها — base64 بحجمه الأصلي يملأ حصّة localStorage بسرعة */
function readImage(file, maxW, cb) {
  if (!file.type.startsWith('image/')) { toast('الملف ليس صورة', 'err'); return; }
  const fr = new FileReader();
  fr.onload = () => {
    const img = new Image();
    img.onload = () => {
      const scale = Math.min(1, maxW / img.width);
      const w = Math.round(img.width * scale), h = Math.round(img.height * scale);
      const cnv = document.createElement('canvas');
      cnv.width = w; cnv.height = h;
      cnv.getContext('2d').drawImage(img, 0, 0, w, h);
      cb(cnv.toDataURL('image/jpeg', 0.86));
    };
    img.onerror = () => toast('تعذّر قراءة الصورة', 'err');
    img.src = fr.result;
  };
  fr.onerror = () => toast('تعذّر قراءة الملف', 'err');
  fr.readAsDataURL(file);
}

function imageBox(container, label, get, set, maxW) {
  const cur = get();
  container.innerHTML = '';
  if (cur) {
    const t = document.createElement('div');
    t.className = 'thumb';
    t.innerHTML = `<img src="${cur}" alt=""><button class="rm" type="button" title="إزالة">✕</button>`;
    t.querySelector('.rm').addEventListener('click', () => {
      set(null); snapshot(); renderImageBoxes(); onEdit(true);
    });
    container.appendChild(t);
  } else {
    const l = document.createElement('label');
    l.className = 'upload';
    l.innerHTML = `<span class="ic">🖼️</span>${label}<input type="file" accept="image/*">`;
    l.querySelector('input').addEventListener('change', e => {
      const f = e.target.files[0];
      if (f) readImage(f, maxW, url => { set(url); snapshot(); renderImageBoxes(); onEdit(true); });
    });
    container.appendChild(l);
  }
}

function renderImageBoxes() {
  imageBox($('#graphicBox'), 'ارفع صورة الجرافيك العلوي',
    () => P.screens.content.graphic,
    v => P.screens.content.graphic = v, 1000);
  imageBox($('#hookBgBox'), 'ارفع خلفية شاشة الهوك',
    () => P.screens.hook.bgImage,
    v => P.screens.hook.bgImage = v, 1080);
}

/* ═══ صيغ الهوك ═══ */
function renderHookStyles() {
  const box = $('#hookStyles');
  box.innerHTML = '';
  HOOK_STYLE_KEYS.forEach(k => {
    const b = document.createElement('button');
    b.type = 'button';
    b.textContent = HOOK_STYLES[k].label;
    if (P.screens.hook.badge === HOOK_STYLES[k].badge) {
      b.style.borderColor = 'var(--color-primary)';
      b.style.color = 'var(--color-text)';
    }
    b.addEventListener('click', () => {
      P.screens.hook.badge = HOOK_STYLES[k].badge;
      snapshot(); syncAll(); markDirty();
    });
    box.appendChild(b);
  });
}

/* ═══ الاقتراحات ═══ */
function renderSuggestions() {
  const ts = $('#titleSuggest');
  TITLE_PATTERNS.forEach(p => {
    const b = document.createElement('button');
    b.type = 'button'; b.textContent = p.label;
    b.addEventListener('click', () => {
      P.screens.content.title = p.fn(P.screens.content.title || P.screens.hook.text || '');
      snapshot(); syncAll(); markDirty();
    });
    ts.appendChild(b);
  });

  const cs = $('#ctaTemplates');
  CTA_TEMPLATES.forEach(t => {
    const b = document.createElement('button');
    b.type = 'button'; b.textContent = t.replace(/\s*👇$/, '');
    b.title = t;
    b.addEventListener('click', () => {
      P.screens.content.cta = t; snapshot(); syncAll(); markDirty();
    });
    cs.appendChild(b);
  });

  const os = $('#outroSuggest');
  ['اجعلها جملة واحدة تُحفظ', 'ابدأها بـ«الـ»', 'اختمها بمفارقة'].forEach((hintTxt, i) => {
    const b = document.createElement('button');
    b.type = 'button'; b.textContent = hintTxt;
    b.addEventListener('click', () => {
      const t = P.screens.content.title || 'الموضوع';
      const opts = [
        `${t} ليست قرارًا واحدًا — بل عادةً تتكرّر.`,
        `الفرق بين من يعرف ومن يطبّق.. سنة كاملة.`,
        `أكثر ما يكلّفك ليس ما فعلته — بل ما أجّلته.`
      ];
      P.screens.content.outro = opts[i];
      snapshot(); syncAll(); markDirty();
    });
    os.appendChild(b);
  });
}

/* توليد 3 عناوين بديلة من العنوان الحالي */
function altTitles() {
  const base = stripLead(P.screens.content.title || P.screens.hook.text || 'الموضوع');
  const n = P.screens.content.points.filter(p => p.head).length || 6;
  const outs = [
    `${toArabicDigits(n)} أشياء عن ${base}`,
    `توقّف عن ${base}`,
    `ما لا يقوله أحد عن ${base}`,
    `${base} — ما تحتاج تعرفه فعلًا`,
    `POV: تكتشف حقيقة ${base}`
  ].sort(() => Math.random() - 0.5).slice(0, 3);

  const box = $('#altTitles');
  box.innerHTML = '';
  outs.forEach(t => {
    const b = document.createElement('button');
    b.type = 'button'; b.textContent = t;
    b.addEventListener('click', () => {
      P.screens.content.title = t; snapshot(); syncAll(); markDirty();
    });
    box.appendChild(b);
  });
}

/* ═══ المكتبة السريعة ═══ */
function renderQuick() {
  const box = $('#quickList');
  box.innerHTML = '';
  TOPICS.slice(0, 8).forEach(t => {
    const b = document.createElement('button');
    b.className = 'topic';
    b.type = 'button';
    b.innerHTML = `<div class="t-hd"><span class="t-emoji">${t.emoji}</span>
      <h4>${t.title}</h4></div>
      <div class="t-meta"><span class="chip">${AXES[t.axis].label}</span>
      <span class="chip">${toArabicDigits(t.points.length)} نقاط</span>
      <span class="t-swatch" style="background:${THEMES[t.theme].accent}"></span></div>`;
    b.addEventListener('click', () => {
      P = topicToProject(t, P);
      snapshot(); syncAll(); markDirty();
      toast('تم تحميل: ' + t.title);
    });
    box.appendChild(b);
  });
}

/* ═══ الربط العام ═══ */
function bind() {
  /* التبويبات */
  $$('.tabs button').forEach(b => b.addEventListener('click', () => {
    $$('.tabs button').forEach(x => x.classList.toggle('on', x === b));
    $$('.tab-panel').forEach(p => p.classList.toggle('on', p.id === 'tab-' + b.dataset.tab));
  }));

  /* تبديل الشاشات */
  $$('.screens button').forEach(b => b.addEventListener('click', () => setScreen(b.dataset.screen)));

  /* الحقول النصّية */
  const text = [
    ['#fHook',  v => { P.screens.hook.text = v; setVal('#fHook2', v);
                       $('#hookLen').textContent = v.length + ' / 120';
                       $('#hookLen2').textContent = v.length + ' / 120'; }],
    ['#fHook2', v => { P.screens.hook.text = v; setVal('#fHook', v);
                       $('#hookLen').textContent = v.length + ' / 120';
                       $('#hookLen2').textContent = v.length + ' / 120'; }],
    ['#fBadge', v => P.screens.hook.badge = v],
    ['#fSub',   v => P.screens.hook.sub = v],
    ['#fEmoji', v => P.screens.content.emoji = v],
    ['#fTitle', v => { P.screens.content.title = v;
                       $('#titleLen').textContent = v.length + ' حرفًا'; }],
    ['#fOutro', v => P.screens.content.outro = v],
    ['#fCta',   v => P.screens.content.cta = v],
    ['#fBrand', v => P.screens.content.brand = v]
  ];
  text.forEach(([sel, fn]) => {
    const el = $(sel);
    if (el) el.addEventListener('input', e => { fn(e.target.value); onEdit(); });
  });

  $('#projName').addEventListener('input', () => markDirty());

  /* منتقي الإيموجي */
  const ep = $('#emojiPick');
  EMOJI_SET.forEach(e => {
    const b = document.createElement('button');
    b.type = 'button'; b.textContent = e;
    b.addEventListener('click', () => {
      P.screens.content.emoji = e; setVal('#fEmoji', e);
      $('#fEmoji').value = e;
      snapshot(); onEdit(true);
    });
    ep.appendChild(b);
  });

  $('#btnAddPoint').addEventListener('click', () => {
    const pts = P.screens.content.points;
    if (pts.length >= 10) { toast('الحدّ الأقصى 10 نقاط', 'warn'); return; }
    pts.push(blankPoint()); snapshot(); renderPoints(); onEdit(true);
  });

  $('#btnAltTitles').addEventListener('click', altTitles);
  $('#btnCycleStyle').addEventListener('click', () => {
    const cur = HOOK_STYLE_KEYS.findIndex(k => HOOK_STYLES[k].badge === P.screens.hook.badge);
    const nx = HOOK_STYLE_KEYS[(cur + 1 + HOOK_STYLE_KEYS.length) % HOOK_STYLE_KEYS.length];
    P.screens.hook.badge = HOOK_STYLES[nx].badge;
    snapshot(); syncAll(); markDirty();
  });

  /* الخط والمنزلقات */
  const fsel = $('#fFont');
  Object.keys(FONTS).forEach(k => {
    const o = document.createElement('option');
    o.value = k; o.textContent = FONTS[k].label;
    fsel.appendChild(o);
  });
  fsel.addEventListener('change', e => { P.settings.font = e.target.value; snapshot(); onEdit(true); });

  [['#sPt','ptFontSize','#vPt'], ['#sTitle','titleFontSize','#vTitle'],
   ['#sLh','lineHeight','#vLh'], ['#sGh','graphicHeight','#vGh'],
   ['#sRad','cornerRadius','#vRad'], ['#sMar','margins','#vMar'],
   ['#sGap','pointGap','#vGap']].forEach(([sel, key, lbl]) => {
    const el = $(sel);
    el.addEventListener('input', e => {
      P.settings[key] = parseFloat(e.target.value);
      $(lbl).textContent = e.target.value;
      onEdit();
    });
    el.addEventListener('change', snapshot);
  });

  $('#btnResetColors').addEventListener('click', () => {
    P.colorOverrides = {}; snapshot(); renderSwatches(); onEdit(true);
    toast('رجعت ألوان الثيم');
  });

  /* أدوات المعاينة */
  $('#btnZoomIn').addEventListener('click',  () => setZoom(zoom + 10));
  $('#btnZoomOut').addEventListener('click', () => setZoom(zoom - 10));
  $('#btnRuler').addEventListener('click',   () => $('#stage').classList.toggle('ruler'));
  $('#btnPlay').addEventListener('click', togglePlay);
  $('#btnSafe').addEventListener('click', () => { showSafe = !showSafe; draw(); });
  $('#btnVideo').addEventListener('click', doExportVideo);

  /* نمط الحركة */
  const msel = $('#fMotion');
  MOTION_KEYS.forEach(k => msel.appendChild(new Option(MOTION[k].label, k)));
  const showHint = k => $('#motionHint').textContent = MOTION[k].hint;
  msel.addEventListener('change', e => {
    P.settings.motion = e.target.value;
    showHint(e.target.value);
    snapshot(); markDirty();
    togglePlay();                      /* عرض فوري للنمط الجديد */
  });
  $('#btnFull').addEventListener('click', () => {
    const st = $('#stage');
    if (document.fullscreenElement) document.exitFullscreen();
    else if (st.requestFullscreen) st.requestFullscreen().catch(() => toast('المتصفّح رفض ملء الشاشة', 'warn'));
  });

  /* الحفظ والتصدير */
  $('#btnSave').addEventListener('click', () => save());
  $('#btnExport').addEventListener('click', () => {
    save(true);
    exportCurrentPNG(rc, P, screen, 1);
    draw();
    toast('تم تصدير الشاشة الحالية 1080×1920');
  });
  $('#btnUndo').addEventListener('click', undo);
  $('#btnRedo').addEventListener('click', redo);

  /* الاختصارات */
  document.addEventListener('keydown', e => {
    const typing = /^(INPUT|TEXTAREA|SELECT)$/.test((e.target.tagName || ''));
    if (e.code === 'Space' && !typing && !(e.ctrlKey || e.metaKey)) {
      e.preventDefault(); togglePlay(); return;
    }
    if (!(e.ctrlKey || e.metaKey)) return;
    const k = e.key.toLowerCase();
    if (k === 's') { e.preventDefault(); save(); }
    else if (k === 'e') { e.preventDefault(); $('#btnExport').click(); }
    else if (k === 'z') { e.preventDefault(); e.shiftKey ? redo() : undo(); }
    else if (k === 'y') { e.preventDefault(); redo(); }
    else if (k === 't') {
      e.preventDefault();
      P.theme = nextTheme(P.theme); P.colorOverrides = {};
      snapshot(); renderThemeGrid(); renderSwatches(); onEdit(true);
      toast('الثيم: ' + THEMES[P.theme].name);
    }
    else if (k === '1') { e.preventDefault(); setScreen('hook'); }
    else if (k === '2') { e.preventDefault(); setScreen('content'); }
    else if (k === '3') { e.preventDefault(); setScreen('flow'); }
  });

  /* حفظ تلقائي كل 20 ثانية + عند مغادرة الصفحة */
  setInterval(() => { if (dirty) save(true); }, 20000);
  addEventListener('beforeunload', e => {
    if (dirty) { save(true); }
  });
}

function setScreen(s) {
  screen = s;
  $$('.screens button').forEach(b => b.classList.toggle('on', b.dataset.screen === s));
  draw();
}

/* ═══ تشغيل الحركة داخل المحرّر ═══ */
function togglePlay() {
  if (playing) { stopPlay(); return; }
  playing = true;
  $('#btnPlay').textContent = '⏹️ إيقاف';
  const t0 = performance.now();
  const tick = () => {
    if (!playing) return;
    const t = (performance.now() - t0) / 1000;
    if (t >= T_TOTAL) { stopPlay(); return; }
    const sc = renderAt(rc, P, t);
    if (sc !== screen) {
      screen = sc;
      $$('.screens button').forEach(b => b.classList.toggle('on', b.dataset.screen === sc));
    }
    playRaf = requestAnimationFrame(tick);
  };
  playRaf = requestAnimationFrame(tick);
}

function stopPlay() {
  playing = false;
  cancelAnimationFrame(playRaf);
  $('#btnPlay').textContent = '▶️ شغّل';
  draw();
}

/* ═══ تصدير الفيديو ═══ */
let recording = false;
function doExportVideo() {
  if (recording) return;
  if (!videoSupported()) {
    toast('متصفّحك لا يدعم تسجيل الفيديو — جرّب كروم أو إيدج حديثًا', 'err');
    return;
  }
  recording = true;
  stopPlay();
  const wasSafe = showSafe;
  showSafe = false;                    /* الدليل للمعاينة فقط */
  playing = true;                      /* نمنع draw من مقاطعة التسجيل */
  $('#btnVideo').disabled = true;
  $('#vidWrap').hidden = false;
  save(true);

  exportVideo(rc, P, {
    onProgress: (p, t) => {
      $('#vidBar').style.width = (p * 100) + '%';
      $('#vidMsg').textContent = `يسجّل… ${t.toFixed(1)} / ${T_TOTAL} ثانية`;
    }
  }).then(type => {
    toast('نزل الريل فيديو ' + type.label + ' ✓');
    $('#vidMsg').textContent = 'تمّ — ' + type.label;
  }).catch(err => {
    toast(err.message, 'err');
    $('#vidMsg').textContent = err.message;
  }).then(() => {
    recording = false; playing = false; showSafe = wasSafe;
    $('#btnVideo').disabled = false;
    setTimeout(() => { $('#vidWrap').hidden = true; $('#vidBar').style.width = '0%'; }, 2600);
    draw();
  });
}

function setZoom(z) {
  zoom = Math.max(50, Math.min(160, z));
  $('#zoomVal').textContent = zoom + '%';
  $('#stage').style.width = `calc(var(--canvas-width) * ${zoom / 100})`;
}

/* ═══ الإقلاع ═══ */
function boot() {
  P = loadOrCreateCurrent();
  rc = new ReelCanvas($('#cv'));
  rc.onReady = draw;                    // إعادة رسم بعد تحميل صورة

  bind();
  renderSuggestions();
  renderQuick();
  syncAll();
  snapshot();
  $('#saveState').textContent = 'محفوظ · ' + fmtDate(P.updated_at);

  /* الخطوط قد تصل بعد أول رسم — نعيد الرسم عند جاهزيّتها */
  waitForFonts().then(draw);
}

document.addEventListener('DOMContentLoaded', boot);
