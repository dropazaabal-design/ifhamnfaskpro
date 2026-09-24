/**
 * فحص علمي لكل اختبار قبل البناء.
 *
 * المنصّة تعد زوّارها بالصدق، وهذا الملفّ يحوّل الوعد إلى شرط بناء: أي
 * اختبار ينقصه مصدر أو ثبات أو «ما لا يستطيع قوله» يُسقط البناء.
 *
 * ويفحص سلامة التصحيح نفسه: مفاتيح العكس، وتغطية الفئات لمدى المقياس
 * بلا ثغرة، وأن لكل سؤال اختيار جواباً صحيحاً واحداً بالضبط.
 *
 * وفي النهاية يطبع ما لم يُتحقَّق منه من المصدر الأصلي، كي يعرف صاحب
 * المنصّة بالضبط ما يحتاج مراجعة قبل الإطلاق.
 */
import { tests } from '../src/data/tests/index.ts';
import { scoreTest } from '../src/lib/scoring.ts';
import { assessQuality } from '../src/lib/quality.ts';

const errors = [];
const pending = [];
const fail = ( slug, msg ) => errors.push( `${ slug }: ${ msg }` );

for ( const t of tests ) {
	// 1 — حقول الصدق الإلزامية.
	for ( const f of [ 'name', 'authors', 'citation', 'licenseNote', 'translationNote' ] ) {
		if ( ! t.instrument?.[ f ] ) fail( t.slug, `المصدر ينقصه ${ f }` );
	}
	if ( ! t.cannot || t.cannot.length < 2 ) fail( t.slug, 'يحتاج بندين على الأقل في «ما لا يستطيع قوله»' );
	if ( ! t.article || t.article.length < 2 ) fail( t.slug, 'يحتاج قسمين على الأقل من المحتوى (AdSense يرفض الصفحات الرقيقة)' );
	if ( ! t.faq || t.faq.length < 3 ) fail( t.slug, 'يحتاج ثلاثة أسئلة شائعة على الأقل' );
	if ( t.description.length < 70 || t.description.length > 170 ) fail( t.slug, `وصف البحث ${ t.description.length } حرفاً (المدى المفيد 70–170)` );

	// ما تطبعه الصفحة فعلاً: المقدّمة، والمقال، والأسئلة، والحدود، ودليل
	// قراءة النتائج بكل مستوياته (يُعرض قبل الاختبار لا بعده فقط).
	const words = [
		t.intro,
		...t.article.map( ( a ) => a.body ),
		...t.faq.map( ( f ) => `${ f.q } ${ f.a }` ),
		...t.cannot,
		...t.subscales.flatMap( ( s ) => [ s.about ?? '', ...s.bands.map( ( b ) => b.text ) ] ),
	]
		.join( ' ' )
		.split( /\s+/ ).length;
	if ( words < 350 ) fail( t.slug, `المحتوى المكتوب ${ words } كلمة فقط (الحدّ الأدنى 350)` );

	// 2 — سلامة البنود.
	const ids = new Set();
	for ( const item of t.items ) {
		if ( ids.has( item.id ) ) fail( t.slug, `معرّف مكرّر ${ item.id }` );
		ids.add( item.id );
		if ( ! t.subscales.some( ( s ) => s.id === item.scale ) ) fail( t.slug, `البند ${ item.id } يشير إلى مقياس غير موجود` );
		if ( t.kind === 'choice' ) {
			const right = ( item.choices ?? [] ).filter( ( c ) => c.correct ).length;
			if ( right !== 1 ) fail( t.slug, `البند ${ item.id } له ${ right } جواب صحيح (يجب واحد)` );
			if ( ! item.solution ) fail( t.slug, `البند ${ item.id } بلا شرح حلّ` );
		} else if ( ! ( item.options ?? t.options )?.length ) {
			fail( t.slug, `البند ${ item.id } بلا بدائل` );
		}
	}

	// 3 — المقاييس: فئات تغطّي المدى، وثبات منطقي.
	for ( const s of t.subscales ) {
		const top = s.bands[ s.bands.length - 1 ]?.upTo;
		if ( top !== s.max ) fail( t.slug, `فئات ${ s.id } تنتهي عند ${ top } لا عند ${ s.max }` );
		for ( let k = 1; k < s.bands.length; k++ ) {
			if ( s.bands[ k ].upTo <= s.bands[ k - 1 ].upTo ) fail( t.slug, `فئات ${ s.id } غير متصاعدة` );
		}
		if ( ! s.uncalibrated && ( s.alpha <= 0 || s.alpha >= 1 ) ) fail( t.slug, `ثبات ${ s.id } خارج (0، 1)` );
		if ( ! s.uncalibrated && s.sd <= 0 ) fail( t.slug, `انحراف ${ s.id } غير موجب` );
		if ( ! s.paramsSource ) fail( t.slug, `${ s.id } بلا مصدر للثبات` );
		if ( ! s.verified ) pending.push( `${ t.slug } · ${ s.name }: ${ s.paramsSource }` );
	}

	// 4 — التصحيح نفسه: أدنى الإجابات وأعلاها يجب أن يعطيا حدود المقياس.
	const answerAll = ( pick ) => {
		const a = {};
		for ( const item of t.items ) {
			if ( t.kind === 'choice' ) {
				const idx = item.choices.findIndex( ( c ) => ( pick === 'best' ? c.correct : ! c.correct ) );
				a[ item.id ] = { value: idx, ms: 5000 };
			} else {
				const vals = ( item.options ?? t.options ).map( ( o ) => o.value );
				const hi = Math.max( ...vals );
				const lo = Math.min( ...vals );
				const wantHigh = pick === 'best';
				const raw = ( item.key === -1 ) === wantHigh ? lo : hi;
				a[ item.id ] = { value: raw, ms: 5000 };
			}
		}
		return a;
	};

	const best = scoreTest( t, answerAll( 'best' ) );
	const worst = scoreTest( t, answerAll( 'worst' ) );
	for ( const s of t.subscales ) {
		const b = best.find( ( r ) => r.id === s.id );
		const w = worst.find( ( r ) => r.id === s.id );
		if ( b.score !== s.max ) fail( t.slug, `أعلى إجابات ${ s.id } تعطي ${ b.score } لا ${ s.max } — مفاتيح العكس خاطئة؟` );
		if ( w.score !== s.min ) fail( t.slug, `أدنى إجابات ${ s.id } تعطي ${ w.score } لا ${ s.min }` );
	}

	// 5 — ميزان الثقة يلتقط «الموافقة على كل شيء» حيث توجد بنود معكوسة.
	if ( t.kind === 'likert' && t.items.some( ( i ) => i.key === -1 ) && t.options ) {
		const top = Math.max( ...t.options.map( ( o ) => o.value ) );
		const all = Object.fromEntries( t.items.map( ( i ) => [ i.id, { value: top, ms: 400 } ] ) );
		const q = assessQuality( t, all );
		for ( const id of [ 'speed', 'longstring', 'acquiescence' ] ) {
			if ( ! q.flags.some( ( f ) => f.id === id ) ) fail( t.slug, `ميزان الثقة لم يلتقط «${ id }» في إجابة موافِقة على كل شيء بسرعة` );
		}
	}
}

console.log( `فُحص ${ tests.length } اختبارات · ${ tests.reduce( ( n, t ) => n + t.items.length, 0 ) } بنداً · ${ tests.reduce( ( n, t ) => n + t.subscales.length, 0 ) } مقياساً فرعياً` );

if ( pending.length ) {
	console.log( `\nقيم تحتاج مراجعة من المصدر الأصلي قبل الإطلاق (${ pending.length }):` );
	for ( const p of pending ) console.log( `  · ${ p }` );
}

if ( errors.length ) {
	console.error( `\n${ errors.length } خطأً علمياً يمنع البناء:` );
	for ( const e of errors ) console.error( `  ✗ ${ e }` );
	process.exit( 1 );
}

console.log( '\nكل اختبار له مصدر، وثبات منشور، وحدود مكتوبة، وتصحيح يبلغ طرفَي مقياسه.' );
