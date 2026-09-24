/**
 * الكتالوج الكامل — الواحد والأربعون في خطّة بصيرة الأصلية.
 *
 * «قريباً» هنا ليست وعداً فارغاً: لكل اختبار مؤجَّل سبب مكتوب، ويُعرض
 * في صفحة المنهجية. بعضها ينتظر ترخيصاً، وبعضها لا يوجد له مقياس مُحكَّم
 * أصلاً، ونفضّل ألّا نُطلقه على أن نخترع له أسئلة ونسمّيها علماً.
 */
import type { CatalogEntry } from '../lib/types.ts';

export const catalog: CatalogEntry[] = [
	// علم النفس والسلوك
	{ slug: 'anxiety-scale', title: 'مستوى القلق', blurb: 'مقياس GAD-7 المعتمد في العيادات.', category: 'psychology', icon: '🫧', minutes: '~2 د', status: 'live' },
	{ slug: 'resilience-test', title: 'الصلابة النفسية', blurb: 'كم تحتاج لتنهض بعد الشدائد؟', category: 'psychology', icon: '🛡️', minutes: '~2 د', status: 'live' },
	{ slug: 'burnout-assessment', title: 'الاحتراق الوظيفي', blurb: 'مقياس كوبنهاغن: إنهاكك العامّ وإنهاكك من العمل.', category: 'psychology', icon: '🔥', minutes: '~3 د', status: 'live' },
	{ slug: 'self-esteem', title: 'تقدير الذات والثقة بالنفس', blurb: 'مقياس روزنبرغ منذ ستّين عاماً.', category: 'psychology', icon: '🪞', minutes: '~2 د', status: 'live' },
	{ slug: 'sensitive-test', title: 'الأشخاص الحسّاسون', blurb: 'عمق المشاعر والحسّ الجمالي والتأثّر بالضغط.', category: 'psychology', icon: '🌊', minutes: '~3 د', status: 'live' },
	{ slug: 'eq-test', title: 'الذكاء العاطفي (EQ)', blurb: 'ما مدى فهمك لمشاعرك ومشاعر غيرك؟', category: 'psychology', icon: '💗', minutes: '~3 د', status: 'live' },
	{ slug: 'attachment-style', title: 'نمط التعلّق العاطفي', blurb: 'آمن، قلِق، أم متجنّب؟', category: 'psychology', icon: '🔗', minutes: '~3 د', status: 'live' },
	{ slug: 'impostor-syndrome', title: 'متلازمة المحتال', blurb: 'هل تشعر أنك لا تستحقّ نجاحك؟', category: 'psychology', icon: '🎭', minutes: '~3 د', status: 'soon', holdReason: 'مقياس كلانس لظاهرة المحتال (CIPS)، الأشهر، مملوك لمؤلّفته ويحتاج إذناً، ولم نجد مقياساً مفتوحاً موثّقاً للمفهوم. لن ننشر بنوده بلا إذن، ولن نخترع بديلاً ونسمّيه باسمه.' },

	// العلوم المعرفية والانتباه
	{ slug: 'brainrot-test', title: 'مقياس التعفّن الدماغي', blurb: 'مقياس بيرغن لعلاقتك بوسائل التواصل.', category: 'cognitive', icon: '🫠', minutes: '~2 د', status: 'live' },
	{ slug: 'focus-test', title: 'التركيز والانتباه', blurb: 'مهمّة دقيقتين تلتقط سرحان الانتباه.', category: 'cognitive', icon: '🎯', minutes: '~2 د', status: 'live' },
	{ slug: 'cognitive-biases', title: 'الانحيازات المعرفية', blurb: 'كيف تخدعك عقليتك في القرارات؟', category: 'cognitive', icon: '🌀', minutes: '~5 د', status: 'live' },
	{ slug: 'thinking-style', title: 'نمط التفكير', blurb: 'تجريدي، خيالي، أم متأنٍّ؟ ميول لا «نمط».', category: 'cognitive', icon: '💭', minutes: '~3 د', status: 'live' },
	{ slug: 'decision-making', title: 'اتخاذ القرار', blurb: 'كيف تقرّر تحت الضغط والغموض؟', category: 'cognitive', icon: '⚖️', minutes: '~3 د', status: 'live' },
	{ slug: 'working-memory', title: 'الذاكرة العاملة', blurb: 'كم رقماً تتذكّر؟ مهمّة تفاعلية.', category: 'cognitive', icon: '🧷', minutes: '~3 د', status: 'live' },
	{ slug: 'curiosity-test', title: 'الفضول المعرفي', blurb: 'كم أنت متعطّش للمعرفة والاكتشاف؟', category: 'cognitive', icon: '🔍', minutes: '~3 د', status: 'live' },

	// الذكاء والقدرات
	{ slug: 'iq-test', title: 'اختبار الذكاء IQ', blurb: 'اثنا عشر سؤال استدلال — بلا رقم مُخترَع.', category: 'intelligence', icon: '🧠', minutes: '~6 د', status: 'live' },
	{ slug: 'genius-test', title: 'هل أنت عبقري؟', blurb: 'ألغاز يخطئ فيها طلّاب هارفارد.', category: 'intelligence', icon: '🌟', minutes: '~4 د', status: 'live' },
	{ slug: 'logical-reasoning', title: 'الاستدلال المنطقي', blurb: 'قدرتك على الاستنتاج والأنماط.', category: 'intelligence', icon: '🧮', minutes: '~4 د', status: 'live' },
	{ slug: 'numerical-reasoning', title: 'الذكاء العددي', blurb: 'مدى قوّتك في التعامل مع الأرقام.', category: 'intelligence', icon: '🔢', minutes: '~5 د', status: 'live' },
	{ slug: 'verbal-reasoning', title: 'الذكاء اللفظي', blurb: 'قدرتك اللغوية والتناظر اللفظي.', category: 'intelligence', icon: '📖', minutes: '~4 د', status: 'live' },
	{ slug: 'spatial-reasoning', title: 'الذكاء المكاني', blurb: 'تصوّرك للأشكال في الفراغ.', category: 'intelligence', icon: '🧊', minutes: '~4 د', status: 'live' },

	// الوعي المالي
	{ slug: 'salary-planner', title: 'حاسبة تخطيط الراتب', blurb: 'قسّم دخلك بقاعدة 50/30/20 في دقيقة.', category: 'financial', icon: '🧮', minutes: 'أداة', status: 'tool' },
	{ slug: 'financial-literacy', title: 'الثقافة المالية', blurb: 'اختبر معرفتك بأساسيات المال.', category: 'financial', icon: '🏦', minutes: '~2 د', status: 'live' },
	{ slug: 'money-personality', title: 'شخصيتك المالية', blurb: 'منفِق، مدّخِر، أم مستثمر؟', category: 'financial', icon: '💳', minutes: '~3 د', status: 'soon', holdReason: 'أقرب مقياس علمي (مقياس المقتِّر والمبذِّر، Rick وزملاؤه 2008) لم نتحقّق بعد من نصّه الأصلي وشروط استخدامه، ولن ننشره من نسخ منقولة.' },
	{ slug: 'wealth-mindset', title: 'عقلية الثراء', blurb: 'كيف يفكّر عقلك تجاه المال؟', category: 'financial', icon: '💎', minutes: '~3 د', status: 'soon', holdReason: '«عقلية الثراء» مفهوم من كتب التنمية لا من البحث العلمي، ولا مقياس مُحكَّماً له. لن نخترع أسئلة ونسمّيها علماً؛ اختبار علاقتك بالمال يقيس ما يمكن قياسه بصدق.' },
	{ slug: 'saving-style', title: 'أسلوبك في الادّخار', blurb: 'ما نمطك في إدارة مصاريفك؟', category: 'financial', icon: '🪙', minutes: '~3 د', status: 'soon', holdReason: 'لا مقياس مُحكَّماً لـ«أسلوب الادّخار» كنمط. اختبار الثقافة المالية واختبار علاقتك بالمال يقيسان ما يمكن قياسه بصدق، وحاسبة الراتب تساعد عملياً.' },
	{ slug: 'risk-tolerance', title: 'تحمّل المخاطر المالية', blurb: 'ما مدى جرأتك الاستثمارية؟', category: 'financial', icon: '📉', minutes: '~1 د', status: 'live' },
	{ slug: 'money-relationship', title: 'علاقتك بالمال', blurb: 'هل تشعر بالأمان والحرية في مالك؟', category: 'financial', icon: '🧾', minutes: '~2 د', status: 'live' },

	// العادات ونمط الحياة
	{ slug: 'self-discipline', title: 'الانضباط الذاتي', blurb: 'هل تبدأ المهامّ أم تؤجّلها؟', category: 'habits', icon: '🧗', minutes: '~2 د', status: 'live' },
	{ slug: 'procrastination-test', title: 'المماطلة والتأجيل', blurb: 'ما مدى ميلك لتأجيل المهامّ؟', category: 'habits', icon: '⏳', minutes: '~3 د', status: 'soon', holdReason: 'مقاييس المماطلة المعتمدة مجّانية للأبحاث، واستخدامها التجاري غير محسوم. حتى يُحسم، اختبار الانضباط الذاتي يقيس الجانب المقابل بمقياس في الملك العام.' },
	{ slug: 'sleep-quality', title: 'جودة النوم', blurb: 'هل نومك يجدّد طاقتك فعلاً؟', category: 'habits', icon: '😴', minutes: '~3 د', status: 'soon', holdReason: 'مقياسا جودة النوم الأشهر، بيتسبرغ (PSQI) وRU-SATED، مملوكان لجامعة بيتسبرغ ويحتاجان إذناً للاستخدام التجاري. ندرس بديلاً مفتوحاً.' },
	{ slug: 'productivity-test', title: 'مستوى إنتاجيتك', blurb: 'السمات التي تحرّك إنجازك.', category: 'habits', icon: '📈', minutes: '~3 د', status: 'live' },
	{ slug: 'habit-strength', title: 'قوّة عاداتك', blurb: 'هل صارت عادتك تلقائية؟', category: 'habits', icon: '🧱', minutes: '~1 د', status: 'live' },
	{ slug: 'time-management', title: 'إدارة الوقت', blurb: 'كيف تتعامل مع وقتك وأولوياتك؟', category: 'habits', icon: '⏱️', minutes: '~3 د', status: 'live' },
	{ slug: 'work-life-balance', title: 'التوازن بين العمل والحياة', blurb: 'هل تعيش أم تعمل فقط؟', category: 'habits', icon: '⚖️', minutes: '~3 د', status: 'soon', holdReason: 'مقاييس صراع العمل والأسرة المعتمدة منشورة للأبحاث، واستخدامها في موقع تجاري غير محسوم. حتى يُحسم، اختبار الاحتراق الوظيفي يقيس جانباً قريباً بمقياس مفتوح.' },

	// الشخصية
	{ slug: 'big-five', title: 'العوامل الخمسة للشخصية', blurb: 'النموذج المعتمد في أبحاث الشخصية.', category: 'personality', icon: '🖐️', minutes: '~8 د', status: 'live' },
	{ slug: 'introvert-extrovert', title: 'انطوائي أم انبساطي؟', blurb: 'والجواب العلمي الذي لا يقوله أحد.', category: 'personality', icon: '🎭', minutes: '~2 د', status: 'live' },
	{ slug: 'career-test', title: 'قيادي أم تنفيذي؟', blurb: 'هل تميل لرسم الرؤية أم للتنفيذ؟', category: 'personality', icon: '🧭', minutes: '~2 د', status: 'live' },
	{ slug: 'core-values', title: 'قيمك الأساسية', blurb: 'ما الذي يحرّكك في الحياة؟', category: 'personality', icon: '🧿', minutes: 'أداة', status: 'tool' },
	{ slug: 'communication-style', title: 'نمط التواصل', blurb: 'كيف تتواصل مع الآخرين؟', category: 'personality', icon: '🗣️', minutes: '~3 د', status: 'live' },
	{ slug: 'work-style', title: 'نمط العمل المفضّل', blurb: 'في أي بيئة تُنجز بأفضل صورة؟', category: 'personality', icon: '🗂️', minutes: '~3 د', status: 'live' },
];

export const liveCatalog = catalog.filter( ( c ) => c.status !== 'soon' );
