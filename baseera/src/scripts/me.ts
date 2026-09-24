import { readProfile, clearProfile, reliableChange, type SavedResult } from '../lib/profile.ts';
import { num, countNoun, TIMES, formatDate } from '../lib/format.ts';

const box = document.querySelector<HTMLElement>( '[data-profile]' )!;
const esc = ( s: string ) => s.replace( /[&<>"']/g, ( c ) => ( { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' } )[ c ]! );
// النتائج القديمة بلا ‎mean‎: كانت كل مقاييس المتوسّط حينها حتى 5.
const f = ( n: number, s: { max: number; mean?: boolean } ) => num( ( s.mean ?? s.max <= 5 ) ? n.toFixed( 2 ) : String( Math.round( n ) ) );
const date = ( iso: string ) => formatDate( iso, 'short' );

function render() {
	const list = readProfile();

	if ( ! list.length ) {
		box.innerHTML = `<div class="card" style="text-align:center;padding:30px 18px"><p style="font-size:15px;font-weight:700">لا نتائج محفوظة بعد</p><p style="font-size:13.5px;color:var(--color-text-muted);margin-top:6px">بعد أيّ اختبار، اضغط «احفظ في ملفّي» لتجده هنا وتقارن به مرّتك القادمة.</p><a class="btn btn--primary" href="/" style="margin-top:14px">تصفّح الاختبارات</a></div>`;

		return;
	}

	const bySlug = new Map<string, SavedResult[]>();
	list.forEach( ( r ) => bySlug.set( r.slug, [ ...( bySlug.get( r.slug ) ?? [] ), r ] ) );

	box.innerHTML = [ ...bySlug.entries() ]
		.map( ( [ slug, runs ] ) => {
			const last = runs[ runs.length - 1 ];
			const prev = runs[ runs.length - 2 ];
			const scales = last.scales
				.map( ( s ) => {
					let change = '';

					if ( prev ) {
						const p = prev.scales.find( ( x ) => x.id === s.id );

						if ( p ) {
							const c = reliableChange( p.score, s.score, s.sem, s.higherIs );
							const verdict = c.rci === null ? 'غير قابل للحكم' : c.reliable ? ( c.direction === 'better' ? 'تغيّر موثوق نحو الأفضل' : c.direction === 'worse' ? 'تغيّر موثوق يستحقّ الانتباه' : 'تغيّر موثوق' ) : 'ضمن هامش الخطأ';
							change = `<span style="font-size:12px;color:var(--color-text-muted)"> · ${ c.delta >= 0 ? '+' : '−' }${ f( Math.abs( c.delta ), s ) } — ${ verdict }</span>`;
						}
					}

					return `<li style="display:flex;justify-content:space-between;gap:10px;font-size:14px;padding:6px 0;border-bottom:1px solid var(--color-border)"><span>${ esc( s.name ) }${ change }</span><b class="num">${ f( s.score, s ) } <span style="font-weight:500;color:var(--color-text-muted);font-size:12px">${ esc( s.band ) }</span></b></li>`;
				} )
				.join( '' );

			return `<section class="card" style="margin-bottom:12px"><div style="display:flex;justify-content:space-between;gap:10px;align-items:baseline"><a href="/tests/${ esc( slug ) }/" style="font-weight:800;font-size:15.5px;color:var(--color-primary)">${ esc( last.title ) }</a><span style="font-size:12px;color:var(--color-text-muted)">${ date( last.at ) } · ${ countNoun( runs.length, TIMES ) }</span></div>${ last.quality === 'caution' ? '<p style="font-size:12px;color:var(--tone-warn);margin-top:4px">⚠️ رصد ميزان الثقة إجابات متسرّعة في هذه المرّة.</p>' : '' }<ul style="list-style:none;margin-top:8px">${ scales }</ul></section>`;
		} )
		.join( '' );
}

document.querySelector( '[data-clear]' )?.addEventListener( 'click', () => {
	if ( window.confirm( 'تحذف كل النتائج المحفوظة على هذا الجهاز؟ لا يمكن التراجع.' ) ) {
		clearProfile();
		render();
	}
} );

document.querySelector( '[data-export]' )?.addEventListener( 'click', () => {
	const blob = new Blob( [ JSON.stringify( readProfile(), null, 2 ) ], { type: 'application/json' } );
	const a = document.createElement( 'a' );
	a.href = URL.createObjectURL( blob );
	a.download = 'baseera-profile.json';
	a.click();
	window.setTimeout( () => URL.revokeObjectURL( a.href ), 4000 );
} );

render();
