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
import { countNoun, TESTS } from '../src/lib/format.ts';
import { ROTATIONS, NETS, normalize, mirror, turns } from '../src/data/tests/spatial-reasoning.ts';

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

	// المهامّ التفاعلية بلا بنود: درجتها من الأداء، وتصحيحها في src/scripts/tasks.ts.
	if ( t.kind === 'task' ) {
		if ( ! t.task ) fail( t.slug, 'مهمّة تفاعلية بلا نوع (task)' );
		if ( t.items.length ) fail( t.slug, 'المهمّة التفاعلية لا تحمل بنوداً' );
		if ( t.subscales.some( ( s ) => ! s.uncalibrated ) ) fail( t.slug, 'نسخ المهامّ هنا غير مقنّنة: يجب أن تكون uncalibrated' );
		continue;
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

// 6 — الأشكال المكانية: تُتحقَّق هندسياً لا بالعين.
const key = ( c ) => JSON.stringify( normalize( c ) );
ROTATIONS.forEach( ( r, i ) => {
	const rotations = [ 0, 1, 2, 3 ].map( ( k ) => key( turns( r.shape, k ) ) );
	if ( rotations.includes( key( mirror( r.shape ) ) ) ) fail( 'spatial-reasoning', `شكل التدوير ${ i + 1 } متناظر: صورة المرآة تدوير له` );
	const mirrors = r.mirrors.map( ( k ) => key( turns( mirror( r.shape ), k ) ) );
	if ( new Set( mirrors ).size !== 3 ) fail( 'spatial-reasoning', `خيارات المرآة في السؤال ${ i + 1 } مكرّرة` );
	if ( mirrors.some( ( m ) => rotations.includes( m ) ) ) fail( 'spatial-reasoning', `خيار مرآة في السؤال ${ i + 1 } هو تدوير للأصل` );
} );

// شبكة المكعّب صالحة إن غطّت دحرجة مكعّب عليها ستّة أوجه مختلفة.
function foldsToCube( cells ) {
	const set = new Set( cells.map( ( [ x, y ] ) => `${ x },${ y }` ) );
	const roll = {
		e: ( o ) => ( { ...o, bottom: o.e, e: o.top, top: o.w, w: o.bottom } ),
		w: ( o ) => ( { ...o, bottom: o.w, w: o.top, top: o.e, e: o.bottom } ),
		s: ( o ) => ( { ...o, bottom: o.s, s: o.top, top: o.n, n: o.bottom } ),
		n: ( o ) => ( { ...o, bottom: o.n, n: o.top, top: o.s, s: o.bottom } ),
	};
	const step = { e: [ 1, 0 ], w: [ -1, 0 ], s: [ 0, 1 ], n: [ 0, -1 ] };
	const start = cells[ 0 ];
	const seen = new Map( [ [ `${ start[ 0 ] },${ start[ 1 ] }`, { top: 'T', bottom: 'B', n: 'N', s: 'S', e: 'E', w: 'W' } ] ] );
	const queue = [ start ];
	while ( queue.length ) {
		const [ x, y ] = queue.shift();
		const o = seen.get( `${ x },${ y }` );
		for ( const d of Object.keys( step ) ) {
			const nx = x + step[ d ][ 0 ];
			const ny = y + step[ d ][ 1 ];
			const k = `${ nx },${ ny }`;
			if ( set.has( k ) && ! seen.has( k ) ) {
				seen.set( k, roll[ d ]( o ) );
				queue.push( [ nx, ny ] );
			}
		}
	}
	return seen.size === 6 && new Set( [ ...seen.values() ].map( ( o ) => o.bottom ) ).size === 6;
}
NETS.forEach( ( n, i ) => {
	const ok = n.options.map( foldsToCube );
	if ( ok.filter( Boolean ).length !== 1 || ! ok[ n.valid ] ) fail( 'spatial-reasoning', `سؤال الشبكة ${ i + 1 }: الصالحة ${ ok.map( ( v, j ) => ( v ? j + 1 : '' ) ).filter( Boolean ).join( '، ' ) || 'لا شيء' }، لا ${ n.valid + 1 } وحدها` );
} );
// ضابط للمحاكي نفسه: الشبكات الإحدى عشرة المعروفة كلّها صالحة.
const ELEVEN = [
	'0,1 1,1 2,1 3,1 0,0 0,2', '0,1 1,1 2,1 3,1 0,0 1,2', '0,1 1,1 2,1 3,1 0,0 2,2', '0,1 1,1 2,1 3,1 0,0 3,2',
	'0,1 1,1 2,1 3,1 1,0 1,2', '0,1 1,1 2,1 3,1 1,0 2,2', '0,0 1,0 1,1 2,1 3,1 3,2', '0,0 1,0 1,1 2,1 2,2 3,2',
	'0,0 1,0 2,0 2,1 3,1 4,1', '0,0 1,0 1,1 2,1 3,1 2,2', '0,0 1,0 1,1 2,1 3,1 1,2',
];
ELEVEN.forEach( ( s, i ) => {
	if ( ! foldsToCube( s.split( ' ' ).map( ( p ) => p.split( ',' ).map( Number ) ) ) ) fail( 'check-science', `محاكي المكعّب يرفض الشبكة المعروفة ${ i + 1 }` );
} );

console.log( `فُحص ${ countNoun( tests.length, TESTS ) } · ${ tests.reduce( ( n, t ) => n + t.items.length, 0 ) } بنداً · ${ tests.reduce( ( n, t ) => n + t.subscales.length, 0 ) } مقياساً فرعياً · والأشكال المكانية متحقَّق منها هندسياً` );

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
