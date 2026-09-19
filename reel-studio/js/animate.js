/* ═══════════════════════════════════════════════════════
   animate.js — محرّك الحركة
   يحوّل المشروع من ثلاث صور ثابتة إلى تسلسل زمني متحرّك
   مدّته 13 ثانية، يُعرض في المعاينة ويُسجَّل فيديو.
   ═══════════════════════════════════════════════════════ */

/* التسلسل الزمني — نفس التوقيت المقترح لـ CapCut */
const T_HOOK    = [0,  3];
const T_CONTENT = [3,  11];
const T_FLOW    = [11, 13];
const T_TOTAL   = 13;

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

/* تقدّم مقطع زمني: 0 قبل البداية، 1 بعد النهاية */
function seg(t, from, to) {
  if (to <= from) return t >= to ? 1 : 0;
  return Math.max(0, Math.min(1, (t - from) / (to - from)));
}

/* يحوّل تقدّمًا خامًا إلى تأثير بصري حسب النمط المختار */
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
    default: {                     /* انزلاق — من جهة البداية في RTL */
      const e = ease.out(p);
      return { a: Math.min(1, p * 1.5), tx: (1 - e) * 120, ty:0, s:1 };
    }
  }
}

/* ══════════ حالة الحركة عند لحظة معيّنة ══════════ */

/* يرجّع الشاشة المعروضة وكائن الحركة الموافق للزمن t */
function animAt(project, t) {
  const motion = (project.settings && project.settings.motion) || 'slide';
  const pts = (project.screens.content.points || []).filter(p => p.head || p.body);
  const n = Math.max(1, pts.length);

  if (t < T_CONTENT[0]) {
    /* ── الهوك ── */
    const o = t - T_HOOK[0], len = T_HOOK[1] - T_HOOK[0];
    return { screen:'hook', anim: {
      motion,
      bg:     seg(o, 0,    0.45),
      badge:  seg(o, 0.20, 0.75),
      text:   seg(o, 0.40, 1.30),
      sub:    seg(o, 1.10, 1.70),
      out:    1 - seg(o, len - 0.28, len)      /* تلاشٍ قبل الانتقال */
    }};
  }

  if (t < T_FLOW[0]) {
    /* ── المحتوى ── */
    const o = t - T_CONTENT[0], len = T_CONTENT[1] - T_CONTENT[0];
    /* النقاط تتتابع داخل نافذة ثابتة مهما كان عددها.
       النافذة تبدأ باكرًا حتى لا تبقى البطاقة بيضاء فارغة،
       وتنتهي مبكرًا بما يكفي ليبقى السؤال ظاهرًا ثانيتين. */
    const winFrom = 0.80, winTo = len - 2.75;
    const step = Math.min(0.55, Math.max(0.15, (winTo - winFrom) / n));
    const points = [];
    for (let i = 0; i < n; i++) {
      const s = winFrom + i * step;
      points.push(seg(o, s, s + Math.min(0.5, step * 1.5)));
    }
    return { screen:'content', anim: {
      motion,
      card:    seg(o, 0,    0.38),
      graphic: seg(o, 0.14, 0.60),
      title:   seg(o, 0.34, 0.88),
      points,
      outro:   seg(o, len - 2.55, len - 1.95),
      footer:  seg(o, len - 1.85, len - 1.35),
      out:     1 - seg(o, len - 0.28, len)
    }};
  }

  /* ── المتدفّق ── */
  const o = t - T_FLOW[0], len = T_FLOW[1] - T_FLOW[0];
  return { screen:'flow', anim: {
    motion,
    text: seg(o, 0.05, 0.85),
    out:  1 - seg(o, len - 0.55, len)
  }};
}

/* يرسم الإطار الموافق للزمن t */
function renderAt(rc, project, t) {
  const { screen, anim } = animAt(project, t);
  rc.render(project, screen, anim);
  return screen;
}

/* ══════════ تشغيل التسلسل بالزمن الحقيقي ══════════ */

/* يشغّل التسلسل كاملًا ويستدعي onTick كل إطار.
   التسجيل يلتقط اللوحة حيّةً، فلا بدّ أن يجري بالزمن الحقيقي. */
function playTimeline(rc, project, onTick, onScreen) {
  return new Promise(resolve => {
    const t0 = performance.now();
    let last = null;
    const step = () => {
      const t = (performance.now() - t0) / 1000;
      if (t >= T_TOTAL) {
        renderAt(rc, project, T_TOTAL - 0.001);
        onTick && onTick(T_TOTAL, 1);
        resolve();
        return;
      }
      const sc = renderAt(rc, project, t);
      if (sc !== last) { last = sc; onScreen && onScreen(sc); }
      onTick && onTick(t, t / T_TOTAL);
      requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  });
}

/* ══════════ مناطق الأمان ══════════
   إنستغرام وفيسبوك يغطّيان أطراف الريل بواجهتهما:
   اسم الحساب والوصف أسفلَ اليسار، وأزرار التفاعل أسفلَ اليمين،
   وشريط الحالة أعلى الشاشة. ما يقع داخل هذه المناطق قد لا يُرى. */
const SAFE_ZONES = {
  top:    { y:0,    h:0.07, label:'شريط الحالة' },
  bottom: { y:0.82, h:0.18, label:'واجهة المنصّة — الحساب والتفاعل' },
  side:   { w:0.14,         label:'أزرار التفاعل' }
};

/* يرسم دليل مناطق الأمان فوق اللوحة — للمعاينة فقط، لا يُصدَّر */
function drawSafeZones(rc) {
  const ctx = rc.ctx, W = rc.W, H = rc.H;
  ctx.save();
  ctx.fillStyle = 'rgba(239,68,68,.22)';
  ctx.fillRect(0, 0, W, H * SAFE_ZONES.top.h);
  ctx.fillRect(0, H * SAFE_ZONES.bottom.y, W, H * SAFE_ZONES.bottom.h);
  ctx.fillRect(0, H * 0.55, W * SAFE_ZONES.side.w, H * (SAFE_ZONES.bottom.y - 0.55));

  ctx.strokeStyle = 'rgba(16,185,129,.9)';
  ctx.lineWidth = 4;
  ctx.setLineDash([18, 14]);
  ctx.strokeRect(W * SAFE_ZONES.side.w, H * SAFE_ZONES.top.h,
                 W * (1 - SAFE_ZONES.side.w), H * (SAFE_ZONES.bottom.y - SAFE_ZONES.top.h));
  ctx.setLineDash([]);

  ctx.font = '700 30px Tajawal, sans-serif';
  ctx.fillStyle = 'rgba(16,185,129,1)';
  ctx.direction = 'rtl';
  ctx.textAlign = 'center';
  ctx.fillText('المنطقة الآمنة', W / 2, H * SAFE_ZONES.top.h + 46);
  ctx.restore();
}
