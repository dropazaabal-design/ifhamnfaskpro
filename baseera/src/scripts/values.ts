/**
 * أداة القيم: اختيار ثلاث قيم، ثم رسمها على دائرة شوارتز.
 *
 * لا درجات: الناتج خريطة. والتعارض يُذكر حين تقع القيم المختارة على
 * قطبين متقابلين في الدائرة، لأنه أهمّ ما في النظرية لقارئها.
 */
import type { Pole, ValueCard } from '../data/tools/core-values.ts';

const esc = ( s: string ) =>
	s.replace( /[&<>"']/g, ( c ) => ( { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' } )[ c ]! );

const PICK = 3;
const KEY = 'baseera-values';

function wheel( values: ValueCard[], chosen: string[] ): string {
	const cx = 140;
	const cy = 140;
	const r = 118;
	const n = values.length;
	let out = '';

	values.forEach( ( v, i ) => {
		// تبدأ الدائرة من الأعلى وتسير مع عقارب الساعة.
		const a0 = ( ( i / n ) * 2 - 0.5 ) * Math.PI;
		const a1 = ( ( ( i + 1 ) / n ) * 2 - 0.5 ) * Math.PI;
		const on = chosen.includes( v.id );
		const p = ( a: number, rr: number ) => `${ ( cx + rr * Math.cos( a ) ).toFixed( 1 ) } ${ ( cy + rr * Math.sin( a ) ).toFixed( 1 ) }`;
		out += `<path d="M${ cx } ${ cy } L${ p( a0, r ) } A${ r } ${ r } 0 0 1 ${ p( a1, r ) } Z" fill="${ on ? 'var(--color-primary)' : 'var(--color-surface-2)' }" fill-opacity="${ on ? 0.85 : 1 }" stroke="var(--color-surface)" stroke-width="2"/>`;
		const mid = ( a0 + a1 ) / 2;
		const [ tx, ty ] = p( mid, r * 0.68 ).split( ' ' );
		out += `<text x="${ tx }" y="${ ty }" text-anchor="middle" dominant-baseline="middle"${ on ? ' style="fill:var(--color-primary-contrast)"' : '' }>${ esc( v.icon ) }</text>`;
	} );

	return `<svg class="wheel" viewBox="0 0 280 280" role="img" aria-label="دائرة القيم العشر، والمختارة منها ملوّنة">${ out }<circle cx="${ cx }" cy="${ cy }" r="30" fill="var(--color-surface)"/></svg>`;
}

function boot( root: HTMLElement ) {
	const { values, poles } = JSON.parse( root.querySelector( '[data-values-data]' )!.textContent! ) as {
		values: ValueCard[];
		poles: Record<Pole, { name: string; opposite: Pole; text: string }>;
	};
	const cards = [ ...root.querySelectorAll<HTMLButtonElement>( '[data-value]' ) ];
	const count = root.querySelector<HTMLElement>( '[data-count]' )!;
	const showBtn = root.querySelector<HTMLButtonElement>( '[data-show]' )!;
	const result = root.querySelector<HTMLElement>( '[data-result]' )!;
	let chosen: string[] = [];

	const sync = () => {
		cards.forEach( ( c ) => {
			const on = chosen.includes( c.dataset.value! );
			c.setAttribute( 'aria-pressed', String( on ) );
			c.disabled = ! on && chosen.length >= PICK;
		} );
		count.textContent = `${ chosen.length } / ${ PICK }`;
		showBtn.disabled = chosen.length !== PICK;
	};

	cards.forEach( ( c ) =>
		c.addEventListener( 'click', () => {
			const id = c.dataset.value!;
			chosen = chosen.includes( id ) ? chosen.filter( ( x ) => x !== id ) : [ ...chosen, id ].slice( 0, PICK );
			sync();
		} )
	);

	const render = () => {
		const picked = chosen.map( ( id ) => values.find( ( v ) => v.id === id )! );
		const hit = new Set( picked.flatMap( ( v ) => v.poles ) );
		const tensions = ( [ 'open', 'enhance' ] as Pole[] )
			.filter( ( p ) => hit.has( p ) && hit.has( poles[ p ].opposite ) )
			.map( ( p ) => {
				const a = picked.filter( ( v ) => v.poles.includes( p ) ).map( ( v ) => v.name ).join( ' و' );
				const b = picked.filter( ( v ) => v.poles.includes( poles[ p ].opposite ) ).map( ( v ) => v.name ).join( ' و' );

				return `<p class="tension">⚖️ <b>${ esc( a ) }</b> و<b>${ esc( b ) }</b> على طرفين متقابلين في الدائرة (${ esc( poles[ p ].name ) } مقابل ${ esc( poles[ poles[ p ].opposite ].name ) }). هذا ليس تناقضاً فيك: كثيرون يقدّرون الاثنين. لكنه يفسّر لماذا تبدو بعض القرارات صعبة — حين يطلب منك موقف أن تقدّم إحداهما على الأخرى.</p>`;
			} );

		result.innerHTML = `
			<div class="vres">
				${ wheel( values, chosen ) }
				<h3>قيمك الثلاث</h3>
				<ol>${ picked.map( ( v ) => `<li><b>${ esc( v.icon ) } ${ esc( v.name ) }</b> — ${ esc( v.text ) }</li>` ).join( '' ) }</ol>
				<h3>اتّجاهك</h3>
				<div class="poles">${ [ ...hit ].map( ( p ) => `<span class="tchip">${ esc( poles[ p ].name ) }</span>` ).join( '' ) }</div>
				<p style="font-size:13.5px;line-height:1.85;margin-top:8px">${ [ ...hit ].map( ( p ) => esc( poles[ p ].text ) ).join( ' ' ) }</p>
				${ tensions.join( '' ) || '<p style="font-size:13.5px;line-height:1.85;margin-top:10px">قيمك الثلاث متجاورة في الدائرة: يسهل السعي إليها معاً، وقراراتك في الغالب تسير في اتّجاه واحد.</p>' }
				<button type="button" class="btn btn--ghost btn--block redo" data-redo>اختر من جديد</button>
			</div>`;
		result.hidden = false;
		result.focus( { preventScroll: true } );
		result.scrollIntoView( { behavior: 'smooth', block: 'start' } );
		result.querySelector( '[data-redo]' )!.addEventListener( 'click', () => {
			chosen = [];
			result.hidden = true;
			sync();
			root.scrollIntoView( { behavior: 'smooth', block: 'start' } );
		} );

		try {
			localStorage.setItem( KEY, JSON.stringify( chosen ) );
		} catch {
			// بلا تخزين: الأداة تعمل ولا تتذكّر.
		}
	};

	showBtn.addEventListener( 'click', render );

	try {
		const saved = JSON.parse( localStorage.getItem( KEY ) ?? '[]' );

		if ( Array.isArray( saved ) && saved.length === PICK && saved.every( ( id ) => values.some( ( v ) => v.id === id ) ) ) {
			chosen = saved;
		}
	} catch {
		// لا شيء محفوظ.
	}

	sync();
}

document.querySelectorAll<HTMLElement>( '[data-values]' ).forEach( boot );
