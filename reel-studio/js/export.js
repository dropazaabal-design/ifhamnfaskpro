/* ═══════════════════════════════════════════════════════
   export.js — التصدير والحفظ
   PNG · ZIP · JSON · PDF — كلها مكتوبة بـ Vanilla JS
   بلا أي مكتبة خارجية وبلا أي طلب شبكة.
   ═══════════════════════════════════════════════════════ */

/* ── تنزيل عام ── */
function saveBlob(blob, filename) {
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.rel = 'noopener';
  a.style.display = 'none';
  document.body.appendChild(a);
  a.click();
  /* حذف العنصر فورًا يُفقد بعض المتصفّحات اسم الملف ويسمّيه «download»،
     فنؤجّل الحذف وإفلات الرابط معًا. */
  setTimeout(() => { a.remove(); URL.revokeObjectURL(url); }, 4000);
}

function dataURLToBytes(dataURL) {
  const i = dataURL.indexOf(',');
  const bin = atob(dataURL.slice(i + 1));
  const out = new Uint8Array(bin.length);
  for (let k = 0; k < bin.length; k++) out[k] = bin.charCodeAt(k);
  return out;
}

function downloadDataURL(dataURL, filename) {
  saveBlob(new Blob([dataURLToBytes(dataURL)], { type:'image/png' }), filename);
}

/* ── نقحرة عربي → لاتيني ──
   كروم يتجاهل اسم الملف في خاصية download إذا احتوى حروفًا غير لاتينية،
   ويسمّي الملف «download» بدلًا منه. فننقحر الاسم حتى يصل للمستخدم
   اسمٌ يميّز المشروع بدل download و download (1) و download (2)… */
const AR_LATIN = {
  'ا':'a','أ':'a','إ':'i','آ':'a','ٱ':'a','ب':'b','ت':'t','ث':'th','ج':'j','ح':'h',
  'خ':'kh','د':'d','ذ':'dh','ر':'r','ز':'z','س':'s','ش':'sh','ص':'s','ض':'d',
  'ط':'t','ظ':'z','ع':'a','غ':'gh','ف':'f','ق':'q','ك':'k','ل':'l','م':'m',
  'ن':'n','ه':'h','و':'w','ي':'y','ى':'a','ة':'h','ء':'','ؤ':'w','ئ':'y','لا':'la',
  '٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9'
};

function translitAR(str) {
  let out = '';
  for (const ch of String(str || '')) {
    if (/[\u064B-\u0652\u0670]/.test(ch)) continue;        // تشكيل — يُحذف
    if (AR_LATIN[ch] !== undefined) out += AR_LATIN[ch];
    else if (/[a-zA-Z0-9]/.test(ch)) out += ch;
    else if (/[\s._-]/.test(ch)) out += '-';
    /* أي شيء آخر (إيموجي، رموز) يُسقَط */
  }
  return out;
}

/* اسم ملف آمن مبني على اسم المشروع — لاتيني بالكامل ليصل سليمًا */
function safeName(s, fallback) {
  const clean = translitAR(s)
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '')
    .slice(0, 48)
    .replace(/-$/, '');
  return clean || (fallback || 'reel');
}

function stamp() {
  const d = new Date(), p = n => String(n).padStart(2, '0');
  return `${d.getFullYear()}${p(d.getMonth() + 1)}${p(d.getDate())}-${p(d.getHours())}${p(d.getMinutes())}`;
}

/* ═══════════ ZIP (طريقة التخزين بلا ضغط) ═══════════
   تكفي تمامًا لصور PNG لأنها مضغوطة أصلًا، وتوفّر علينا
   إدخال مكتبة ضغط خارجية.                              */

const CRC_TABLE = (() => {
  const t = new Uint32Array(256);
  for (let n = 0; n < 256; n++) {
    let c = n;
    for (let k = 0; k < 8; k++) c = (c & 1) ? (0xEDB88320 ^ (c >>> 1)) : (c >>> 1);
    t[n] = c >>> 0;
  }
  return t;
})();

function crc32(bytes) {
  let c = 0xFFFFFFFF;
  for (let i = 0; i < bytes.length; i++) c = CRC_TABLE[(c ^ bytes[i]) & 0xFF] ^ (c >>> 8);
  return (c ^ 0xFFFFFFFF) >>> 0;
}

function u16(n) { return [n & 255, (n >> 8) & 255]; }
function u32(n) { return [n & 255, (n >> 8) & 255, (n >> 16) & 255, (n >>> 24) & 255]; }
function strBytes(s) { return Array.from(new TextEncoder().encode(s)); }

/* files: [{ name, bytes:Uint8Array }] */
function makeZip(files) {
  const chunks = [], central = [];
  let offset = 0;

  files.forEach(f => {
    const nameB = strBytes(f.name);
    const crc = crc32(f.bytes);
    const size = f.bytes.length;

    const local = [].concat(
      u32(0x04034b50), u16(20), u16(0x0800), u16(0),  // 0x0800 = أسماء UTF-8
      u16(0), u16(0),
      u32(crc), u32(size), u32(size),
      u16(nameB.length), u16(0), nameB
    );
    chunks.push(new Uint8Array(local), f.bytes);

    central.push([].concat(
      u32(0x02014b50), u16(20), u16(20), u16(0x0800), u16(0),
      u16(0), u16(0),
      u32(crc), u32(size), u32(size),
      u16(nameB.length), u16(0), u16(0), u16(0), u16(0),
      u32(0), u32(offset), nameB
    ));
    offset += local.length + size;
  });

  const centralFlat = [].concat.apply([], central);
  chunks.push(new Uint8Array(centralFlat));
  chunks.push(new Uint8Array([].concat(
    u32(0x06054b50), u16(0), u16(0),
    u16(files.length), u16(files.length),
    u32(centralFlat.length), u32(offset), u16(0)
  )));

  return new Blob(chunks, { type:'application/zip' });
}

/* ═══════════ PDF بسيط — صورة JPEG في كل صفحة ═══════════ */

function makePDF(images) {
  /* images: [{ jpegBytes:Uint8Array, w, h }] */
  const enc = new TextEncoder();
  const parts = [];
  let len = 0;
  const push = x => {
    const b = typeof x === 'string' ? enc.encode(x) : x;
    parts.push(b); len += b.length; return len;
  };

  const objOffsets = [];
  const nPages = images.length;
  /* ترتيب الكائنات: 1 catalog · 2 pages · ثم لكل صفحة 3 كائنات */
  const pageObjId  = i => 3 + i * 3;
  const imgObjId   = i => 4 + i * 3;
  const contObjId  = i => 5 + i * 3;
  const totalObjs  = 2 + nPages * 3;

  const startObj = id => { objOffsets[id] = len; push(`${id} 0 obj\n`); };
  const endObj   = () => push('endobj\n');

  push('%PDF-1.4\n%\xFF\xFF\xFF\xFF\n');

  startObj(1);
  push('<< /Type /Catalog /Pages 2 0 R >>\n');
  endObj();

  startObj(2);
  push(`<< /Type /Pages /Kids [${images.map((_, i) => `${pageObjId(i)} 0 R`).join(' ')}] /Count ${nPages} >>\n`);
  endObj();

  images.forEach((im, i) => {
    startObj(pageObjId(i));
    push(`<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ${im.w} ${im.h}] ` +
         `/Resources << /XObject << /Im0 ${imgObjId(i)} 0 R >> >> ` +
         `/Contents ${contObjId(i)} 0 R >>\n`);
    endObj();

    startObj(imgObjId(i));
    push(`<< /Type /XObject /Subtype /Image /Width ${im.w} /Height ${im.h} ` +
         `/ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode ` +
         `/Length ${im.jpegBytes.length} >>\nstream\n`);
    push(im.jpegBytes);
    push('\nendstream\n');
    endObj();

    const content = `q\n${im.w} 0 0 ${im.h} 0 0 cm\n/Im0 Do\nQ\n`;
    startObj(contObjId(i));
    push(`<< /Length ${content.length} >>\nstream\n${content}endstream\n`);
    endObj();
  });

  const xrefAt = len;
  let xref = `xref\n0 ${totalObjs + 1}\n0000000000 65535 f \n`;
  for (let id = 1; id <= totalObjs; id++) {
    xref += String(objOffsets[id] || 0).padStart(10, '0') + ' 00000 n \n';
  }
  push(xref);
  push(`trailer\n<< /Size ${totalObjs + 1} /Root 1 0 R >>\nstartxref\n${xrefAt}\n%%EOF\n`);

  return new Blob(parts, { type:'application/pdf' });
}

/* ═══════════ عمليات التصدير عالية المستوى ═══════════ */

const SCREEN_LABELS = { hook:'1-hook', content:'2-content', flow:'3-flow' };

/* الشاشة الحالية فقط */
function exportCurrentPNG(rc, project, screen, scale) {
  rc.render(project, screen);
  const url = rc.exportPNG(scale || 1);
  downloadDataURL(url, `${safeName(project.name)}-${SCREEN_LABELS[screen] || screen}-${stamp()}.png`);
}

/* الشاشات الثلاث في ملف مضغوط واحد */
function exportAllZip(rc, project, scale) {
  const shots = rc.exportAll(project, scale || 1);
  const base = safeName(project.name);
  const files = Object.keys(SCREEN_LABELS).map(k => ({
    name: `${base}-${SCREEN_LABELS[k]}.png`,
    bytes: dataURLToBytes(shots[k])
  }));
  files.push({
    name: `${base}.json`,
    bytes: new TextEncoder().encode(exportProjectJSON(project))
  });
  saveBlob(makeZip(files), `${base}-${stamp()}.zip`);
  return files.length;
}

/* ملف المشروع للحفظ وإعادة التعديل */
function exportJSON(project) {
  const blob = new Blob([exportProjectJSON(project)], { type:'application/json' });
  saveBlob(blob, `${safeName(project.name)}-${stamp()}.json`);
}

/* PDF من ثلاث صفحات للطباعة أو المراجعة */
function exportPDF(rc, project) {
  const images = ['hook', 'content', 'flow'].map(sc => {
    rc.render(project, sc);
    return { jpegBytes: dataURLToBytes(rc.exportJPEG(0.92)), w: rc.W, h: rc.H };
  });
  saveBlob(makePDF(images), `${safeName(project.name)}-${stamp()}.pdf`);
}

/* ═══════════ توقيت CapCut المقترح ═══════════ */

const CAPCUT_TIMELINE = [
  { screen:'الشاشة ١: الهوك',    time:'0 – 3 ث',   note:'ظهور تدريجي + الموسيقى بدأت' },
  { screen:'الشاشة ٢: المحتوى',  time:'3 – 11 ث',  note:'النقاط تنزلق واحدة تلو الأخرى' },
  { screen:'الشاشة ٣: الخاتمة',  time:'11 – 13 ث', note:'ظهور بطيء + fade out' }
];

function timelineText() {
  return CAPCUT_TIMELINE.map(t => `${t.screen} — ${t.time}\n  ${t.note}`).join('\n');
}

/* ═══════════ النسخ إلى الحافظة ═══════════ */

function copyText(text) {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    return navigator.clipboard.writeText(text);
  }
  /* بديل يعمل في الصفحات المفتوحة عبر file:// */
  return new Promise((res, rej) => {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.cssText = 'position:fixed;top:-9999px';
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); res(); }
    catch (e) { rej(e); }
    finally { ta.remove(); }
  });
}
