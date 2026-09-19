/* ═══════════════════════════════════════════════════════
   video.js — تسجيل الريل فيديو من اللوحة مباشرة
   MediaRecorder + canvas.captureStream — بلا أي مكتبة،
   وبلا رفع أي شيء: الترميز يجري داخل متصفّحك.
   ═══════════════════════════════════════════════════════ */

/* نفضّل MP4 لأن إنستغرام وفيسبوك يقبلانه رفعًا مباشرًا.
   كروم 130+ يدعم تسجيله؛ وإن لم يتوفّر نرجع إلى WebM. */
const VIDEO_TYPES = [
  { mime:'video/mp4;codecs=avc1.42E01E', ext:'mp4',  label:'MP4 · H.264' },
  { mime:'video/mp4',                    ext:'mp4',  label:'MP4' },
  { mime:'video/webm;codecs=vp9',        ext:'webm', label:'WebM · VP9' },
  { mime:'video/webm;codecs=vp8',        ext:'webm', label:'WebM · VP8' },
  { mime:'video/webm',                   ext:'webm', label:'WebM' }
];

function pickVideoType() {
  if (typeof MediaRecorder === 'undefined') return null;
  for (const t of VIDEO_TYPES) {
    try { if (MediaRecorder.isTypeSupported(t.mime)) return t; } catch (e) {}
  }
  return null;
}

function videoSupported() {
  return !!(typeof MediaRecorder !== 'undefined'
    && HTMLCanvasElement.prototype.captureStream
    && pickVideoType());
}

/* ── التسجيل ──
   يُسجَّل بالزمن الحقيقي (13 ثانية) لأن MediaRecorder يلتقط
   اللوحة حيّةً إطارًا بإطار — فلا سبيل لتسريعه. */
function recordReel(rc, project, opts) {
  const o = Object.assign({ fps:30, bitrate:12e6, onProgress:null, onScreen:null }, opts || {});
  const type = pickVideoType();

  if (!type) {
    return Promise.reject(new Error('متصفّحك لا يدعم تسجيل الفيديو من اللوحة. جرّب كروم أو إيدج حديثًا.'));
  }

  let stream;
  try { stream = rc.canvas.captureStream(o.fps); }
  catch (e) { return Promise.reject(new Error('تعذّر التقاط اللوحة: ' + e.message)); }

  let rec;
  try {
    rec = new MediaRecorder(stream, { mimeType: type.mime, videoBitsPerSecond: o.bitrate });
  } catch (e) {
    return Promise.reject(new Error('تعذّر بدء المسجّل: ' + e.message));
  }

  const chunks = [];
  rec.ondataavailable = e => { if (e.data && e.data.size) chunks.push(e.data); };

  const stopped = new Promise((res, rej) => {
    rec.onstop  = () => res(new Blob(chunks, { type: type.mime }));
    rec.onerror = e => rej(new Error('انقطع التسجيل: ' + (e.error || e).message));
  });

  rec.start(200);                       /* قطعة كل 200ms */

  return playTimeline(rc, project, (t, p) => o.onProgress && o.onProgress(p, t), o.onScreen)
    .then(() => new Promise(r => setTimeout(r, 260)))   /* نترك آخر إطار يُلتقط */
    .then(() => { if (rec.state !== 'inactive') rec.stop(); return stopped; })
    .then(blob => {
      stream.getTracks().forEach(t => t.stop());
      if (!blob.size) throw new Error('خرج الفيديو فارغًا — أعد المحاولة.');
      return { blob, type };
    })
    .catch(err => {
      try { stream.getTracks().forEach(t => t.stop()); } catch (e) {}
      try { if (rec.state !== 'inactive') rec.stop(); } catch (e) {}
      throw err;
    });
}

/* يسجّل ثم ينزّل الملف */
function exportVideo(rc, project, opts) {
  return recordReel(rc, project, opts).then(({ blob, type }) => {
    saveBlob(blob, `${safeName(project.name)}-${stamp()}.${type.ext}`);
    return type;
  });
}

/* تقدير حجم الملف قبل التسجيل — لطمأنة المستخدم */
function estimateSize(bitrate) {
  return Math.round((bitrate / 8) * T_TOTAL / 1048576);   /* ميجابايت */
}

/* جودات جاهزة */
const VIDEO_QUALITY = [
  { key:'high',   label:'عالية — للنشر (موصى بها)', bitrate:12e6, fps:30 },
  { key:'medium', label:'متوسّطة — ملف أصغر',        bitrate:6e6,  fps:30 },
  { key:'light',  label:'خفيفة — للمراجعة السريعة',  bitrate:3e6,  fps:24 }
];
