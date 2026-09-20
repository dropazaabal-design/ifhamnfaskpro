/* ═══════════════════════════════════════════════════════
   canva.js — حزمة تُبنى في Canva
   Canva لا تستورد ملف تصميم من خارجها، فأقرب طريق عملي:
   خلفيات جاهزة بلا نصّ + النصوص للّصق + دليل بإحداثيات
   وأحجام مأخوذة من التخطيط الفعلي لا من حساب موازٍ.
   ═══════════════════════════════════════════════════════ */

/* أسماء حركات Canva الأقرب لكل نمط عندنا.
   أسماء Canva تتغيّر بين حين وآخر، فنذكر بديلًا لكل واحدة. */
const CANVA_MOTION = {
  slide: { name:'Pan',  alt:'Slide أو Rise' },
  fade:  { name:'Fade', alt:'Dissolve' },
  zoom:  { name:'Pop',  alt:'Breathe' },
  pop:   { name:'Pop',  alt:'Stomp' }
};

/* متى تظهر كل نقطة — نستنبطه بالعيّنات من محرّك الحركة نفسه،
   فلا يتباعد الدليل عن السلوك الحقيقي إن تغيّرت المعادلة. */
function pointStartTimes(project) {
  const TL = timeline(project);
  const n = (project.screens.content.points || []).filter(p => p.head || p.body).length;
  const out = new Array(n).fill(null);
  for (let t = TL.content[0]; t <= TL.content[1]; t += 0.02) {
    const a = animAt(project, t).anim;
    if (!a || !a.points) continue;
    for (let i = 0; i < n; i++) {
      if (out[i] === null && a.points[i] > 0.02) out[i] = +t.toFixed(2);
    }
  }
  return out.map((v, i) => v === null ? +(TL.content[0] + i * 0.4).toFixed(2) : v);
}

function fmtHex(h) { return String(h || '').toUpperCase(); }

/* تمييز المعدود: 2 مثنّى · 3-10 جمع · 11-99 مفرد منصوب */
function arCount(n, one, two, few, many) {
  if (n === 1) return one;
  if (n === 2) return two;
  if (n >= 3 && n <= 10) return toArabicDigits(n) + ' ' + few;
  return toArabicDigits(n) + ' ' + many;
}

/* يبني دليل البناء نصًّا عربيًّا مقروءًا */
function buildCanvaGuide(project, rc) {
  const th = resolveTheme(project.theme, project.colorOverrides);
  const st = project.settings || {};
  const TL = timeline(project);
  const L = rc.layout || {};
  const c = project.screens.content;
  const h = project.screens.hook;
  const pts = (c.points || []).filter(p => p.head || p.body);
  const starts = pointStartTimes(project);
  const mo = CANVA_MOTION[st.motion || 'slide'] || CANVA_MOTION.slide;
  const bodyFont = st.font || 'Tajawal';
  /* عنوان البطاقة يُرسم بـCairo دائمًا، والهوك بـCairo إن كان
     الخطّ المختار Tajawal — وإلّا فبالخطّ المختار نفسه. */
  const titleFont = 'Cairo';
  const hookFont  = bodyFont === 'Tajawal' ? 'Cairo' : bodyFont;

  const L1 = [];
  const P = x => L1.push(x);
  const line = () => P('─'.repeat(52));

  P('═══════════════════════════════════════════════════');
  P('  بناء الريل في Canva — ' + project.name);
  P('═══════════════════════════════════════════════════');
  P('');
  P('كل الأرقام هنا مأخوذة من التخطيط الفعلي للتصميم،');
  P('ووحدتها بكسل على لوحة 1080×1920 — وهي نفس وحدة Canva');
  P('حين يكون التصميم بهذا المقاس، فانقلها كما هي.');
  P('');

  line();
  P('١ — أنشئ التصميم');
  line();
  P('Canva ← Create a design ← Custom size');
  P('العرض 1080 بكسل · الارتفاع 1920 بكسل');
  P('(أو اختر جاهزًا: Instagram Reel / Mobile Video)');
  P('');
  P('المدّة الكلّية: ' + TL.total + ' ثانية على ثلاث صفحات:');
  P('  صفحة ١ — الهوك     : ' + (TL.hook[1] - TL.hook[0]).toFixed(1) + ' ث');
  P('  صفحة ٢ — المحتوى   : ' + (TL.content[1] - TL.content[0]).toFixed(1) + ' ث');
  P('  صفحة ٣ — الخاتمة   : ' + (TL.flow[1] - TL.flow[0]).toFixed(1) + ' ث');
  P('');

  line();
  P('٢ — الألوان');
  line();
  P('أضفها في Brand Kit أو من منتقي اللون:');
  P('  المميّز    ' + fmtHex(th.accent));
  P('  الخلفية    ' + fmtHex(th.bg));
  P('  البطاقة    ' + fmtHex(th.card));
  P('  اللوحة     ' + fmtHex(th.panel));
  P('  العنوان    ' + fmtHex(th.title));
  P('  المتن      ' + fmtHex(th.txt));
  P('  الاقتباس   ' + fmtHex(th.quote));
  P('');

  line();
  P('٣ — الخطوط');
  line();
  P('نصّ الهوك      : ' + hookFont + ' — وزن Black');
  P('عنوان البطاقة  : ' + titleFont + ' — وزن Black');
  P('النقاط والمتن  : ' + bodyFont + ' — ExtraBold للعنوان، عادي للشرح');
  P('النصّ المتدفّق  : ' + bodyFont + ' — وزن Bold');
  P('');
  P('Cairo وTajawal متوفّران عادةً في Canva. إن لم تجدهما');
  P('فارفعهما من Brand Kit (يحتاج Canva Pro)، أو استعمل');
  P('أقرب بديل عربي ثقيل متاح عندك.');
  P('');
  P('⚠ اضبط محاذاة النصّ إلى اليمين واتجاه الفقرة RTL.');
  P('');

  /* ── الصفحة ١ ── */
  line();
  P('٤ — الصفحة ١: الهوك   (' + (TL.hook[1] - TL.hook[0]).toFixed(1) + ' ث)');
  line();
  P('الخلفية: ارفع 1-hook-plate.png واجعلها تملأ الصفحة.');
  P('');
  if (L.hook && L.hook.badge) {
    P('● شارة الصيغة  «' + (h.badge || '') + '»');
    P('   حجم الخطّ ' + L.hook.badge.size + ' · وزن ثقيل · لون النصّ أبيض');
    P('   خلفية الشارة ' + fmtHex(th.accent) + ' · زوايا دائرية كاملة');
    P('   مركزها أفقيًا · أعلى الشارة عند y = ' + Math.round(L.hook.badge.y));
    P('');
  }
  if (L.hook && L.hook.text) {
    P('● نصّ الهوك');
    P('   حجم الخطّ ' + Math.round(L.hook.text.size) + ' · وزن Black · أبيض');
    P('   تباعد الأسطر ' + (L.hook.text.lineHeight / L.hook.text.size).toFixed(2));
    P('   عرض مربّع النصّ ' + Math.round(L.hook.text.maxW) +
      ' · x = ' + Math.round((1080 - L.hook.text.maxW) / 2));
    P('   أعلى المربّع عند y = ' + Math.round(L.hook.text.y));
    P('   محاذاة وسط · ظلّ خفيف أسود لرفع الوضوح');
    P('');
  }
  if (L.hook && L.hook.sub) {
    P('● السطر الفرعي');
    P('   حجم ' + L.hook.sub.size + ' · أبيض بشفافية 62٪ · محاذاة وسط');
    P('   y = ' + Math.round(L.hook.sub.y));
    P('');
  }
  if (L.hook && L.hook.brand) {
    P('● المعرّف');
    P('   حجم ' + L.hook.brand.size + ' · أبيض بشفافية 42٪ · محاذاة وسط');
    P('   y = ' + Math.round(L.hook.brand.y) + '  ⚠ اتجاه LTR');
    P('');
  }

  /* ── الصفحة ٢ ── */
  line();
  P('٥ — الصفحة ٢: المحتوى   (' + (TL.content[1] - TL.content[0]).toFixed(1) + ' ث)');
  line();
  P('الخلفية: ارفع 2-content-plate.png واجعلها تملأ الصفحة.');
  P('(فيها البطاقة والشريط العلوي وشريط التقدّم — كلّها جاهزة)');
  P('');
  if (L.content && L.content.title) {
    P('● العنوان  «' + (c.title || '') + '»');
    P('   حجم ' + Math.round(L.content.title.size) + ' · وزن Black · لون ' + fmtHex(th.title));
    P('   تباعد الأسطر ' +
      (L.content.title.lineHeight / L.content.title.size).toFixed(2));
    P('   محاذاة يمين · الحافة اليمنى عند x = ' + Math.round(L.content.title.rightX));
    P('   أعلى المربّع عند y = ' + Math.round(L.content.title.y));
    if (c.emoji) {
      P('   ضع الإيموجي ' + c.emoji + ' يمين العنوان بحجم ' +
        Math.round(L.content.title.size * 0.94));
    }
    P('');
  }
  if (L.content && L.content.points) {
    const pp = L.content.points;
    P('● النقاط — ' + arCount(pts.length, 'نقطة واحدة', 'نقطتان', 'نقاط', 'نقطة'));
    P('   أول نقطة: أعلاها عند y = ' + Math.round(pp.startY));
    P('   حجم خطّ العنوان ' + Math.round(pp.headSize) +
      (pp.noBody ? '' : ' · حجم الشرح ' + Math.round(pp.bodySize)));
    P('   مربّع الترقيم ' + Math.round(pp.numSize) + '×' + Math.round(pp.numSize) +
      ' بلون ' + fmtHex(th.accent) + ' على الحافة اليمنى');
    if (pp.noBody) {
      P('   ⚠ الشروح مُسقَطة هنا عمدًا: النقاط كثيرة، فلو أُبقيت');
      P('     لنزل النصّ تحت حدّ المقروئية على الجوال.');
    }
    P('');
    P('   ارتفاع كل نقطة ومسافتها:');
    pts.forEach((p, i) => {
      P('     ' + toArabicDigits(i + 1) + '. ارتفاع ' + Math.round(pp.heights[i]) +
        ' · تبدأ عند ' + starts[i] + ' ث');
    });
    P('');
  }
  if (L.content && L.content.bar) {
    P('● شريط التقدّم  (اختياري)');
    P('   الشريط في الخلفية مرسوم ممتلئًا. ولتجعله يمتلئ مع النقاط:');
    P('   ارسم مستطيلًا بلون ' + fmtHex(th.accent) + ' فوقه بنفس مقاسه');
    P('   x = ' + Math.round(L.content.bar.x) + ' · y = ' + Math.round(L.content.bar.y) +
      ' · عرض ' + Math.round(L.content.bar.w) + ' · ارتفاع ' + Math.round(L.content.bar.h));
    P('   ثم أعطه حركة Wipe من اليمين، ومدّتها مدّة تتابع النقاط.');
    P('');
    P('● عدّاد النقاط  (اختياري)');
    P('   شارة صغيرة «٣ / ' + toArabicDigits(pts.length) + '» أسفل يسار الجرافيك،');
    P('   بخلفية سوداء شفافة 42٪ وزوايا دائرية كاملة، حجم الخطّ 24.');
    P('   في Canva تحتاج نسخة لكل رقم مع توقيت مطابق لتوقيت نقطته.');
    P('');
  }

  if (L.content && L.content.outro) {
    P('● الخاتمة');
    P('   مربّع بلون ' + fmtHex(th.panel) + ' · زوايا 18');
    P('   x = ' + Math.round(L.content.outro.x) + ' · y = ' + Math.round(L.content.outro.y));
    P('   عرض ' + Math.round(L.content.outro.w) + ' · ارتفاع ' + Math.round(L.content.outro.h));
    P('   النصّ حجم ' + L.content.outro.size + ' · وزن ثقيل · لون ' + fmtHex(th.quote));
    P('   شريط رفيع بلون الاقتباس على حافته اليمنى');
    P('');
  }
  if (L.content && L.content.footer) {
    P('● التذييل');
    P('   السؤال  : حجم ' + L.content.footer.ctaSize + ' · لون ' + fmtHex(th.cta) +
      ' · يمين عند x = ' + Math.round(L.content.footer.ctaRight));
    P('   المعرّف : حجم ' + L.content.footer.brandSize + ' · لون ' + fmtHex(th.accent) +
      ' · يسار عند x = ' + Math.round(L.content.footer.brandLeft) + ' · اتجاه LTR');
    P('   كلاهما على y = ' + Math.round(L.content.footer.y));
    P('');
  }

  /* ── الصفحة ٣ ── */
  line();
  P('٦ — الصفحة ٣: الخاتمة   (' + (TL.flow[1] - TL.flow[0]).toFixed(1) + ' ث)');
  line();
  P('الخلفية: ارفع 3-flow-plate.png.');
  P('');
  if (L.flow) {
    P('● النصّ المتدفّق');
    P('   حجم ' + Math.round(L.flow.size) + ' · وزن ثقيل · لون ' + fmtHex(L.flow.fg));
    P('   تباعد الأسطر ' + (L.flow.lineHeight / L.flow.size).toFixed(2));
    P('   عرض المربّع ' + Math.round(L.flow.maxW) +
      ' · x = ' + Math.round((1080 - L.flow.maxW) / 2));
    P('   أعلى المربّع عند y = ' + Math.round(L.flow.y) + ' · محاذاة وسط');
    P('');
  }

  /* ── الحركة ── */
  line();
  P('٧ — الحركة والتوقيت');
  line();
  P('النمط المختار عندك: ' + (MOTION[st.motion || 'slide'] || MOTION.slide).label);
  P('أقرب ما يقابله في Canva: ' + mo.name + '  (بديل: ' + mo.alt + ')');
  P('');
  P('على كل عنصر: Animate ← اختر الحركة.');
  P('');
  P('ولتتابع النقاط واحدةً تلو الأخرى استعمل الخطّ الزمني');
  P('(Timeline أسفل المحرّر): اسحب بداية كل عنصر إلى وقته');
  P('من الجدول أعلاه. بدون ذلك ستظهر النقاط دفعةً واحدة.');
  P('');
  if (st.loop !== false) {
    P('● الحلقة السلسة');
    P('   في تصميمنا يذوب آخر المقطع في أوّله فلا يظهر قطع');
    P('   عند الإعادة. لمحاكاتها في Canva: كرّر صفحة الهوك في');
    P('   آخر التصميم بمدّة 0.5 ث، وأعطها حركة Fade داخلة فقط.');
    P('');
  }

  line();
  P('٨ — التصدير من Canva');
  line();
  P('Share ← Download ← MP4 Video');
  P('الجودة: الأعلى المتاحة. وCanva تصدّر 1080×1920 تلقائيًا');
  P('ما دام التصميم بهذا المقاس.');
  P('');
  P('⚠ الصوت: أضفه داخل Canva أو عند الرفع لإنستغرام.');
  P('   الصوت المختار من مكتبة المنصّة يدخل في التوصيات.');
  P('');

  line();
  P('٩ — قبل النشر');
  line();
  auditReel(project, rc).forEach(it => {
    P('  [' + (it.ok ? '✓' : '!') + '] (' + it.level + ') ' + it.title);
    if (!it.ok) P('        ' + it.why);
  });
  P('');
  P('بنود «منصّة» شروط تنشرها إنستغرام. وبنود «ممارسة» ليست');
  P('معلنة — هي ما استقرّ عليه صنّاع المحتوى، فخذها اجتهادًا.');
  P('');

  return L1.join('\n');
}

/* نصوص جاهزة للّصق */
function buildCanvaTexts(project) {
  const c = project.screens.content;
  const h = project.screens.hook;
  const pts = (c.points || []).filter(p => p.head || p.body);
  const o = [];
  o.push('نصوص الريل — انسخ والصق في Canva');
  o.push('═'.repeat(44));
  o.push('');
  o.push('▸ الصفحة ١ — الهوك');
  o.push('');
  o.push('الشارة:');
  o.push(h.badge || '');
  o.push('');
  o.push('النصّ:');
  o.push(h.text || '');
  o.push('');
  if (h.sub) { o.push('السطر الفرعي:'); o.push(h.sub); o.push(''); }
  o.push('المعرّف:');
  o.push(c.brand || '@kitabwbs');
  o.push('');
  o.push('▸ الصفحة ٢ — المحتوى');
  o.push('');
  o.push('العنوان:');
  o.push((c.emoji ? c.emoji + ' ' : '') + (c.title || ''));
  o.push('');
  pts.forEach((p, i) => {
    o.push('النقطة ' + toArabicDigits(i + 1) + ':');
    o.push(p.head || '');
    if (p.body) { o.push('  الشرح: ' + p.body); }
    o.push('');
  });
  if (c.outro) { o.push('الخاتمة:'); o.push(c.outro); o.push(''); }
  if (c.cta)   { o.push('السؤال:');  o.push(c.cta);   o.push(''); }
  o.push('▸ الصفحة ٣ — الخاتمة');
  o.push('');
  o.push(ReelCanvas.buildFlowText(c));
  o.push('');
  if (project.caption) {
    o.push('▸ الكابشن');
    o.push('');
    o.push(project.caption);
  }
  return o.join('\n');
}

/* يبني الحزمة كاملةً ويُنزّلها */
function exportCanvaKit(rc, project) {
  const files = [];
  const base = safeName(project.name);
  const enc = new TextEncoder();

  /* خلفيات بلا نصّ + مرجع مرسوم كاملًا */
  const screens = [['hook','1-hook'], ['content','2-content'], ['flow','3-flow']];
  screens.forEach(([sc, name]) => {
    rc.render(project, sc, null, true);          /* اللوح: بلا نصّ */
    files.push({ name: `${name}-plate.png`, bytes: dataURLToBytes(rc.exportPNG(1)) });
  });
  screens.forEach(([sc, name]) => {
    rc.render(project, sc);                      /* المرجع الكامل */
    files.push({ name: `مرجع/${name}-full.png`, bytes: dataURLToBytes(rc.exportPNG(1)) });
  });

  /* الدليل يُبنى بعد رسمة كاملة حتى يكون التخطيط مسجَّلًا */
  rc.render(project, 'content');
  const guide = buildCanvaGuide(project, rc);
  files.push({ name:'دليل-البناء-في-Canva.txt', bytes: enc.encode(guide) });
  files.push({ name:'نصوص-جاهزة.txt', bytes: enc.encode(buildCanvaTexts(project)) });
  files.push({ name:'المشروع.json', bytes: enc.encode(exportProjectJSON(project)) });

  saveBlob(makeZip(files), `${base}-canva-${stamp()}.zip`);
  return files.length;
}
