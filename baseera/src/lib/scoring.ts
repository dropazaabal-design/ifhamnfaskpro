/**
 * محرّك التصحيح — يعمل في المتصفّح والبناء معاً.
 *
 * لا شيء هنا يُرسَل إلى خادم: الإجابات تُصحَّح على جهاز صاحبها.
 */
import type { Item, Subscale, Test } from './types.ts';

export interface Answer {
	/** قيمة البديل المختار (ليكرت) أو فهرس الاختيار (أسئلة الجواب الصحيح). */
	value: number;
	/** مللي ثانية من عرض السؤال حتى الإجابة. */
	ms: number;
}

export interface ScaleResult {
	id: string;
	name: string;
	score: number;
	min: number;
	max: number;
	/** الموضع على مدى المقياس (0–100)، لا مئيني مقابل مجتمع. */
	position: number;
	/** نطاق الثقة 90% من الخطأ المعياري للقياس. */
	low: number;
	high: number;
	sem: number;
	band: Subscale['bands'][number];
	higherIs: Subscale['higherIs'];
	about?: string;
	verified: boolean;
	uncalibrated: boolean;
	/** درجة متوسّط تُعرض بمنزلتين عشريّتين؛ المجموع والعدّ أعداد صحيحة. */
	mean: boolean;
}

/** z لنطاق ثقة 90%. */
const Z90 = 1.645;

/**
 * الخطأ المعياري للقياس.
 *
 * SEM = SD × √(1 − α). كلما قلّ ثبات المقياس اتّسع النطاق — وهذا بالضبط
 * ما يخفيه موقعٌ يقول «أنت 73%» بلا هامش.
 */
export function standardError( sd: number, alpha: number ): number {
	return sd * Math.sqrt( Math.max( 0, 1 - alpha ) );
}

function itemRange( test: Test, item: Item ): [ number, number ] {
	const opts = item.options ?? test.options ?? [];
	const values = opts.map( ( o ) => o.value );

	return [ Math.min( ...values ), Math.max( ...values ) ];
}

/** القيمة بعد عكس البنود السالبة. */
export function itemScore( test: Test, item: Item, raw: number ): number {
	if ( test.kind === 'choice' ) {
		return item.choices?.[ raw ]?.correct ? 1 : 0;
	}

	if ( item.key === -1 ) {
		const [ lo, hi ] = itemRange( test, item );

		return lo + hi - raw;
	}

	return raw;
}

export function round( n: number, places = 1 ): number {
	const f = 10 ** places;

	return Math.round( n * f ) / f;
}

export function scoreTest( test: Test, answers: Record<string, Answer> ): ScaleResult[] {
	return test.subscales.map( ( scale ) => {
		const items = test.items.filter( ( i ) => i.scale === scale.id );
		const values = items
			.filter( ( i ) => answers[ i.id ] !== undefined )
			.map( ( i ) => itemScore( test, i, answers[ i.id ].value ) );

		let score = 0;

		if ( scale.scoring === 'sum' || scale.scoring === 'count' ) {
			score = values.reduce( ( a, b ) => a + b, 0 );
		} else if ( scale.scoring === 'lookup' ) {
			const raw = values.reduce( ( a, b ) => a + b, 0 );
			const table = scale.table ?? [];
			score = table[ Math.max( 0, Math.min( table.length - 1, Math.round( raw ) ) ) ] ?? 0;
		} else {
			score = values.length ? values.reduce( ( a, b ) => a + b, 0 ) / values.length : 0;
		}

		return resultFor( scale, score );
	} );
}

/**
 * النتيجة الكاملة لدرجة واحدة: الموضع، ونطاق الثقة، والفئة.
 *
 * يستخدمها التصحيح، والمهامّ التفاعلية التي تحسب درجتها بنفسها (مدى
 * الأرقام مثلاً)، فتمرّ النتيجتان بالعرض وميزان الثقة نفسيهما.
 */
export function resultFor( scale: Subscale, score: number ): ScaleResult {
	const sem = scale.uncalibrated ? 0 : standardError( scale.sd, scale.alpha );
	const low = Math.max( scale.min, score - Z90 * sem );
	const high = Math.min( scale.max, score + Z90 * sem );
	const band = scale.bands.find( ( b ) => score <= b.upTo ) ?? scale.bands[ scale.bands.length - 1 ];

	return {
		id: scale.id,
		name: scale.name,
		score: round( score, scale.scoring === 'mean' ? 2 : 0 ),
		min: scale.min,
		max: scale.max,
		position: round( ( ( score - scale.min ) / ( scale.max - scale.min ) ) * 100, 0 ),
		low: round( low, scale.scoring === 'mean' ? 2 : 0 ),
		high: round( high, scale.scoring === 'mean' ? 2 : 0 ),
		sem: round( sem, 2 ),
		band,
		higherIs: scale.higherIs,
		about: scale.about,
		verified: scale.verified,
		uncalibrated: Boolean( scale.uncalibrated ),
		mean: scale.scoring === 'mean',
	};
}

/** عدد الإجابات الحدسية الخاطئة (اختبار الانعكاس المعرفي). */
export function intuitiveCount( test: Test, answers: Record<string, Answer> ): number {
	return test.items.filter( ( i ) => {
		const a = answers[ i.id ];

		return a !== undefined && i.choices?.[ a.value ]?.intuitive;
	} ).length;
}
