/* ═══════════════════════════════════════════════════════
   canvas.js — محرّك الرسم
   كل الرسم يتم على لوحة 1080×1920 (مقاس ريلز/فيسبوك)
   والعرض في الصفحة يُصغَّر بـ CSS فقط، فالتصدير دائمًا بدقّة كاملة.
   ═══════════════════════════════════════════════════════ */

const REEL_W = 1080, REEL_H = 1920;

/* الأرقام العربية-الهندية للترقيم داخل الريل */
function toArabicDigits(n) {
  return String(n).replace(/\d/g, d => '٠١٢٣٤٥٦٧٨٩'[d]);
}

/* عائلات الخطوط المتاحة */
const FONTS = {
  Tajawal: { label:'Tajawal ExtraBold — موصى به للعنوان', stack:"'Tajawal',sans-serif", head:800, body:500 },
  Cairo:   { label:'Cairo Black — للعناوين الثقيلة',       stack:"'Cairo',sans-serif",  head:900, body:600 },
  Almarai: { label:'Almarai Regular — للمتن',              stack:"'Almarai',sans-serif",head:800, body:400 },
  Plex:    { label:'IBM Plex Sans Arabic — تحريري',        stack:"'IBM Plex Sans Arabic',sans-serif", head:700, body:400 }
};

class ReelCanvas {
  constructor(canvasEl, width = REEL_W, height = REEL_H) {
    this.canvas = canvasEl;
    this.W = width;
    this.H = height;
    this.canvas.width = width;
    this.canvas.height = height;
    this.ctx = canvasEl.getContext('2d');
    this._imgCache = new Map();
    this.onReady = null;      // يُستدعى بعد تحميل صورة ليُعاد الرسم
    this.lastFit = 1;         // معامل التصغير التلقائي الأخير
  }

  /* ── أدوات أساسية ── */

  font(size, weight, family) {
    const f = FONTS[family] || FONTS.Tajawal;
    return `${weight} ${Math.round(size)}px ${f.stack}`;
  }

  roundRect(x, y, w, h, r) {
    const ctx = this.ctx;
    const rr = Math.min(r, w / 2, h / 2);
    ctx.beginPath();
    ctx.moveTo(x + rr, y);
    ctx.arcTo(x + w, y,     x + w, y + h, rr);
    ctx.arcTo(x + w, y + h, x,     y + h, rr);
    ctx.arcTo(x,     y + h, x,     y,     rr);
    ctx.arcTo(x,     y,     x + w, y,     rr);
    ctx.closePath();
  }

  /* زوايا مستديرة من الأعلى فقط — لصورة الجرافيك داخل البطاقة */
  roundRectTop(x, y, w, h, r) {
    const ctx = this.ctx;
    const rr = Math.min(r, w / 2, h);
    ctx.beginPath();
    ctx.moveTo(x, y + h);
    ctx.lineTo(x, y + rr);
    ctx.arcTo(x, y, x + rr, y, rr);
    ctx.lineTo(x + w - rr, y);
    ctx.arcTo(x + w, y, x + w, y + rr, rr);
    ctx.lineTo(x + w, y + h);
    ctx.closePath();
  }

  /* تقسيم النص لأسطر لا تتجاوز العرض — يحترم أسطر المستخدم اليدوية */
  wrapText(text, maxWidth) {
    const out = [];
    String(text || '').split('\n').forEach(para => {
      const words = para.trim().split(/\s+/).filter(Boolean);
      if (!words.length) { out.push(''); return; }
      let line = words[0];
      for (let i = 1; i < words.length; i++) {
        const test = line + ' ' + words[i];
        if (this.ctx.measureText(test).width <= maxWidth) line = test;
        else { out.push(line); line = words[i]; }
      }
      out.push(line);
    });
    return out;
  }

  /* رسم نص عربي — الاتجاه rtl يجعل المتصفّح يشكّل الحروف ويرتّبها */
  drawTextRTL(text, x, y, align) {
    const ctx = this.ctx;
    ctx.direction = 'rtl';
    ctx.textAlign = align || 'right';
    ctx.fillText(text, x, y);
  }

  /* المعرّف والروابط تبدأ بـ @ أو http — لو رُسمت rtl انتقلت @ لآخر السطر */
  drawTextLTR(text, x, y, align) {
    const ctx = this.ctx;
    ctx.direction = 'ltr';
    ctx.textAlign = align || 'left';
    ctx.fillText(text, x, y);
  }

  /* يغلّف رسم عنصر واحد بتأثير الحركة الموافق لتقدّمه.
     الحساب الهندسي يجري خارجه دائمًا، فالتغليف لا يزحزح التخطيط. */
  withFx(p, motion, cx, cy, fn) {
    const ctx = this.ctx;
    if (p == null) { fn(); return; }
    const e = fx(p, motion);
    if (e.skip) return;
    ctx.save();
    ctx.globalAlpha *= e.a;
    if (e.tx || e.ty || e.s !== 1) {
      ctx.translate(cx + e.tx, cy + e.ty);
      if (e.s !== 1) ctx.scale(e.s, e.s);
      ctx.translate(-cx, -cy);
    }
    fn();
    ctx.restore();
  }

  /* صورة تملأ المساحة مع قصّ متوازن (object-fit: cover) */
  drawCover(img, x, y, w, h) {
    const ar = img.width / img.height, tr = w / h;
    let sw, sh, sx, sy;
    if (ar > tr) { sh = img.height; sw = sh * tr; sx = (img.width - sw) / 2; sy = 0; }
    else         { sw = img.width;  sh = sw / tr; sx = 0; sy = (img.height - sh) / 2; }
    this.ctx.drawImage(img, sx, sy, sw, sh, x, y, w, h);
  }

  /* تحميل كسول للصور المخزّنة كـ base64، مع إعادة رسم بعد الجاهزية */
  ensureImage(src) {
    if (!src) return null;
    const hit = this._imgCache.get(src);
    if (hit) return hit.complete && hit.naturalWidth ? hit : null;
    const img = new Image();
    img.onload = () => { if (typeof this.onReady === 'function') this.onReady(); };
    img.onerror = () => this._imgCache.delete(src);
    img.src = src;
    this._imgCache.set(src, img);
    return img.complete && img.naturalWidth ? img : null;
  }

  /* ════════════ الشاشة ١: الهوك ════════════ */

  drawHookScreen(data, theme, st, anim) {
    const ctx = this.ctx, W = this.W, H = this.H;
    const fam = st.font || 'Tajawal';
    const M = (st.margins || 42) + 46;

    ctx.clearRect(0, 0, W, H);
    ctx.fillStyle = theme.bg;
    ctx.fillRect(0, 0, W, H);

    /* خلفية اختيارية معتّمة حتى يبقى النص مقروءًا */
    const bg = this.ensureImage(data.bgImage);
    if (bg) {
      this.drawCover(bg, 0, 0, W, H);
      const g = ctx.createLinearGradient(0, 0, 0, H);
      g.addColorStop(0,   'rgba(0,0,0,.62)');
      g.addColorStop(0.5, 'rgba(0,0,0,.74)');
      g.addColorStop(1,   'rgba(0,0,0,.88)');
      ctx.fillStyle = g;
      ctx.fillRect(0, 0, W, H);
    } else {
      /* توهّج خفيف بلون الثيم يمنع الخلفية المسطّحة */
      const g = ctx.createRadialGradient(W / 2, H * 0.34, 60, W / 2, H * 0.34, W * 0.92);
      g.addColorStop(0, theme.accent + '2e');
      g.addColorStop(1, 'rgba(0,0,0,0)');
      ctx.fillStyle = g;
      ctx.fillRect(0, 0, W, H);
    }

    const maxW = W - M * 2;
    const cx = W / 2;
    const A = anim || null;
    ctx.save();
    if (A && A.out != null) ctx.globalAlpha = A.out;

    /* قياس كتلة النص أولًا حتى نتوسّط رأسيًا */
    let size = 104;
    let lines;
    ctx.font = this.font(size, 900, fam === 'Tajawal' ? 'Cairo' : fam);
    lines = this.wrapText(data.text || 'اكتب نص الهوك هنا', maxW);
    while (lines.length > 4 && size > 56) {
      size -= 6;
      ctx.font = this.font(size, 900, fam === 'Tajawal' ? 'Cairo' : fam);
      lines = this.wrapText(data.text || 'اكتب نص الهوك هنا', maxW);
    }
    const lh = size * 1.38;
    const textH = lines.length * lh;
    const badgeH = data.badge ? 104 : 0;
    const subH = data.sub ? 74 : 0;
    const blockH = badgeH + textH + subH;
    let y = (H - blockH) / 2;

    /* شارة الصيغة */
    if (data.badge) {
      const by = y;
      this.withFx(A && A.badge, A && A.motion, cx, by + 34, () => {
        ctx.font = this.font(38, 800, fam);
        ctx.direction = 'rtl';
        const tw = ctx.measureText(data.badge).width;
        const pw = tw + 62, ph = 68;
        ctx.fillStyle = theme.accent;
        this.roundRect(cx - pw / 2, by, pw, ph, ph / 2);
        ctx.fill();
        ctx.fillStyle = readableOn(theme.accent);
        ctx.textBaseline = 'middle';
        this.drawTextRTL(data.badge, cx, by + ph / 2 + 2, 'center');
      });
      y += badgeH;
    }

    /* نص الهوك */
    const ty = y;
    /* الأسطر تظهر متتابعة قليلًا فيبدو النص وكأنه يُكتب */
    lines.forEach((ln, i) => {
      const lp = A ? Math.max(0, Math.min(1, A.text * lines.length - i)) : null;
      this.withFx(lp, A && A.motion, cx, ty + lh * i + lh / 2, () => {
        ctx.font = this.font(size, 900, fam === 'Tajawal' ? 'Cairo' : fam);
        ctx.fillStyle = '#ffffff';
        ctx.textBaseline = 'middle';
        ctx.shadowColor = 'rgba(0,0,0,.5)';
        ctx.shadowBlur = 18;
        ctx.shadowOffsetY = 4;
        this.drawTextRTL(ln, cx, ty + lh * i + lh / 2, 'center');
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetY = 0;
      });
    });
    y += textH;

    /* سطر فرعي خافت */
    if (data.sub) {
      const sy = y;
      this.withFx(A && A.sub, A && A.motion, cx, sy + 40, () => {
        ctx.font = this.font(36, 500, fam);
        ctx.fillStyle = 'rgba(255,255,255,.62)';
        ctx.textBaseline = 'middle';
        this.drawTextRTL(data.sub, cx, sy + 40, 'center');
      });
    }

    /* العلامة في الأسفل */
    this.withFx(A && A.sub, A && A.motion, cx, H - 72, () => {
      ctx.font = this.font(30, 700, fam);
      ctx.fillStyle = 'rgba(255,255,255,.42)';
      ctx.textBaseline = 'alphabetic';
      this.drawTextLTR(data.brand || '@kitabwbs', cx, H - 72, 'center');
    });

    ctx.restore();
  }

  /* ════════════ الشاشة ٢: المحتوى ════════════ */

  /* قياس كتلة النقاط قبل الرسم — أساس التصغير التلقائي */
  measurePoints(points, innerW, headF, bodyF, gap, fam) {
    const ctx = this.ctx;
    const numW = 66;                      // عرض مربّع الترقيم + مسافته
    const textW = innerW - numW;
    const blocks = [];
    let total = 0;

    points.forEach(p => {
      ctx.font = this.font(headF, 800, fam);
      const headLines = p.head ? this.wrapText(p.head, textW) : [];
      ctx.font = this.font(bodyF, 400, fam);
      const bodyLines = p.body ? this.wrapText(p.body, textW) : [];
      const h = headLines.length * headF * 1.32
              + (bodyLines.length ? bodyLines.length * bodyF * 1.5 + 6 : 0);
      blocks.push({ headLines, bodyLines, h: Math.max(h, numW * 0.72) });
      total += blocks[blocks.length - 1].h + gap;
    });
    return { blocks, total: Math.max(0, total - gap), numW, textW };
  }

  drawContentScreen(data, theme, st, anim) {
    const ctx = this.ctx, W = this.W, H = this.H;
    const fam = st.font || 'Tajawal';
    const M = st.margins || 42;
    const RAD = (st.cornerRadius || 20) * 1.9;
    const PAD = 40;

    ctx.clearRect(0, 0, W, H);
    ctx.fillStyle = theme.bg;
    ctx.fillRect(0, 0, W, H);

    const cardX = M, cardY = M, cardW = W - M * 2, cardH = H - M * 2;
    const innerX = cardX + PAD, innerW = cardW - PAD * 2;
    const rightX = cardX + cardW - PAD;          // حافة البدء في RTL

    /* البطاقة كلها تدخل ككتلة واحدة، ثم تتتابع عناصرها داخلها */
    const A = anim || null;
    ctx.save();
    if (A) {
      ctx.globalAlpha = (A.out != null ? A.out : 1) * fx(A.card, 'fade').a;
      ctx.translate(0, (1 - ease.out(Math.max(0, Math.min(1, A.card)))) * 70);
    }

    /* جسم البطاقة */
    ctx.fillStyle = theme.card;
    this.roundRect(cardX, cardY, cardW, cardH, RAD);
    ctx.fill();

    let y = cardY;

    /* ── الجرافيك العلوي ── */
    const gH = st.graphicHeight || 340;
    this.withFx(A && A.graphic, A && A.motion, cardX + cardW / 2, cardY + gH / 2, () => {
    ctx.save();
    this.roundRectTop(cardX, cardY, cardW, gH, RAD);
    ctx.clip();
    const gimg = this.ensureImage(data.graphic);
    if (gimg) {
      this.drawCover(gimg, cardX, cardY, cardW, gH);
    } else {
      const g = ctx.createLinearGradient(cardX, cardY, cardX + cardW, cardY + gH);
      g.addColorStop(0, theme.accent);
      g.addColorStop(1, shade(theme.accent, -46));
      ctx.fillStyle = g;
      ctx.fillRect(cardX, cardY, cardW, gH);
      /* علامة مائية خفيفة بدل الصورة الفارغة */
      ctx.font = this.font(150, 900, 'Cairo');
      ctx.fillStyle = 'rgba(255,255,255,.14)';
      ctx.textBaseline = 'middle';
      this.drawTextRTL(data.emoji || '📌', cardX + cardW / 2, cardY + gH / 2, 'center');
    }
    ctx.restore();
    /* الحدّ المميّز تحت الجرافيك */
    ctx.fillStyle = theme.accent;
    ctx.fillRect(cardX, cardY + gH, cardW, 11);
    });
    y += gH + 11 + 34;

    /* ── سطر العنوان: إيموجي + عنوان ── */
    const titleF = (st.titleFontSize || 56) * 1.38;
    ctx.font = this.font(titleF * 0.94, 900, 'Cairo');
    ctx.textBaseline = 'middle';
    const emo = data.emoji || '';
    let emoW = emo ? ctx.measureText(emo).width + 20 : 0;
    ctx.font = this.font(titleF, 900, 'Cairo');
    const titleLines = this.wrapText(data.title || 'عنوان الريل', innerW - emoW).slice(0, 2);
    const tY = y;
    this.withFx(A && A.title, A && A.motion, rightX, tY + titleF * 0.6, () => {
      if (emo) {
        ctx.font = this.font(titleF * 0.94, 900, 'Cairo');
        ctx.textBaseline = 'middle';
        ctx.fillStyle = theme.title;
        this.drawTextRTL(emo, rightX, tY + titleF * 0.6, 'right');
      }
      ctx.font = this.font(titleF, 900, 'Cairo');
      ctx.fillStyle = theme.title;
      titleLines.forEach((ln, i) => {
        this.drawTextRTL(ln, rightX - emoW, tY + titleF * 0.6 + i * titleF * 1.26, 'right');
      });
    });
    y += titleLines.length * titleF * 1.26 + 30;

    /* ── الخاتمة والتذييل: نحجز مساحتهما قبل توزيع النقاط ── */
    const outroF = 40, ctaF = 34;
    ctx.font = this.font(outroF, 700, fam);
    const outroLines = data.outro ? this.wrapText(data.outro, innerW - 52) : [];
    const outroH = outroLines.length ? outroLines.length * outroF * 1.55 + 54 : 0;
    const footH = 92;

    const availH = (cardY + cardH) - y - outroH - footH - PAD - 16;

    /* ── النقاط مع تصغير تلقائي عند الامتلاء ── */
    const pts = (data.points || []).filter(p => p.head || p.body);
    let fit = 1;
    if (pts.length) {
      const gap0 = st.pointGap || 12;
      let m = this.measurePoints(pts, innerW, (st.ptFontSize || 34) * 1.22,
                                 (st.ptFontSize || 34) * 1.0, gap0, fam);
      if (m.total > availH && m.total > 0) {
        fit = Math.max(0.52, availH / m.total);
      }
      this.lastFit = fit;

      const headF = (st.ptFontSize || 34) * 1.22 * fit;
      const bodyF = (st.ptFontSize || 34) * 1.0 * fit;
      const gap   = gap0 * fit;
      m = this.measurePoints(pts, innerW, headF, bodyF, gap, fam);

      /* الفراغ الفائض يُوزَّع بين النقاط بدل أن يتجمّع فجوةً ميّتة قبل الخاتمة */
      const slack = Math.max(0, availH - m.total);
      const gapExtra = pts.length > 1 ? Math.min(slack / (pts.length - 1), 46) : 0;
      y += (slack - gapExtra * (pts.length - 1)) * 0.34;

      const numS = 52 * Math.max(0.72, fit);
      pts.forEach((p, i) => {
        const b = m.blocks[i];
        const pY = y;
        this.withFx(A && A.points && A.points[i], A && A.motion,
                    rightX, pY + b.h / 2, () => {
        /* مربّع الترقيم */
        ctx.fillStyle = theme.accent;
        this.roundRect(rightX - numS, pY + 4, numS, numS, numS * 0.32);
        ctx.fill();
        ctx.font = this.font(numS * 0.54, 800, 'Cairo');
        ctx.fillStyle = readableOn(theme.accent);
        ctx.textBaseline = 'middle';
        this.drawTextRTL(toArabicDigits(i + 1), rightX - numS / 2, pY + 4 + numS / 2 + 1, 'center');

        /* عنوان النقطة */
        let ly = pY;
        const tx = rightX - numS - 16;
        ctx.font = this.font(headF, 800, fam);
        ctx.fillStyle = theme.title;
        b.headLines.forEach(ln => {
          this.drawTextRTL(ln, tx, ly + headF * 0.78, 'right');
          ly += headF * 1.32;
        });
        /* شرح النقطة */
        if (b.bodyLines.length) {
          ly += 6;
          ctx.font = this.font(bodyF, 400, fam);
          ctx.fillStyle = theme.txt;
          b.bodyLines.forEach(ln => {
            this.drawTextRTL(ln, tx, ly + bodyF * 0.78, 'right');
            ly += bodyF * 1.5;
          });
        }
        });
        y += b.h + gap + gapExtra;
      });
      y -= gap + gapExtra;
    }

    /* ── الخاتمة الاقتباسية ── */
    if (outroLines.length) {
      const boxY = cardY + cardH - PAD - footH - outroH + 8;
      this.withFx(A && A.outro, A && A.motion, rightX, boxY + outroH / 2, () => {
      ctx.fillStyle = theme.panel;
      this.roundRect(innerX, boxY, innerW, outroH - 18, 18);
      ctx.fill();
      ctx.fillStyle = theme.quote;
      this.roundRect(rightX - 7, boxY + 14, 7, outroH - 46, 4);
      ctx.fill();

      ctx.font = this.font(outroF, 700, fam);
      ctx.fillStyle = theme.quote;
      ctx.textBaseline = 'middle';
      outroLines.forEach((ln, i) => {
        this.drawTextRTL(ln, rightX - 26, boxY + 30 + i * outroF * 1.55 + outroF * 0.5, 'right');
      });
      });
    }

    /* ── التذييل: السؤال + العلامة ── */
    const fy = cardY + cardH - PAD - 22;
    const brand = data.brand || '@kitabwbs';
    ctx.font = this.font(29, 700, fam);
    const brandW = ctx.measureText(brand).width;

    this.withFx(A && A.footer, A && A.motion, W / 2, fy, () => {
    if (data.cta) {
      ctx.font = this.font(ctaF, 800, fam);
      ctx.fillStyle = theme.cta;
      ctx.textBaseline = 'middle';
      /* نترك للمعرّف عرضه كاملًا + فاصلًا، فلا يتداخل السطران */
      const ctaLines = this.wrapText(data.cta, innerW - brandW - 34);
      this.drawTextRTL(ctaLines[0], rightX, fy, 'right');
    }

    ctx.font = this.font(29, 700, fam);
    ctx.fillStyle = theme.accent;
    ctx.textBaseline = 'middle';
    this.drawTextLTR(brand, innerX, fy, 'left');
    });

    ctx.restore();
  }

  /* ════════════ الشاشة ٣: المتدفّق ════════════ */

  /* نص المتدفّق يُبنى تلقائيًا من عناوين النقاط + الخاتمة */
  static buildFlowText(content) {
    const heads = (content.points || []).map(p => (p.head || '').trim()).filter(Boolean);
    const parts = [];
    if (content.title) parts.push(content.title.trim());
    if (heads.length) parts.push(heads.join('،\n'));
    if (content.outro) parts.push(content.outro.trim());
    return parts.join('\n\n');
  }

  drawFlowScreen(data, theme, st, themeKey, anim) {
    const ctx = this.ctx, W = this.W, H = this.H;
    const fam = st.font || 'Tajawal';
    const dark = isDarkTheme(themeKey);
    const bg = dark ? '#000000' : '#ffffff';
    const fg = dark ? '#f5f5f5' : '#111827';

    ctx.clearRect(0, 0, W, H);
    ctx.fillStyle = bg;
    ctx.fillRect(0, 0, W, H);

    /* شريطان بلون الثيم أعلى وأسفل */
    ctx.fillStyle = theme.accent;
    ctx.fillRect(0, 0, W, 13);
    ctx.fillRect(0, H - 13, W, 13);

    const M = (st.margins || 42) + 62;
    const maxW = W - M * 2;
    const text = (data.text || '').trim() || 'النص المتدفّق يُبنى تلقائيًا من عناوين النقاط والخاتمة.';

    let size = 62;
    ctx.font = this.font(size, 700, fam);
    let lines = this.wrapText(text, maxW);
    while (lines.length * size * 1.92 > H - 360 && size > 30) {
      size -= 3;
      ctx.font = this.font(size, 700, fam);
      lines = this.wrapText(text, maxW);
    }

    const lh = size * 1.92;
    const totalH = lines.length * lh;
    const y = (H - totalH) / 2;
    const A = anim || null;

    ctx.save();
    if (A && A.out != null) ctx.globalAlpha = A.out;
    this.withFx(A && A.text, A && A.motion, W / 2, H / 2, () => {
      ctx.fillStyle = fg;
      ctx.textBaseline = 'middle';
      ctx.font = this.font(size, 700, fam);
      lines.forEach((ln, i) => {
        if (!ln) return;
        this.drawTextRTL(ln, W / 2, y + lh * i + lh / 2, 'center');
      });

      /* العلامة */
      ctx.font = this.font(30, 700, fam);
      ctx.fillStyle = dark ? 'rgba(255,255,255,.45)' : 'rgba(17,24,39,.42)';
      ctx.textBaseline = 'alphabetic';
      this.drawTextLTR(data.brand || '@kitabwbs', W / 2, H - 76, 'center');
    });
    ctx.restore();
  }

  /* ════════════ الواجهة العامة ════════════ */

  /* anim اختياري — بدونه يُرسم الإطار ثابتًا كما كان */
  render(project, screen, anim) {
    const theme = resolveTheme(project.theme, project.colorOverrides);
    const st = project.settings || DEFAULTS.settings;
    const s = project.screens;
    const brand = s.content.brand;

    if (screen === 'hook') {
      this.drawHookScreen(Object.assign({}, s.hook, { brand }), theme, st, anim);
    } else if (screen === 'flow') {
      const text = s.flow.auto === false && s.flow.text
        ? s.flow.text
        : ReelCanvas.buildFlowText(s.content);
      this.drawFlowScreen({ text, brand }, theme, st, project.theme, anim);
    } else {
      this.drawContentScreen(s.content, theme, st, anim);
    }
  }

  /* تصدير الشاشة الحالية — scale=1 يعطي 1080×1920 وهو مقاس المنصّات */
  exportPNG(scale = 1) {
    if (scale === 1) return this.canvas.toDataURL('image/png');
    const off = document.createElement('canvas');
    off.width = this.W * scale;
    off.height = this.H * scale;
    const c = off.getContext('2d');
    c.imageSmoothingEnabled = true;
    c.imageSmoothingQuality = 'high';
    c.drawImage(this.canvas, 0, 0, off.width, off.height);
    return off.toDataURL('image/png');
  }

  exportJPEG(quality = 0.92) {
    return this.canvas.toDataURL('image/jpeg', quality);
  }

  /* يرسم الشاشات الثلاث ويرجّع صورها — يعيد الشاشة الحالية بعد الانتهاء */
  exportAll(project, scale = 1) {
    const out = {};
    ['hook', 'content', 'flow'].forEach(sc => {
      this.render(project, sc);
      out[sc] = this.exportPNG(scale);
    });
    return out;
  }
}

/* تحميل الخطوط قبل أول رسم — بدونه يرسم Canvas بخط بديل */
function waitForFonts() {
  if (!document.fonts) return Promise.resolve();
  const probes = [
    '900 84px Cairo', '800 42px Tajawal',
    '400 36px Almarai', '400 36px "IBM Plex Sans Arabic"'
  ];
  return Promise.all(probes.map(f => document.fonts.load(f, 'نص').catch(() => {})))
    .then(() => document.fonts.ready)
    .catch(() => {});
}
