/**
 * بدائل الإجابة المشتركة، بصياغة كل مقياس الأصلية.
 *
 * لا نوحّد البدائل بين المقاييس: تغيير «أبداً… تقريباً كل يوم» في مقياس
 * القلق إلى «لا أوافق… أوافق» يغيّر ما يقيسه المقياس ويُبطل مقارنته
 * بالدراسات التي بُنيت عليه.
 */
import type { Option } from '../lib/types.ts';

/** IPIP: مدى انطباق العبارة. */
export const ACCURACY5: Option[] = [
	{ value: 1, label: 'لا تنطبق عليّ إطلاقاً' },
	{ value: 2, label: 'لا تنطبق غالباً' },
	{ value: 3, label: 'بين بين' },
	{ value: 4, label: 'تنطبق غالباً' },
	{ value: 5, label: 'تنطبق عليّ تماماً' },
];

/** مقياس الصلابة الموجز: الموافقة. */
export const AGREE5: Option[] = [
	{ value: 1, label: 'لا أوافق بشدّة' },
	{ value: 2, label: 'لا أوافق' },
	{ value: 3, label: 'محايد' },
	{ value: 4, label: 'أوافق' },
	{ value: 5, label: 'أوافق بشدّة' },
];

/** GAD-7: التكرار خلال أسبوعين، من 0 إلى 3. */
export const GAD4: Option[] = [
	{ value: 0, label: 'أبداً' },
	{ value: 1, label: 'عدّة أيام' },
	{ value: 2, label: 'أكثر من نصف الأيام' },
	{ value: 3, label: 'تقريباً كل يوم' },
];

/** كوبنهاغن: التكرار، والقيم 0–100 كما في المقياس الأصلي. */
export const CBI_FREQ: Option[] = [
	{ value: 100, label: 'دائماً' },
	{ value: 75, label: 'غالباً' },
	{ value: 50, label: 'أحياناً' },
	{ value: 25, label: 'نادراً' },
	{ value: 0, label: 'أبداً أو نادراً جداً' },
];

/** كوبنهاغن: الدرجة، لبنود العمل الثلاثة الأولى. */
export const CBI_DEGREE: Option[] = [
	{ value: 100, label: 'بدرجة كبيرة جداً' },
	{ value: 75, label: 'بدرجة كبيرة' },
	{ value: 50, label: 'إلى حدّ ما' },
	{ value: 25, label: 'بدرجة قليلة' },
	{ value: 0, label: 'بدرجة قليلة جداً' },
];

/** روزنبرغ: أربعة بدائل بلا نقطة وسطى، كما صمّمه صاحبه. */
export const AGREE4: Option[] = [
	{ value: 1, label: 'لا أوافق بشدّة' },
	{ value: 2, label: 'لا أوافق' },
	{ value: 3, label: 'أوافق' },
	{ value: 4, label: 'أوافق بشدّة' },
];

/** مقياس بيرغن لإدمان التواصل: التكرار خلال السنة الأخيرة. */
export const BSMAS5: Option[] = [
	{ value: 1, label: 'نادراً جداً' },
	{ value: 2, label: 'نادراً' },
	{ value: 3, label: 'أحياناً' },
	{ value: 4, label: 'غالباً' },
	{ value: 5, label: 'غالباً جداً' },
];

/** سبع نقاط للموافقة، كما في ECR-RS الأصلي (Fraley 2011) وSRBAI (Gardner 2012). */
export const AGREE7: Option[] = [
	{ value: 1, label: 'لا أوافق بشدّة' },
	{ value: 2, label: 'لا أوافق' },
	{ value: 3, label: 'لا أوافق قليلاً' },
	{ value: 4, label: 'محايد' },
	{ value: 5, label: 'أوافق قليلاً' },
	{ value: 6, label: 'أوافق' },
	{ value: 7, label: 'أوافق بشدّة' },
];

/** مقياس الرفاه المالي (CFPB)، الجزء الأوّل: «هذه العبارة تصفني…» — بترتيب ورقة التصحيح الرسمية. */
export const CFPB_DESCRIBE: Option[] = [
	{ value: 4, label: 'تصفني تماماً' },
	{ value: 3, label: 'تصفني جيّداً' },
	{ value: 2, label: 'تصفني إلى حدّ ما' },
	{ value: 1, label: 'تصفني قليلاً' },
	{ value: 0, label: 'لا تصفني إطلاقاً' },
];

/** مقياس الرفاه المالي (CFPB)، الجزء الثاني: «هذه العبارة تنطبق عليّ…». */
export const CFPB_FREQ: Option[] = [
	{ value: 4, label: 'دائماً' },
	{ value: 3, label: 'غالباً' },
	{ value: 2, label: 'أحياناً' },
	{ value: 1, label: 'نادراً' },
	{ value: 0, label: 'أبداً' },
];

/** سؤال المخاطرة في SOEP (Dohmen et al. 2011): من 0 إلى 10، والطرفان وحدهما موسومان. */
export const RISK11: Option[] = Array.from( { length: 11 }, ( _, v ) => ( {
	value: v,
	label: v === 0 ? 'أتجنّب المخاطرة تماماً' : v === 10 ? 'مستعدّ تماماً للمخاطرة' : String( v ),
} ) );
