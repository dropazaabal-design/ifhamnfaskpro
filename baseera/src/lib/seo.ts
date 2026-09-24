/**
 * بيانات منظَّمة (JSON-LD) — ما تقرؤه محرّكات البحث لتفهم الصفحة.
 *
 * نولّد فقط ما يطابق محتوى الصفحة الظاهر: Google يعاقب البيانات المنظَّمة
 * التي تصف شيئاً لا يراه الزائر.
 */
import { SITE } from '../config.ts';

export const abs = ( path: string ) => new URL( path, SITE.url ).toString();

export function organization() {
	return {
		'@type': 'Organization',
		'@id': abs( '/#org' ),
		name: SITE.publisher,
		url: SITE.publisherUrl,
		sameAs: [ `https://x.com/${ SITE.twitter.replace( '@', '' ) }` ],
	};
}

export function website() {
	return {
		'@type': 'WebSite',
		'@id': abs( '/#site' ),
		url: abs( '/' ),
		name: SITE.name,
		inLanguage: 'ar',
		publisher: { '@id': abs( '/#org' ) },
		potentialAction: {
			'@type': 'SearchAction',
			target: { '@type': 'EntryPoint', urlTemplate: abs( '/?q={search_term_string}' ) },
			'query-input': 'required name=search_term_string',
		},
	};
}

export function breadcrumbs( trail: { name: string; path: string }[] ) {
	return {
		'@type': 'BreadcrumbList',
		itemListElement: trail.map( ( t, i ) => ( {
			'@type': 'ListItem',
			position: i + 1,
			name: t.name,
			item: abs( t.path ),
		} ) ),
	};
}

export function faqPage( faq: { q: string; a: string }[] ) {
	return {
		'@type': 'FAQPage',
		mainEntity: faq.map( ( f ) => ( {
			'@type': 'Question',
			name: f.q,
			acceptedAnswer: { '@type': 'Answer', text: f.a },
		} ) ),
	};
}

/**
 * الاختبار كـ Quiz — النوع الذي يعرّفه schema.org للاختبارات التعليمية،
 * مع «about» يشير إلى المقياس العلمي ومرجعه.
 */
export function quiz( t: { slug: string; title: string; description: string; minutes: number; updated: string; instrument: { name: string; citation: string } } ) {
	return {
		'@type': 'Quiz',
		'@id': abs( `/tests/${ t.slug }/#quiz` ),
		name: t.title,
		description: t.description,
		inLanguage: 'ar',
		url: abs( `/tests/${ t.slug }/` ),
		timeRequired: `PT${ t.minutes }M`,
		isAccessibleForFree: true,
		dateModified: t.updated,
		educationalUse: 'self-assessment',
		about: { '@type': 'Thing', name: t.instrument.name, description: t.instrument.citation },
		publisher: { '@id': abs( '/#org' ) },
	};
}

export function article( a: { title: string; description: string; path: string; updated: string } ) {
	return {
		'@type': 'Article',
		headline: a.title,
		description: a.description,
		inLanguage: 'ar',
		url: abs( a.path ),
		dateModified: a.updated,
		datePublished: a.updated,
		author: { '@id': abs( '/#org' ) },
		publisher: { '@id': abs( '/#org' ) },
		mainEntityOfPage: abs( a.path ),
		image: abs( `/og${ a.path.replace( /\/$/, '' ) || '/home' }.png` ),
	};
}

export function graph( ...nodes: object[] ) {
	return { '@context': 'https://schema.org', '@graph': [ organization(), website(), ...nodes ] };
}
