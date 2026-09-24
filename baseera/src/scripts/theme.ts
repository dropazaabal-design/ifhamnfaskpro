/** زرّ الوضع الليلي: يحفظ الاختيار ويحدّث لون شريط المتصفّح. */
document.querySelectorAll<HTMLButtonElement>( '[data-theme-toggle]' ).forEach( ( btn ) => {
	btn.addEventListener( 'click', () => {
		const root = document.documentElement;
		const next = root.getAttribute( 'data-theme' ) === 'dark' ? 'light' : 'dark';
		root.setAttribute( 'data-theme', next );

		try {
			localStorage.setItem( 'baseera-theme', next );
		} catch {
			// التصفّح الخاص: يعمل الزرّ ولا يُحفظ الاختيار.
		}
	} );
} );
