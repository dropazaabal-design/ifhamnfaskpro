/** النسخ ورسالة التأكيد — مشتركة بين المشغّل وأزرار كود الخصم. */
export function toast( msg: string ) {
	const t = document.createElement( 'div' );
	t.className = 'toast';
	t.setAttribute( 'role', 'status' );
	t.textContent = msg;
	document.body.appendChild( t );
	window.setTimeout( () => t.remove(), 2200 );
}

export async function copy( text: string ) {
	try {
		await navigator.clipboard.writeText( text );
		toast( 'نُسخ' );
	} catch {
		window.prompt( 'انسخ:', text );
	}
}

// أزرار نسخ كود الخصم أينما كانت. مرّة واحدة وإن استُورد الملفّ مرّتين.
const w = window as unknown as { __baseeraCopy?: boolean };

if ( ! w.__baseeraCopy ) {
	w.__baseeraCopy = true;
	document.addEventListener( 'click', ( e ) => {
		const btn = ( e.target as HTMLElement ).closest<HTMLElement>( '[data-copy]' );

		if ( btn?.dataset.copy ) {
			copy( btn.dataset.copy );
		}
	} );
}
