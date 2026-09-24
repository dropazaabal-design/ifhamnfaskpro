import { qs, store } from './util.js';

const KEY = 'mp:announcement-dismissed';

/**
 * شريط الإعلان: يُخفى بنقرة ويبقى مخفياً على الجهاز نفسه.
 */
export default function announcement() {
	const bar = qs( '[data-mp-announcement]' );

	if ( ! bar ) {
		return;
	}

	const signature = bar.textContent.trim().slice( 0, 120 );

	// إعلان جديد يظهر مرة أخرى حتى لو أغلق الزائر الإعلان السابق.
	if ( store.get( KEY ) === signature ) {
		bar.hidden = true;
		return;
	}

	const button = qs( '[data-mp-dismiss]', bar );

	if ( ! button ) {
		return;
	}

	button.addEventListener( 'click', () => {
		bar.hidden = true;
		store.set( KEY, signature );
	} );
}
