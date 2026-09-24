/**
 * الكتالوج الكامل — الأربعون اختباراً في خطّة بصيرة الأصلية.
 *
 * «قريباً» هنا ليست وعداً فارغاً: لكل اختبار مؤجَّل سبب مكتوب، ويُعرض
 * في صفحة المنهجية. بعضها ينتظر ترخيصاً، وبعضها لا يوجد له مقياس مُحكَّم
 * أصلاً، ونفضّل ألّا نُطلقه على أن نخترع له أسئلة ونسمّيها علماً.
 */
import type { CatalogEntry } from '../lib/types.ts';

const LICENSE = 'المقياس المعتمد مملوك لمؤلّفه ويحتاج إذناً للاستخدام التجاري — طلبناه قبل النشر.';
const NO_SCALE = 'لا يوجد مقياس مُحكَّم لهذا المفهوم كما يُطرح شعبياً. سننشره كأداة تأمّل موسومة بذلك، لا كاختبار.';
const BUILDING = 'نُعدّ بنوده من مقياس في الملك العام، مع دليل قراءة ومصادر.';

export const catalog: CatalogEntry[] = [
	// علم النفس والسلوك
	{ slug: 'anxiety-scale', title: 'مستوى القلق', blurb: 'مقياس GAD-7 المعتمد في العيادات.', category: 'psychology', icon: '🫧', minutes: '~٢ د', status: 'live' },
	{ slug: 'resilience-test', title: 'الصلابة النفسية', blurb: 'كم تحتاج لتنهض بعد الشدائد؟', category: 'psychology', icon: '🛡️', minutes: '~٢ د', status: 'live' },
	{ slug: 'burnout-assessment', title: 'الاحتراق الوظيفي', blurb: 'مقياس كوبنهاغن: إنهاكك العامّ وإنهاكك من العمل.', category: 'psychology', icon: '🔥', minutes: '~٣ د', status: 'live' },
	{ slug: 'self-esteem', title: 'تقدير الذات والثقة بالنفس', blurb: 'مقياس روزنبرغ منذ ستّين عاماً.', category: 'psychology', icon: '🪞', minutes: '~٢ د', status: 'live' },
	{ slug: 'sensitive-test', title: 'الأشخاص الحسّاسون', blurb: 'قِس مدى رهافة حسّك وعالمك الداخلي.', category: 'psychology', icon: '🌊', minutes: '~٣ د', status: 'soon', holdReason: 'مقياس HSP مملوك للدكتورة إيلين آرون ويحتاج إذناً للنشر في موقع تجاري. لن ننشر بنودها بلا إذن، ولن نخترع بديلاً ونسمّيه المقياس نفسه.' },
	{ slug: 'eq-test', title: 'الذكاء العاطفي (EQ)', blurb: 'ما مدى فهمك لمشاعرك ومشاعر غيرك؟', category: 'psychology', icon: '💗', minutes: '~٤ د', status: 'soon', holdReason: 'المقاييس الأقوى للذكاء العاطفي مملوكة ومدفوعة، والمفهوم نفسه محلّ جدل علمي حول تمايزه عن الشخصية. ندرس مقياساً مفتوحاً مع شرح هذا الجدل.' },
	{ slug: 'attachment-style', title: 'نمط التعلّق العاطفي', blurb: 'آمن، قلِق، أم متجنّب؟', category: 'psychology', icon: '🔗', minutes: '~٤ د', status: 'soon', holdReason: BUILDING },
	{ slug: 'impostor-syndrome', title: 'متلازمة المحتال', blurb: 'هل تشعر أنك لا تستحقّ نجاحك؟', category: 'psychology', icon: '🎭', minutes: '~٣ د', status: 'soon', holdReason: LICENSE },

	// العلوم المعرفية والانتباه
	{ slug: 'brainrot-test', title: 'مقياس التعفّن الدماغي', blurb: 'مقياس بيرغن لعلاقتك بوسائل التواصل.', category: 'cognitive', icon: '🫠', minutes: '~٢ د', status: 'live' },
	{ slug: 'focus-test', title: 'التركيز والانتباه', blurb: 'ما مدى قدرتك على التركيز العميق؟', category: 'cognitive', icon: '🎯', minutes: '~٣ د', status: 'soon', holdReason: 'الانتباه يُقاس بمهمّة مُوقَّتة لا باستبيان. نبنيها مهمّةً تفاعلية، وهي أصعب من سؤال وجواب.' },
	{ slug: 'cognitive-biases', title: 'الانحيازات المعرفية', blurb: 'كيف تخدعك عقليتك في القرارات؟', category: 'cognitive', icon: '🌀', minutes: '~٥ د', status: 'soon', holdReason: BUILDING },
	{ slug: 'thinking-style', title: 'نمط التفكير', blurb: 'تحليلي، إبداعي، أم عملي؟', category: 'cognitive', icon: '💭', minutes: '~٣ د', status: 'soon', holdReason: NO_SCALE },
	{ slug: 'decision-making', title: 'اتخاذ القرار', blurb: 'كيف تقرّر تحت الضغط والغموض؟', category: 'cognitive', icon: '⚖️', minutes: '~٤ د', status: 'soon', holdReason: BUILDING },
	{ slug: 'working-memory', title: 'الذاكرة العاملة', blurb: 'اختبار سريع لقوّة ذاكرتك اللحظية.', category: 'cognitive', icon: '🧷', minutes: '~٣ د', status: 'soon', holdReason: 'الذاكرة العاملة تُقاس بمهمّة (تذكّر تسلسلات متزايدة الطول) لا بأسئلة. نبنيها مهمّةً تفاعلية.' },
	{ slug: 'curiosity-test', title: 'الفضول المعرفي', blurb: 'كم أنت متعطّش للمعرفة والاكتشاف؟', category: 'cognitive', icon: '🔍', minutes: '~٣ د', status: 'soon', holdReason: BUILDING },

	// الذكاء والقدرات
	{ slug: 'iq-test', title: 'اختبار الذكاء IQ', blurb: 'اثنا عشر سؤال استدلال — بلا رقم مُخترَع.', category: 'intelligence', icon: '🧠', minutes: '~٦ د', status: 'live' },
	{ slug: 'genius-test', title: 'هل أنت عبقري؟', blurb: 'ألغاز يخطئ فيها طلّاب هارفارد.', category: 'intelligence', icon: '🌟', minutes: '~٤ د', status: 'live' },
	{ slug: 'logical-reasoning', title: 'الاستدلال المنطقي', blurb: 'قدرتك على الاستنتاج والأنماط.', category: 'intelligence', icon: '🧮', minutes: '~٤ د', status: 'soon', holdReason: BUILDING },
	{ slug: 'numerical-reasoning', title: 'الذكاء العددي', blurb: 'مدى قوّتك في التعامل مع الأرقام.', category: 'intelligence', icon: '🔢', minutes: '~٤ د', status: 'soon', holdReason: BUILDING },
	{ slug: 'verbal-reasoning', title: 'الذكاء اللفظي', blurb: 'قدرتك اللغوية والتناظر اللفظي.', category: 'intelligence', icon: '📖', minutes: '~٤ د', status: 'soon', holdReason: 'التناظر اللفظي يحتاج بنوداً عربية أصلية لا ترجمة، لأن اللغة هي موضوع القياس. نكتبها ثم نجرّبها قبل النشر.' },
	{ slug: 'spatial-reasoning', title: 'الذكاء المكاني', blurb: 'تصوّرك للأشكال في الفراغ.', category: 'intelligence', icon: '🧊', minutes: '~٤ د', status: 'soon', holdReason: BUILDING },

	// الوعي المالي
	{ slug: 'salary-planner', title: 'حاسبة تخطيط الراتب', blurb: 'قسّم دخلك بقاعدة ٥٠/٣٠/٢٠ في دقيقة.', category: 'financial', icon: '🧮', minutes: 'أداة', status: 'tool' },
	{ slug: 'financial-literacy', title: 'الثقافة المالية', blurb: 'اختبر معرفتك بأساسيات المال.', category: 'financial', icon: '🏦', minutes: '~٤ د', status: 'soon', holdReason: BUILDING },
	{ slug: 'money-personality', title: 'شخصيتك المالية', blurb: 'منفِق، مدّخِر، أم مستثمر؟', category: 'financial', icon: '💳', minutes: '~٣ د', status: 'soon', holdReason: NO_SCALE },
	{ slug: 'wealth-mindset', title: 'عقلية الثراء', blurb: 'كيف يفكّر عقلك تجاه المال؟', category: 'financial', icon: '💎', minutes: '~٣ د', status: 'soon', holdReason: NO_SCALE },
	{ slug: 'saving-style', title: 'أسلوبك في الادّخار', blurb: 'ما نمطك في إدارة مصاريفك؟', category: 'financial', icon: '🪙', minutes: '~٣ د', status: 'soon', holdReason: NO_SCALE },
	{ slug: 'risk-tolerance', title: 'تحمّل المخاطر المالية', blurb: 'ما مدى جرأتك الاستثمارية؟', category: 'financial', icon: '📉', minutes: '~٣ د', status: 'soon', holdReason: BUILDING },
	{ slug: 'money-relationship', title: 'علاقتك بالمال', blurb: 'ما جذور عاداتك المالية؟', category: 'financial', icon: '🧾', minutes: '~٣ د', status: 'soon', holdReason: NO_SCALE },

	// العادات ونمط الحياة
	{ slug: 'self-discipline', title: 'الانضباط الذاتي', blurb: 'هل تبدأ المهامّ أم تؤجّلها؟', category: 'habits', icon: '🧗', minutes: '~٢ د', status: 'live' },
	{ slug: 'procrastination-test', title: 'المماطلة والتأجيل', blurb: 'ما مدى ميلك لتأجيل المهامّ؟', category: 'habits', icon: '⏳', minutes: '~٣ د', status: 'soon', holdReason: 'مقاييس المماطلة المعتمدة مجّانية للأبحاث، واستخدامها التجاري غير محسوم. حتى يُحسم، اختبار الانضباط الذاتي يقيس الجانب المقابل بمقياس في الملك العام.' },
	{ slug: 'sleep-quality', title: 'جودة النوم', blurb: 'هل نومك يجدّد طاقتك فعلاً؟', category: 'habits', icon: '😴', minutes: '~٣ د', status: 'soon', holdReason: 'مقياس بيتسبرغ لجودة النوم (PSQI)، الأشهر، مملوك لجامعة بيتسبرغ ويحتاج إذناً. ندرس بديلاً مفتوحاً.' },
	{ slug: 'productivity-test', title: 'مستوى إنتاجيتك', blurb: 'كم أنت فعّال في إنجاز ما يهمّك؟', category: 'habits', icon: '📈', minutes: '~٣ د', status: 'soon', holdReason: NO_SCALE },
	{ slug: 'habit-strength', title: 'قوّة عاداتك', blurb: 'هل عاداتك تبني مستقبلك؟', category: 'habits', icon: '🧱', minutes: '~٣ د', status: 'soon', holdReason: BUILDING },
	{ slug: 'time-management', title: 'إدارة الوقت', blurb: 'كيف تتعامل مع وقتك وأولوياتك؟', category: 'habits', icon: '⏱️', minutes: '~٣ د', status: 'soon', holdReason: BUILDING },
	{ slug: 'work-life-balance', title: 'التوازن بين العمل والحياة', blurb: 'هل تعيش أم تعمل فقط؟', category: 'habits', icon: '⚖️', minutes: '~٣ د', status: 'soon', holdReason: BUILDING },

	// الشخصية
	{ slug: 'big-five', title: 'العوامل الخمسة للشخصية', blurb: 'النموذج المعتمد في أبحاث الشخصية.', category: 'personality', icon: '🖐️', minutes: '~٨ د', status: 'live' },
	{ slug: 'introvert-extrovert', title: 'انطوائي أم انبساطي؟', blurb: 'والجواب العلمي الذي لا يقوله أحد.', category: 'personality', icon: '🎭', minutes: '~٢ د', status: 'live' },
	{ slug: 'career-test', title: 'قيادي أم تنفيذي؟', blurb: 'هل تميل لرسم الرؤية أم للتنفيذ؟', category: 'personality', icon: '🧭', minutes: '~٢ د', status: 'soon', holdReason: NO_SCALE },
	{ slug: 'core-values', title: 'قيمك الأساسية', blurb: 'ما الذي يحرّكك في الحياة؟', category: 'personality', icon: '🧿', minutes: '~٤ د', status: 'soon', holdReason: BUILDING },
	{ slug: 'communication-style', title: 'نمط التواصل', blurb: 'كيف تتواصل مع الآخرين؟', category: 'personality', icon: '🗣️', minutes: '~٣ د', status: 'soon', holdReason: NO_SCALE },
	{ slug: 'work-style', title: 'نمط العمل المفضّل', blurb: 'في أي بيئة تُنجز بأفضل صورة؟', category: 'personality', icon: '🗂️', minutes: '~٣ د', status: 'soon', holdReason: NO_SCALE },
];

export const liveCatalog = catalog.filter( ( c ) => c.status !== 'soon' );
