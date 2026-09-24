/** حاسبة تقسيم الراتب — تقبل الأرقام العربية والإنجليزية معاً. */
import { num } from '../lib/format.ts';

const root = document.querySelector<HTMLElement>( '[data-salary]' );

if ( root ) {
	const input = root.querySelector<HTMLInputElement>( '#salary' )!;
	const cur = root.querySelector<HTMLInputElement>( '#currency' )!;
	const out = root.querySelector<HTMLElement>( '[data-out]' )!;
	const chips = Array.from( root.querySelectorAll<HTMLButtonElement>( '[data-split]' ) );
	let split = [ 50, 30, 20 ];

	const parse = ( s: string ) =>
		// لوحة المفاتيح العربية تكتب أرقاماً مشرقية: نقبلها ونحوّلها.
		Number( s.replace( /[٠-٩]/g, ( d ) => String( '٠١٢٣٤٥٦٧٨٩'.indexOf( d ) ) ).replace( /[٬,\s]/g, '' ).replace( '٫', '.' ) );

	const money = ( n: number ) => Math.round( n ).toLocaleString( 'en-US' );

	const labels = [
		{ name: 'الضروريات', note: 'سكن، طعام، مواصلات، فواتير، الحدّ الأدنى من الأقساط', color: 'var(--cat-cognitive)' },
		{ name: 'الرغبات', note: 'مطاعم، ترفيه، تسوّق، اشتراكات', color: 'var(--cat-intelligence)' },
		{ name: 'الادّخار والديون', note: 'اقتطعه أوّل الشهر لا من المتبقّي آخره', color: 'var(--cat-financial)' },
	];

	const render = () => {
		const total = parse( input.value );

		if ( ! Number.isFinite( total ) || total <= 0 ) {
			out.innerHTML = '';

			return;
		}

		out.innerHTML = labels
			.map( ( l, i ) => `
				<div style="border:1px solid var(--color-border);border-radius:14px;padding:13px 15px;background:var(--color-surface)">
					<div style="display:flex;justify-content:space-between;align-items:baseline;gap:10px">
						<b style="font-size:15px">${ l.name } <span style="font-size:12px;color:var(--color-text-muted)">${ num( split[ i ] ) }%</span></b>
						<b style="font-size:20px;color:${ l.color }">${ money( ( total * split[ i ] ) / 100 ) } <span style="font-size:13px;color:var(--color-text-muted)">${ cur.value.replace( /[<>&"]/g, '' ) }</span></b>
					</div>
					<div style="height:8px;border-radius:100px;background:var(--color-surface-2);margin-top:8px;overflow:hidden"><span style="display:block;height:100%;width:${ split[ i ] }%;background:${ l.color };border-radius:100px"></span></div>
					<p style="font-size:12.5px;color:var(--color-text-muted);margin-top:6px">${ l.note }</p>
				</div>` )
			.join( '' );
	};

	chips.forEach( ( c ) =>
		c.addEventListener( 'click', () => {
			split = c.dataset.split!.split( ',' ).map( Number );
			chips.forEach( ( x ) => x.setAttribute( 'aria-pressed', String( x === c ) ) );
			render();
		} )
	);

	input.addEventListener( 'input', render );
	cur.addEventListener( 'input', render );
}
