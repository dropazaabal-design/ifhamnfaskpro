/**
 * الفئات الستّ — بأسمائها وألوانها كما في تصميم بصيرة الأصلي.
 */
import type { CategoryId } from '../lib/types.ts';

export interface Category {
	id: CategoryId;
	name: string;
	icon: string;
	/** متغيّر CSS المعرَّف في الرموز. */
	color: string;
	intro: string;
}

export const categories: Category[] = [
	{
		id: 'psychology',
		name: 'علم النفس والسلوك',
		icon: '🧠',
		color: 'var(--cat-psychology)',
		intro: 'مقاييس للقلق والصلابة والاحتراق وتقدير الذات — كلّها من أدوات منشورة تستخدمها الأبحاث والعيادات، مع حدود كل منها مكتوبة بوضوح.',
	},
	{
		id: 'cognitive',
		name: 'العلوم المعرفية والانتباه',
		icon: '🧩',
		color: 'var(--cat-cognitive)',
		intro: 'كيف ينتبه عقلك ويقرّر ويتشتّت. نقيس ما له مقياس منشور، ونقول بصراحة حين يكون المصطلح الشائع أوسع من العلم.',
	},
	{
		id: 'intelligence',
		name: 'الذكاء والقدرات',
		icon: '⚡',
		color: 'var(--cat-intelligence)',
		intro: 'ألغاز واستدلال — بلا أرقام ذكاء مُخترَعة. ستعرف كم أصبت ولماذا، وهذا أقصى ما يستطيعه اختبار قصير على الإنترنت بصدق.',
	},
	{
		id: 'financial',
		name: 'الوعي المالي',
		icon: '💰',
		color: 'var(--cat-financial)',
		intro: 'أدوات عملية لقرارات المال اليومية، بقواعد معروفة ومصادرها — وبصراحة عن متى لا تناسب واقعك.',
	},
	{
		id: 'habits',
		name: 'العادات ونمط الحياة',
		icon: '🔁',
		color: 'var(--cat-habits)',
		intro: 'الانضباط والعادات وعلاقتك بالشاشة. قياس الحاضر خطوة أولى نحو تغيير ما تريد تغييره.',
	},
	{
		id: 'personality',
		name: 'الشخصية',
		icon: '🧭',
		color: 'var(--cat-personality)',
		intro: 'نموذج العوامل الخمسة، المعتمد في أبحاث الشخصية — لا الأبراج ولا الأنماط الستّة عشر.',
	},
];

export const categoryById = new Map( categories.map( ( c ) => [ c.id, c ] ) );
