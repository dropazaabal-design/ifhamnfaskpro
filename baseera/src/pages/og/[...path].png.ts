/**
 * نقطة بناء صور المعاينة: ‎/og/<مسار الصفحة>.png‎.
 */
import type { APIRoute, GetStaticPaths } from 'astro';
import { ogPng } from '../../lib/og.ts';
import { tests } from '../../data/tests/index.ts';
import { categories, categoryById } from '../../data/categories.ts';
import { articles } from '../../data/articles.ts';
import { legalPages } from '../../data/legal.ts';
import { salaryPlanner } from '../../data/tools/salary-planner.ts';
import { coreValues } from '../../data/tools/core-values.ts';
import { SITE } from '../../config.ts';
import { countNoun, QUESTIONS, STATEMENTS, MINUTES } from '../../lib/format.ts';

const CAT_HEX: Record<string, string> = {
	psychology: '#0d9488',
	cognitive: '#2563eb',
	intelligence: '#7c3aed',
	financial: '#059669',
	habits: '#0284c7',
	personality: '#4f46e5',
};

export const getStaticPaths: GetStaticPaths = () => {
	const pages: { path: string; title: string; subtitle: string; accent?: string; kicker?: string }[] = [
		{ path: 'home', title: 'اعرف نفسك بعُمق', subtitle: 'اختبارات علمية مجانية — مع هامش الخطأ لكل نتيجة', kicker: SITE.name },
		...tests.map( ( t ) => ( {
			path: `tests/${ t.slug }`,
			title: t.title.split( ' — ' )[ 0 ],
			// عربية خالصة: resvg يختار خطّ المقطع من أوّل حرف فيه، فيسقط اللاتيني في مقطع عربي.
			// «مقياس منشور» وصف صادق للمقاييس وحدها، لا للبنود الأصلية ولا للمهامّ.
			subtitle: t.kind === 'task'
				? `مهمّة تفاعلية · نحو ${ countNoun( t.minutes, MINUTES, 'gen' ) } · على جهازك`
				: `${ countNoun( t.items.length, t.kind === 'choice' ? QUESTIONS : STATEMENTS ) } · نحو ${ countNoun( t.minutes, MINUTES, 'gen' ) } · ${ t.instrument.license === 'original' ? 'مع شرح كل حلّ' : 'مقياس منشور' }`,
			accent: CAT_HEX[ t.category ],
			kicker: categoryById.get( t.category )!.name,
		} ) ),
		{ path: `tools/${ salaryPlanner.slug }`, title: salaryPlanner.title, subtitle: 'قاعدة 50/30/20 — وتعديلها حين لا تناسب مدينتك', accent: CAT_HEX.financial, kicker: 'أداة' },
		{ path: `tools/${ coreValues.slug }`, title: coreValues.title, subtitle: 'عشر قيم على دائرة شوارتز — أيّها يحرّكك؟', accent: CAT_HEX.personality, kicker: 'أداة تأمّل' },
		...categories.map( ( c ) => ( { path: `category/${ c.id }`, title: c.name, subtitle: 'اختبارات بمصادر مكتوبة وحدود صادقة', accent: CAT_HEX[ c.id ], kicker: 'فئة' } ) ),
		...articles.map( ( a ) => ( { path: `articles/${ a.slug }`, title: a.title, subtitle: '', kicker: 'مقال' } ) ),
		{ path: 'articles', title: 'مقالات', subtitle: 'لمن يريد أن يفهم ما وراء النتيجة' },
		{ path: 'methodology', title: 'منهجيتنا وميزان الثقة', subtitle: 'كل رقم نستخدمه، مع مصدره', kicker: 'الشفافية' },
		{ path: 'library', title: 'مكتبة الوعي البصري', subtitle: 'خمسون خريطة بصرية في المال والإنتاجية والعادات وعلم النفس', kicker: SITE.publisher },
		...legalPages.map( ( l ) => ( { path: `legal/${ l.slug }`, title: l.title, subtitle: '', kicker: SITE.name } ) ),
	];

	return pages.map( ( p ) => ( { params: { path: p.path }, props: p } ) );
};

export const GET: APIRoute = ( { props } ) => {
	const png = ogPng( props.title, props.subtitle, props.accent, props.kicker );

	return new Response( new Uint8Array( png ), { headers: { 'Content-Type': 'image/png' } } );
};
