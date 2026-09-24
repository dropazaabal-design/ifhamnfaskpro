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

/** GAD-7: التكرار خلال أسبوعين، من ٠ إلى ٣. */
export const GAD4: Option[] = [
	{ value: 0, label: 'أبداً' },
	{ value: 1, label: 'عدّة أيام' },
	{ value: 2, label: 'أكثر من نصف الأيام' },
	{ value: 3, label: 'تقريباً كل يوم' },
];

/** كوبنهاغن: التكرار، والقيم ٠–١٠٠ كما في المقياس الأصلي. */
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
