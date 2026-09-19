/* ═══════════════════════════════════════════════════════
   themes.js — الثيمات الثمانية
   كل ثيم 8 ألوان يستهلكها محرّك الرسم مباشرة
   ═══════════════════════════════════════════════════════ */

const THEMES = {
  classic: {
    name:'كلاسيكي',
    accent:'#dc2626', bg:'#1f2937', card:'#ffffff', panel:'#f1f5f9',
    title:'#111827',  quote:'#047857', txt:'#1f2937', cta:'#111827'
  },
  golden: {
    name:'ليلي ذهبي',
    accent:'#f59e0b', bg:'#0b0f19', card:'#15192a', panel:'#1e2338',
    title:'#fdf6e3',  quote:'#fbbf24', txt:'#cbd5e1', cta:'#fdf6e3'
  },
  blue: {
    name:'أزرق',
    accent:'#2563eb', bg:'#0f172a', card:'#ffffff', panel:'#eff6ff',
    title:'#0f172a',  quote:'#1d4ed8', txt:'#1e293b', cta:'#0f172a'
  },
  emerald: {
    name:'زمرّدي',
    accent:'#059669', bg:'#052e2b', card:'#ffffff', panel:'#ecfdf5',
    title:'#052e2b',  quote:'#047857', txt:'#134e4a', cta:'#052e2b'
  },
  purple: {
    name:'بنفسجي',
    accent:'#7c3aed', bg:'#1e1b31', card:'#ffffff', panel:'#f5f3ff',
    title:'#1e1b31',  quote:'#6d28d9', txt:'#312e51', cta:'#1e1b31'
  },
  sandy: {
    name:'رملي',
    accent:'#b45309', bg:'#231a12', card:'#fffbf3', panel:'#fdf3e3',
    title:'#3b2a17',  quote:'#92400e', txt:'#4b3a26', cta:'#3b2a17'
  },
  pink: {
    name:'وردي',
    accent:'#be185d', bg:'#2a0f1c', card:'#ffffff', panel:'#fdf2f8',
    title:'#2a0f1c',  quote:'#9d174d', txt:'#500724', cta:'#2a0f1c'
  },
  carbon: {
    name:'فحمي',
    accent:'#e5e7eb', bg:'#000000', card:'#0d0d0d', panel:'#171717',
    title:'#fafafa',  quote:'#a3a3a3', txt:'#d4d4d4', cta:'#fafafa'
  }
};

const THEME_KEYS = Object.keys(THEMES);

/* الألوان القابلة للتعديل يدويًا في لوحة الخصائص */
const EDITABLE_COLORS = [
  { key:'accent', label:'اللون المميّز' },
  { key:'bg',     label:'الخلفية' },
  { key:'card',   label:'البطاقة' },
  { key:'panel',  label:'اللوحة الداخلية' },
  { key:'quote',  label:'الاقتباس' }
];

/* يرجّع الثيم مدموجًا مع أي تخصيص لوني حفظه المستخدم */
function resolveTheme(key, overrides) {
  const base = THEMES[key] || THEMES.classic;
  return overrides ? Object.assign({}, base, overrides) : Object.assign({}, base);
}

/* الثيم التالي في الدورة — لاختصار Ctrl+T */
function nextTheme(key) {
  const i = THEME_KEYS.indexOf(key);
  return THEME_KEYS[(i + 1) % THEME_KEYS.length];
}

/* هل الثيم داكن؟ يحدّد لون شاشة المتدفّق */
function isDarkTheme(key) {
  return ['golden','carbon'].includes(key);
}

/* تفتيح/تغميق لون — لتدرّجات الجرافيك البديل */
function shade(hex, amt) {
  const n = parseInt(hex.slice(1), 16);
  const cl = v => Math.max(0, Math.min(255, v));
  const r = cl((n >> 16) + amt), g = cl(((n >> 8) & 255) + amt), b = cl((n & 255) + amt);
  return '#' + ((r << 16) | (g << 8) | b).toString(16).padStart(6, '0');
}

/* لون نص مقروء فوق خلفية معيّنة */
function readableOn(hex) {
  const n = parseInt(hex.slice(1), 16);
  const l = (0.299 * (n >> 16) + 0.587 * ((n >> 8) & 255) + 0.114 * (n & 255)) / 255;
  return l > 0.58 ? '#111827' : '#ffffff';
}
