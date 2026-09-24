/**
 * ما تشترك فيه اختبارات الاستدلال الأربعة: بنود أصلية لم تُجرَّب بعد.
 *
 * لا نعرف صعوبتها ولا ثباتها، فلا نعرض نطاق ثقة ولا مقارنة بالناس، ولا
 * نسمّي النتيجة «ذكاءً». نقول كم أصبت، ونشرح كل حلّ.
 */
import type { Instrument, Subscale } from '../lib/types.ts';

export function reasoningInstrument( kind: string ): Instrument {
	return {
		name: `بنود ${ kind } أصلية — بصيرة`,
		authors: 'بصيرة، 2026',
		citation: 'بنود أصلية من إعداد المنصّة، على طراز أنواع البنود في ICAR (International Cognitive Ability Resource). Condon, D. M., & Revelle, W. (2014). The International Cognitive Ability Resource: Development and initial validation of a public-domain measure. Intelligence, 43, 52–64.',
		license: 'original',
		licenseNote: 'البنود من إعداد المنصّة.',
		translation: 'original',
		translationNote: 'كُتبت بالعربية مباشرة.',
	};
}

export function countScale( n: number, bands: Subscale[ 'bands' ] ): Subscale {
	return {
		id: 'R',
		name: 'الإجابات الصحيحة',
		scoring: 'count',
		min: 0,
		max: n,
		alpha: 0,
		sd: 0,
		uncalibrated: true,
		paramsSource: 'بنود أصلية لم تُجرَّب على عيّنة بعد: لا ثبات معروفاً',
		verified: false,
		higherIs: 'better',
		about: `عدد الإجابات الصحيحة من ${ n } — ولا شيء أكثر.`,
		bands,
	};
}

export const REASONING_CANNOT = [
	'بنوده أصلية ولم تُجرَّب على عيّنة بعد: لا نعرف صعوبة كل سؤال ولا ثبات المجموعة، لذلك لا نطاق ثقة ولا مقارنة بالآخرين.',
	'لا يعطي رقم ذكاء ولا «نسبة مئوية»: عدد قليل من الأسئلة غير المعايَرة لا يسمح بذلك بصدق.',
	'يتأثّر بالتعب والتوتّر والألفة بنوع الأسئلة: من اعتاد هذه الألغاز يصيب أكثر، لا لأنه أقدر بالضرورة.',
];
