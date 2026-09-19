/* ═══════════════════════════════════════════════════════
   storage.js — كل البيانات في localStorage
   لا خادم · لا شبكة · كل شيء محلي على جهاز المستخدم
   ═══════════════════════════════════════════════════════ */

const DB_KEY = 'kitabwbs_reel_studio_v1';

/* القيم الافتراضية — عدّل هنا لتغيير هوية الحساب بالكامل */
const DEFAULTS = {
  theme: 'classic',
  font: 'Tajawal',
  brand: '@kitabwbs · www.kitabwbs.com',
  handle: '@kitabwbs',
  settings: {
    font:'Tajawal', ptFontSize:34, titleFontSize:56, lineHeight:1.55,
    cornerRadius:20, margins:42, graphicHeight:340, pointGap:12
  }
};

const CHECKLIST_ITEMS = [
  'غيّرت الثيم عن الريل السابق',
  'الهوك متحرّك في أول 3 ثوانٍ',
  'غيّرت المقطع الصوتي',
  'سؤال الختام يطلب قصة أو إكمالاً',
  'أضفت دعوة «احفظ المنشور»',
  'الجرافيك العلوي مختلف',
  'راجعت الإملاء والنقاط الطويلة',
  'مدّة الريل 10-15 ثانية'
];

/* ── قراءة/كتابة قاعدة البيانات ── */
function dbRead() {
  try {
    const raw = localStorage.getItem(DB_KEY);
    if (!raw) return dbBlank();
    const db = JSON.parse(raw);
    db.projects      = Array.isArray(db.projects) ? db.projects : [];
    db.custom_topics = Array.isArray(db.custom_topics) ? db.custom_topics : [];
    db.settings      = Object.assign({}, dbBlank().settings, db.settings || {});
    return db;
  } catch (e) {
    console.warn('تعذّرت قراءة البيانات المحفوظة، بدأنا من جديد:', e);
    return dbBlank();
  }
}

function dbBlank() {
  return {
    projects: [],
    custom_topics: [],
    settings: {
      defaultTheme: DEFAULTS.theme,
      defaultFont:  DEFAULTS.font,
      brand:        DEFAULTS.brand,
      lastProjectId: null,
      exportScale: 3
    }
  };
}

function dbWrite(db) {
  try {
    localStorage.setItem(DB_KEY, JSON.stringify(db));
    return { ok:true };
  } catch (e) {
    /* أكثر سبب: الصور المرفوعة كـ base64 تجاوزت حصّة المتصفّح (~5 ميجا) */
    const quota = e && (e.name === 'QuotaExceededError' || e.code === 22);
    return {
      ok:false,
      quota,
      msg: quota
        ? 'امتلأت مساحة التخزين. احذف مشاريع قديمة أو أزل الصور المرفوعة.'
        : 'تعذّر الحفظ: ' + (e && e.message ? e.message : e)
    };
  }
}

/* ── مشروع جديد فارغ ── */
function newProject(name) {
  const db = dbRead();
  return {
    id: uid(),
    name: name || 'ريل بلا عنوان',
    created_at: Date.now(),
    updated_at: Date.now(),
    theme: db.settings.defaultTheme || DEFAULTS.theme,
    colorOverrides: {},
    screens: {
      hook:    { badge:'POV', text:'', sub:'', bgImage:null },
      content: {
        graphic:null, emoji:'🛑', title:'',
        points:[ blankPoint(), blankPoint(), blankPoint() ],
        outro:'', cta:'', brand: db.settings.brand || DEFAULTS.brand
      },
      flow:    { text:'', auto:true }
    },
    caption: '',
    captionMeta: { category:'wai', style:'direct', length:'medium' },
    settings: Object.assign({}, DEFAULTS.settings, { font: db.settings.defaultFont }),
    checklist: new Array(CHECKLIST_ITEMS.length).fill(false)
  };
}

function blankPoint() { return { head:'', body:'' }; }

function uid() {
  if (crypto && crypto.randomUUID) return crypto.randomUUID();
  return 'p' + Date.now().toString(36) + Math.random().toString(36).slice(2, 9);
}

/* ── عمليات المشاريع ── */
function listProjects() {
  return dbRead().projects.slice().sort((a, b) => b.updated_at - a.updated_at);
}

function getProject(id) {
  return dbRead().projects.find(p => p.id === id) || null;
}

function saveProject(project) {
  const db = dbRead();
  project.updated_at = Date.now();
  const i = db.projects.findIndex(p => p.id === project.id);
  if (i >= 0) db.projects[i] = project; else db.projects.push(project);
  db.settings.lastProjectId = project.id;
  return dbWrite(db);
}

function deleteProject(id) {
  const db = dbRead();
  db.projects = db.projects.filter(p => p.id !== id);
  if (db.settings.lastProjectId === id) db.settings.lastProjectId = null;
  return dbWrite(db);
}

function duplicateProject(id) {
  const src = getProject(id);
  if (!src) return null;
  const copy = JSON.parse(JSON.stringify(src));
  copy.id = uid();
  copy.name = src.name + ' — نسخة';
  copy.created_at = copy.updated_at = Date.now();
  saveProject(copy);
  return copy;
}

/* ── المشروع الحالي (يُمرَّر بين الصفحات) ── */
function setCurrentId(id) {
  const db = dbRead();
  db.settings.lastProjectId = id;
  dbWrite(db);
}

function getCurrentId() { return dbRead().settings.lastProjectId; }

function loadOrCreateCurrent() {
  const id = getCurrentId();
  const found = id && getProject(id);
  if (found) return found;
  const p = newProject();
  saveProject(p);
  return p;
}

/* ── الإعدادات العامة ── */
function getSettings() { return dbRead().settings; }

function saveSettings(patch) {
  const db = dbRead();
  db.settings = Object.assign({}, db.settings, patch);
  return dbWrite(db);
}

/* ── المواضيع المخصّصة ── */
function listCustomTopics() { return dbRead().custom_topics.slice(); }

function saveCustomTopic(topic) {
  const db = dbRead();
  topic.id = topic.id || uid();
  topic.custom = true;
  const i = db.custom_topics.findIndex(t => t.id === topic.id);
  if (i >= 0) db.custom_topics[i] = topic; else db.custom_topics.push(topic);
  return dbWrite(db);
}

function deleteCustomTopic(id) {
  const db = dbRead();
  db.custom_topics = db.custom_topics.filter(t => t.id !== id);
  return dbWrite(db);
}

/* ── الإحصائيات ── */
function getStats() {
  const ps = listProjects();
  const now = new Date();
  const thisMonth = ps.filter(p => {
    const d = new Date(p.created_at);
    return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
  }).length;

  const counts = {};
  ps.forEach(p => { counts[p.theme] = (counts[p.theme] || 0) + 1; });
  let topTheme = null, topN = 0;
  for (const k in counts) if (counts[k] > topN) { topN = counts[k]; topTheme = k; }

  return {
    thisMonth,
    total: ps.length,
    last: ps[0] || null,
    topTheme,
    topThemeName: topTheme ? (THEMES[topTheme] || {}).name : null
  };
}

/* ── التصدير/الاستيراد JSON ── */
function exportProjectJSON(project) {
  return JSON.stringify({ _type:'kitabwbs-reel', _version:1, project }, null, 2);
}

function importProjectJSON(text) {
  let data;
  try { data = JSON.parse(text); }
  catch (e) { return { ok:false, msg:'الملف ليس JSON صالحًا.' }; }

  const p = data && data.project ? data.project : data;
  if (!p || !p.screens || !p.screens.content) {
    return { ok:false, msg:'الملف لا يحتوي على مشروع ريل صالح.' };
  }
  /* نمنح المستورد معرّفًا جديدًا حتى لا يستبدل مشروعًا قائمًا */
  const fresh = newProject(p.name || 'مشروع مستورَد');
  const merged = Object.assign(fresh, p, {
    id: fresh.id,
    created_at: Date.now(),
    updated_at: Date.now()
  });
  merged.settings  = Object.assign({}, DEFAULTS.settings, p.settings || {});
  merged.checklist = Array.isArray(p.checklist)
    ? p.checklist.slice(0, CHECKLIST_ITEMS.length)
    : new Array(CHECKLIST_ITEMS.length).fill(false);
  while (merged.checklist.length < CHECKLIST_ITEMS.length) merged.checklist.push(false);

  const r = saveProject(merged);
  return r.ok ? { ok:true, project:merged } : { ok:false, msg:r.msg };
}

/* ── تنسيق التاريخ بالعربية ── */
function fmtDate(ts) {
  if (!ts) return '—';
  try {
    return new Intl.DateTimeFormat('ar-u-ca-gregory-nu-latn', {
      day:'numeric', month:'short', hour:'2-digit', minute:'2-digit'
    }).format(new Date(ts));
  } catch (e) { return new Date(ts).toLocaleString(); }
}

function fmtRelative(ts) {
  if (!ts) return '—';
  const s = Math.floor((Date.now() - ts) / 1000);
  if (s < 60) return 'قبل لحظات';
  if (s < 3600) return 'قبل ' + Math.floor(s / 60) + ' دقيقة';
  if (s < 86400) return 'قبل ' + Math.floor(s / 3600) + ' ساعة';
  if (s < 604800) return 'قبل ' + Math.floor(s / 86400) + ' يوم';
  return fmtDate(ts);
}
