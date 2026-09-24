/**
 * عقد البيانات: كل اختبار في المنصّة يُوصَف بهذه الأنواع.
 *
 * الحقول الإلزامية هنا ليست تنظيماً للكود بل التزام علمي: اختبارٌ بلا
 * مصدر، أو بلا ثبات منشور، أو بلا قائمة «ما لا يستطيع قوله»، لا يمرّ من
 * tools/check-science.mjs ولا يُبنى.
 */

export type CategoryId = 'psychology' | 'cognitive' | 'intelligence' | 'financial' | 'habits' | 'personality';

export interface Option {
	value: number;
	label: string;
}

export interface Choice {
	label: string;
	/** بديل مرسوم (مصفوفات الاستدلال): SVG يحلّ محلّ النصّ، والنصّ يبقى لقارئ الشاشة. */
	figure?: string;
	correct?: boolean;
	/** الجواب الحدسي الخاطئ الذي يقع فيه أغلب الناس (اختبار الانعكاس المعرفي). */
	intuitive?: boolean;
}

export interface Item {
	id: string;
	text: string;
	/** ‎-1‎ للبند المعكوس الصياغة. */
	key?: 1 | -1;
	/** المقياس الفرعي الذي يُحسب فيه. */
	scale: string;
	/** بدائل خاصّة بالبند (مقياس كوبنهاغن يخلط صيغتين). */
	options?: Option[];
	/** للأسئلة ذات الجواب الصحيح. */
	choices?: Choice[];
	/** رسم SVG للمصفوفات. */
	figure?: string;
	/** شرح الحلّ يظهر بعد النتيجة. */
	solution?: string;
}

export interface Band {
	/** الحدّ الأعلى الشامل للفئة. */
	upTo: number;
	label: string;
	tone: 'low' | 'mid' | 'high' | 'alert';
	text: string;
}

export interface Subscale {
	id: string;
	name: string;
	/**
	 * sum: مجموع البنود · mean: متوسّطها · count: عدد الصحيح.
	 * البدائل تحمل قيمها النهائية، فمقياس كوبنهاغن (٠–١٠٠) يُحسب بـ mean.
	 */
	scoring: 'sum' | 'mean' | 'count';
	min: number;
	max: number;
	/** الثبات المنشور (ألفا كرونباخ). */
	alpha: number;
	/** الانحراف المعياري في المجتمع، بوحدات الدرجة نفسها. */
	sd: number;
	/** مصدر القيمتين. */
	paramsSource: string;
	/**
	 * هل نُقلت القيمتان من المصدر الأصلي مباشرة؟ false تعني تقديراً من
	 * الأدبيات، وتظهر كذلك في صفحة المنهجية ليراجعها من يشاء.
	 */
	verified: boolean;
	/** «أعلى = أفضل» يحكم لون الشريط لا معناه. */
	higherIs: 'better' | 'worse' | 'neutral';
	bands: Band[];
	/** شرح ما يعنيه المقياس بجملة. */
	about?: string;
	/**
	 * بنود أصلية لم يُقَس ثباتها بعد: لا يُعرض نطاق ثقة، لأن حسابه يحتاج
	 * ثباتاً لا نملكه — واختراعه أسوأ من غيابه.
	 */
	uncalibrated?: boolean;
}

export interface Instrument {
	name: string;
	authors: string;
	citation: string;
	url?: string;
	license: 'public-domain' | 'free-with-citation' | 'original';
	licenseNote: string;
	translation: 'platform' | 'validated' | 'original';
	translationNote: string;
}

export interface Test {
	slug: string;
	title: string;
	/** عنوان الصفحة في محرّك البحث: السؤال كما يكتبه الناس. */
	seoTitle: string;
	description: string;
	/** العبارات التي يبحث بها الناس، تُوجّه كتابة المحتوى. */
	queries: string[];
	category: CategoryId;
	icon: string;
	minutes: number;
	kind: 'likert' | 'choice';
	instrument: Instrument;
	intro: string;
	instructions: string;
	/** البدائل المشتركة لبنود ليكرت. */
	options?: Option[];
	items: Item[];
	subscales: Subscale[];
	measures: string[];
	/** ما لا يستطيع الاختبار قوله — إلزامي، ولا يُبنى اختبار بدونه. */
	cannot: string[];
	/** تنبيه بارز إضافي لبعض الاختبارات. */
	caution?: string;
	/** اختبار حسّاس: لا إعلانات، وموارد دعم ظاهرة. */
	sensitive?: boolean;
	article: { heading: string; body: string }[];
	faq: { q: string; a: string }[];
	related: string[];
	/** موضوع المنتج الرقمي المرتبط، ولماذا. */
	product?: { topic: string; why: string };
	updated: string;
}

export interface CatalogEntry {
	slug: string;
	title: string;
	blurb: string;
	category: CategoryId;
	icon: string;
	minutes: string;
	status: 'live' | 'soon' | 'tool';
	/** سبب عدم الإطلاق، يُكتب بصدق. */
	holdReason?: string;
}
