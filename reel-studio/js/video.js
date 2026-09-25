/* ═══════════════════════════════════════════════════════
   video.js — تسجيل الريل فيديو من اللوحة
   MediaRecorder + captureStream — بلا مكتبات، وبلا رفع:
   الترميز كلّه يجري داخل متصفّحك.
   ═══════════════════════════════════════════════════════ */

/* نفضّل MP4 لأن إنستغرام وفيسبوك يقبلانه رفعًا مباشرًا،
   ونقدّم H.264 بمواصفة High لأنها أنظف في التدرّجات والنصّ
   الدقيق من Baseline عند نفس معدّل البِتّ. */
const VIDEO_TYPES = [
  { mime:'video/mp4;codecs=avc1.640028', ext:'mp4',  label:'MP4 · H.264 High' },
  { mime:'video/mp4;codecs=avc1.4D401F', ext:'mp4',  label:'MP4 · H.264 Main' },
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

/* جودات — معدّل البتّ مرتفع عمدًا لأن إنستغرام تعيد الضغط،
   فكلّما دخل المقطع أنظف خرج أنظف. */
const VIDEO_QUALITY = [
  { key:'max',    label:'الأعلى — حتى ٦٠ إطارًا · ٢٠ ميجابت', bitrate:20e6, fps:60 },
  { key:'high',   label:'عالية — ٣٠ إطارًا · ١٤ ميجابت',  bitrate:14e6, fps:30 },
  { key:'medium', label:'متوسّطة — ملف أصغر',             bitrate:7e6,  fps:30 },
  { key:'light',  label:'خفيفة — للمراجعة السريعة',       bitrate:3e6,  fps:24 }
];

/* حدّ أعلى لا رقمًا متوقّعًا: المرمّز متغيّر المعدّل، وينزل كثيرًا
   تحت السقف حين يكون المشهد ثابتًا — وأكثر مشاهد الريل كذلك. */
function estimateSize(bitrate, seconds) {
  return Math.max(1, Math.round((bitrate / 8) * seconds / 1048576));
}

/* ── قيادة الإطارات ──
   نلتقط كل إطار يدويًا بعد اكتمال رسمه (requestFrame)، بدل ترك
   المتصفّح يعتيّن اللوحة على فترات قد تقع في منتصف رسمة.
   هذا يمنع الإطارات المكرّرة والممزّقة. */
function driveFrames(rc, project, fps, total, track, onProgress) {
  const frameDur = 1000 / fps;
  return new Promise(resolve => {
    const t0 = performance.now();
    let lastEmit = -1e9, emitted = 0;
    const step = () => {
      const now = performance.now();
      const t = (now - t0) / 1000;
      if (t >= total) {
        renderAt(rc, project, total - 0.0005);
        if (track) track.requestFrame();
        emitted++;
        onProgress && onProgress(1, total, emitted);
        resolve(emitted);
        return;
      }
      if (now - lastEmit >= frameDur - 1.5) {
        renderAt(rc, project, t);
        if (track) track.requestFrame();
        lastEmit = now; emitted++;
        onProgress && onProgress(t / total, t, emitted);
      }
      requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  });
}

/* ── التسجيل ──
   يجري بالزمن الحقيقي لأن MediaRecorder يختم كل إطار بساعة
   الحائط — فلا سبيل لتسريعه دون إفساد المدّة. */
function recordReel(rc, project, opts) {
  const o = Object.assign({ fps:30, bitrate:14e6, onProgress:null }, opts || {});
  const type = pickVideoType();
  const total = timeline(project).total;

  if (!type) {
    return Promise.reject(new Error('متصفّحك لا يدعم تسجيل الفيديو من اللوحة. جرّب كروم أو إيدج حديثًا.'));
  }

  /* captureStream(0) يعطينا التحكّم اليدوي؛ وإن لم يتوفّر نرجع
     إلى الالتقاط التلقائي بمعدّل ثابت. */
  let stream = null, track = null, manual = false;
  try {
    stream = rc.canvas.captureStream(0);
    track = stream.getVideoTracks()[0];
    manual = !!(track && typeof track.requestFrame === 'function');
    if (!manual) {
      stream.getTracks().forEach(t => t.stop());
      stream = rc.canvas.captureStream(o.fps);
      track = null;
    }
  } catch (e) {
    try { stream = rc.canvas.captureStream(o.fps); track = null; }
    catch (e2) { return Promise.reject(new Error('تعذّر التقاط اللوحة: ' + e2.message)); }
  }

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
    rec.onerror = e => rej(new Error('انقطع التسجيل: ' + ((e.error || e).message || e)));
  });

  rec.start(200);

  return driveFrames(rc, project, o.fps, total, track,
                     (p, t, n) => o.onProgress && o.onProgress(p, t, n))
    .then(frames => new Promise(r => setTimeout(() => r(frames), 300)))
    .then(frames => {
      if (rec.state !== 'inactive') rec.stop();
      return stopped.then(blob => ({ blob, frames }));
    })
    .then(({ blob, frames }) => {
      stream.getTracks().forEach(t => t.stop());
      if (!blob.size) throw new Error('خرج الفيديو فارغًا — أعد المحاولة.');
      return { blob, type, frames, manual, seconds: total };
    })
    .catch(err => {
      try { stream.getTracks().forEach(t => t.stop()); } catch (e) {}
      try { if (rec.state !== 'inactive') rec.stop(); } catch (e) {}
      throw err;
    });
}

function exportVideo(rc, project, opts) {
  return recordReel(rc, project, opts).then(res => {
    saveBlob(res.blob, `${safeName(project.name)}-${stamp()}.${res.type.ext}`);
    return res;
  });
}
