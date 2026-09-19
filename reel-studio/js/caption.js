/* ═══════════════════════════════════════════════════════
   caption.js — مولّد الكابشن
   يبني الكابشن من بيانات المشروع + التصنيف + الأسلوب،
   مع تنويع عشوائي حتى يعطي «ولّد نسخة أخرى» نتيجة مختلفة.
   ═══════════════════════════════════════════════════════ */

/* ── مكتبة الهاشتاقات — 12 مجموعة ── */
const HASHTAG_SETS = {
  wai:     '#وعي #تطوير_الذات #كتاب_وبس #نمو_شخصي #حكمة #تنمية_بشرية #اقتباسات #وعي_ذاتي #كتب #قراءة',
  nafs:    '#صحة_نفسية #وعي #تطوير_الذات #كتاب_وبس #سلام_داخلي #طمأنينة #علم_النفس #قلق #راحة_نفسية #وعي_ذاتي',
  mal:     '#وعي_مالي #ثقافة_مالية #تطوير_الذات #كتاب_وبس #ادخار #استثمار #حرية_مالية #مال #اقتصاد #تخطيط_مالي',
  quwa:    '#قوة_داخلية #هيبة #تطوير_الذات #كتاب_وبس #ثقة_بالنفس #شخصية_قوية #احترام_الذات #وعي #حضور #كاريزما',
  ilaqat:  '#علاقات #فهم_الناس #علم_النفس #كتاب_وبس #وعي #ذكاء_عاطفي #تواصل #شخصيات #حدود_صحية #صداقة',
  adat:    '#عادات #انضباط #إنتاجية #تطوير_الذات #كتاب_وبس #تنظيم_الوقت #تركيز #نجاح #روتين #انضباط_ذاتي',
  dini:    '#تزكية #وعي #تطوير_الذات #كتاب_وبس #سلام_داخلي #بركة #ذكر_الله #إيمان #تدبر #روحانيات',
  viral:   '#كتاب_وبس #تطوير_الذات #وعي #نمو_شخصي #تنمية_بشرية #اكسبلور #فولو #ترند #محتوى_هادف #اقتباسات',
  kutub:   '#كتب #قراءة #ملخصات_كتب #كتاب_وبس #مكتبة #اقتباسات #كتاب #مراجعات #قارئ #ثقافة',
  nagah:   '#نجاح #طموح #تطوير_الذات #كتاب_وبس #أهداف #إنجاز #تحفيز #إصرار #بناء_الذات #مستقبل',
  waqt:    '#إدارة_الوقت #إنتاجية #تنظيم #كتاب_وبس #تركيز #تأجيل #أولويات #انضباط #روتين_يومي #فعالية',
  hikma:   '#حكمة #اقتباسات #كتاب_وبس #تأملات #خواطر #دروس_الحياة #وعي #فلسفة #تجارب #عبر'
};

const CAPTION_CATEGORIES = [
  { key:'wai',    label:'وعي وتطوير ذات',     tags:'wai'    },
  { key:'nafs',   label:'صحة نفسية',          tags:'nafs'   },
  { key:'mal',    label:'وعي مالي',           tags:'mal'    },
  { key:'quwa',   label:'قوة وهيبة',          tags:'quwa'   },
  { key:'ilaqat', label:'علاقات وفهم الناس',  tags:'ilaqat' },
  { key:'adat',   label:'عادات وانضباط',      tags:'adat'   },
  { key:'dini',   label:'ديني وروحي',         tags:'dini'   }
];

const CAPTION_STYLES = [
  { key:'story',   label:'سردي قصصي — شخصية وقصة مبتورة' },
  { key:'direct',  label:'مباشر فيروسي — hook + سؤال + حفظ' },
  { key:'science', label:'علمي موثوق — مصداقية ومفاهيم' },
  { key:'reverse', label:'عكسي جريء — تسويق عكسي' },
  { key:'list',    label:'تكميل القائمة — يطلب الإكمال' }
];

const CAPTION_LENGTHS = [
  { key:'short',  label:'قصير (3-4 أسطر)',     lines:[3,4] },
  { key:'medium', label:'متوسط (5-7 أسطر)',    lines:[5,7] },
  { key:'long',   label:'طويل/روائي (8+ أسطر)', lines:[8,12] }
];

/* ── عبارات متنوّعة — الاختيار العشوائي يمنع تكرار الكابشن ── */
const OPENERS = {
  story: [
    'واحد صاحبي قال لي جملة ما نمت بعدها:',
    'قبل سنة، شخص أعرفه سألني سؤالًا بسيطًا.. وما زلت أفكّر فيه:',
    'في جلسة عابرة، سمعت ملاحظة غيّرت طريقة نظري لكل شيء:',
    'كان يجلس قبالتي ويقول بهدوء:'
  ],
  direct: [
    'اقرأها مرّة.. وراح ترجع لها.',
    'إذا وصلت لآخر الريل، فأنت من القلّة.',
    'هذي مو نصايح — هذي أشياء تسوّيها يوميًا وما تنتبه لها.',
    'خذ دقيقة وراجع نفسك مع كل نقطة.'
  ],
  science: [
    'ما نفعله يوميًا ليس عشوائيًا — له تفسير يمكن فهمه.',
    'حين تفهم الآلية، يصير التغيير أسهل بكثير من محاولة الإرادة وحدها.',
    'أغلب سلوكنا يتبع أنماطًا مدروسة — وهذه أوضحها.',
    'الفكرة ليست جديدة، لكن تطبيقها هو ما يصنع الفرق.'
  ],
  reverse: [
    'لا تقرأ هذا المنشور إذا كنت مرتاحًا لوضعك الحالي.',
    'تجاهل هذه النقاط.. إذا كنت تحب تكرار السنة نفسها.',
    'هذا المنشور لن يعجب كثيرين — وهذا مقصود.',
    'إذا كنت تبحث عن كلام مريح، مرّ من هنا بسرعة.'
  ],
  list: [
    'القائمة ناقصة عمدًا — وأنت من يكملها.',
    'كتبت اللي أعرفه، والباقي عندكم.',
    'هذي البداية فقط.. النقطة الأهم غالبًا في التعليقات.',
    'أكيد فاتني شيء — قوله لنا.'
  ]
};

const BRIDGES = [
  'والفرق بين من يعرفها ومن يطبّقها.. سنة كاملة.',
  'كل نقطة منها تبدو صغيرة، ومجموعها يغيّر اتجاهك.',
  'ما تحتاج تغيّر حياتك كلها — تكفي وحدة تبدأ فيها الليلة.',
  'اقرأها بهدوء، وتوقّف عند اللي يخصّك أنت.',
  'ليست كلها تخصّك — لكن واحدة منها بالتأكيد تخصّك.'
];

const SAVE_CTAS = [
  '📌 احفظ المنشور وارجع له وقت تحتاجه.',
  '📌 احفظها — راح تحتاجها في يوم ما.',
  '📌 خذ سكرين شوت للنقطة اللي تخصّك.'
];

const LIST_CTAS = [
  '✍️ وش النقطة اللي تضيفها؟ اكتبها تحت.',
  '✍️ أكمل القائمة معنا في التعليقات.',
  '✍️ ناقصة وحدة.. عندك أي وحدة؟'
];

const ISLAMIC_TOUCHES = [
  '🤍 «وَذَكِّرْ فَإِنَّ الذِّكْرَىٰ تَنفَعُ الْمُؤْمِنِينَ»',
  '🤍 اللهم اجعلنا ممن يستمعون القول فيتّبعون أحسنه.',
  '🤍 ونسأل الله علمًا نافعًا وعملًا متقبّلًا.',
  '🤍 «إِنَّ اللَّهَ لَا يُغَيِّرُ مَا بِقَوْمٍ حَتَّىٰ يُغَيِّرُوا مَا بِأَنفُسِهِمْ»'
];

/* المعرّف لاتيني داخل نص عربي، فتنتقل @ لآخر الكلمة بصريًا.
   علامتا LRM (غير مرئيّتين) تعزلانه فيظهر صحيحًا هنا وفي إنستغرام بعد اللصق. */
const HANDLE = '\u200E@kitabwbs\u200E';

const FOLLOW_LINE = [
  '📚 تابع ' + HANDLE + ' — خلاصات كتب تنفع في الواقع.',
  '📚 ' + HANDLE + ' · نقرأ لك ونعطيك الزبدة.',
  '📚 تابعنا ' + HANDLE + ' لخلاصات تنفع فعلًا.'
];

function pick(arr, seed) {
  if (!arr || !arr.length) return '';
  const i = typeof seed === 'number'
    ? seed % arr.length
    : Math.floor(Math.random() * arr.length);
  return arr[i];
}

/* ── المولّد الرئيسي ── */
function generateCaption(input, opts) {
  const o = Object.assign({
    category:'wai', style:'direct', length:'medium',
    islamic:false, saveCta:true, listCta:false, tagCount:7
  }, opts || {});

  const hook   = (input.hook   || '').trim();
  const title  = (input.title  || '').trim();
  const outro  = (input.outro  || '').trim();
  const cta    = (input.cta    || '').trim();
  const heads  = (input.heads  || []).filter(Boolean);
  const count  = input.pointCount || heads.length || 0;

  const lines = [];

  /* 1 — الفتحة حسب الأسلوب */
  const opener = pick(OPENERS[o.style] || OPENERS.direct);
  if (o.style === 'story') {
    lines.push(opener);
    lines.push(outro || hook || title);
    lines.push('');
    lines.push('وقتها فهمت إن المشكلة مو في المعرفة.. المشكلة في التكرار.');
  } else if (o.style === 'reverse') {
    lines.push(opener);
    lines.push('');
    if (title) lines.push(title + (count ? ` — ${toArabicDigits(count)} نقطة.` : ''));
  } else {
    if (hook) lines.push(hook);
    if (title && title !== hook) lines.push(title + (count ? ` — ${toArabicDigits(count)} نقاط.` : ''));
    lines.push('');
    lines.push(opener);
  }

  const wantLong = o.length === 'long';
  const wantShort = o.length === 'short';

  /* 2 — المتن */
  if (!wantShort && heads.length) {
    lines.push('');
    if (o.style === 'science') {
      lines.push('المفاهيم الأساسية في الريل:');
    } else if (o.style === 'list') {
      lines.push('اللي ذكرناه:');
    } else {
      lines.push('من داخل الريل:');
    }
    const n = wantLong ? Math.min(heads.length, 8) : Math.min(heads.length, 4);
    heads.slice(0, n).forEach((h, i) => lines.push(`${toArabicDigits(i + 1)}. ${h}`));
    if (heads.length > n) lines.push(`… و${toArabicDigits(heads.length - n)} غيرها في الريل.`);
  }

  /* 3 — جملة الربط */
  if (!wantShort) {
    lines.push('');
    lines.push(pick(BRIDGES));
  }

  /* 4 — الخاتمة الاقتباسية */
  if (wantLong && outro && o.style !== 'story') {
    lines.push('');
    lines.push('«' + outro + '»');
  }

  /* 5 — الدعوات */
  lines.push('');
  if (cta) lines.push(cta);
  if (o.listCta) lines.push(pick(LIST_CTAS));
  if (o.saveCta) lines.push(pick(SAVE_CTAS));
  if (o.islamic) { lines.push(''); lines.push(pick(ISLAMIC_TOUCHES)); }

  /* 6 — المتابعة والهاشتاقات */
  lines.push('');
  lines.push(pick(FOLLOW_LINE));
  lines.push('');
  lines.push(buildHashtags(o.category, o.tagCount));

  /* تنظيف الأسطر الفارغة المتتالية */
  return lines.join('\n').replace(/\n{3,}/g, '\n\n').trim();
}

/* يبني سطر الهاشتاقات بالعدد المطلوب، مع خلط من المجموعة الفيروسية */
function buildHashtags(category, count) {
  const n = Math.max(3, Math.min(10, count || 7));
  const cat = CAPTION_CATEGORIES.find(c => c.key === category);
  const primary = (HASHTAG_SETS[cat ? cat.tags : 'wai'] || HASHTAG_SETS.wai).split(' ');
  const viral   = HASHTAG_SETS.viral.split(' ');

  const out = [];
  primary.forEach(t => { if (out.length < n && !out.includes(t)) out.push(t); });
  viral.forEach(t   => { if (out.length < n && !out.includes(t)) out.push(t); });
  return out.join(' ');
}

/* يستخرج مدخلات المولّد من مشروع محفوظ */
function captionInputFromProject(p) {
  const c = p.screens.content;
  return {
    hook:  p.screens.hook.text,
    title: c.title,
    outro: c.outro,
    cta:   c.cta,
    heads: (c.points || []).map(pt => pt.head).filter(Boolean),
    pointCount: (c.points || []).filter(pt => pt.head || pt.body).length
  };
}
