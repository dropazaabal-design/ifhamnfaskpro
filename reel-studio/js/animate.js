/* ═══════════════════════════════════════════════════════
   animate.js — محرّك الحركة والتوقيت
   التسلسل مبنيّ على ما تنشره إنستغرام نفسها عن الريلز
   (مناطق الواجهة، المقاس، الإطارات) وعلى ما استقرّ عليه
   صنّاع المحتوى: أول ثلاث ثوانٍ تحسم البقاء، والمشاهدة
   المتكرّرة ترفع زمن المشاهدة الكلّي.
   ═══════════════════════════════════════════════════════ */

/* ── مدد جاهزة ──
   لكل مدّة نصيبٌ للهوك وللخاتمة، والباقي للمحتوى. */
const DURATIONS = {
  s7:  { label:'٧ ثوانٍ — ٣ نقاط أو أقل',  total:7,  hook:2.2, flow:1.4 },
  s13: { label:'١٣ ثانية — ٤ إلى ٦ نقاط',  total:13, hook:3.0, flow:2.0 },
  s20: { label:'٢٠ ثانية — ٧ إلى ٩ نقاط',  total:20, hook:3.2, flow:2.4 },
  s30: { label:'٣٠ ثانية — شرح موسّع',     total:30, hook:3.6, flow:3.0 }
};
const DURATION_KEYS = Object.keys(DURATIONS);

/* يحسب حدود المشاهد الثلاثة لمشروع معيّن */
function timeline(project) {
  const key = (project && project.settings && project.settings.duration) || 's13';
  const d = DURATIONS[key] || DURATIONS.s13;
  return {
    total: d.total,
    hook:    [0, d.hook],
    content: [d.hook, d.total - d.flow],
    flow:    [d.total - d.flow, d.total],
    key
  };
}

/* قيم افتراضية لمن يقرأ الثوابت مباشرة */
const T_HOOK = [0, 3], T_CONTENT = [3, 11], T_FLOW = [11, 13], T_TOTAL = 13;

/* زمن الذوبان في بداية الحلقة — آخر جزء يعود إلى الإطار الأول
   بالضبط، فتبدو الإعادة بلا قطع. المشاهدة المتكرّرة أقوى ما
   يرفع زمن المشاهدة، وهو أهم إشارات الترتيب. */
const LOOP_FADE = 0.45;

/* أنماط الحركة */
const MOTION = {
  slide: { label:'انزلاق',  hint:'العناصر تدخل من جهة البداية' },
  fade:  { label:'تلاشٍ',   hint:'ظهور تدريجي هادئ بلا إزاحة' },
  zoom:  { label:'تكبير',   hint:'تقترب العناصر قليلًا وهي تظهر' },
  pop:   { label:'نبضة',    hint:'دخول بارتداد خفيف — أنسب للهوك' }
};
const MOTION_KEYS = Object.keys(MOTION);

/* ── دوال التنعيم ── */
const ease = {
  out:   t => 1 - Math.pow(1 - t, 3),
  inOut: t => t < 0.5 ? 4*t*t*t : 1 - Math.pow(-2*t + 2, 3) / 2,
  back:  t => { const c = 2.70158; return 1 + (c + 1) * Math.pow(t - 1, 3) + c * Math.pow(t - 1, 2); }
};

function seg(t, from, to) {
  if (to <= from) return t >= to ? 1 : 0;
  return Math.max(0, Math.min(1, (t - from) / (to - from)));
}

function fx(p, motion) {
  if (p >= 1) return { a:1, tx:0, ty:0, s:1 };
  if (p <= 0) return { a:0, tx:0, ty:0, s:1, skip:true };
  switch (motion) {
    case 'fade':
      return { a: p, tx:0, ty:0, s:1 };
    case 'zoom': {
      const e = ease.out(p);
      return { a: e, tx:0, ty:0, s: 0.88 + 0.12 * e };
    }
    case 'pop': {
      const e = ease.back(p);
      return { a: Math.min(1, p * 1.6), tx:0, ty:0, s: 0.72 + 0.28 * e };
    }
    default: {
      const e = ease.out(p);
      return { a: Math.min(1, p * 1.5), tx: (1 - e) * 120, ty:0, s:1 };
    }
  }
}

/* ══════════ حالة الحركة عند لحظة معيّنة ══════════ */

function animAt(project, t) {
  const st = project.settings || {};
  const motion = st.motion || 'slide';
  const TL = timeline(project);
  const pts = (project.screens.content.points || []).filter(p => p.head || p.body);
  const n = Math.max(1, pts.length);

  if (t < TL.content[0]) {
    const o = t - TL.hook[0], len = TL.hook[1] - TL.hook[0];
    return { screen:'hook', tl:TL, anim: {
      motion,
      bg:    seg(o, 0,          len * 0.15),
      badge: seg(o, len * 0.07, len * 0.25),
      text:  seg(o, len * 0.13, len * 0.44),
      sub:   seg(o, len * 0.37, len * 0.57),
      out:   1 - seg(o, len - 0.28, len)
    }};
  }

  if (t < TL.flow[0]) {
    const o = t - TL.content[0], len = TL.content[1] - TL.content[0];
    /* نافذة تتابع النقاط: تبدأ باكرًا فلا تبقى البطاقة فارغة،
       وتنتهي مبكرًا بما يكفي ليبقى السؤال مقروءًا. */
    const winFrom = Math.min(0.80, len * 0.14);
    const winTo   = len - Math.min(2.75, len * 0.34);
    const step = Math.min(0.62, Math.max(0.14, (winTo - winFrom) / n));
    const points = [];
    let shown = 0;
    for (let i = 0; i < n; i++) {
      const s = winFrom + i * step;
      const p = seg(o, s, s + Math.min(0.5, step * 1.5));
      points.push(p);
      if (p > 0.5) shown++;
    }
    return { screen:'content', tl:TL, anim: {
      motion,
      card:    seg(o, 0,                 Math.min(0.38, len * 0.06)),
      graphic: seg(o, len * 0.02,        Math.min(0.60, len * 0.10)),
      title:   seg(o, Math.min(0.34, len*0.05), Math.min(0.88, len * 0.13)),
      points,
      shown, total:n,
      /* شريط التقدّم تحت الجرافيك: يملأ مع تقدّم النقاط،
         فيعرف المشاهد كم بقي — وهذا يرفع نسبة إكمال المقطع. */
      bar:     Math.max(0, Math.min(1, (o - winFrom) / Math.max(0.001, (winTo + step) - winFrom))),
      outro:   seg(o, len - 2.55, len - 1.95),
      footer:  seg(o, len - 1.85, len - 1.35),
      out:     1 - seg(o, len - 0.28, len)
    }};
  }

  const o = t - TL.flow[0], len = TL.flow[1] - TL.flow[0];
  return { screen:'flow', tl:TL, anim: {
    motion,
    text: seg(o, 0.05, Math.min(0.85, len * 0.42)),
    out:  1
  }};
}

/* يرسم الإطار عند الزمن t — ويذوّب نهايته في بدايته إن كانت الحلقة مفعّلة */
function renderAt(rc, project, t) {
  const st = project.settings || {};
  const TL = timeline(project);
  const { screen, anim } = animAt(project, t);
  rc.render(project, screen, anim);

  if (st.loop !== false) {
    const left = TL.total - t;
    if (left <= LOOP_FADE) {
      /* الإطار الأول بالضبط يُركَّب فوق الأخير بشفافية متصاعدة،
         فعند الإعادة لا يظهر قطع. */
      const a = 1 - (left / LOOP_FADE);
      const first = animAt(project, 0);
      rc.blend(project, first.screen, first.anim, ease.inOut(a));
    }
  }
  return screen;
}

function playTimeline(rc, project, onTick, onScreen) {
  const total = timeline(project).total;
  return new Promise(resolve => {
    const t0 = performance.now();
    let last = null;
    const step = () => {
      const t = (performance.now() - t0) / 1000;
      if (t >= total) {
        renderAt(rc, project, total - 0.001);
        onTick && onTick(total, 1);
        resolve();
        return;
      }
      const sc = renderAt(rc, project, t);
      if (sc !== last) { last = sc; onScreen && onScreen(sc); }
      onTick && onTick(t, t / total);
      requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  });
}

/* ══════════ مناطق الأمان ══════════
   الثابت SAFE معرّف في canvas.js لأن التخطيط يحتاجه، وهذا
   الملف يرسم دليله فوق اللوحة فحسب. */
function drawSafeZones(rc) {
  const ctx = rc.ctx, W = rc.W, H = rc.H;
  ctx.save();
  ctx.fillStyle = 'rgba(239,68,68,.22)';
  ctx.fillRect(0, 0, W, H * SAFE.top);
  ctx.fillRect(0, H * (1 - SAFE.bottom), W, H * SAFE.bottom);
  ctx.fillRect(0, H * 0.55, W * SAFE.side, H * (1 - SAFE.bottom - 0.55));

  ctx.strokeStyle = 'rgba(16,185,129,.9)';
  ctx.lineWidth = 4;
  ctx.setLineDash([18, 14]);
  ctx.strokeRect(W * SAFE.side, H * SAFE.top,
                 W * (1 - SAFE.side), H * (1 - SAFE.bottom - SAFE.top));
  ctx.setLineDash([]);
  ctx.font = '700 30px Tajawal, sans-serif';
  ctx.fillStyle = 'rgba(16,185,129,1)';
  ctx.direction = 'rtl';
  ctx.textAlign = 'center';
  ctx.fillText('المنطقة الآمنة', W / 2, H * SAFE.top + 46);
  ctx.restore();
}

/* ══════════ فحص التوافق ══════════
   كل بند إمّا شرط تنشره المنصّة، أو ممارسة مستقرّة بين الصنّاع.
   المصدر مذكور في كل بند حتى لا يختلط المؤكَّد بالمرجَّح. */
function auditReel(project, render) {
  const st = project.settings || {};
  const c = project.screens.content;
  const h = project.screens.hook;
  const TL = timeline(project);
  const pts = (c.points || []).filter(p => p.head || p.body);
  const out = [];
  const add = (ok, level, title, why) => out.push({ ok, level, title, why });

  /* شروط تنشرها إنستغرام */
  add(true, 'منصّة', 'المقاس 1080×1920 (9:16)',
      'مقاس الريلز الذي تنشره إنستغرام — ثابت في هذا الاستوديو.');

  add(st.safeLayout !== false, 'منصّة', 'المحتوى خارج مناطق الواجهة',
      st.safeLayout !== false
        ? 'التخطيط الآمن مفعّل، فالبطاقة داخل الحدود التي لا تغطّيها الواجهة.'
        : 'التخطيط الآمن مطفأ — السؤال والمعرّف أسفل البطاقة قد تغطّيهما واجهة إنستغرام.');

  add((st.fps || 30) >= 30, 'منصّة', 'معدّل الإطارات ٣٠ فأكثر',
      'إنستغرام توصي بـ٣٠ إطارًا فأكثر. الحالي: ' + (st.fps || 30) + '.');

  /* ممارسات مستقرّة، لا شروطًا معلنة */
  const hookLen = (h.text || '').length;
  add(hookLen > 0 && hookLen <= 60, 'ممارسة', 'الهوك قصير يُقرأ في ثانيتين',
      hookLen === 0 ? 'لا يوجد نص هوك.'
        : hookLen <= 60 ? `${hookLen} حرفًا — يُقرأ في زمن الهوك.`
        : `${hookLen} حرفًا — طويل، قد لا يكتمل قراءةً قبل انتقال المشهد.`);

  const perPoint = pts.length ? (TL.content[1] - TL.content[0]) / pts.length : 0;
  add(pts.length > 0 && perPoint >= 0.85, 'ممارسة', 'لكل نقطة زمن كافٍ',
      pts.length === 0 ? 'لا توجد نقاط.'
        : `${perPoint.toFixed(2)} ثانية للنقطة الواحدة` +
          (perPoint >= 0.85 ? '.' : ' — قصير. زد المدّة أو أنقص النقاط.'));

  const longest = pts.reduce((m, p) => Math.max(m, (p.head||'').length + (p.body||'').length), 0);
  add(longest <= 110, 'ممارسة', 'شروح النقاط مختصرة',
      `أطول نقطة ${longest} حرفًا` + (longest <= 110 ? '.' : ' — اختصرها لتُقرأ في زمنها.'));

  add(st.loop !== false, 'ممارسة', 'الحلقة سلسة',
      st.loop !== false
        ? 'آخر المقطع يذوب في أوله، فتبدو الإعادة بلا قطع وترتفع المشاهدة المتكرّرة.'
        : 'الحلقة مطفأة — ينتهي المقطع بقطع ظاهر عند الإعادة.');

  add(!!(c.cta || '').trim(), 'ممارسة', 'سؤال ختام يطلب تعليقًا',
      (c.cta || '').trim() ? 'موجود.' : 'غائب — السؤال يفتح باب التعليقات.');

  /* المقروئية على الجوال — يُقاس من آخر رسمة فعلية */
  if (render && render.lastTextPt) {
    const pt = render.lastTextPt;
    add(pt >= 9, 'ممارسة', 'حجم النصّ مقروء على الجوال',
        `أصغر نصّ يظهر بحجم ${pt}pt على جوال عرضه 390pt` +
        (pt >= 9 ? '.' : ' — أنقص النقاط أو زد المدّة.') +
        (render.lastNoBody ? ' وقد أُسقطت الشروح تلقائيًا لتبقى العناوين مقروءة.' : ''));
    if (render.lastOverflow) {
      add(false, 'ممارسة', 'المحتوى يتجاوز البطاقة',
          'النقاط أكثر من أن تتّسع حتى بعد التصغير — أنقصها.');
    }
  }

  add(!!(c.outro || '').trim(), 'ممارسة', 'خاتمة تستحقّ الحفظ',
      (c.outro || '').trim() ? 'موجودة.' : 'غائبة — الخاتمة المكثّفة تدفع للحفظ والمشاركة.');

  return out;
}
